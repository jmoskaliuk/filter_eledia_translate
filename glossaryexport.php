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

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');

$sourcephrase = optional_param('sourcephrase', '', PARAM_TEXT);
$targetphrase = optional_param('targetphrase', '', PARAM_TEXT);
$sourcelanguage = optional_param('sourcelanguage', '', PARAM_TEXT);
$targetlanguage = optional_param('targetlanguage', '', PARAM_TEXT);
$status = optional_param('status', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$context = context_system::instance();
require_login();
require_capability('filter/translations:exporttranslations', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/filter/translations/glossaryexport.php'));

$fields = [
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

$wheres = [];
$params = [];

if ($sourcephrase !== '') {
    $params['sourcephrase'] = '%' . $DB->sql_like_escape($sourcephrase) . '%';
    $wheres[] = $DB->sql_like('sourcephrase', ':sourcephrase', false);
}
if ($targetphrase !== '') {
    $params['targetphrase'] = '%' . $DB->sql_like_escape($targetphrase) . '%';
    $wheres[] = $DB->sql_like('targetphrase', ':targetphrase', false);
}
if ($sourcelanguage !== '') {
    $params['sourcelanguage'] = $sourcelanguage;
    $wheres[] = 'sourcelanguage = :sourcelanguage';
}
if ($targetlanguage !== '') {
    $params['targetlanguage'] = $targetlanguage;
    $wheres[] = 'targetlanguage = :targetlanguage';
}
if ($status > 0) {
    $params['status'] = $status;
    $wheres[] = 'status = :status';
}
if ($courseid === -1) {
    $wheres[] = 'courseid IS NULL';
} else if ($courseid > 0) {
    $params['courseid'] = $courseid;
    $wheres[] = 'courseid = :courseid';
}

$where = $wheres ? implode(' AND ', $wheres) : '1=1';
$records = $DB->get_records_select('filter_translations_glossary', $where, $params,
    'sourcelanguage ASC, targetlanguage ASC, sourcephrase ASC, priority ASC');

$csv = new csv_export_writer('comma');
$csv->set_filename('filter_translations_glossary');
$csv->add_data($fields);

foreach ($records as $record) {
    $csv->add_data([
        $record->sourcephrase,
        $record->targetphrase,
        $record->sourcelanguage,
        $record->targetlanguage,
        empty($record->courseid) ? '' : $record->courseid,
        $record->status,
        $record->priority,
        empty($record->casesensitive) ? 0 : 1,
        empty($record->wholeword) ? 0 : 1,
        $record->notes,
        $record->deeplglossaryid,
    ]);
}

$csv->download_file();
