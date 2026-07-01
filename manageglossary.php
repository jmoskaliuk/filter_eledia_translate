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
use filter_translations\glossary_sync;
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

$syncstatuses = glossary_sync::status_options();
$glossarygroups = glossary_sync::groups();
$courses = [];
$courseids = [];
foreach ($glossarygroups as $group) {
    if (!empty($group->courseid)) {
        $courseids[] = (int)$group->courseid;
    }
}
$courseids = array_values(array_unique($courseids));
if (!empty($courseids)) {
    $courserecords = $DB->get_records_list('course', 'id', $courseids, '', 'id, fullname');
    foreach ($courserecords as $course) {
        $courses[$course->id] = format_string($course->fullname, true,
            ['context' => context_course::instance($course->id)]);
    }
}
$languagecode = static function(string $language): string {
    return strtoupper(str_replace('_', '-', $language));
};
$iconaction = static function(moodle_url $url, string $label, string $icon, string $modifier = ''): string {
    return html_writer::link($url,
        html_writer::tag('i', '', ['class' => 'fa ' . $icon, 'aria-hidden' => 'true']) .
        html_writer::span($label, 'sr-only'),
        [
            'class' => trim('lh-icon-action ' . $modifier),
            'aria-label' => $label,
            'title' => $label,
        ]
    );
};

shell::require_css();
echo $OUTPUT->header();
shell::open(get_string('manageglossary', 'filter_translations'),
    get_string('dashboardglossary_desc', 'filter_translations'));

echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-workbench-card filter-translations-filter-card']);
echo html_writer::tag('div',
    html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-filter', 'aria-hidden' => 'true']),
        'lh-plugin-card__icon lh-plugin-card__icon--generic') .
    html_writer::tag('div',
        html_writer::tag('h2', get_string('filteroptions', 'filter_translations'),
            ['class' => 'lh-plugin-card__title']),
        ['class' => 'lh-plugin-card__meta']
    ),
    ['class' => 'lh-plugin-card__top']
);
echo html_writer::start_div('lh-plugin-card__body filter-translations-form-card');
echo $form->render();
echo html_writer::end_div();
echo html_writer::end_tag('section');

$actions = [];
$actions[] = $iconaction(
    new moodle_url('/filter/translations/editglossaryentry.php', ['returnurl' => $PAGE->url->out(false)]),
    get_string('createglossaryentry', 'filter_translations'),
    'fa-plus',
    'lh-icon-action--primary'
);
$actions[] = $iconaction(
    new moodle_url('/filter/translations/manageglossarysync.php'),
    get_string('deeplglossarysync', 'filter_translations'),
    'fa-refresh'
);
if (has_capability('filter/translations:exporttranslations', $context)) {
    $actions[] = $iconaction(new moodle_url('/filter/translations/glossaryexport.php', [
        'sourcephrase' => $sourcephrase,
        'targetphrase' => $targetphrase,
        'sourcelanguage' => $sourcelanguage,
        'targetlanguage' => $targetlanguage,
        'status' => $status,
        'courseid' => $courseid,
    ]),
        get_string('exportglossary', 'filter_translations'),
        'fa-download');
}
if (has_capability('filter/translations:bulkimporttranslations', $context)) {
    $actions[] = $iconaction(new moodle_url('/filter/translations/glossaryimport.php'),
        get_string('importglossary', 'filter_translations'),
        'fa-upload');
}

echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-workbench-card']);
echo html_writer::tag('div',
    html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-sitemap', 'aria-hidden' => 'true']),
        'lh-plugin-card__icon lh-plugin-card__icon--generic') .
    html_writer::tag('div',
        html_writer::tag('h2', get_string('glossarygroups', 'filter_translations'),
            ['class' => 'lh-plugin-card__title']),
        ['class' => 'lh-plugin-card__meta']
    ),
    ['class' => 'lh-plugin-card__top']
);
echo html_writer::start_div('lh-plugin-card__body filter-translations-table-card filter-translations-glossary-groups');
if (empty($glossarygroups)) {
    echo html_writer::div(
        html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-sitemap', 'aria-hidden' => 'true']),
            'lh-plugin-empty-state__icon') .
        html_writer::tag('p', get_string('deeplglossarynosyncgroups', 'filter_translations'),
            ['class' => 'lh-plugin-empty-state__text']),
        'lh-plugin-empty-state lh-table-empty'
    );
} else {
    $grouptable = new html_table();
    $grouptable->head = [
        get_string('glossaryscope', 'filter_translations'),
        get_string('languagepair', 'filter_translations'),
        get_string('entries', 'filter_translations'),
        get_string('status', 'filter_translations'),
        get_string('actions'),
    ];
    foreach ($glossarygroups as $group) {
        $scope = empty($group->courseid) ? get_string('glossaryscope_global', 'filter_translations') :
            ($courses[$group->courseid] ?? (string)$group->courseid);
        $statuslabel = $syncstatuses[$group->syncstatus] ?? $group->syncstatus;
        $statusclass = empty($group->pending) && (int)$group->syncstatus === glossary_sync::STATUS_SYNCED ?
            'lh-plugin-tag lh-plugin-tag--active' : 'lh-plugin-tag lh-plugin-tag--warning';
        $syncurl = new moodle_url('/filter/translations/manageglossarysync.php', [
            'sync' => 'single',
            'courseid' => empty($group->courseid) ? 0 : $group->courseid,
            'sourcelanguage' => $group->sourcelanguage,
            'targetlanguage' => $group->targetlanguage,
            'sesskey' => sesskey(),
        ]);
        $languagepair = $languagecode($group->sourcelanguage) . ' -> ' . $languagecode($group->targetlanguage);
        $synclabel = get_string('sync', 'filter_translations') . ': ' . $scope . ', ' . $languagepair;
        $grouptable->data[] = [
            s($scope),
            html_writer::span($languagecode($group->sourcelanguage), 'lh-plugin-tag') .
                html_writer::span(' -> ', 'filter-translations-language-pair-separator') .
                html_writer::span($languagecode($group->targetlanguage), 'lh-plugin-tag'),
            (int)$group->entrycount,
            html_writer::span($statuslabel, $statusclass),
            html_writer::div(
                $iconaction($syncurl, $synclabel, 'fa-refresh', 'lh-icon-action--primary'),
                'filter-translations-table-actions'
            ),
        ];
    }
    echo html_writer::table($grouptable);
}
echo html_writer::end_div();
echo html_writer::end_tag('section');

echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-workbench-card']);
echo html_writer::tag('div',
    html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-book', 'aria-hidden' => 'true']),
        'lh-plugin-card__icon lh-plugin-card__icon--generic') .
    html_writer::tag('div',
        html_writer::tag('h2', get_string('manageglossary', 'filter_translations'),
            ['class' => 'lh-plugin-card__title']),
        ['class' => 'lh-plugin-card__meta']
    ) .
    html_writer::tag('div', implode('', $actions), ['class' => 'lh-plugin-card__actions filter-translations-card-header-actions']),
    ['class' => 'lh-plugin-card__top']
);
echo html_writer::start_div('lh-plugin-card__body filter-translations-table-card');
$table->out(100, true);
echo html_writer::end_div();
echo html_writer::end_tag('section');
shell::close();
echo $OUTPUT->footer();
