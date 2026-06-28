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

use advanced_testcase;

/**
 * Tests for translation task column defaults.
 *
 * @package filter_translations
 */
class column_definition_test extends advanced_testcase {
    /**
     * Default JSON includes Quiz and Question Bank fields.
     */
    public function test_default_json_includes_quiz_and_question_bank_fields(): void {
        $columns = json_decode(column_definition::default_json(), true);

        $this->assertSame(['intro'], $columns['quiz']);
        $this->assertContains('questiontext', $columns['question']);
        $this->assertContains('generalfeedback', $columns['question']);
        $this->assertContains('answer', $columns['question_answers']);
        $this->assertContains('feedback', $columns['question_answers']);
        $this->assertContains('hint', $columns['question_hints']);
        $this->assertContains('correctfeedback', $columns['qtype_multichoice_options']);
        $this->assertContains('partiallycorrectfeedback', $columns['qtype_multichoice_options']);
        $this->assertContains('incorrectfeedback', $columns['qtype_multichoice_options']);
        $this->assertContains('graderinfo', $columns['qtype_aitext']);
        $this->assertContains('responsetemplate', $columns['qtype_aitext']);
    }

    /**
     * Existing custom column definitions are preserved during upgrades.
     */
    public function test_merge_defaults_preserves_custom_columns(): void {
        $existing = json_encode([
            'page' => ['content'],
            'question' => ['questiontext'],
        ]);

        $columns = json_decode(column_definition::merge_defaults($existing), true);

        $this->assertSame(['content'], $columns['page']);
        $this->assertContains('questiontext', $columns['question']);
        $this->assertContains('generalfeedback', $columns['question']);
        $this->assertContains('feedback', $columns['question_answers']);
    }

    /**
     * Invalid JSON is left untouched so admins still see the configuration problem.
     */
    public function test_merge_defaults_returns_null_for_invalid_json(): void {
        $this->assertNull(column_definition::merge_defaults('{invalid'));
    }
}
