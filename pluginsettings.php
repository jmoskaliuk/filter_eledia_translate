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

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filterlib.php');

$component = 'filter_translations';
$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);

$baseurl = new moodle_url('/filter/translations/pluginsettings.php');
$config = get_config($component);

$splitconfig = static function($value): array {
    if (is_array($value)) {
        return $value;
    }
    $value = trim((string)$value);
    if ($value === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $value)), static function($item): bool {
        return $item !== '';
    }));
};

$setting = static function(string $name, $default = '') use ($config) {
    return $config->$name ?? $default;
};

if (optional_param('action', '', PARAM_ALPHAEXT) === 'save' && confirm_sesskey()) {
    $listoftranslations = get_string_manager()->get_list_of_translations(true);
    $allowedlangs = array_keys($listoftranslations);

    $cleanlangs = static function(array $values) use ($allowedlangs): array {
        return array_values(array_intersect($values, $allowedlangs));
    };

    $cachemode = optional_param('cachingmode', cache_store::MODE_REQUEST, PARAM_INT);
    if (!in_array($cachemode, [cache_store::MODE_REQUEST, cache_store::MODE_SESSION, cache_store::MODE_APPLICATION], true)) {
        $cachemode = cache_store::MODE_REQUEST;
    }

    $coursecontrolsource = optional_param('coursecontrolsource',
        \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE, PARAM_RAW_TRIMMED);
    if (!in_array($coursecontrolsource, ['tags', 'customfields', 'customfields_fallback_tags'], true)) {
        $coursecontrolsource = \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE;
    }

    filter_set_global_state('translations', optional_param('filterenabled', 0, PARAM_BOOL) ? TEXTFILTER_ON : TEXTFILTER_OFF);
    filter_set_applies_to_strings('translations', optional_param('filterheadings', 0, PARAM_BOOL));
    reset_text_filters_cache();
    core_plugin_manager::reset_caches();

    set_config('showperfdata', optional_param('showperfdata', 0, PARAM_BOOL), $component);
    set_config('cachingmode', $cachemode, $component);
    set_config('untranslatedpages', optional_param('untranslatedpages', '', PARAM_RAW_TRIMMED), $component);
    set_config('excludelang', implode(',', $cleanlangs(optional_param_array('excludelang', [], PARAM_LANG))), $component);

    set_config('coursecontrolsource', $coursecontrolsource, $component);
    set_config('coursetagenabled', optional_param('coursetagenabled', 'deepl', PARAM_ALPHANUMEXT), $component);
    set_config('coursefieldenabled', optional_param('coursefieldenabled', 'eledia_translate_enabled', PARAM_ALPHANUMEXT),
        $component);
    set_config('coursefieldlanguages', optional_param('coursefieldlanguages', 'eledia_translate_languages',
        PARAM_ALPHANUMEXT), $component);

    set_config('logexcludelang', implode(',', $cleanlangs(optional_param_array('logexcludelang', [], PARAM_LANG))),
        $component);
    set_config('loghistory', optional_param('loghistory', 0, PARAM_BOOL), $component);
    set_config('logmissing', optional_param('logmissing', 0, PARAM_BOOL), $component);
    set_config('logstale', optional_param('logstale', 0, PARAM_BOOL), $component);
    set_config('logdebounce', optional_param('logdebounce', DAYSECS, PARAM_INT), $component);

    set_config('columndefinition', optional_param('columndefinition',
        \filter_translations\column_definition::default_json(), PARAM_RAW_TRIMMED), $component);

    set_config('languagestringreverse_enable', optional_param('languagestringreverse_enable', 0, PARAM_BOOL),
        $component);

    set_config('deepl_enable', optional_param('deepl_enable', 0, PARAM_BOOL), $component);
    set_config('deepl_backoffonerror', optional_param('deepl_backoffonerror', 0, PARAM_BOOL), $component);
    set_config('deepl_apiendpoint', optional_param('deepl_apiendpoint',
        'https://api-free.deepl.com/v2/translate', PARAM_URL), $component);
    $apikey = optional_param('deepl_apikey', '', PARAM_RAW_TRIMMED);
    if ($apikey !== '') {
        set_config('deepl_apikey', $apikey, $component);
    }
    set_config('deepl_sourcelang', strtoupper(optional_param('deepl_sourcelang', '', PARAM_ALPHANUMEXT)), $component);
    set_config('deepl_taghandlinghtml', optional_param('deepl_taghandlinghtml', 0, PARAM_BOOL), $component);
    set_config('deepl_glossaryid', optional_param('deepl_glossaryid', '', PARAM_RAW_TRIMMED), $component);

    redirect(new moodle_url($baseurl, ['saved' => 1]));
}

