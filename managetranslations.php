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
 */

use filter_translations\managetranslations_filterform;
use filter_translations\managetranslations_table;
use filter_translations\output\shell;

require_once(dirname(__FILE__) . '/../../config.php');

$rawtext = optional_param('rawtext', '', PARAM_TEXT);
$substitutetext = optional_param('substitutetext', '', PARAM_TEXT);
$targetlanguage = optional_param('targetlanguage', current_language(), PARAM_TEXT);
$hash = optional_param('hash', '', PARAM_TEXT);
$usermodified = optional_param('usermodified', -1, PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$context = context_system::instance();

require_login();

require_capability('filter/translations:edittranslations', $context);

$PAGE->set_context($context);

$title = get_string('managetranslations', 'filter_translations');
$baseurl = new moodle_url('/filter/translations/managetranslations.php');

$form = new managetranslations_filterform();
$formdata = $form->get_data();

if ($formdata) {
    $urlparams = [
        'rawtext' => $rawtext,
        'substitutetext' => $substitutetext,
        'targetlanguage' => $targetlanguage,
        'hash' => $hash,
        'usermodified' => $usermodified,
    ];
    $baseurl->params($urlparams);
    redirect($baseurl);
}

$data = new stdClass();
$data->rawtext = $rawtext;
$data->substitutetext = $substitutetext;
$data->targetlanguage = $targetlanguage;
$data->hash = $hash;

if ($usermodified > 0) {
    $data->usermodified = $usermodified;
}

$data->tsort = optional_param('tsort', 'id', PARAM_ALPHA);
$form->set_data($data);

$baseurl->params((array)$data);
$baseurl->param('page', optional_param('page', '', PARAM_INT));
$PAGE->set_url($baseurl);

$table = new managetranslations_table($data, 'translationsname', $download);
$table->define_baseurl($baseurl);

if ($download) {
    $table->download($download);
} else {
    // Only print headers if not asked to download data.
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title($title);
    $PAGE->set_heading('');
    shell::require_css();
    echo $OUTPUT->header();
    shell::open($title, get_string('dashboardtranslations_desc', 'filter_translations'));

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

    echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-workbench-card']);
    echo html_writer::tag('div',
        html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-language', 'aria-hidden' => 'true']),
            'lh-plugin-card__icon lh-plugin-card__icon--generic') .
        html_writer::tag('div',
            html_writer::tag('h2', get_string('translations', 'filter_translations'),
                ['class' => 'lh-plugin-card__title']),
            ['class' => 'lh-plugin-card__meta']
        ) .
        html_writer::tag('div',
            html_writer::link(new moodle_url('/filter/translations/edittranslation.php', ['returnurl' => $PAGE->url]),
                html_writer::tag('i', '', ['class' => 'fa fa-plus', 'aria-hidden' => 'true']) .
                html_writer::span(get_string('createtranslation', 'filter_translations'), 'sr-only'),
                [
                    'class' => 'lh-icon-action lh-icon-action--primary',
                    'aria-label' => get_string('createtranslation', 'filter_translations'),
                    'title' => get_string('createtranslation', 'filter_translations'),
                ]
            ),
            ['class' => 'lh-plugin-card__actions filter-translations-card-header-actions']
        ),
        ['class' => 'lh-plugin-card__top']
    );
    echo html_writer::start_div('lh-plugin-card__body filter-translations-table-card');
    $table->out(100, true);
    echo html_writer::end_div();
    echo html_writer::end_tag('section');

    shell::close();
    echo $OUTPUT->footer();
}
