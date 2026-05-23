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
 * Form for editing glossary entries.
 *
 * @package filter_translations
 */
class glossary_entry_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;
        $languages = get_string_manager()->get_list_of_translations();

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        $mform->addElement('textarea', 'sourcephrase', get_string('sourcephrase', 'filter_translations'),
            ['rows' => 3, 'cols' => 80]);
        $mform->setType('sourcephrase', PARAM_RAW);
        $mform->addRule('sourcephrase', null, 'required');

        $mform->addElement('textarea', 'targetphrase', get_string('targetphrase', 'filter_translations'),
            ['rows' => 3, 'cols' => 80]);
        $mform->setType('targetphrase', PARAM_RAW);
        $mform->addRule('targetphrase', null, 'required');

        $mform->addElement('select', 'sourcelanguage', get_string('sourcelanguage', 'filter_translations'), $languages);
        $mform->addRule('sourcelanguage', null, 'required');

        $mform->addElement('select', 'targetlanguage', get_string('targetlanguage', 'filter_translations'), $languages);
        $mform->addRule('targetlanguage', null, 'required');

        $courseoptions = [
            0 => get_string('glossaryscope_global', 'filter_translations'),
        ];
        $courses = $DB->get_records_select('course', 'id > :siteid', ['siteid' => SITEID], 'fullname ASC', 'id, fullname');
        foreach ($courses as $course) {
            $courseoptions[$course->id] = format_string($course->fullname);
        }

        $mform->addElement('select', 'courseid', get_string('glossaryscope', 'filter_translations'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('select', 'status', get_string('status', 'filter_translations'), glossary_entry::status_options());
        $mform->setDefault('status', glossary_entry::STATUS_DRAFT);

        $mform->addElement('text', 'priority', get_string('priority', 'filter_translations'));
        $mform->setType('priority', PARAM_INT);
        $mform->setDefault('priority', 100);

        $mform->addElement('advcheckbox', 'casesensitive', get_string('casesensitive', 'filter_translations'));
        $mform->addElement('advcheckbox', 'wholeword', get_string('wholeword', 'filter_translations'));
        $mform->setDefault('wholeword', 1);

        $mform->addElement('text', 'deeplglossaryid', get_string('deepl_glossaryid', 'filter_translations'), ['size' => 60]);
        $mform->setType('deeplglossaryid', PARAM_RAW_TRIMMED);

        $mform->addElement('textarea', 'notes', get_string('notes', 'filter_translations'), ['rows' => 4, 'cols' => 80]);
        $mform->setType('notes', PARAM_RAW);

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
