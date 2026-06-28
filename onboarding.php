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

use filter_translations\output\shell;
use local_lernhive\output\plugin_page;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filterlib.php');

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$step = optional_param('step', 'filter', PARAM_ALPHAEXT);
$action = optional_param('action', '', PARAM_ALPHAEXT);
$component = 'filter_translations';
$baseurl = new moodle_url('/filter/translations/onboarding.php');

$steps = [
    'filter' => get_string('onboardingstep_filter', $component),
    'course' => get_string('onboardingstep_course', $component),
    'provider' => get_string('onboardingstep_provider', $component),
    'logging' => get_string('onboardingstep_logging', $component),
    'glossary' => get_string('onboardingstep_glossary', $component),
    'finish' => get_string('onboardingstep_finish', $component),
];
if (!array_key_exists($step, $steps)) {
    $step = 'filter';
}

$config = get_config($component);
$saved = false;

$nextstep = function(string $current) use ($steps): string {
    $keys = array_keys($steps);
    $position = array_search($current, $keys, true);
    return $keys[min($position + 1, count($keys) - 1)];
};

$setting = function(string $name, $default = '') use ($config) {
    return $config->$name ?? $default;
};

$checkbox = function(string $name, bool $checked, string $label, string $help = ''): string {
    $attributes = [
        'type' => 'checkbox',
        'name' => $name,
        'value' => 1,
        'id' => 'id_' . $name,
    ];
    if ($checked) {
        $attributes['checked'] = 'checked';
    }

    $output = html_writer::start_div('form-check mb-2');
    $output .= html_writer::empty_tag('input', $attributes + ['class' => 'form-check-input']);
    $output .= html_writer::tag('label', $label, ['class' => 'form-check-label', 'for' => 'id_' . $name]);
    if ($help !== '') {
        $output .= html_writer::div($help, 'form-text text-muted');
    }
    $output .= html_writer::end_div();
    return $output;
};

$textinput = function(string $name, string $label, string $value, string $type = 'text', string $help = ''): string {
    $output = html_writer::start_div('form-group');
    $output .= html_writer::tag('label', $label, ['for' => 'id_' . $name]);
    $output .= html_writer::empty_tag('input', [
        'type' => $type,
        'name' => $name,
        'id' => 'id_' . $name,
        'value' => $value,
        'class' => 'form-control',
    ]);
    if ($help !== '') {
        $output .= html_writer::div($help, 'form-text text-muted');
    }
    $output .= html_writer::end_div();
    return $output;
};

$textarea = function(string $name, string $label, string $value, string $help = ''): string {
    $output = html_writer::start_div('form-group');
    $output .= html_writer::tag('label', $label, ['for' => 'id_' . $name]);
    $output .= html_writer::tag('textarea', s($value), [
        'name' => $name,
        'id' => 'id_' . $name,
        'class' => 'form-control',
        'rows' => 5,
    ]);
    if ($help !== '') {
        $output .= html_writer::div($help, 'form-text text-muted');
    }
    $output .= html_writer::end_div();
    return $output;
};

if ($action === 'save' && confirm_sesskey()) {
    switch ($step) {
        case 'filter':
            $enabled = optional_param('filterenabled', 0, PARAM_BOOL);
            $headings = optional_param('filterheadings', 0, PARAM_BOOL);
            filter_set_global_state('translations', $enabled ? TEXTFILTER_ON : TEXTFILTER_OFF);
            filter_set_applies_to_strings('translations', $headings);
            reset_text_filters_cache();
            core_plugin_manager::reset_caches();
            break;

        case 'course':
            $coursecontrolsource = required_param('coursecontrolsource', PARAM_RAW_TRIMMED);
            if (!in_array($coursecontrolsource, ['tags', 'customfields', 'customfields_fallback_tags'], true)) {
                $coursecontrolsource = \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE;
            }
            set_config('coursecontrolsource', $coursecontrolsource, $component);
            set_config('coursetagenabled', required_param('coursetagenabled', PARAM_ALPHANUMEXT), $component);
            set_config('coursefieldenabled', required_param('coursefieldenabled', PARAM_ALPHANUMEXT), $component);
            set_config('coursefieldlanguages', required_param('coursefieldlanguages', PARAM_ALPHANUMEXT), $component);
            break;

        case 'provider':
            set_config('languagestringreverse_enable', optional_param('languagestringreverse_enable', 0, PARAM_BOOL), $component);
            set_config('deepl_enable', optional_param('deepl_enable', 0, PARAM_BOOL), $component);
            set_config('deepl_backoffonerror', optional_param('deepl_backoffonerror', 0, PARAM_BOOL), $component);
            set_config('deepl_apiendpoint', required_param('deepl_apiendpoint', PARAM_URL), $component);
            $apikey = optional_param('deepl_apikey', '', PARAM_RAW_TRIMMED);
            if ($apikey !== '') {
                set_config('deepl_apikey', $apikey, $component);
            }
            set_config('deepl_sourcelang', strtoupper(optional_param('deepl_sourcelang', '', PARAM_ALPHANUMEXT)), $component);
            set_config('deepl_taghandlinghtml', optional_param('deepl_taghandlinghtml', 0, PARAM_BOOL), $component);
            set_config('deepl_glossaryid', optional_param('deepl_glossaryid', '', PARAM_RAW_TRIMMED), $component);
            break;

        case 'logging':
            set_config('logmissing', optional_param('logmissing', 0, PARAM_BOOL), $component);
            set_config('logstale', optional_param('logstale', 0, PARAM_BOOL), $component);
            set_config('loghistory', optional_param('loghistory', 0, PARAM_BOOL), $component);
            set_config('logdebounce', optional_param('logdebounce', DAYSECS, PARAM_INT), $component);
            set_config('untranslatedpages', optional_param('untranslatedpages', '', PARAM_RAW_TRIMMED), $component);
            break;
    }

    $config = get_config($component);
    redirect(new moodle_url($baseurl, ['step' => $nextstep($step), 'saved' => 1]));
}

