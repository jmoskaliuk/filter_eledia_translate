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

use filter_translations\manageglossary_filterform;
use filter_translations\manageglossary_table;
use filter_translations\output\shell;

require(__DIR__ . '/../../config.php');

$sourcephrase = optional_param('sourcephrase', '', PARAM_TEXT);
$targetphrase = optional_param('targetphrase', '', PARAM_TEXT);
$sourcelanguage = optional_param('sourcelanguage', '', PARAM_TEXT);
$targetlanguage = optional_param('targetlanguage', '', PARAM_TEXT);
$status = optional_param('status', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$context = context_system::instance();
require_login();
require_capability('filter/translations:edittranslations', $context);

$baseurl = new moodle_url('/filter/translations/manageglossary.php');
$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manageglossary', 'filter_translations'));
$PAGE->set_heading('');

$form = new manageglossary_filterform();
if ($formdata = $form->get_data()) {
    $baseurl->params([
        'sourcephrase' => $sourcephrase,
        'targetphrase' => $targetphrase,
        'sourcelanguage' => $sourcelanguage,
        'targetlanguage' => $targetlanguage,
        'status' => $status,
        'courseid' => $courseid,
    ]);
    redirect($baseurl);
}

$data = (object)[
    'sourcephrase' => $sourcephrase,
    'targetphrase' => $targetphrase,
    'sourcelanguage' => $sourcelanguage,
    'targetlanguage' => $targetlanguage,
    'status' => $status,
    'courseid' => $courseid,
    'tsort' => optional_param('tsort', 'id', PARAM_ALPHA),
];
$form->set_data($data);
$baseurl->params((array)$data);
$PAGE->set_url($baseurl);

$table = new manageglossary_table($data, 'sourcephrase');
$table->define_baseurl($baseurl);

shell::require_css();
echo $OUTPUT->header();
shell::open(get_string('manageglossary', 'filter_translations'),
    get_string('dashboardglossary_desc', 'filter_translations'));
echo $form->render();
echo html_writer::start_div('mb-3');
echo $OUTPUT->single_button(new moodle_url('/filter/translations/editglossaryentry.php', ['returnurl' => $PAGE->url->out(false)]),
    get_string('createglossaryentry', 'filter_translations'));
echo $OUTPUT->single_button(new moodle_url('/filter/translations/manageglossarysync.php'),
    get_string('deeplglossarysync', 'filter_translations'));
if (has_capability('filter/translations:exporttranslations', $context)) {
    echo $OUTPUT->single_button(new moodle_url('/filter/translations/glossaryexport.php', [
        'sourcephrase' => $sourcephrase,
        'targetphrase' => $targetphrase,
        'sourcelanguage' => $sourcelanguage,
        'targetlanguage' => $targetlanguage,
        'status' => $status,
        'courseid' => $courseid,
    ]), get_string('exportglossary', 'filter_translations'));
}
if (has_capability('filter/translations:bulkimporttranslations', $context)) {
    echo $OUTPUT->single_button(new moodle_url('/filter/translations/glossaryimport.php'),
        get_string('importglossary', 'filter_translations'));
}
echo html_writer::end_div();
$table->out(100, true);
shell::close();
echo $OUTPUT->footer();
