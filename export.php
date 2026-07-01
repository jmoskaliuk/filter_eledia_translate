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
 * Export translations landing page
 *
 * @package    filter_translations
 * @copyright  2023 Rajneel Totaram <rajneel.totaram@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use filter_translations\translation;
use filter_translations\output\shell;

require(__DIR__ . '/../../config.php');

$courseid = optional_param('id', SITEID, PARAM_INT);
$targetlanguage = optional_param('targetlanguage', current_language(), PARAM_TEXT);

if ($courseid > 1) {
    if (!$course = $DB->get_record('course', ['id' => $courseid])) {
        throw new \moodle_exception('invalidcourseid');
    }
}

require_login();

$context = context_system::instance();
require_capability('filter/translations:exporttranslations', $context);

$url = new moodle_url('/filter/translations/export.php', ['id' => $courseid, 'targetlanguage' => $targetlanguage]);
$PAGE->set_url($url);
$PAGE->set_context($context);

$title = get_string('exporttranslations', 'filter_translations');
$PAGE->set_title($title);
$PAGE->set_heading('');
$PAGE->set_pagelayout('standard');

$form = new \filter_translations\form\exporttranslations_form(new moodle_url('/filter/translations/processexport.php'));

$data = new stdClass();
$data->targetlanguage = $targetlanguage;
$data->course = $courseid;
$form->set_data($data);

shell::require_css();
echo $OUTPUT->header();
shell::open($title, get_string('dashboardexport_desc', 'filter_translations'),
    shell::MODIFIER_EDITING);

echo html_writer::start_tag('section', ['class' => 'lh-plugin-card filter-translations-workbench-card']);
echo html_writer::tag('div',
    html_writer::span(html_writer::tag('i', '', ['class' => 'fa fa-download', 'aria-hidden' => 'true']),
        'lh-plugin-card__icon lh-plugin-card__icon--generic') .
    html_writer::tag('div',
        html_writer::tag('h2', $title, ['class' => 'lh-plugin-card__title']),
        ['class' => 'lh-plugin-card__meta']
    ),
    ['class' => 'lh-plugin-card__top']
);
echo html_writer::start_div('lh-plugin-card__body filter-translations-form-card');
echo html_writer::div(get_string('exportdescription', 'filter_translations'),
    'filter-translations-card-description');
$form->display();
echo html_writer::end_div();
echo html_writer::end_tag('section');

shell::close();
echo $OUTPUT->footer();
