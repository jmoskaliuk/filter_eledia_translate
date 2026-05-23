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
 * Tests the configured DeepL connection.
 *
 * @package filter_translations
 */

use filter_translations\translationproviders\deepltranslate;

require(__DIR__ . '/../../config.php');

$targetlanguage = optional_param('targetlanguage', 'DE', PARAM_ALPHANUMEXT);

require_login();
$context = context_system::instance();
require_capability('moodle/site:config', $context);
require_sesskey();

$url = new moodle_url('/filter/translations/testdeepl.php', [
    'sesskey' => sesskey(),
    'targetlanguage' => $targetlanguage,
]);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('deepltest', 'filter_translations'));
$PAGE->set_heading(get_string('deepltest', 'filter_translations'));

$provider = new deepltranslate();
$translated = $provider->test_connection($targetlanguage);

echo $OUTPUT->header();
if ($translated === null) {
    echo $OUTPUT->notification(get_string('deepltestfailed', 'filter_translations'), \core\output\notification::NOTIFY_ERROR);
} else {
    echo $OUTPUT->notification(get_string('deepltestsuccess', 'filter_translations', $translated),
        \core\output\notification::NOTIFY_SUCCESS);
}
echo $OUTPUT->continue_button(new moodle_url('/admin/settings.php', ['section' => 'filtersettingtranslations']));
echo $OUTPUT->footer();
