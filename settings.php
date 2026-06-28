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

/**
 * @package filter_translations
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 * @copyright 2023, Tina John <johnt.22.tijo@gmail.com> 
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('filtersettings', new admin_externalpage('filtertranslationsdashboard',
        get_string('pluginsetup', 'filter_translations'),
        $CFG->wwwroot . '/filter/translations/index.php',
        'filter/translations:edittranslations'));

    $ADMIN->add('filtersettings', new admin_externalpage('filtertranslationsonboarding',
        get_string('onboardingtitle', 'filter_translations'),
        $CFG->wwwroot . '/filter/translations/onboarding.php',
        'moodle/site:config'));
}

if ($ADMIN->fulltree) {
    $listoftranslations = get_string_manager()->get_list_of_translations(true);

    $settings->add(new admin_setting_heading('pluginsetup', '',
        html_writer::link(new moodle_url('/filter/translations/index.php'),
            get_string('pluginsetup', 'filter_translations'), ['class' => "btn btn-primary"])));

    $settings->add(new admin_setting_heading('onboarding', '',
        html_writer::link(new moodle_url('/filter/translations/onboarding.php'),
            get_string('onboardingtitle', 'filter_translations'), ['class' => "btn btn-primary"])));

    $settings->add(new admin_setting_heading('managetranslations', '',
        html_writer::link(new moodle_url('/filter/translations/managetranslations.php'),
            get_string('managetranslations', 'filter_translations'), ['class' => "btn btn-primary"])));

    $settings->add(new admin_setting_heading('managetranslationissues', '',
        html_writer::link(new moodle_url('/filter/translations/managetranslationissues.php'),
            get_string('managetranslationissues', 'filter_translations'), ['class' => "btn btn-primary"])));

    $settings->add(new admin_setting_heading('manageglossary', '',
        html_writer::link(new moodle_url('/filter/translations/manageglossary.php'),
            get_string('manageglossary', 'filter_translations'), ['class' => "btn btn-primary"])));

    $settings->add(new admin_setting_heading('deeplglossarysync', '',
        html_writer::link(new moodle_url('/filter/translations/manageglossarysync.php'),
            get_string('deeplglossarysync', 'filter_translations'), ['class' => "btn btn-secondary"])));

    $settings->add(new admin_setting_heading('performance', get_string('performance', 'admin'), ''));

    $settings->add(new admin_setting_configcheckbox('filter_translations/showperfdata',
        get_string('showperfdata', 'filter_translations'), '', false));

    $options = [];
    foreach ([cache_store::MODE_REQUEST, cache_store::MODE_SESSION, cache_store::MODE_APPLICATION] as $mode) {
        $options[$mode] = get_string('mode_' . $mode, 'cache');
    }
    $settings->add(new admin_setting_configselect('filter_translations/cachingmode',
        get_string('cachingmode', 'filter_translations'), get_string('cachingmode_desc', 'filter_translations'),
        cache_store::MODE_REQUEST, $options));

    $settings->add(new admin_setting_configtextarea('filter_translations/untranslatedpages',
        new lang_string('untranslatedpages', 'filter_translations'),
        new lang_string('untranslatedpages_desc', 'filter_translations'),
        '/blocks/configurable_reports/viewreport.php')
    );

    $settings->add(new admin_setting_configmultiselect('filter_translations/excludelang',
        get_string('excludelang', 'filter_translations'),
        get_string('excludelang_desc', 'filter_translations'), [],
        $listoftranslations));

    $settings->add(new admin_setting_heading('coursecontrol',
        get_string('coursecontrol', 'filter_translations'),
        get_string('coursecontrol_desc', 'filter_translations')));

    $settings->add(new admin_setting_heading('setupcoursefields', '',
        html_writer::link(new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]),
            get_string('setupcoursefields', 'filter_translations'), ['class' => "btn btn-secondary"])));

    $controloptions = [
        'tags' => get_string('coursecontrolsource_tags', 'filter_translations'),
        'customfields' => get_string('coursecontrolsource_customfields', 'filter_translations'),
        'customfields_fallback_tags' => get_string('coursecontrolsource_customfields_fallback_tags', 'filter_translations'),
    ];
    $settings->add(new admin_setting_configselect('filter_translations/coursecontrolsource',
        get_string('coursecontrolsource', 'filter_translations'),
        get_string('coursecontrolsource_desc', 'filter_translations'),
        \filter_translations\course_translation_policy::DEFAULT_CONTROL_SOURCE,
        $controloptions));

    $settings->add(new admin_setting_configtext('filter_translations/coursetagenabled',
        get_string('coursetagenabled', 'filter_translations'),
        get_string('coursetagenabled_desc', 'filter_translations'),
        'deepl',
        PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('filter_translations/coursefieldenabled',
        get_string('coursefieldenabled', 'filter_translations'),
        get_string('coursefieldenabled_desc', 'filter_translations'),
        'eledia_translate_enabled',
        PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_configtext('filter_translations/coursefieldlanguages',
        get_string('coursefieldlanguages', 'filter_translations'),
        get_string('coursefieldlanguages_desc', 'filter_translations'),
        'eledia_translate_languages',
        PARAM_ALPHANUMEXT));

    $settings->add(new admin_setting_heading('logging', get_string('logging', 'filter_translations'), ''));

    $settings->add(new admin_setting_configmultiselect('filter_translations/logexcludelang',
        get_string('logexcludelang', 'filter_translations'),
        get_string('logexcludelang_desc', 'filter_translations'), [],
        $listoftranslations));

    $settings->add(new admin_setting_configcheckbox('filter_translations/loghistory',
        get_string('loghistory', 'filter_translations'), '', false));

    $settings->add(new admin_setting_configcheckbox('filter_translations/logmissing',
        get_string('logmissing', 'filter_translations'), '', false));

    $settings->add(new admin_setting_configcheckbox('filter_translations/logstale',
        get_string('logstale', 'filter_translations'), '', false));

    $settings->add(new admin_setting_configduration('filter_translations/logdebounce',
        get_string('logdebounce', 'filter_translations'), '', DAYSECS));

    $settings->add(new admin_setting_heading('scheduledtasks', get_string('scheduledtasksheading', 'filter_translations'), ''));

    $settings->add(new admin_setting_configtextarea('filter_translations/columndefinition',
        new lang_string('columndefinition', 'filter_translations'),
        new lang_string('columndefinition_desc', 'filter_translations'),
        \filter_translations\column_definition::default_json())
    );

    $settings->add(new admin_setting_heading('languagestringreverseapi',
        get_string('languagestringreverse', 'filter_translations'), ''));

    $settings->add(new admin_setting_configcheckbox('filter_translations/languagestringreverse_enable',
        get_string('languagestringreverse_enable', 'filter_translations'), '', false));

    $settings->add(new admin_setting_heading('deepltranslateapi',
        get_string('deepltranslate', 'filter_translations'), ''));

    $settings->add(new admin_setting_heading('deepltest', '',
        html_writer::link(new moodle_url('/filter/translations/testdeepl.php', ['sesskey' => sesskey()]),
            get_string('deepltest', 'filter_translations'), ['class' => "btn btn-secondary"])));

    $settings->add(new admin_setting_configcheckbox('filter_translations/deepl_enable',
        get_string('deepl_enable', 'filter_translations'), '', false));

    $settings->add(new admin_setting_configcheckbox('filter_translations/deepl_backoffonerror',
        get_string('deepl_backoffonerror', 'filter_translations'), '', false));

    $settings->add(new admin_setting_configtext('filter_translations/deepl_apiendpoint',
        get_string('deepl_apiendpoint', 'filter_translations'), '', 'https://api-free.deepl.com/v2/translate',
        PARAM_URL));

    $settings->add(new admin_setting_configpasswordunmask('filter_translations/deepl_apikey',
        get_string('deepl_apikey', 'filter_translations'), '', ''));

    $settings->add(new admin_setting_configtext('filter_translations/deepl_sourcelang',
        get_string('deepl_sourcelang', 'filter_translations'), get_string('deepl_sourcelang_desc', 'filter_translations'),
        '', PARAM_ALPHANUMEXT, 10));

    $settings->add(new admin_setting_configcheckbox('filter_translations/deepl_taghandlinghtml',
        get_string('deepl_taghandlinghtml', 'filter_translations'), get_string('deepl_taghandlinghtml_desc', 'filter_translations'),
        true));

    $settings->add(new admin_setting_configtext('filter_translations/deepl_glossaryid',
        get_string('deepl_glossaryid', 'filter_translations'), get_string('deepl_glossaryid_desc', 'filter_translations'),
        '', PARAM_RAW_TRIMMED, 80));
    }
