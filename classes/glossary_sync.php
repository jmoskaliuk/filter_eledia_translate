<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace filter_translations;

use context;

/**
 * Builds and synchronises local glossary entries with DeepL v3 glossaries.
 *
 * @package filter_translations
 */
class glossary_sync {
    /** Local sync succeeded. */
    public const STATUS_SYNCED = 10;

    /** Local content changed or was never synced. */
    public const STATUS_PENDING = 20;

    /** Last sync failed. */
    public const STATUS_ERROR = 30;

    /**
     * Return all sync groups with approved glossary entries.
     *
     * @return \stdClass[]
     */
    public static function groups(): array {
        global $DB;

        $sql = "SELECT MIN(g.id) AS id,
                       g.courseid,
                       g.sourcelanguage,
                       g.targetlanguage,
                       COUNT(1) AS entrycount
                  FROM {filter_translations_glossary} g
                 WHERE g.status = :status
              GROUP BY g.courseid, g.sourcelanguage, g.targetlanguage
              ORDER BY g.courseid ASC, g.sourcelanguage ASC, g.targetlanguage ASC";
        $records = $DB->get_records_sql($sql, ['status' => glossary_entry::STATUS_APPROVED]);
        $groups = [];

        foreach ($records as $record) {
            $group = (object)[
                'scope' => empty($record->courseid) ? 'global' : 'course',
                'courseid' => empty($record->courseid) ? null : (int)$record->courseid,
                'sourcelanguage' => $record->sourcelanguage,
                'targetlanguage' => $record->targetlanguage,
                'entrycount' => (int)$record->entrycount,
            ];
            $state = self::get_state($group);
            $group->deeplglossaryid = $state->deeplglossaryid ?? '';
            $group->syncstatus = $state->status ?? self::STATUS_PENDING;
            $group->lastsyncerror = $state->lastsyncerror ?? '';
            $group->timemodified = $state->timemodified ?? 0;
            $group->contenthash = self::content_hash($group);
            $group->pending = empty($state->contenthash) || $state->contenthash !== $group->contenthash ||
                (int)$group->syncstatus !== self::STATUS_SYNCED;
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Synchronise one group.
     *
     * @param string $scope
     * @param int|null $courseid
     * @param string $sourcelanguage
     * @param string $targetlanguage
     * @return \stdClass
     */
    public static function sync_group(string $scope, ?int $courseid, string $sourcelanguage, string $targetlanguage): \stdClass {
        $group = (object)[
            'scope' => $scope,
            'courseid' => $courseid,
            'sourcelanguage' => $sourcelanguage,
            'targetlanguage' => $targetlanguage,
        ];
        $entries = self::entries_for_group($group);
        $group->entrycount = count($entries);
        $state = self::get_state($group);

        if (empty($entries)) {
            return self::save_state($group, $state, [
                'entrycount' => 0,
                'contenthash' => '',
                'status' => self::STATUS_PENDING,
                'lastsyncerror' => get_string('deeplglossarynoentries', 'filter_translations'),
            ]);
        }

        $invalid = self::invalid_entries($entries);
        if (!empty($invalid)) {
            return self::save_state($group, $state, [
                'entrycount' => count($entries),
                'contenthash' => self::content_hash($group, $entries),
                'status' => self::STATUS_ERROR,
                'lastsyncerror' => get_string('deeplglossaryinvalidentries', 'filter_translations', count($invalid)),
            ]);
        }

        $client = new deepl_glossary_client();
        $entriesbody = self::entries_as_tsv($entries);
        $source = self::map_language_to_deepl($sourcelanguage);
        $target = self::map_language_to_deepl($targetlanguage);

        try {
            if (!empty($state->deeplglossaryid)) {
                $client->replace_dictionary($state->deeplglossaryid, $source, $target, $entriesbody);
                $deeplid = $state->deeplglossaryid;
            } else if ($deeplid = self::find_scope_glossary_id($group)) {
                $client->replace_dictionary($deeplid, $source, $target, $entriesbody);
            } else {
                $response = $client->create_glossary(self::glossary_name($group), $source, $target, $entriesbody);
                $deeplid = $response['glossary_id'] ?? '';
            }

            if ($deeplid === '') {
                throw new \moodle_exception('deeplglossarymissingid', 'filter_translations');
            }

            return self::save_state($group, $state, [
                'deeplglossaryid' => $deeplid,
                'entrycount' => count($entries),
                'contenthash' => md5($entriesbody),
                'status' => self::STATUS_SYNCED,
                'lastsyncerror' => '',
            ]);
        } catch (\Throwable $exception) {
            return self::save_state($group, $state, [
                'entrycount' => count($entries),
                'contenthash' => md5($entriesbody),
                'status' => self::STATUS_ERROR,
                'lastsyncerror' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Resolve the synced DeepL glossary id for a translation context.
     *
     * @param context|null $context
     * @param string $sourcelanguage
     * @param string $targetlanguage
     * @return string|null
     */
    public static function resolve_deepl_glossary_id(?context $context, string $sourcelanguage, string $targetlanguage): ?string {
        global $DB;

        $coursecontext = $context ? $context->get_course_context(false) : null;
        if (!empty($coursecontext) && $coursecontext->instanceid != SITEID) {
            $record = $DB->get_record('filter_translations_glossync', [
                'scope' => 'course',
                'courseid' => $coursecontext->instanceid,
                'sourcelanguage' => $sourcelanguage,
                'targetlanguage' => $targetlanguage,
                'status' => self::STATUS_SYNCED,
            ], '*', IGNORE_MISSING);
            if (!empty($record->deeplglossaryid)) {
                return $record->deeplglossaryid;
            }
        }

        $select = "scope = :scope AND courseid IS NULL AND sourcelanguage = :source
                   AND targetlanguage = :target AND status = :status";
        $record = $DB->get_record_select('filter_translations_glossync', $select, [
            'scope' => 'global',
            'source' => $sourcelanguage,
            'target' => $targetlanguage,
            'status' => self::STATUS_SYNCED,
        ], '*', IGNORE_MISSING);

        return empty($record->deeplglossaryid) ? null : $record->deeplglossaryid;
    }

    /**
     * Status options.
     *
     * @return string[]
     */
    public static function status_options(): array {
        return [
            self::STATUS_SYNCED => get_string('deeplglossarystatus_synced', 'filter_translations'),
            self::STATUS_PENDING => get_string('deeplglossarystatus_pending', 'filter_translations'),
            self::STATUS_ERROR => get_string('deeplglossarystatus_error', 'filter_translations'),
        ];
    }

    /**
     * Get sync state.
     *
     * @param \stdClass $group
     * @return \stdClass|null
     */
    private static function get_state(\stdClass $group): ?\stdClass {
        global $DB;

        if (empty($group->courseid)) {
            $select = "scope = :scope AND courseid IS NULL AND sourcelanguage = :source AND targetlanguage = :target";
            return $DB->get_record_select('filter_translations_glossync', $select, [
                'scope' => 'global',
                'source' => $group->sourcelanguage,
                'target' => $group->targetlanguage,
            ], '*', IGNORE_MISSING) ?: null;
        }

        return $DB->get_record('filter_translations_glossync', [
            'scope' => 'course',
            'courseid' => $group->courseid,
            'sourcelanguage' => $group->sourcelanguage,
            'targetlanguage' => $group->targetlanguage,
        ], '*', IGNORE_MISSING) ?: null;
    }

    /**
     * Find an existing DeepL glossary id for the same scope.
     *
     * @param \stdClass $group
     * @return string|null
     */
    private static function find_scope_glossary_id(\stdClass $group): ?string {
        global $DB;

        if (empty($group->courseid)) {
            $select = "scope = :scope AND courseid IS NULL AND deeplglossaryid IS NOT NULL AND deeplglossaryid <> ''";
            $record = $DB->get_record_select('filter_translations_glossync', $select, ['scope' => 'global'],
                'id, deeplglossaryid', IGNORE_MULTIPLE);
        } else {
            $record = $DB->get_record_select('filter_translations_glossync',
                "scope = :scope AND courseid = :courseid AND deeplglossaryid IS NOT NULL AND deeplglossaryid <> ''",
                ['scope' => 'course', 'courseid' => $group->courseid], 'id, deeplglossaryid', IGNORE_MULTIPLE);
        }

        return empty($record->deeplglossaryid) ? null : $record->deeplglossaryid;
    }

    /**
     * Save sync state.
     *
     * @param \stdClass $group
     * @param \stdClass|null $state
     * @param array $changes
     * @return \stdClass
     */
    private static function save_state(\stdClass $group, ?\stdClass $state, array $changes): \stdClass {
        global $DB, $USER;

        $now = time();
        $record = $state ?: (object)[
            'scope' => empty($group->courseid) ? 'global' : 'course',
            'courseid' => empty($group->courseid) ? null : $group->courseid,
            'sourcelanguage' => $group->sourcelanguage,
            'targetlanguage' => $group->targetlanguage,
            'timecreated' => $now,
        ];

        foreach ($changes as $key => $value) {
            $record->{$key} = $value;
        }
        $record->usermodified = $USER->id;
        $record->timemodified = $now;

        if (empty($record->id)) {
            $record->id = $DB->insert_record('filter_translations_glossync', $record);
        } else {
            $DB->update_record('filter_translations_glossync', $record);
        }

        return $record;
    }

    /**
     * Load entries for a group.
     *
     * @param \stdClass $group
     * @return \stdClass[]
     */
    private static function entries_for_group(\stdClass $group): array {
        global $DB;

        $params = [
            'status' => glossary_entry::STATUS_APPROVED,
            'source' => $group->sourcelanguage,
            'target' => $group->targetlanguage,
        ];
        if (empty($group->courseid)) {
            $select = "status = :status AND sourcelanguage = :source AND targetlanguage = :target AND courseid IS NULL";
        } else {
            $select = "status = :status AND sourcelanguage = :source AND targetlanguage = :target AND courseid = :courseid";
            $params['courseid'] = $group->courseid;
        }

        return $DB->get_records_select('filter_translations_glossary', $select, $params,
            'priority ASC, sourcephrase ASC');
    }

    /**
     * Build a hash for a group.
     *
     * @param \stdClass $group
     * @param \stdClass[]|null $entries
     * @return string
     */
    private static function content_hash(\stdClass $group, ?array $entries = null): string {
        $entries = $entries ?? self::entries_for_group($group);
        return md5(self::entries_as_tsv($entries));
    }

    /**
     * Convert entries to TSV.
     *
     * @param \stdClass[] $entries
     * @return string
     */
    private static function entries_as_tsv(array $entries): string {
        $lines = [];
        foreach ($entries as $entry) {
            $lines[] = trim($entry->sourcephrase) . "\t" . trim($entry->targetphrase);
        }
        return implode("\n", $lines);
    }

    /**
     * Return entries that cannot be represented as DeepL TSV glossary entries.
     *
     * @param \stdClass[] $entries
     * @return \stdClass[]
     */
    private static function invalid_entries(array $entries): array {
        return array_filter($entries, function($entry): bool {
            return preg_match('/[\t\r\n]/', $entry->sourcephrase . $entry->targetphrase) === 1;
        });
    }

    /**
     * Name a DeepL glossary for a sync group.
     *
     * @param \stdClass $group
     * @return string
     */
    private static function glossary_name(\stdClass $group): string {
        if (empty($group->courseid)) {
            return 'eLeDia Translation Glossary - global';
        }

        return 'eLeDia Translation Glossary - course ' . $group->courseid;
    }

    /**
     * Map Moodle language codes to DeepL language codes.
     *
     * @param string $moodlecode
     * @return string
     */
    public static function map_language_to_deepl(string $moodlecode): string {
        static $map = [
            'en_us' => 'EN-US',
            'en_uk' => 'EN-GB',
            'en_gb' => 'EN-GB',
            'pt_br' => 'PT-BR',
            'pt_pt' => 'PT-PT',
            'zh_cn' => 'ZH-HANS',
            'zh_hans' => 'ZH-HANS',
            'zh_tw' => 'ZH-HANT',
            'zh_hant' => 'ZH-HANT',
        ];

        $moodlecode = strtolower(str_replace('-', '_', $moodlecode));
        if (isset($map[$moodlecode])) {
            return $map[$moodlecode];
        }

        if (strpos($moodlecode, '_') !== false) {
            return strtoupper((string)preg_replace('/_.*/', '', $moodlecode));
        }

        return strtoupper($moodlecode);
    }
}
