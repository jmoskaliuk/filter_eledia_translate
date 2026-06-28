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
use local_lernhive\output\plugin_page;
use local_lernhive\output\plugin_shell;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filterlib.php');

$context = context_system::instance();
require_login();
require_capability('filter/translations:edittranslations', $context);

$canconfig = has_capability('moodle/site:config', $context);
$cansetupcoursefields = has_capability('moodle/course:configurecustomfields', $context);

// Render inside the LernHive plugin shell (standard layout, no admin tree
// chrome) for a consistent look with the rest of the suite.
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url(new moodle_url('/filter/translations/index.php'));
$PAGE->set_title(get_string('pluginsetup', 'filter_translations'));
$PAGE->set_heading('');
$PAGE->requires->css(new moodle_url('/local/lernhive/styles.css'));

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
$onboardingurl = new moodle_url('/filter/translations/onboarding.php');
$filtermanageurl = new moodle_url('/admin/filters.php');
$scheduledtasksurl = new moodle_url('/admin/tool/task/scheduledtasks.php');
$setupcoursefieldsurl = new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]);
$deepltesturl = new moodle_url('/filter/translations/testdeepl.php', ['sesskey' => sesskey()]);

$actions = [
    [
        'title' => get_string('managetranslations', 'filter_translations'),
        'description' => get_string('dashboardtranslations_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/managetranslations.php'),
        'icon' => 'fa-language',
        'modifier' => 'lh-icon-action--primary',
        'show' => true,
    ],
    [
        'title' => get_string('managetranslationissues', 'filter_translations'),
        'description' => get_string('dashboardissues_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/managetranslationissues.php'),
        'icon' => 'fa-exclamation-triangle',
        'modifier' => '',
        'show' => true,
    ],
    [
        'title' => get_string('manageglossary', 'filter_translations'),
        'description' => get_string('dashboardglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/manageglossary.php'),
        'icon' => 'fa-book',
        'modifier' => 'lh-icon-action--primary',
        'show' => true,
    ],
    [
        'title' => get_string('deeplglossarysync', 'filter_translations'),
        'description' => get_string('dashboardsync_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/manageglossarysync.php'),
        'icon' => 'fa-refresh',
        'modifier' => '',
        'show' => true,
    ],
    [
        'title' => get_string('createglossaryentry', 'filter_translations'),
        'description' => get_string('dashboardcreateglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/editglossaryentry.php',
            ['returnurl' => (new moodle_url('/filter/translations/index.php'))->out(false)]),
        'icon' => 'fa-plus',
        'modifier' => '',
        'show' => true,
    ],
    [
        'title' => get_string('importglossary', 'filter_translations'),
        'description' => get_string('dashboardimportglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/glossaryimport.php'),
        'icon' => 'fa-upload',
        'modifier' => '',
        'show' => has_capability('filter/translations:bulkimporttranslations', $context),
    ],
    [
        'title' => get_string('exportglossary', 'filter_translations'),
        'description' => get_string('dashboardexportglossary_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/glossaryexport.php'),
        'icon' => 'fa-download',
        'modifier' => '',
        'show' => has_capability('filter/translations:exporttranslations', $context),
    ],
    [
        'title' => get_string('importtranslations', 'filter_translations'),
        'description' => get_string('dashboardimport_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/import.php'),
        'icon' => 'fa-upload',
        'modifier' => '',
        'show' => has_capability('filter/translations:bulkimporttranslations', $context),
    ],
    [
        'title' => get_string('exporttranslations', 'filter_translations'),
        'description' => get_string('dashboardexport_desc', 'filter_translations'),
        'url' => new moodle_url('/filter/translations/export.php'),
        'icon' => 'fa-download',
        'modifier' => '',
        'show' => has_capability('filter/translations:exporttranslations', $context),
    ],
];

$headeractions = [];
if ($canconfig) {
    $headeractions[] = [
        'url' => $settingsurl->out(false),
        'label' => get_string('pluginsettings', 'filter_translations'),
        'faicon' => 'fa-gear',
        'modifierclass' => '',
    ];
    $headeractions[] = [
        'url' => $filtermanageurl->out(false),
        'label' => get_string('managefilters'),
        'faicon' => 'fa-filter',
        'modifierclass' => '',
    ];
    $headeractions[] = [
        'url' => $scheduledtasksurl->out(false),
        'label' => get_string('scheduledtasksheading', 'filter_translations'),
        'faicon' => 'fa-clock',
        'modifierclass' => '',
    ];
}
if ($cansetupcoursefields) {
    $headeractions[] = [
        'url' => $setupcoursefieldsurl->out(false),
        'label' => get_string('setupcoursefields', 'filter_translations'),
        'faicon' => 'fa-sliders-h',
        'modifierclass' => '',
    ];
}
$shellheader = [
    'name' => get_string('pluginname', 'filter_translations'),
    'tagline' => get_string('shell_tagline', 'filter_translations'),
    'subtitle' => get_string('pluginsetup_desc', 'filter_translations'),
    'ctas' => $canconfig ? [
        [
            'modifier' => 'open',
            'url' => $onboardingurl->out(false),
            'label' => get_string('onboardingtitle', 'filter_translations'),
            'fa' => 'fa-check-square',
        ],
    ] : [],
    'createurl' => (new moodle_url('/filter/translations/editglossaryentry.php',
        ['returnurl' => (new moodle_url('/filter/translations/index.php'))->out(false)]))->out(false),
    'createlabel' => get_string('createglossaryentry', 'filter_translations'),
    'headeractionicons' => $headeractions,
    'hasstats' => true,
    'stats' => [
        [
            'faicon' => 'fa-language',
            'value' => s($translationcount),
            'label' => get_string('translations', 'filter_translations'),
        ],
        [
            'faicon' => 'fa-exclamation-triangle',
            'value' => s($issuecount),
            'label' => get_string('translationissues', 'filter_translations'),
        ],
        [
            'faicon' => 'fa-book',
            'value' => s($glossarycount),
            'label' => get_string('manageglossary', 'filter_translations'),
        ],
        [
            'faicon' => 'fa-refresh',
            'value' => s(count($pendingsyncgroups)),
            'label' => get_string('dashboardpendingsyncgroups', 'filter_translations'),
        ],
    ],
];

