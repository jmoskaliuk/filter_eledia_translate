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

namespace filter_translations;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Filter form for glossary entries.
 *
 * @package filter_translations
 */
class manageglossary_filterform extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'filteroptions', get_string('filteroptions', 'filter_translations'));

        $languages = ['' => get_string('any', 'filter_translations')] + get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'sourcelanguage', get_string('sourcelanguage', 'filter_translations'), $languages);
        $mform->addElement('select', 'targetlanguage', get_string('targetlanguage', 'filter_translations'), $languages);

        $mform->addElement('text', 'sourcephrase', get_string('sourcephrase', 'filter_translations'));
        $mform->setType('sourcephrase', PARAM_TEXT);

        $mform->addElement('text', 'targetphrase', get_string('targetphrase', 'filter_translations'));
        $mform->setType('targetphrase', PARAM_TEXT);

        $mform->addElement('select', 'status', get_string('status', 'filter_translations'),
            [0 => get_string('any', 'filter_translations')] + glossary_entry::status_options());

        $courseoptions = [
            0 => get_string('any', 'filter_translations'),
            -1 => get_string('glossaryscope_globalonly', 'filter_translations'),
        ];
        $courses = $DB->get_records_select('course', 'id > :siteid', ['siteid' => SITEID], 'fullname ASC', 'id, fullname');
        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        $mform->addElement('select', 'courseid', get_string('glossaryscope', 'filter_translations'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('submit', 'submit', get_string('update'));
    }
}
