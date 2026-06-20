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

use filter_translations\translation;

function xmldb_filter_translations_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021110908) {
        $table = new xmldb_table('filter_translations');
        $field = new xmldb_field('rawtext', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2021110908, 'filter', 'translations');
    }

    if ($oldversion < 2022012400) {
        $table = new xmldb_table('filter_translations');
        $field = new xmldb_field('translationsource', XMLDB_TYPE_INTEGER, 10, null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $DB->execute("UPDATE {filter_translations} SET translationsource = :manual", ['manual' => translation::SOURCE_MANUAL]);
        }

        upgrade_plugin_savepoint(true, 2022012400, 'filter', 'translations');
    }

    if ($oldversion < 2022022312) {
        $table = new xmldb_table('filter_translation_issues');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('issue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->add_field('md5key', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL);
        $table->add_field('targetlanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('translationid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('rawtext', XMLDB_TYPE_TEXT);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('generatedhash', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2022022312, 'filter', 'translations');
    }

    if ($oldversion < 2022022319) {
        $table = new xmldb_table('filter_translation_issues');
        $field = new xmldb_field('url', XMLDB_TYPE_TEXT);
        $dbman->change_field_type($table, $field);

        upgrade_plugin_savepoint(true, 2022022319, 'filter', 'translations');
    }

    if ($oldversion < 2022042709) {

        // Define index targetlanguage_md5key (not unique) to be dropped from filter_translation_issues.
        $table = new xmldb_table('filter_translation_issues');
        $index = new xmldb_index('targetlanguage_md5key', XMLDB_INDEX_NOTUNIQUE, ['targetlanguage', 'md5key']);

        // Conditionally launch drop index targetlanguage_md5key.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index targetlanguage_issue (not unique) to be added to filter_translation_issues.
        $index = new xmldb_index('targetlanguage_issue', XMLDB_INDEX_NOTUNIQUE, ['targetlanguage', 'issue']);

        // Conditionally launch add index targetlanguage_issue.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2022042709, 'filter', 'translations');
    }

    if ($oldversion < 2022042711) {
        // Context id cannot be 0.
        // Update context id to 1.
        $DB->execute("UPDATE {filter_translations} SET contextid = :contextid WHERE contextid=0",
            ['contextid' => \context_system::instance()->id]);
        $DB->execute("UPDATE {filter_translation_issues} SET contextid = :contextid WHERE contextid=0",
            ['contextid' => \context_system::instance()->id]);

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2022042711, 'filter', 'translations');
    }

    if ($oldversion < 2022042714) {
        $logexcludelang = get_config('filter_translations', 'excludelang');
        if (!empty($logexcludelang)) {
            set_config('logexcludelang', $logexcludelang, 'filter_translations');
        }
        set_config('excludelang', '', 'filter_translations');

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2022042714, 'filter', 'translations');
    }

    if ($oldversion < 2022042715) {
        $table = new xmldb_table('filter_translations');
        $index = new xmldb_index('targetlang_md5key_lastgen', XMLDB_INDEX_NOTUNIQUE,
            ['targetlanguage', 'md5key', 'lastgeneratedhash']);

        // Conditionally launch add index targetlanguage_issue.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2022042715, 'filter', 'translations');
    }

    if ($oldversion < 2022042717) {
        $table = new xmldb_table('filter_translations');
        $index = new xmldb_index('targetlanguage_lastgen', XMLDB_INDEX_NOTUNIQUE,
            ['targetlanguage', 'lastgeneratedhash']);

        // Conditionally launch add index targetlanguage_issue.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('targetlang_md5key_lastgen', XMLDB_INDEX_NOTUNIQUE,
            ['targetlanguage', 'md5key', 'lastgeneratedhash']);

        // Conditionally launch drop index targetlanguage_md5key.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2022042717, 'filter', 'translations');
    }

    if ($oldversion < 2023031002) {
        $table = new xmldb_table('filter_translations_history');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('md5key', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL);
        $table->add_field('lastgeneratedhash', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL);
        $table->add_field('targetlanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('rawtext', XMLDB_TYPE_TEXT);
        $table->add_field('substitutetext', XMLDB_TYPE_TEXT);
        $table->add_field('substitutetextformat', XMLDB_TYPE_INTEGER, '1', null, null);
        $table->add_field('translationsource', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('prevlastgeneratedhash', XMLDB_TYPE_CHAR, '32', null, null);
        $table->add_field('prevrawtext', XMLDB_TYPE_TEXT);
        $table->add_field('prevsubstitutetext', XMLDB_TYPE_TEXT);
        $table->add_field('crud', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            // Add indexes.
            $index = new xmldb_index('targetlanguage_md5key', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'md5key']);
            $dbman->add_index($table, $index);

            $index = new xmldb_index('targetlanguage_lastgen', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'lastgeneratedhash']);
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2023031002, 'filter', 'translations');
    }

    if ($oldversion < 2024110103) {
        $table = new xmldb_table('filter_translations_history');

        // Drop the index temporarily. We will add them back later.
        $index = new xmldb_index('targetlanguage_md5key', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'md5key']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('targetlanguage_lastgen', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'lastgeneratedhash']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Rename field 'prevgeneratedhash' on table filter_translations_history to 'prevlastgeneratedhash'.
        $field = new xmldb_field('prevgeneratedhash', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'translationsource');
        $renamedfield = new xmldb_field('prevlastgeneratedhash', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'translationsource');

        if ($dbman->field_exists($table, $field) && !$dbman->field_exists($table, $renamedfield)) {
            // Launch rename field prevgeneratedhash.
            $dbman->rename_field($table, $field, 'prevlastgeneratedhash');
        }

        // Changing the default of field targetlanguage on table filter_translations_history to en.
        $field = new xmldb_field('targetlanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'en', 'lastgeneratedhash');

        // Launch change of default for field targetlanguage.
        $dbman->change_field_default($table, $field);

        // Add indexes.
        $index = new xmldb_index('targetlanguage_md5key', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'md5key']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('targetlanguage_lastgen', XMLDB_INDEX_NOTUNIQUE,
                ['targetlanguage', 'lastgeneratedhash']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('filttranhist_use_ix', XMLDB_INDEX_NOTUNIQUE, ['usermodified']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('filttranhist_con_ix', XMLDB_INDEX_NOTUNIQUE, ['contextid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Translations savepoint reached.
        upgrade_plugin_savepoint(true, 2024110103, 'filter', 'translations');
    }

    if ($oldversion < 2026052300) {
        $table = new xmldb_table('filter_translations_glossary');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('sourcephrase', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('targetphrase', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $table->add_field('sourcelanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('targetlanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '10');
        $table->add_field('priority', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100');
        $table->add_field('casesensitive', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('wholeword', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('notes', XMLDB_TYPE_TEXT);
        $table->add_field('deeplglossaryid', XMLDB_TYPE_CHAR, '80');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('contextid', XMLDB_KEY_FOREIGN, ['contextid'], 'context', ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        $table->add_index('source_target_lang', XMLDB_INDEX_NOTUNIQUE, ['sourcelanguage', 'targetlanguage']);
        $table->add_index('target_status', XMLDB_INDEX_NOTUNIQUE, ['targetlanguage', 'status']);
        $table->add_index('course_status', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'status']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        try {
            \filter_translations\course_customfields::ensure();
        } catch (\Throwable $e) {
            debugging('Could not create filter_translations course custom fields: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }

        upgrade_plugin_savepoint(true, 2026052300, 'filter', 'translations');
    }

    if ($oldversion < 2026052301) {
        $table = new xmldb_table('filter_translations_glossync');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('scope', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null);
        $table->add_field('sourcelanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('targetlanguage', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL);
        $table->add_field('deeplglossaryid', XMLDB_TYPE_CHAR, '80');
        $table->add_field('entrycount', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('contenthash', XMLDB_TYPE_CHAR, '32');
        $table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '10');
        $table->add_field('lastsyncerror', XMLDB_TYPE_TEXT);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        $table->add_index('scope_lang', XMLDB_INDEX_NOTUNIQUE, ['scope', 'sourcelanguage', 'targetlanguage']);
        $table->add_index('course_lang', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'sourcelanguage', 'targetlanguage']);
        $table->add_index('deeplglossaryid', XMLDB_INDEX_NOTUNIQUE, ['deeplglossaryid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026052301, 'filter', 'translations');
    }

    if ($oldversion < 2026061500) {
        $table = new xmldb_table('filter_translations_glossync');

        // Remove any duplicate sync rows before enforcing uniqueness, keeping the lowest id per natural key.
        $rs = $DB->get_recordset('filter_translations_glossync', null, 'id ASC');
        $seen = [];
        foreach ($rs as $rec) {
            $courseid = empty($rec->courseid) ? 0 : (int)$rec->courseid;
            $key = $rec->scope . '|' . $courseid . '|' . $rec->sourcelanguage . '|' . $rec->targetlanguage;
            if (isset($seen[$key])) {
                $DB->delete_records('filter_translations_glossync', ['id' => $rec->id]);
            } else {
                $seen[$key] = $rec->id;
            }
        }
        $rs->close();

        // Drop the old non-unique index if present.
        $oldindex = new xmldb_index('scope_lang', XMLDB_INDEX_NOTUNIQUE, ['scope', 'sourcelanguage', 'targetlanguage']);
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }

        // Normalise global rows before the unique index is added. Unique indexes do not make NULL values unique.
        $DB->execute("UPDATE {filter_translations_glossync} SET courseid = 0 WHERE courseid IS NULL");
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'scope');
        $dbman->change_field_default($table, $field);
        $dbman->change_field_notnull($table, $field);

        // Add the unique index over the natural key.
        $newindex = new xmldb_index('scope_course_lang', XMLDB_INDEX_UNIQUE,
            ['scope', 'courseid', 'sourcelanguage', 'targetlanguage']);
        if (!$dbman->index_exists($table, $newindex)) {
            $dbman->add_index($table, $newindex);
        }

        upgrade_plugin_savepoint(true, 2026061500, 'filter', 'translations');
    }

    if ($oldversion < 2026061501) {
        $table = new xmldb_table('filter_translations_glossync');

        $oldindex = new xmldb_index('scope_course_lang', XMLDB_INDEX_UNIQUE,
            ['scope', 'courseid', 'sourcelanguage', 'targetlanguage']);
        if ($dbman->index_exists($table, $oldindex)) {
            $dbman->drop_index($table, $oldindex);
        }

        $DB->execute("UPDATE {filter_translations_glossync} SET courseid = 0 WHERE courseid IS NULL");

        $rs = $DB->get_recordset('filter_translations_glossync', null, 'id ASC');
        $seen = [];
        foreach ($rs as $rec) {
            $key = $rec->scope . '|' . (int)$rec->courseid . '|' . $rec->sourcelanguage . '|' . $rec->targetlanguage;
            if (isset($seen[$key])) {
                $DB->delete_records('filter_translations_glossync', ['id' => $rec->id]);
            } else {
                $seen[$key] = $rec->id;
            }
        }
        $rs->close();

        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'scope');
        $dbman->change_field_default($table, $field);
        $dbman->change_field_notnull($table, $field);

        $newindex = new xmldb_index('scope_course_lang', XMLDB_INDEX_UNIQUE,
            ['scope', 'courseid', 'sourcelanguage', 'targetlanguage']);
        if (!$dbman->index_exists($table, $newindex)) {
            $dbman->add_index($table, $newindex);
        }

        upgrade_plugin_savepoint(true, 2026061501, 'filter', 'translations');
    }

    return true;
}
