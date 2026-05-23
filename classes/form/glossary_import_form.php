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

namespace filter_translations\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Glossary CSV import form.
 *
 * @package filter_translations
 */
class glossary_import_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('filepicker', 'file', get_string('filetoimport', 'filter_translations'), null, [
            'accepted_types' => ['.csv'],
        ]);
        $mform->addHelpButton('file', 'filetoimport', 'filter_translations');

        $this->add_action_buttons(true, get_string('submit'));
    }
}
