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

require(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('moodle/course:configurecustomfields', $context);
require_sesskey();

$url = new moodle_url('/filter/translations/setupcoursefields.php', ['sesskey' => sesskey()]);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('setupcoursefields', 'filter_translations'));
$PAGE->set_heading(get_string('setupcoursefields', 'filter_translations'));

$messages = course_customfields::ensure();

echo $OUTPUT->header();
foreach ($messages as $message) {
    echo $OUTPUT->notification($message, \core\output\notification::NOTIFY_SUCCESS);
}
echo $OUTPUT->continue_button(new moodle_url('/admin/settings.php', ['section' => 'filtersettingtranslations']));
echo $OUTPUT->footer();
