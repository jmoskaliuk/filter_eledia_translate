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

use filter_translations\glossary_entry;
use filter_translations\glossary_entry_form;

require(__DIR__ . '/../../config.php');

$id = optional_param('id', 0, PARAM_INT);
$defaultreturnurl = (new moodle_url('/filter/translations/manageglossary.php'))->out(false);
$returnurl = optional_param('returnurl', $defaultreturnurl, PARAM_LOCALURL);

$context = context_system::instance();
require_login();
require_capability('filter/translations:edittranslations', $context);

$url = new moodle_url('/filter/translations/editglossaryentry.php', ['id' => $id, 'returnurl' => $returnurl]);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($id ? get_string('editglossaryentry', 'filter_translations') :
    get_string('createglossaryentry', 'filter_translations'));
$PAGE->set_heading($PAGE->title);

$entry = $id ? new glossary_entry($id) : new glossary_entry();
$form = new glossary_entry_form($url->out(false));

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $record = clone($data);
    $record->courseid = empty($record->courseid) ? null : $record->courseid;
    $record->contextid = empty($record->courseid) ? $context->id : context_course::instance($record->courseid)->id;

    $entry->from_record($record);
    if ($entry->get('id')) {
        $entry->update();
    } else {
        $entry->create();
    }
    redirect($returnurl);
}

$formdata = $entry->to_record();
$formdata->returnurl = $returnurl;
$form->set_data($formdata);

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
