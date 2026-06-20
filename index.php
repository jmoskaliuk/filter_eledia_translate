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
use filter_translations\glossary_sync;
use filter_translations\translation_issue;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filterlib.php');

$context = context_system::instance();
require_login();
require_capability('filter/translations:edittranslations', $context);

$canconfig = has_capability('moodle/site:config', $context);
$cansetupcoursefields = has_capability('moodle/course:configurecustomfields', $context);
if ($canconfig) {
    admin_externalpage_setup('filtertranslationsdashboard');
} else {
    $PAGE->set_context($context);
    $PAGE->set_pagelayout('standard');
}

$PAGE->set_url(new moodle_url('/filter/translations/index.php'));
$PAGE->set_title(get_string('pluginsetup', 'filter_translations'));
$PAGE->set_heading(get_string('pluginsetup', 'filter_translations'));

$config = get_config('filter_translations');
$filterenabled = filter_get_active_state('translations') === TEXTFILTER_ON;

$translationcount = $DB->count_records('filter_translations');
$issuecount = $DB->count_records('filter_translation_issues');
$glossarycount = $DB->count_records('filter_translations_glossary');
$syncgroups = glossary_sync::groups();
$pendingsyncgroups = array_filter($syncgroups, function($group): bool {
    return !empty($group->pending);
});

$issuecounts = $DB->get_records_sql_menu("
    SELECT issue, COUNT(1)
      FROM {filter_translation_issues}
  GROUP BY issue
", []);
$glossarystatuscounts = $DB->get_records_sql_menu("
    SELECT status, COUNT(1)
      FROM {filter_translations_glossary}
  GROUP BY status
", []);

$statusyes = get_string('yes');
$statusno = get_string('no');
$issueoptions = translation_issue::get_issue_types();
$glossarystatusoptions = glossary_entry::status_options();

$settingsurl = new moodle_url('/admin/settings.php', ['section' => 'filtersettingtranslations']);
$filtermanageurl = new moodle_url('/admin/filters.php');
$scheduledtasksurl = new moodle_url('/admin/tool/task/scheduledtasks.php');
$setupcoursefieldsurl = new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]);
$deepltesturl = new moodle_url('/filter/translations/testdeepl.php', ['sesskey' => sesskey()]);