$saved = optional_param('saved', 0, PARAM_BOOL);
$PAGE->set_url(new moodle_url($baseurl, ['step' => $step]));
$PAGE->set_title(get_string('onboardingtitle', $component));
$PAGE->set_heading('');

$PAGE->requires->css(new moodle_url('/local/lernhive/styles.css'));
echo $OUTPUT->header();
shell::open(get_string('onboardingtitle', $component),
    get_string('pluginsetup_desc', $component), plugin_page::MODIFIER_READING);
echo html_writer::tag('p', get_string('onboardingintro', $component), ['class' => 'lead']);
if ($saved) {
    echo $OUTPUT->notification(get_string('changessaved'), \core\output\notification::NOTIFY_SUCCESS);
}

echo html_writer::start_div('mb-4 d-flex flex-wrap');
foreach ($steps as $key => $label) {
    $class = $key === $step ? 'btn btn-primary mr-2 mb-2' : 'btn btn-secondary mr-2 mb-2';
    echo html_writer::link(new moodle_url($baseurl, ['step' => $key]), $label, ['class' => $class]);
}
echo html_writer::end_div();

echo html_writer::start_div('card');
echo html_writer::tag('h2', $steps[$step], ['class' => 'h4 card-header']);
echo html_writer::start_div('card-body');

$formstart = function() use ($baseurl, $step): string {
    $actionurl = new moodle_url($baseurl, ['step' => $step]);
    return html_writer::start_tag('form', ['method' => 'post', 'action' => $actionurl->out(false)]) .
        html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]) .
        html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save']);
};
$formend = function() use ($component): string {
    return html_writer::tag('button', get_string('saveandcontinue', $component), ['type' => 'submit', 'class' => 'btn btn-primary']) .
        html_writer::end_tag('form');
};

