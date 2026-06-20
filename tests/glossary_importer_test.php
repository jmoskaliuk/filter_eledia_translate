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
 * @package    filter_translations
 * @category   test
 * @copyright  2026 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_translations;

use advanced_testcase;
use context_system;

/**
 * Tests for the glossary CSV row importer.
 *
 * @covers \filter_translations\glossary_importer
 */
class glossary_importer_test extends advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Pretend en, de and fr are installed.
     *
     * @return array<string,string>
     */
    private function languages(): array {
        return ['en' => 'English', 'de' => 'Deutsch', 'fr' => 'Francais'];
    }

    /**
     * Build a CSV row with sensible defaults.
     *
     * @param array $overrides Column index => value.
     * @return array
     */
    private function row(array $overrides = []): array {
        $defaults = [
            0 => 'Cloud', 1 => 'Wolke', 2 => 'en', 3 => 'de',
            4 => '', 5 => 'approved', 6 => '100', 7 => '0', 8 => '1', 9 => '', 10 => '',
        ];
        foreach ($overrides as $k => $v) {
            $defaults[$k] = $v;
        }
        return $defaults;
    }

    public function test_import_row_creates_entry(): void {
        global $DB;

        $result = glossary_importer::import_row($this->row(), $this->languages(), context_system::instance()->id);

        $this->assertSame(glossary_importer::ACTION_CREATED, $result->action);
        $this->assertEquals(1, $DB->count_records('filter_translations_glossary'));

        $record = $DB->get_record('filter_translations_glossary', ['sourcephrase' => 'Cloud']);
        $this->assertEquals('Wolke', $record->targetphrase);
        $this->assertEquals(glossary_entry::STATUS_APPROVED, $record->status);
        $this->assertNull($record->courseid);
    }

    public function test_import_row_updates_existing_entry(): void {
        global $DB;

        $contextid = context_system::instance()->id;
        glossary_importer::import_row($this->row(), $this->languages(), $contextid);
        $result = glossary_importer::import_row($this->row([1 => 'Datenwolke']), $this->languages(), $contextid);

        $this->assertSame(glossary_importer::ACTION_UPDATED, $result->action);
        $this->assertEquals(1, $result->updated);
        $this->assertEquals(1, $DB->count_records('filter_translations_glossary'));

        $record = $DB->get_record('filter_translations_glossary', ['sourcephrase' => 'Cloud']);
        $this->assertEquals('Datenwolke', $record->targetphrase);
    }

    public function test_import_row_updates_all_preexisting_duplicates(): void {
        // Regression test for bug01: a CSV row matching several historical duplicates of the
        // same natural key must update them all without a "found more than one record" notice.
        global $DB;

        $now = time();
        $base = (object)[
            'sourcephrase' => 'Cloud', 'targetphrase' => 'Alt', 'sourcelanguage' => 'en',
            'targetlanguage' => 'de', 'contextid' => context_system::instance()->id, 'courseid' => null,
            'status' => glossary_entry::STATUS_DRAFT, 'priority' => 100, 'casesensitive' => 0,
            'wholeword' => 1, 'notes' => '', 'deeplglossaryid' => '',
            'usermodified' => 2, 'timecreated' => $now, 'timemodified' => $now,
        ];
        $DB->insert_record('filter_translations_glossary', $base);
        $DB->insert_record('filter_translations_glossary', $base);
        $this->assertEquals(2, $DB->count_records('filter_translations_glossary'));

        $result = glossary_importer::import_row($this->row([1 => 'Wolke']), $this->languages(),
            context_system::instance()->id);

        $this->assertSame(glossary_importer::ACTION_UPDATED, $result->action);
        $this->assertEquals(2, $result->updated);

        $records = $DB->get_records('filter_translations_glossary');
        $this->assertCount(2, $records);
        foreach ($records as $record) {
            $this->assertEquals('Wolke', $record->targetphrase);
        }
    }

    public function test_course_scope_is_independent_from_global(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $contextid = context_system::instance()->id;

        glossary_importer::import_row($this->row(), $this->languages(), $contextid);
        $result = glossary_importer::import_row($this->row([4 => (string)$course->id]), $this->languages(), $contextid);

        $this->assertSame(glossary_importer::ACTION_CREATED, $result->action);
        $this->assertEquals(2, $DB->count_records('filter_translations_glossary'));
    }

    public function test_skips_invalid_rows(): void {
        $contextid = context_system::instance()->id;
        $languages = $this->languages();

        $this->assertSame(glossary_importer::REASON_MISSING_DATA,
            glossary_importer::import_row($this->row([0 => '']), $languages, $contextid)->reason);
        $this->assertSame(glossary_importer::REASON_INVALID_LANGUAGE,
            glossary_importer::import_row($this->row([3 => 'xx']), $languages, $contextid)->reason);
        $this->assertSame(glossary_importer::REASON_INVALID_COURSE,
            glossary_importer::import_row($this->row([4 => 'abc']), $languages, $contextid)->reason);
        $this->assertSame(glossary_importer::REASON_INVALID_COURSE,
            glossary_importer::import_row($this->row([4 => '99999']), $languages, $contextid)->reason);
        $this->assertSame(glossary_importer::REASON_INVALID_STATUS,
            glossary_importer::import_row($this->row([5 => 'bogus']), $languages, $contextid)->reason);
        $this->assertSame(glossary_importer::REASON_INVALID_PRIORITY,
            glossary_importer::import_row($this->row([6 => 'high']), $languages, $contextid)->reason);
    }
}
