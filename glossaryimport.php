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
use filter_translations\glossary_importer;

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

    $requiredfields = glossary_importer::REQUIRED_FIELDS;
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
    $reasonstrings = [
        glossary_importer::REASON_MISSING_DATA => 'glossaryimportmissingdata',
        glossary_importer::REASON_INVALID_LANGUAGE => 'glossaryimportinvalidlanguage',
        glossary_importer::REASON_INVALID_COURSE => 'glossaryimportinvalidcourse',
        glossary_importer::REASON_INVALID_STATUS => 'glossaryimportinvalidstatus',
        glossary_importer::REASON_INVALID_PRIORITY => 'glossaryimportinvalidpriority',
    ];

    $processed = 0;
    $created = 0;
    $updated = 0;
    $skipped = [];
    $linenum = 2;

    while ($line = $csvimport->next()) {
        $processed++;

        $result = glossary_importer::import_row($line, $languages, $context->id);

        if ($result->action === glossary_importer::ACTION_CREATED) {
            $created++;
        } else if ($result->action === glossary_importer::ACTION_UPDATED) {
            $updated += $result->updated;
        } else {
            $row = new stdClass();
            $row->linenum = $linenum;
            $row->sourcephrase = trim($line[0] ?? '');
            $row->targetlanguage = trim($line[3] ?? '');
            $stringkey = $reasonstrings[$result->reason] ?? 'glossaryimportmissingdata';
            $row->reason = get_string($stringkey, 'filter_translations');
            $skipped[] = $row;
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
