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

use filter_translations\glossary_sync;
use filter_translations\output\shell;

require(__DIR__ . '/../../config.php');

$sync = optional_param('sync', '', PARAM_ALPHANUMEXT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$sourcelanguage = optional_param('sourcelanguage', '', PARAM_ALPHANUMEXT);
$targetlanguage = optional_param('targetlanguage', '', PARAM_ALPHANUMEXT);

$context = context_system::instance();
require_login();
require_capability('filter/translations:edittranslations', $context);

$url = new moodle_url('/filter/translations/manageglossarysync.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('deeplglossarysync', 'filter_translations'));
$PAGE->set_heading('');
$PAGE->set_pagelayout('standard');

if ($sync !== '') {
    require_sesskey();
    $scope = $courseid > 0 ? 'course' : 'global';
    $state = glossary_sync::sync_group($scope, $courseid > 0 ? $courseid : null, $sourcelanguage, $targetlanguage);

    if ((int)$state->status === glossary_sync::STATUS_SYNCED) {
        redirect($url, get_string('deeplglossarysyncsuccess', 'filter_translations'), null,
            \core\output\notification::NOTIFY_SUCCESS);
    }

    redirect($url, get_string('deeplglossarysyncerror', 'filter_translations', $state->lastsyncerror), null,
        \core\output\notification::NOTIFY_ERROR);
}

$statuses = glossary_sync::status_options();
$courses = [];
$courserecords = $DB->get_records_select('course', 'id > :siteid', ['siteid' => SITEID], '', 'id, fullname');
foreach ($courserecords as $course) {
    $courses[$course->id] = format_string($course->fullname);
}

$rows = [];
foreach (glossary_sync::groups() as $group) {
    $syncurl = new moodle_url('/filter/translations/manageglossarysync.php', [
        'sync' => 'single',
        'courseid' => empty($group->courseid) ? 0 : $group->courseid,
        'sourcelanguage' => $group->sourcelanguage,
        'targetlanguage' => $group->targetlanguage,
        'sesskey' => sesskey(),
    ]);

    $rows[] = (object)[
        'scope' => empty($group->courseid) ? get_string('glossaryscope_global', 'filter_translations') :
            ($courses[$group->courseid] ?? (string)$group->courseid),
        'sourcelanguage' => s($group->sourcelanguage),
        'targetlanguage' => s($group->targetlanguage),
        'entrycount' => $group->entrycount,
        'deeplglossaryid' => s($group->deeplglossaryid),
        'status' => $statuses[$group->syncstatus] ?? $group->syncstatus,
        'pending' => $group->pending ? get_string('yes') : get_string('no'),
        'lastsyncerror' => s($group->lastsyncerror),
        'syncurl' => $syncurl->out(false),
    ];
}

shell::require_css();
echo $OUTPUT->header();
shell::open(get_string('deeplglossarysync', 'filter_translations'),
    get_string('dashboardsync_desc', 'filter_translations'));
echo $OUTPUT->heading(get_string('deeplglossarysyncpreview', 'filter_translations'), 3);

if (empty($rows)) {
    echo $OUTPUT->notification(get_string('deeplglossarynosyncgroups', 'filter_translations'), 'info');
} else {
    $table = new html_table();
    $table->head = [
        get_string('glossaryscope', 'filter_translations'),
        get_string('sourcelanguage', 'filter_translations'),
        get_string('targetlanguage', 'filter_translations'),
        get_string('entries', 'filter_translations'),
        get_string('deepl_glossaryid', 'filter_translations'),
        get_string('status', 'filter_translations'),
        get_string('pending', 'filter_translations'),
        get_string('reason', 'filter_translations'),
        get_string('actions'),
    ];

    foreach ($rows as $row) {
        $table->data[] = [
            $row->scope,
            $row->sourcelanguage,
            $row->targetlanguage,
            $row->entrycount,
            $row->deeplglossaryid,
            $row->status,
            $row->pending,
            $row->lastsyncerror,
            html_writer::link($row->syncurl, get_string('sync', 'filter_translations'), ['class' => 'btn btn-primary btn-sm']),
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->single_button(new moodle_url('/filter/translations/manageglossary.php'),
    get_string('manageglossary', 'filter_translations'), 'get');
shell::close();
echo $OUTPUT->footer();