$config = get_config($component);
$listoftranslations = get_string_manager()->get_list_of_translations(true);
$saved = optional_param('saved', 0, PARAM_BOOL);

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url($baseurl);
$PAGE->set_title(get_string('navsetup', $component));
$PAGE->set_heading('');

shell::require_css();
echo $OUTPUT->header();
shell::open(get_string('navsetup', $component), get_string('pluginsetup_desc', $component), shell::MODIFIER_READING);
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $baseurl->out(false),
    'class' => 'filter-translations-settings-form']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'save']);

if ($saved) {
    echo $OUTPUT->notification(get_string('changessaved'), \core\output\notification::NOTIFY_SUCCESS);
}

$settingrow = static function(string $name, string $label, string $control, string $help = '') use ($component): string {
    return html_writer::tag('div',
        html_writer::tag('div',
            html_writer::tag('label', $label, ['for' => 'id_' . $name]) .
            html_writer::div($component . ' | ' . $name, 'filter-translations-setting-meta'),
            ['class' => 'filter-translations-setting-label']
        ) .
        html_writer::tag('div',
            $control . ($help !== '' ? html_writer::div($help, 'filter-translations-setting-help') : ''),
            ['class' => 'filter-translations-setting-control']
        ),
        ['class' => 'filter-translations-setting-row']
    );
};

$checkbox = static function(string $name, bool $checked, string $label, string $help = '') use ($settingrow): string {
    $attributes = ['type' => 'checkbox', 'name' => $name, 'value' => 1, 'id' => 'id_' . $name,
        'class' => 'form-check-input'];
    if ($checked) {
        $attributes['checked'] = 'checked';
    }
    return $settingrow($name, $label, html_writer::empty_tag('input', $attributes), $help);
};

$textinput = static function(
    string $name,
    string $label,
    string $value,
    string $type = 'text',
    string $help = ''
) use ($settingrow): string {
    return $settingrow($name, $label, html_writer::empty_tag('input', [
        'type' => $type,
        'name' => $name,
        'id' => 'id_' . $name,
        'value' => $value,
        'class' => 'form-control',
    ]), $help);
};

$textarea = static function(string $name, string $label, string $value, string $help = '', int $rows = 5) use ($settingrow): string {
    return $settingrow($name, $label, html_writer::tag('textarea', s($value), [
        'name' => $name,
        'id' => 'id_' . $name,
        'class' => 'form-control',
        'rows' => $rows,
    ]), $help);
};

$select = static function(
    string $name,
    string $label,
    array $options,
    $selected,
    string $help = '',
    bool $multiple = false
) use ($settingrow): string {
    $fieldname = $multiple ? $name . '[]' : $name;
    $attributes = ['id' => 'id_' . $name, 'class' => 'form-control'];
    if ($multiple) {
        $attributes['multiple'] = 'multiple';
        $attributes['size'] = 7;
    }
    return $settingrow($name, $label, html_writer::select($options, $fieldname, $selected, false, $attributes), $help);
};

$cacheoptions = [];
foreach ([cache_store::MODE_REQUEST, cache_store::MODE_SESSION, cache_store::MODE_APPLICATION] as $mode) {
    $cacheoptions[$mode] = get_string('mode_' . $mode, 'cache');
}
$controloptions = [
    'tags' => get_string('coursecontrolsource_tags', $component),
    'customfields' => get_string('coursecontrolsource_customfields', $component),
    'customfields_fallback_tags' => get_string('coursecontrolsource_customfields_fallback_tags', $component),
];

$sections = [
    'setup-filter' => get_string('onboardingstep_filter', $component),
    'setup-course' => get_string('onboardingstep_course', $component),
    'setup-provider' => get_string('onboardingstep_provider', $component),
    'setup-logging' => get_string('onboardingstep_logging', $component),
    'setup-advanced' => get_string('pluginsettings', $component),
];
$currentsection = optional_param('section', 'setup-filter', PARAM_ALPHAEXT);
if (!array_key_exists($currentsection, $sections)) {
    $currentsection = 'setup-filter';
}

