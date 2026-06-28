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

/**
 * @package filter_translations
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace filter_translations\privacy;

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for filter_translations.
 *
 * Most plugin records are site-managed translation assets. User data is limited
 * to the last-modified audit link, so deletion anonymises that link instead of
 * deleting shared translations or glossary state.
 *
 * @package filter_translations
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {

    /** Tables with contextid and usermodified columns. */
    private const CONTEXT_TABLES = [
        'filter_translations',
        'filter_translation_issues',
        'filter_translations_history',
        'filter_translations_glossary',
    ];

    /** Tables without contextid that are treated as system context data. */
    private const SYSTEM_TABLES = [
        'filter_translations_glossync',
    ];

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('filter_translations', [
            'md5key' => 'privacy:metadata:translations:md5key',
            'lastgeneratedhash' => 'privacy:metadata:translations:lastgeneratedhash',
            'targetlanguage' => 'privacy:metadata:translations:targetlanguage',
            'contextid' => 'privacy:metadata:translations:contextid',
            'rawtext' => 'privacy:metadata:translations:rawtext',
            'substitutetext' => 'privacy:metadata:translations:substitutetext',
            'translationsource' => 'privacy:metadata:translations:translationsource',
            'usermodified' => 'privacy:metadata:translations:usermodified',
        ], 'privacy:metadata:translations');

        $collection->add_database_table('filter_translation_issues', [
            'issue' => 'privacy:metadata:issues:issue',
            'url' => 'privacy:metadata:issues:url',
            'md5key' => 'privacy:metadata:issues:md5key',
            'targetlanguage' => 'privacy:metadata:issues:targetlanguage',
            'contextid' => 'privacy:metadata:issues:contextid',
            'generatedhash' => 'privacy:metadata:issues:generatedhash',
            'rawtext' => 'privacy:metadata:issues:rawtext',
            'translationid' => 'privacy:metadata:issues:translationid',
            'usermodified' => 'privacy:metadata:issues:usermodified',
        ], 'privacy:metadata:issues');

        $collection->add_database_table('filter_translations_history', [
            'md5key' => 'privacy:metadata:history:md5key',
            'lastgeneratedhash' => 'privacy:metadata:history:lastgeneratedhash',
            'targetlanguage' => 'privacy:metadata:history:targetlanguage',
            'contextid' => 'privacy:metadata:history:contextid',
            'rawtext' => 'privacy:metadata:history:rawtext',
            'substitutetext' => 'privacy:metadata:history:substitutetext',
            'prevrawtext' => 'privacy:metadata:history:prevrawtext',
            'prevsubstitutetext' => 'privacy:metadata:history:prevsubstitutetext',
            'crud' => 'privacy:metadata:history:crud',
            'usermodified' => 'privacy:metadata:history:usermodified',
        ], 'privacy:metadata:history');

        $collection->add_database_table('filter_translations_glossary', [
            'sourcephrase' => 'privacy:metadata:glossary:sourcephrase',
            'targetphrase' => 'privacy:metadata:glossary:targetphrase',
            'sourcelanguage' => 'privacy:metadata:glossary:sourcelanguage',
            'targetlanguage' => 'privacy:metadata:glossary:targetlanguage',
            'notes' => 'privacy:metadata:glossary:notes',
            'usermodified' => 'privacy:metadata:glossary:usermodified',
        ], 'privacy:metadata:glossary');

        $collection->add_database_table('filter_translations_glossync', [
            'courseid' => 'privacy:metadata:glossarysync:courseid',
            'sourcelanguage' => 'privacy:metadata:glossarysync:sourcelanguage',
            'targetlanguage' => 'privacy:metadata:glossarysync:targetlanguage',
            'deeplglossaryid' => 'privacy:metadata:glossarysync:deeplglossaryid',
            'lastsyncerror' => 'privacy:metadata:glossarysync:lastsyncerror',
            'usermodified' => 'privacy:metadata:glossarysync:usermodified',
        ], 'privacy:metadata:glossarysync');

        return $collection;
    }

    /**
     * Get contexts containing data modified by this user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();
        foreach (self::CONTEXT_TABLES as $table) {
            $contextlist->add_from_sql(
                "SELECT ctx.id
                   FROM {context} ctx
                   JOIN {" . $table . "} t ON t.contextid = ctx.id
                  WHERE t.usermodified = :userid",
                ['userid' => $userid]
            );
        }

        foreach (self::SYSTEM_TABLES as $table) {
            if ($DB->record_exists($table, ['usermodified' => $userid])) {
                $contextlist->add_system_context();
                break;
            }
        }

        return $contextlist;
    }

    /**
     * Export user data for approved contexts.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $export = [];
            foreach (self::CONTEXT_TABLES as $table) {
                $records = $DB->get_records($table, [
                    'contextid' => $context->id,
                    'usermodified' => $userid,
                ], 'id ASC');
                if (!empty($records)) {
                    $export[$table] = array_values($records);
                }
            }

            if ($context instanceof context_system) {
                foreach (self::SYSTEM_TABLES as $table) {
                    $records = $DB->get_records($table, ['usermodified' => $userid], 'id ASC');
                    if (!empty($records)) {
                        $export[$table] = array_values($records);
                    }
                }
            }

            if (!empty($export)) {
                writer::with_context($context)->export_data([
                    get_string('privacy:exportpath', 'filter_translations'),
                ], (object)$export);
            }
        }
    }

    /**
     * Delete all user data in a context.
     *
     * @param context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        foreach (self::CONTEXT_TABLES as $table) {
            $DB->set_field($table, 'usermodified', 0, ['contextid' => $context->id]);
        }

        if ($context instanceof context_system) {
            foreach (self::SYSTEM_TABLES as $table) {
                $DB->set_field_select($table, 'usermodified', 0, 'usermodified <> 0');
            }
        }
    }

    /**
     * Delete user data for approved contexts.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            foreach (self::CONTEXT_TABLES as $table) {
                $DB->set_field($table, 'usermodified', 0, [
                    'contextid' => $context->id,
                    'usermodified' => $userid,
                ]);
            }

            if ($context instanceof context_system) {
                foreach (self::SYSTEM_TABLES as $table) {
                    $DB->set_field($table, 'usermodified', 0, ['usermodified' => $userid]);
                }
            }
        }
    }
}