echo $OUTPUT->header();
plugin_page::open($shellheader, plugin_page::MODIFIER_WIDE);
plugin_shell::content_open('lh-plugin-content-area filter-translations-dashboard');

$rendericon = static function(string $icon): string {
    return html_writer::tag('i', '', ['class' => 'fa ' . $icon, 'aria-hidden' => 'true']);
};
$renderaction = static function(moodle_url $url, string $label, string $icon, string $modifier = '') use ($rendericon): string {
    return html_writer::link($url,
        $rendericon($icon) . html_writer::span($label, 'sr-only'),
        [
            'class' => trim('lh-icon-action ' . $modifier),
            'aria-label' => $label,
            'title' => $label,
        ]
    );
};
$renderkv = static function(array $rows): string {
    $html = html_writer::start_tag('dl', ['class' => 'filter-translations-kv']);
    foreach ($rows as $row) {
        $html .= html_writer::tag('div',
            html_writer::tag('dt', $row[0]) .
            html_writer::tag('dd', $row[1]),
            ['class' => 'filter-translations-kv__row']
        );
    }
    $html .= html_writer::end_tag('dl');
    return $html;
};

$configrows = [
    [get_string('filtername', 'filter_translations'), $filterenabled ? $statusyes : $statusno],
    [get_string('deepl_enable', 'filter_translations'), !empty($config->deepl_enable) ? $statusyes : $statusno],
    [get_string('deepl_sourcelang', 'filter_translations'), s($config->deepl_sourcelang ?? '')],
    [get_string('deepl_glossaryid', 'filter_translations'), s($config->deepl_glossaryid ?? '')],
    [get_string('coursecontrolsource', 'filter_translations'), s($config->coursecontrolsource ?? 'tags')],
    [get_string('logmissing', 'filter_translations'), !empty($config->logmissing) ? $statusyes : $statusno],
    [get_string('logstale', 'filter_translations'), !empty($config->logstale) ? $statusyes : $statusno],
];

$statusrows = [];
foreach ($issueoptions as $status => $label) {
    $statusrows[] = [$label, s($issuecounts[$status] ?? 0)];
}
foreach ($glossarystatusoptions as $status => $label) {
    $statusrows[] = [$label, s($glossarystatuscounts[$status] ?? 0)];
}

echo html_writer::start_div('lh-plugin-grid lh-plugin-grid--cols-2 filter-translations-overview');
echo html_writer::tag('section',
    html_writer::tag('div',
        html_writer::span($rendericon('fa-sliders'), 'lh-plugin-card__icon lh-plugin-card__icon--generic') .
        html_writer::tag('div',
            html_writer::tag('div', get_string('dashboardconfiguration', 'filter_translations'),
                ['class' => 'lh-plugin-card__title']),
            ['class' => 'lh-plugin-card__meta']
        ) .
        ($canconfig ? html_writer::tag('div',
            $renderaction($deepltesturl, get_string('deepltest', 'filter_translations'), 'fa-flask'),
            ['class' => 'lh-plugin-card__actions']
        ) : ''),
        ['class' => 'lh-plugin-card__top']
    ) .
    html_writer::tag('div', $renderkv($configrows), ['class' => 'lh-plugin-card__body']),
    ['class' => 'lh-plugin-card filter-translations-panel']
);
echo html_writer::tag('section',
    html_writer::tag('div',
        html_writer::span($rendericon('fa-chart-bar'), 'lh-plugin-card__icon lh-plugin-card__icon--generic') .
        html_writer::tag('div',
            html_writer::tag('div', get_string('dashboardstatus', 'filter_translations'),
                ['class' => 'lh-plugin-card__title']),
            ['class' => 'lh-plugin-card__meta']
        ),
        ['class' => 'lh-plugin-card__top']
    ) .
    html_writer::tag('div', $renderkv($statusrows), ['class' => 'lh-plugin-card__body']),
    ['class' => 'lh-plugin-card filter-translations-panel']
);
echo html_writer::end_div();

echo html_writer::tag('h2', get_string('dashboardworkflows', 'filter_translations'), [
    'class' => 'filter-translations-section-title',
]);
echo html_writer::start_div('lh-plugin-grid lh-plugin-grid--cols-3 filter-translations-workflows');
foreach ($actions as $action) {
    if (empty($action['show'])) {
        continue;
    }
    echo html_writer::tag('section',
        html_writer::tag('div',
            html_writer::span($rendericon($action['icon']), 'lh-plugin-card__icon lh-plugin-card__icon--generic') .
            html_writer::tag('div',
                html_writer::tag('h3', $action['title'], ['class' => 'lh-plugin-card__title']),
                ['class' => 'lh-plugin-card__meta']
            ) .
            html_writer::tag('div',
                $renderaction($action['url'], $action['title'], 'fa-arrow-right', $action['modifier']),
                ['class' => 'lh-plugin-card__actions']
            ),
            ['class' => 'lh-plugin-card__top']
        ) .
        html_writer::tag('p', $action['description'], ['class' => 'lh-plugin-card__body']),
        ['class' => 'lh-plugin-card filter-translations-workflow-card']
    );
}
echo html_writer::end_div();

plugin_shell::content_close();
plugin_page::close();
echo $OUTPUT->footer();