echo html_writer::start_tag('nav', [
    'class' => 'filter-translations-setup-pills',
    'aria-label' => get_string('navsetup', $component),
]);
foreach ($sections as $id => $label) {
    $attributes = ['class' => 'filter-translations-setup-pill'];
    if ($id === $currentsection) {
        $attributes['aria-current'] = 'page';
    }
    echo html_writer::link(new moodle_url($baseurl, ['section' => $id], $id), $label, $attributes);
}
echo html_writer::end_tag('nav');

$settingsactions = static function() use ($component): string {
    $back = html_writer::link(new moodle_url('/filter/translations/index.php'),
        html_writer::tag('i', '', ['class' => 'fa fa-arrow-left', 'aria-hidden' => 'true']) .
        html_writer::span(get_string('backtooverview', $component)),
        ['class' => 'lh-btn-outline filter-translations-settings-mini-action']);
    $save = html_writer::tag('button',
        html_writer::tag('i', '', ['class' => 'fa fa-save', 'aria-hidden' => 'true']) .
        html_writer::span(get_string('savechanges')),
        ['type' => 'submit', 'class' => 'lh-btn-open filter-translations-settings-mini-action']);

    return html_writer::div($back . $save,
        'filter-translations-wizard-actions filter-translations-settings-submit');
};

$rendersection = static function(
    string $id,
    string $title,
    string $body,
    bool $ready,
    string $extraactions = ''
) use ($component): void {
    $statusclass = $ready ? 'lh-plugin-tag lh-plugin-tag--active' : 'lh-plugin-tag lh-plugin-tag--warning';
    $statuslabel = $ready ? get_string('settingsstatus_ready', $component) : get_string('settingsstatus_check', $component);
    echo html_writer::tag('div',
        html_writer::tag('h2', $title, ['class' => 'filter-translations-settings-heading']) .
        html_writer::span($statuslabel, $statusclass),
        ['class' => 'filter-translations-settings-heading-row']
    );
    echo html_writer::tag('section',
        html_writer::div($body, 'filter-translations-settings-card__body'),
        ['id' => $id, 'class' => 'filter-translations-settings-card filter-translations-wizard-card']
    );
    if ($extraactions !== '') {
        echo html_writer::div($extraactions,
            'filter-translations-wizard-actions filter-translations-settings-section-actions');
    }
};

$body = html_writer::tag('p', get_string('onboardingfilter_desc', $component),
    ['class' => 'filter-translations-settings-desc']);
$body .= $checkbox('filterenabled', filter_get_active_state('translations') === TEXTFILTER_ON,
    get_string('onboardingfilter_enable', $component));
$body .= $checkbox('filterheadings', array_key_exists('translations', filter_get_string_filters()),
    get_string('onboardingfilter_headings', $component), get_string('onboardingfilter_headings_desc', $component));
$rendersection('setup-filter', get_string('onboardingstep_filter', $component), $body,
    filter_get_active_state('translations') === TEXTFILTER_ON && array_key_exists('translations', filter_get_string_filters()));

$body = html_writer::tag('p', get_string('coursecontrol_desc', $component), ['class' => 'filter-translations-settings-desc']);
$body .= $select('coursecontrolsource', get_string('coursecontrolsource', $component), $controloptions,
    $setting('coursecontrolsource', \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE),
    get_string('coursecontrolsource_desc', $component));
$body .= $textinput('coursetagenabled', get_string('coursetagenabled', $component),
    $setting('coursetagenabled', 'deepl'), 'text', get_string('coursetagenabled_desc', $component));
$body .= $textinput('coursefieldenabled', get_string('coursefieldenabled', $component),
    $setting('coursefieldenabled', 'eledia_translate_enabled'), 'text', get_string('coursefieldenabled_desc', $component));
$body .= $textinput('coursefieldlanguages', get_string('coursefieldlanguages', $component),
    $setting('coursefieldlanguages', 'eledia_translate_languages'), 'text',
    get_string('coursefieldlanguages_desc', $component));