switch ($step) {
    case 'filter':
        echo html_writer::tag('p', get_string('onboardingfilter_desc', $component));
        echo $formstart();
        echo $checkbox('filterenabled', filter_get_active_state('translations') === TEXTFILTER_ON,
            get_string('onboardingfilter_enable', $component));
        echo $checkbox('filterheadings', array_key_exists('translations', filter_get_string_filters()),
            get_string('onboardingfilter_headings', $component),
            get_string('onboardingfilter_headings_desc', $component));
        echo $formend();
        break;

    case 'course':
        echo html_writer::tag('p', get_string('onboardingcourse_desc', $component));
        echo $formstart();
        $controloptions = [
            'tags' => get_string('coursecontrolsource_tags', $component),
            'customfields' => get_string('coursecontrolsource_customfields', $component),
            'customfields_fallback_tags' => get_string('coursecontrolsource_customfields_fallback_tags', $component),
        ];
        echo html_writer::start_div('form-group');
        echo html_writer::tag('label', get_string('coursecontrolsource', $component), ['for' => 'id_coursecontrolsource']);
        echo html_writer::select($controloptions, 'coursecontrolsource',
            $setting('coursecontrolsource', \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE), false,
            ['id' => 'id_coursecontrolsource', 'class' => 'form-control']);
        echo html_writer::end_div();
        echo $textinput('coursetagenabled', get_string('coursetagenabled', $component),
            $setting('coursetagenabled', 'deepl'));
        echo $textinput('coursefieldenabled', get_string('coursefieldenabled', $component),
            $setting('coursefieldenabled', 'eledia_translate_enabled'));
        echo $textinput('coursefieldlanguages', get_string('coursefieldlanguages', $component),
            $setting('coursefieldlanguages', 'eledia_translate_languages'));
        echo html_writer::start_div('mb-3');
        echo html_writer::link(new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]),
            get_string('setupcoursefields', $component), ['class' => 'btn btn-secondary']);
        echo html_writer::end_div();
        echo $formend();
        break;

    case 'provider':
        echo html_writer::tag('p', get_string('onboardingprovider_desc', $component));
        echo $formstart();
        echo $checkbox('languagestringreverse_enable', !empty($setting('languagestringreverse_enable', false)),
            get_string('languagestringreverse_enable', $component));
        echo $checkbox('deepl_enable', !empty($setting('deepl_enable', false)), get_string('deepl_enable', $component));
        echo $checkbox('deepl_backoffonerror', !empty($setting('deepl_backoffonerror', false)),
            get_string('deepl_backoffonerror', $component));
        echo $textinput('deepl_apiendpoint', get_string('deepl_apiendpoint', $component),
            $setting('deepl_apiendpoint', 'https://api-free.deepl.com/v2/translate'), 'url');
        echo $textinput('deepl_apikey', get_string('deepl_apikey', $component), '', 'password',
            get_string('onboardingdeeplkey_desc', $component));
        echo $textinput('deepl_sourcelang', get_string('deepl_sourcelang', $component),
            $setting('deepl_sourcelang', ''), 'text', get_string('deepl_sourcelang_desc', $component));
        echo $checkbox('deepl_taghandlinghtml', !empty($setting('deepl_taghandlinghtml', true)),
            get_string('deepl_taghandlinghtml', $component), get_string('deepl_taghandlinghtml_desc', $component));
        echo $textinput('deepl_glossaryid', get_string('deepl_glossaryid', $component),
            $setting('deepl_glossaryid', ''), 'text', get_string('deepl_glossaryid_desc', $component));
        echo html_writer::start_div('mb-3');
        echo html_writer::link(new moodle_url('/filter/translations/testdeepl.php', ['sesskey' => sesskey()]),
            get_string('deepltest', $component), ['class' => 'btn btn-secondary']);
        echo html_writer::end_div();
        echo $formend();
        break;

    case 'logging':
        echo html_writer::tag('p', get_string('onboardinglogging_desc', $component));
        echo $formstart();
        echo $checkbox('logmissing', !empty($setting('logmissing', false)), get_string('logmissing', $component));
        echo $checkbox('logstale', !empty($setting('logstale', false)), get_string('logstale', $component));
        echo $checkbox('loghistory', !empty($setting('loghistory', false)), get_string('loghistory', $component));
        echo $textinput('logdebounce', get_string('logdebounce', $component), (string)$setting('logdebounce', DAYSECS),
            'number');
        echo $textarea('untranslatedpages', get_string('untranslatedpages', $component),
            $setting('untranslatedpages', '/blocks/configurable_reports/viewreport.php'),
            get_string('untranslatedpages_desc', $component));
        echo $formend();
        break;

    case 'glossary':
        echo html_writer::tag('p', get_string('onboardingglossary_desc', $component));
        echo html_writer::start_div('d-flex flex-wrap');
        echo html_writer::link(new moodle_url('/filter/translations/manageglossary.php'),
            get_string('manageglossary', $component), ['class' => 'btn btn-primary mr-2 mb-2']);
        echo html_writer::link(new moodle_url('/filter/translations/glossaryimport.php'),
            get_string('importglossary', $component), ['class' => 'btn btn-secondary mr-2 mb-2']);
        echo html_writer::link(new moodle_url('/filter/translations/glossaryexport.php'),
            get_string('exportglossary', $component), ['class' => 'btn btn-secondary mr-2 mb-2']);
        echo html_writer::link(new moodle_url('/filter/translations/manageglossarysync.php'),
            get_string('deeplglossarysync', $component), ['class' => 'btn btn-secondary mr-2 mb-2']);
        echo html_writer::link(new moodle_url($baseurl, ['step' => 'finish', 'saved' => 1]),
            get_string('continue'), ['class' => 'btn btn-primary mr-2 mb-2']);
        echo html_writer::end_div();
        break;

    case 'finish':
        echo html_writer::tag('p', get_string('onboardingfinish_desc', $component));
        $checks = [
            get_string('onboardingcheck_filter', $component) => filter_get_active_state('translations') === TEXTFILTER_ON,
            get_string('onboardingcheck_headings', $component) => array_key_exists('translations', filter_get_string_filters()),
            get_string('onboardingcheck_course', $component) => !empty($setting('coursecontrolsource', '')),
            get_string('onboardingcheck_provider', $component) => !empty($setting('languagestringreverse_enable', false)) ||
                !empty($setting('deepl_enable', false)),
            get_string('onboardingcheck_deeplsource', $component) => empty($setting('deepl_glossaryid', '')) ||
                !empty($setting('deepl_sourcelang', '')),
        ];
        echo html_writer::start_tag('ul', ['class' => 'list-group mb-3']);
        foreach ($checks as $label => $ok) {
            $badge = $ok ? get_string('yes') : get_string('no');
            $class = $ok ? 'badge badge-success' : 'badge badge-warning';
            echo html_writer::tag('li',
                s($label) . html_writer::span($badge, $class . ' float-right'),
                ['class' => 'list-group-item']);
        }
        echo html_writer::end_tag('ul');
        echo html_writer::link(new moodle_url('/filter/translations/index.php'), get_string('pluginsetup', $component),
            ['class' => 'btn btn-primary']);
        break;
}

echo html_writer::end_div();
echo html_writer::end_div();
shell::close();
echo $OUTPUT->footer();
