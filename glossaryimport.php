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

use filter_translations\form\glossary_import_form;
use filter_translations\glossary_entry;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

$context = context_system::instance();
require_login();
require_capability('filter/translations:bulkimporttranslations', $context);

$url = new moodle_url('/filter/translations/glossaryimport.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('importglossary', 'filter_translations'));
$PAGE->set_heading(get_string('importglossary', 'filter_translations'));
$PAGE->set_pagelayout('standard');

$form = new glossary_import_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/filter/translations/manageglossary.php'));
} else if ($data = $form->get_data()) {
    $filecontents = $form->get_file_content('file');
    $importid = csv_import_reader::get_new_iid('glossaryimport');
    $csvimport = new csv_import_reader($importid, 'glossaryimport');
    $readcount = $csvimport->load_csv_content($filecontents, 'UTF-8', 'comma');

    if ($readcount === false) {
        throw new moodle_exception('csvfileerror', 'error', $PAGE->url, $csvimport->get_error());
    } else if ($readcount == 0) {
        throw new moodle_exception('csvemptyfile', 'error', $PAGE->url, $csvimport->get_error());
    } else if ($readcount == 1) {
        throw new moodle_exception('csvnodata', 'error', $PAGE->url);
    }

    $csvimport->init();
    unset($filecontents);

    $requiredfields = [
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
    $header = $csvimport->get_columns();

    if (count($header) !== count($requiredfields)) {
        throw new moodle_exception('glossaryfieldsmismatch', 'filter_translations', $PAGE->url);
    }

    foreach ($header as $i => $field) {
        if ($field !== $requiredfields[$i]) {
            throw new moodle_exception('glossaryfieldwrongorder', 'filter_translations', $PAGE->url, $field);
        }
    }

    $languages = get_string_manager()->get_list_of_translations(true);
    $statusmap = [
        '10' => glossary_entry::STATUS_DRAFT,
        '20' => glossary_entry::STATUS_REVIEWED,
        '30' => glossary_entry::STATUS_APPROVED,
        '40' => glossary_entry::STATUS_ARCHIVED,
        'draft' => glossary_entry::STATUS_DRAFT,
        'reviewed' => glossary_entry::STATUS_REVIEWED,
        'approved' => glossary_entry::STATUS_APPROVED,
        'archived' => glossary_entry::STATUS_ARCHIVED,
    ];

    $processed = 0;
    $created = 0;
    $updated = 0;
    $skipped = [];
    $linenum = 2;

    while ($line = $csvimport->next()) {
        $processed++;

        $sourcephrase = trim($line[0]);
        $targetphrase = trim($line[1]);
        $sourcelanguage = trim($line[2]);
        $targetlanguage = trim($line[3]);
        $courseid = trim($line[4]);
        $statusvalue = strtolower(trim($line[5]));
        $priority = trim($line[6]);
        $casesensitive = strtolower(trim($line[7]));
        $wholeword = strtolower(trim($line[8]));
        $notes = trim($line[9]);
        $deeplglossaryid = trim($line[10]);

        $skip = function(string $reason) use (&$skipped, $linenum, $sourcephrase, $targetlanguage): void {
            $row = new stdClass();
            $row->linenum = $linenum;
            $row->sourcephrase = $sourcephrase;
            $row->targetlanguage = $targetlanguage;
            $row->reason = $reason;
            $skipped[] = $row;
        };

        if ($sourcephrase === '' || $targetphrase === '' || $sourcelanguage === '' || $targetlanguage === '') {
            $skip(get_string('glossaryimportmissingdata', 'filter_translations'));
            $linenum++;
            continue;
        }

        if (!isset($languages[$sourcelanguage]) || !isset($languages[$targetlanguage])) {
            $skip(get_string('glossaryimportinvalidlanguage', 'filter_translations'));
            $linenum++;
            continue;
        }

        if ($courseid !== '' && !preg_match('/^\d+$/', $courseid)) {
            $skip(get_string('glossaryimportinvalidcourse', 'filter_translations'));
            $linenum++;
            continue;
        }

        $courseid = $courseid === '' ? null : (int)$courseid;
        if (!empty($courseid) && !$DB->record_exists('course', ['id' => $courseid])) {
            $skip(get_string('glossaryimportinvalidcourse', 'filter_translations'));
            $linenum++;
            continue;
        }

        $status = $statusmap[$statusvalue] ?? null;
        if ($status === null) {
            $skip(get_string('glossaryimportinvalidstatus', 'filter_translations'));
            $linenum++;
            continue;
        }

        if ($priority !== '' && !preg_match('/^\d+$/', $priority)) {
            $skip(get_string('glossaryimportinvalidpriority', 'filter_translations'));
            $linenum++;
            continue;
        }

        $priority = $priority === '' ? 100 : (int)$priority;
        $casesensitive = in_array($casesensitive, ['1', 'true', 'yes', 'y'], true) ? 1 : 0;
        $wholeword = $wholeword === '' || in_array($wholeword, ['1', 'true', 'yes', 'y'], true) ? 1 : 0;
        $contextid = empty($courseid) ? $context->id : context_course::instance($courseid)->id;

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
                $updated++;
            }
        } else {
            $entry = new glossary_entry();
            $entry->from_record($record);
            $entry->create();
            $created++;
        }

        $linenum++;
    }

    $csvimport->close();

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('filter_translations/glossary_import_summary', (object)[
        'processedcount' => $processed,
        'createdcount' => $created,
        'updatedcount' => $updated,
        'skippedcount' => count($skipped),
        'hasskipped' => !empty($skipped),
        'skipped' => $skipped,
        'continueurl' => (new moodle_url('/filter/translations/manageglossary.php'))->out(false),
    ]);
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
echo html_writer::div(get_string('importglossarydescription', 'filter_translations'), 'description');
$form->display();
echo $OUTPUT->footer();