$actions = [
    [
        'title' => get_string('managetranslations', 'filter_translations'),
        'description' => get_string('dashboardtranslations_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/managetranslations.php'),
        'button' => get_string('openworkflow', 'filter_translations'),
        'class' => 'btn-primary',
        'show' => true,
    ],
    [
        'title' => get_string('managetranslationissues', 'filter_translations'),
        'description' => get_string('dashboardissues_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/managetranslationissues.php'),
        'button' => get_string('openworkflow', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => true,
    ],
    [
        'title' => get_string('manageglossary', 'filter_translations'),
        'description' => get_string('dashboardglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/manageglossary.php'),
        'button' => get_string('openworkflow', 'filter_translations'),
        'class' => 'btn-primary',
        'show' => true,
    ],
    [
        'title' => get_string('deeplglossarysync', 'filter_translations'),
        'description' => get_string('dashboardsync_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/manageglossarysync.php'),
        'button' => get_string('openworkflow', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => true,
    ],
    [
        'title' => get_string('createglossaryentry', 'filter_translations'),
        'description' => get_string('dashboardcreateglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/editglossaryentry.php',
            ['returnurl' => (new moodle_url('/filter/translations/index.php'))->out(false)]),
        'button' => get_string('createglossaryentry', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => true,
    ],
    [
        'title' => get_string('importglossary', 'filter_translations'),
        'description' => get_string('dashboardimportglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/glossaryimport.php'),
        'button' => get_string('importglossary', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => has_capability('filter/translations:bulkimporttranslations', $context),
    ],
    [
        'title' => get_string('exportglossary', 'filter_translations'),
        'description' => get_string('dashboardexportglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/glossaryexport.php'),
        'button' => get_string('exportglossary', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => has_capability('filter/translations:exporttranslations', $context),
    ],
    [
        'title' => get_string('importtranslations', 'filter_translations'),
        'description' => get_string('dashboardimport_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/import.php'),
        'button' => get_string('import'),
        'class' => 'btn-secondary',
        'show' => has_capability('filter/translations:bulkimporttranslations', $context),
    ],
    [
        'title' => get_string('exporttranslations', 'filter_translations'),
        'description' => get_string('dashboardexport_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/export.php'),
        'button' => get_string('export', 'filter_translations'),
        'class' => 'btn-secondary',
        'show' => has_capability('filter/translations:exporttranslations', $context),
    ],
];

echo $OUTPUT->header();

echo html_writer::start_div('filter-translations-dashboard');
echo html_writer::tag('p', get_string('pluginsetup_desc', 'filter_translations'), ['class' => 'lead']);

echo html_writer::start_div('mb-4');
if ($canconfig) {
    echo $OUTPUT->single_button($settingsurl, get_string('pluginsettings', 'filter_translations'), 'get',
        ['class' => 'btn btn-primary']);
    echo $OUTPUT->single_button($filtermanageurl, get_string('managefilters'), 'get',
        ['class' => 'btn btn-secondary']);
}
if ($cansetupcoursefields) {
    echo $OUTPUT->single_button($setupcoursefieldsurl, get_string('setupcoursefields', 'filter_translations'), 'get',
        ['class' => 'btn btn-secondary']);
}
if ($canconfig) {
    echo $OUTPUT->single_button($scheduledtasksurl, get_string('scheduledtasksheading', 'filter_translations'), 'get',
        ['class' => 'btn btn-secondary']);
}
echo html_writer::end_div();

echo html_writer::start_div('row mb-4');
echo html_writer::start_div('col-md-3 mb-3');
echo html_writer::div(
    html_writer::tag('h3', s($translationcount), ['class' => 'h2 mb-1']) .
    html_writer::div(get_string('translations', 'filter_translations'), 'text-muted'),
    'card card-body h-100'
);
echo html_writer::end_div();
echo html_writer::start_div('col-md-3 mb-3');
echo html_writer::div(
    html_writer::tag('h3', s($issuecount), ['class' => 'h2 mb-1']) .
    html_writer::div(get_string('translationissues', 'filter_translations'), 'text-muted'),
    'card card-body h-100'
);
echo html_writer::end_div();
echo html_writer::start_div('col-md-3 mb-3');
echo html_writer::div(
    html_writer::tag('h3', s($glossarycount), ['class' => 'h2 mb-1']) .
    html_writer::div(get_string('manageglossary', 'filter_translations'), 'text-muted'),
    'card card-body h-100'
);
echo html_writer::end_div();
echo html_writer::start_div('col-md-3 mb-3');
echo html_writer::div(
    html_writer::tag('h3', s(count($pendingsyncgroups)), ['class' => 'h2 mb-1']) .
    html_writer::div(get_string('dashboardpendingsyncgroups', 'filter_translations'), 'text-muted'),
    'card card-body h-100'
);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('row');
echo html_writer::start_div('col-lg-6 mb-4');
echo html_writer::start_div('card h-100');
echo html_writer::tag('h2', get_string('dashboardconfiguration', 'filter_translations'), ['class' => 'h4 card-header']);
echo html_writer::start_div('card-body');
$configtable = new html_table();
$configtable->attributes['class'] = 'table table-sm mb-0';
$configtable->data = [
    [get_string('filtername', 'filter_translations'), $filterenabled ? $statusyes : $statusno],
    [get_string('deepl_enable', 'filter_translations'), !empty($config->deepl_enable) ? $statusyes : $statusno],
    [get_string('deepl_sourcelang', 'filter_translations'), s($config->deepl_sourcelang ?? '')],
    [get_string('deepl_glossaryid', 'filter_translations'), s($config->deepl_glossaryid ?? '')],
    [get_string('coursecontrolsource', 'filter_translations'), s($config->coursecontrolsource ?? 'tags')],
    [get_string('logmissing', 'filter_translations'), !empty($config->logmissing) ? $statusyes : $statusno],
    [get_string('logstale', 'filter_translations'), !empty($config->logstale) ? $statusyes : $statusno],
];
echo html_writer::table($configtable);
echo html_writer::end_div();
if ($canconfig) {
    echo html_writer::start_div('card-footer');
    echo $OUTPUT->single_button($deepltesturl, get_string('deepltest', 'filter_translations'), 'get',
        ['class' => 'btn btn-secondary']);
    echo html_writer::end_div();
}
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('col-lg-6 mb-4');
echo html_writer::start_div('card h-100');
echo html_writer::tag('h2', get_string('dashboardstatus', 'filter_translations'), ['class' => 'h4 card-header']);
echo html_writer::start_div('card-body');
$statustable = new html_table();
$statustable->attributes['class'] = 'table table-sm mb-0';
$statustable->data = [];
foreach ($issueoptions as $status => $label) {
    $statustable->data[] = [$label, $issuecounts[$status] ?? 0];
}
foreach ($glossarystatusoptions as $status => $label) {
    $statustable->data[] = [$label, $glossarystatuscounts[$status] ?? 0];
}
echo html_writer::table($statustable);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::tag('h2', get_string('dashboardworkflows', 'filter_translations'), ['class' => 'h3 mt-2']);
echo html_writer::start_div('row');
foreach ($actions as $action) {
    if (empty($action['show'])) {
        continue;
    }
    echo html_writer::start_div('col-md-6 col-xl-4 mb-3');
    echo html_writer::start_div('card h-100');
    echo html_writer::start_div('card-body d-flex flex-column');
    echo html_writer::tag('h3', $action['title'], ['class' => 'h5']);
    echo html_writer::tag('p', $action['description'], ['class' => 'text-muted']);
    echo html_writer::link($action['url'], $action['button'], ['class' => 'btn ' . $action['class'] . ' mt-auto']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}
echo html_writer::end_div();

echo html_writer::end_div();
echo $OUTPUT->footer();
