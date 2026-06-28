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
 * Ensures recommended course custom fields exist.
 *
 * @package filter_translations
 */

use filter_translations\course_customfields;
use filter_translations\output\shell;
use local_lernhive\output\plugin_page;

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/course:configurecustomfields', $context);
require_sesskey();

$url = new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('setupcoursefields', 'filter_translations'));
$PAGE->set_heading('');
$PAGE->set_pagelayout('standard');
shell::require_css();

$messages = course_customfields::ensure();

echo $OUTPUT->header();
shell::open(get_string('setupcoursefields', 'filter_translations'),
    get_string('coursecontrol_desc', 'filter_translations'), plugin_page::MODIFIER_READING);
echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-wizard-card']);
echo html_writer::tag('div',
    html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-sliders-h', 'aria-hidden' => 'true']),
        'lh-plugin-card__icon lh-plugin-card__icon--generic') .
    html_writer::tag('div',
        html_writer::tag('h2', get_string('setupcoursefields', 'filter_translations'),
            ['class' => 'lh-plugin-card__title']),
        ['class' => 'lh-plugin-card__meta']
    ),
    ['class' => 'lh-plugin-card__top']
);
echo html_writer::start_div('lh-plugin-card__body');
foreach ($messages as $message) {
    echo $OUTPUT->notification($message, \core\output\notification::NOTIFY_SUCCESS);
}
echo html_writer::div(
    html_writer::link(new moodle_url('/filter/translations/index.php'), get_string('pluginsetup', 'filter_translations'),
        ['class' => 'lh-btn-open']) .
    html_writer::link(new moodle_url('/admin/settings.php', ['section' => 'filtersettingtranslations']),
        get_string('pluginsettings', 'filter_translations'), ['class' => 'lh-btn-outline']),
    'filter-translations-wizard-actions'
);
echo html_writer::end_div();
echo html_writer::end_tag('section');
shell::close();
echo $OUTPUT->footer();