$rendersection('setup-course', get_string('onboardingstep_course', $component), $body,
    !empty($setting('coursecontrolsource', '')),
    html_writer::link(new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]),
        html_writer::tag('i', '', ['class' => 'fa fa-sliders-h', 'aria-hidden' => 'true']) .
        html_writer::span(get_string('setupcoursefields', $component)),
        ['class' => 'lh-btn-outline filter-translations-settings-mini-action']));

$body = $checkbox('languagestringreverse_enable', !empty($setting('languagestringreverse_enable', false)),
    get_string('languagestringreverse_enable', $component));
$body .= $checkbox('deepl_enable', !empty($setting('deepl_enable', false)), get_string('deepl_enable', $component));
$body .= $checkbox('deepl_backoffonerror', !empty($setting('deepl_backoffonerror', false)),
    get_string('deepl_backoffonerror', $component));
$body .= $textinput('deepl_apiendpoint', get_string('deepl_apiendpoint', $component),
    $setting('deepl_apiendpoint', 'https://api-free.deepl.com/v2/translate'), 'url');
$body .= $textinput('deepl_apikey', get_string('deepl_apikey', $component), '', 'password',
    get_string('onboardingdeeplkey_desc', $component));
$body .= $textinput('deepl_sourcelang', get_string('deepl_sourcelang', $component),
    $setting('deepl_sourcelang', ''), 'text', get_string('deepl_sourcelang_desc', $component));
$body .= $checkbox('deepl_taghandlinghtml', !empty($setting('deepl_taghandlinghtml', true)),
    get_string('deepl_taghandlinghtml', $component), get_string('deepl_taghandlinghtml_desc', $component));
$body .= $textinput('deepl_glossaryid', get_string('deepl_glossaryid', $component),
    $setting('deepl_glossaryid', ''), 'text', get_string('deepl_glossaryid_desc', $component));
$rendersection('setup-provider', get_string('onboardingstep_provider', $component), $body,
    (!empty($setting('languagestringreverse_enable', false)) || !empty($setting('deepl_enable', false))) &&
        (empty($setting('deepl_glossaryid', '')) || !empty($setting('deepl_sourcelang', ''))),
    html_writer::link(new moodle_url('/filter/translations/testdeepl.php', ['sesskey' => sesskey()]),
        html_writer::tag('i', '', ['class' => 'fa fa-plug', 'aria-hidden' => 'true']) .
        html_writer::span(get_string('deepltest', $component)),
        ['class' => 'lh-btn-outline filter-translations-settings-mini-action']));

$body = $select('logexcludelang', get_string('logexcludelang', $component), $listoftranslations,
    $splitconfig($setting('logexcludelang', '')), get_string('logexcludelang_desc', $component), true);
$body .= $checkbox('loghistory', !empty($setting('loghistory', false)), get_string('loghistory', $component));
$body .= $checkbox('logmissing', !empty($setting('logmissing', false)), get_string('logmissing', $component));
$body .= $checkbox('logstale', !empty($setting('logstale', false)), get_string('logstale', $component));
$body .= $textinput('logdebounce', get_string('logdebounce', $component), (string)$setting('logdebounce', DAYSECS), 'number');
$rendersection('setup-logging', get_string('onboardingstep_logging', $component), $body, true);

$body = $checkbox('showperfdata', !empty($setting('showperfdata', false)), get_string('showperfdata', $component));
$body .= $select('cachingmode', get_string('cachingmode', $component), $cacheoptions,
    $setting('cachingmode', cache_store::MODE_REQUEST), get_string('cachingmode_desc', $component));
$body .= $textarea('untranslatedpages', get_string('untranslatedpages', $component),
    $setting('untranslatedpages', '/blocks/configurable_reports/viewreport.php'),
    get_string('untranslatedpages_desc', $component));
$body .= $select('excludelang', get_string('excludelang', $component), $listoftranslations,
    $splitconfig($setting('excludelang', '')), get_string('excludelang_desc', $component), true);
$body .= $textarea('columndefinition', get_string('columndefinition', $component),
    $setting('columndefinition', \filter_translations\column_definition::default_json()),
    get_string('columndefinition_desc', $component), 10);
$rendersection('setup-advanced', get_string('pluginsettings', $component), $body, true);

echo $settingsactions();
echo html_writer::end_tag('form');
shell::close();
echo $OUTPUT->footer();
