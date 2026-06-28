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

use context_course;

/**
 * Validates and applies glossary CSV rows. Extracted from glossaryimport.php so the
 * row-level logic (validation and duplicate-safe upsert) can be unit tested.
 *
 * @package filter_translations
 */
class glossary_importer {
    /** The CSV columns expected, in order. */
    public const REQUIRED_FIELDS = [
        'sourcephrase',
        'targetphrase',
        'sourcelanguage',
        'targetlanguage',
        'courseid',
        'status',
        'priority',
        'casesensitive',
        'wholeword',
        'notes',
        'deeplglossaryid',
    ];

    /** A new entry was created. */
    public const ACTION_CREATED = 'created';

    /** One or more existing entries were updated. */
    public const ACTION_UPDATED = 'updated';

    /** The row was skipped. */
    public const ACTION_SKIPPED = 'skipped';

    /** @var string Skip reason: required value missing. */
    public const REASON_MISSING_DATA = 'missingdata';

    /** @var string Skip reason: source or target language not installed. */
    public const REASON_INVALID_LANGUAGE = 'invalidlanguage';

    /** @var string Skip reason: course id is not a positive integer or does not exist. */
    public const REASON_INVALID_COURSE = 'invalidcourse';

    /** @var string Skip reason: status value not recognised. */
    public const REASON_INVALID_STATUS = 'invalidstatus';

    /** @var string Skip reason: priority is not a non-negative integer. */
    public const REASON_INVALID_PRIORITY = 'invalidpriority';

    /**
     * Map textual or numeric status values from the CSV to the persistent status constants.
     *
     * @return array<string,int>
     */
    public static function status_map(): array {
        return [
            '10' => glossary_entry::STATUS_DRAFT,
            '20' => glossary_entry::STATUS_REVIEWED,
            '30' => glossary_entry::STATUS_APPROVED,
            '40' => glossary_entry::STATUS_ARCHIVED,
            'draft' => glossary_entry::STATUS_DRAFT,
            'reviewed' => glossary_entry::STATUS_REVIEWED,
            'approved' => glossary_entry::STATUS_APPROVED,
            'archived' => glossary_entry::STATUS_ARCHIVED,
        ];
    }

    /**
     * Validate a single CSV row and create or update the matching glossary entries.
     *
     * Existing entries sharing the natural key (sourcephrase + sourcelanguage + targetlanguage + courseid)
     * are all updated, so historical duplicates do not trigger a "found more than one record" notice.
     *
     * @param array $line Numeric-indexed CSV row matching REQUIRED_FIELDS order.
     * @param array $validlanguages Installed language codes keyed by code (as from get_list_of_translations()).
     * @param int $systemcontextid Context id to use for global (course-less) entries.
     * @return \stdClass Result with ->action, and for skips ->reason, for updates ->updated (count).
     */
    public static function import_row(array $line, array $validlanguages, int $systemcontextid): \stdClass {
        global $DB;

        $sourcephrase = trim($line[0] ?? '');
        $targetphrase = trim($line[1] ?? '');
        $sourcelanguage = trim($line[2] ?? '');
        $targetlanguage = trim($line[3] ?? '');
        $courseid = trim($line[4] ?? '');
        $statusvalue = strtolower(trim($line[5] ?? ''));
        $priority = trim($line[6] ?? '');
        $casesensitive = strtolower(trim($line[7] ?? ''));
        $wholeword = strtolower(trim($line[8] ?? ''));
        $notes = trim($line[9] ?? '');
        $deeplglossaryid = trim($line[10] ?? '');

        if ($sourcephrase === '' || $targetphrase === '' || $sourcelanguage === '' || $targetlanguage === '') {
            return self::skip(self::REASON_MISSING_DATA);
        }

        if (!isset($validlanguages[$sourcelanguage]) || !isset($validlanguages[$targetlanguage])) {
            return self::skip(self::REASON_INVALID_LANGUAGE);
        }

        if ($courseid !== '' && !preg_match('/^\d+$/', $courseid)) {
            return self::skip(self::REASON_INVALID_COURSE);
        }

        $courseid = $courseid === '' ? null : (int)$courseid;
        if (!empty($courseid) && !$DB->record_exists('course', ['id' => $courseid])) {
            return self::skip(self::REASON_INVALID_COURSE);
        }

        $status = self::status_map()[$statusvalue] ?? null;
        if ($status === null) {
            return self::skip(self::REASON_INVALID_STATUS);
        }

        if ($priority !== '' && !preg_match('/^\d+$/', $priority)) {
            return self::skip(self::REASON_INVALID_PRIORITY);
        }

        $priority = $priority === '' ? 100 : (int)$priority;
        $casesensitive = in_array($casesensitive, ['1', 'true', 'yes', 'y'], true) ? 1 : 0;
        $wholeword = $wholeword === '' || in_array($wholeword, ['1', 'true', 'yes', 'y'], true) ? 1 : 0;
        $contextid = empty($courseid) ? $systemcontextid : context_course::instance($courseid)->id;

        $params = [
            'sourcephrase' => $sourcephrase,
            'sourcelanguage' => $sourcelanguage,
            'targetlanguage' => $targetlanguage,
        ];
        if (empty($courseid)) {
            $select = 'sourcephrase = :sourcephrase AND sourcelanguage = :sourcelanguage
                       AND targetlanguage = :targetlanguage AND courseid IS NULL';
        } else {
            $select = 'sourcephrase = :sourcephrase AND sourcelanguage = :sourcelanguage
                       AND targetlanguage = :targetlanguage AND courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        $record = (object)[
            'sourcephrase' => $sourcephrase,
            'targetphrase' => $targetphrase,
            'sourcelanguage' => $sourcelanguage,
            'targetlanguage' => $targetlanguage,
            'contextid' => $contextid,
            'courseid' => empty($courseid) ? null : $courseid,
            'status' => $status,
            'priority' => $priority,
            'casesensitive' => $casesensitive,
            'wholeword' => $wholeword,
            'notes' => $notes,
            'deeplglossaryid' => $deeplglossaryid,
        ];

        $existingrecords = $DB->get_records_select('filter_translations_glossary', $select, $params, 'id ASC', 'id');
        if (!empty($existingrecords)) {
            foreach ($existingrecords as $existing) {
                $entry = new glossary_entry($existing->id);
                $entry->from_record($record);
                $entry->update();
            }

            $result = new \stdClass();
            $result->action = self::ACTION_UPDATED;
            $result->updated = count($existingrecords);
            return $result;
        }

        $entry = new glossary_entry();
        $entry->from_record($record);
        $entry->create();

        $result = new \stdClass();
        $result->action = self::ACTION_CREATED;
        $result->updated = 0;
        return $result;
    }

    /**
     * Build a skip result.
     *
     * @param string $reason One of the REASON_* constants.
     * @return \stdClass
     */
    private static function skip(string $reason): \stdClass {
        $result = new \stdClass();
        $result->action = self::ACTION_SKIPPED;
        $result->reason = $reason;
        return $result;
    }
}
