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
use context_course;
use context_system;

/**
 * Tests for the DeepL glossary sync helper (logic that does not hit the network).
 *
 * @covers \filter_translations\glossary_sync
 */
class glossary_sync_test extends advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Create an approved or other-status glossary entry.
     *
     * @param string $source
     * @param string $target
     * @param string $sl Source language.
     * @param string $tl Target language.
     * @param int $status
     * @param int|null $courseid
     * @return glossary_entry
     */
    private function create_entry(string $source, string $target, string $sl, string $tl,
            int $status, ?int $courseid = null): glossary_entry {
        $context = $courseid ? context_course::instance($courseid) : context_system::instance();
        $entry = new glossary_entry(0, (object)[
            'sourcephrase' => $source,
            'targetphrase' => $target,
            'sourcelanguage' => $sl,
            'targetlanguage' => $tl,
            'contextid' => $context->id,
            'courseid' => $courseid,
            'status' => $status,
        ]);
        $entry->create();
        return $entry;
    }

    /**
     * @dataProvider language_provider
     * @param string $moodlecode
     * @param string $expected
     */
    public function test_map_language_to_deepl(string $moodlecode, string $expected): void {
        $this->assertSame($expected, glossary_sync::map_language_to_deepl($moodlecode));
    }

    /**
     * @return array<string,array{string,string}>
     */
    public static function language_provider(): array {
        return [
            'plain de' => ['de', 'DE'],
            'plain en' => ['en', 'EN'],
            'plain fr' => ['fr', 'FR'],
            'en_us' => ['en_us', 'EN-US'],
            'en-gb hyphen' => ['en-gb', 'EN-GB'],
            'pt_br' => ['pt_br', 'PT-BR'],
            'zh_cn' => ['zh_cn', 'ZH-HANS'],
            'zh_tw' => ['zh_tw', 'ZH-HANT'],
            'regional fallback' => ['de_kids', 'DE'],
        ];
    }

    public function test_groups_only_includes_approved_entries(): void {
        $this->create_entry('Cloud', 'Wolke', 'en', 'de', glossary_entry::STATUS_APPROVED);
        $this->create_entry('Server', 'Server', 'en', 'de', glossary_entry::STATUS_APPROVED);
        $this->create_entry('Draft', 'Entwurf', 'en', 'de', glossary_entry::STATUS_DRAFT);
        $this->create_entry('Cloud', 'Nuage', 'en', 'fr', glossary_entry::STATUS_APPROVED);

        $groups = glossary_sync::groups();
        $this->assertCount(2, $groups);

        $bypair = [];
        foreach ($groups as $group) {
            $bypair[$group->sourcelanguage . '_' . $group->targetlanguage] = $group;
        }

        $this->assertArrayHasKey('en_de', $bypair);
        $this->assertArrayHasKey('en_fr', $bypair);
        $this->assertEquals(2, $bypair['en_de']->entrycount);
        $this->assertEquals(1, $bypair['en_fr']->entrycount);
        $this->assertEquals('global', $bypair['en_de']->scope);
        $this->assertTrue($bypair['en_de']->pending);
    }

    public function test_sync_group_without_entries_is_pending(): void {
        $state = glossary_sync::sync_group('global', null, 'en', 'de');
        $this->assertEquals(glossary_sync::STATUS_PENDING, (int)$state->status);
        $this->assertEquals(0, (int)$state->courseid);
        $this->assertNotEmpty($state->lastsyncerror);
    }

    public function test_sync_group_with_invalid_entries_errors_without_network(): void {
        // A tab character cannot be represented in a DeepL TSV glossary, so the entry is
        // rejected before any API client is constructed.
        $this->create_entry('Cloud', "Wol\tke", 'en', 'de', glossary_entry::STATUS_APPROVED);

        $state = glossary_sync::sync_group('global', null, 'en', 'de');

        $this->assertEquals(glossary_sync::STATUS_ERROR, (int)$state->status);
        $this->assertNotEmpty($state->lastsyncerror);
    }

    public function test_resolve_deepl_glossary_id_prefers_course_over_global(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $now = time();

        $DB->insert_record('filter_translations_glossync', (object)[
            'scope' => 'global', 'courseid' => 0, 'sourcelanguage' => 'en', 'targetlanguage' => 'de',
            'deeplglossaryid' => 'GLOBAL-ID', 'entrycount' => 1, 'contenthash' => md5('global'),
            'status' => glossary_sync::STATUS_SYNCED, 'usermodified' => 2, 'timecreated' => $now, 'timemodified' => $now,
        ]);
        $DB->insert_record('filter_translations_glossync', (object)[
            'scope' => 'course', 'courseid' => $course->id, 'sourcelanguage' => 'en', 'targetlanguage' => 'de',
            'deeplglossaryid' => 'COURSE-ID', 'entrycount' => 1, 'contenthash' => md5('course'),
            'status' => glossary_sync::STATUS_SYNCED, 'usermodified' => 2, 'timecreated' => $now, 'timemodified' => $now,
        ]);

        $this->assertEquals('COURSE-ID',
            glossary_sync::resolve_deepl_glossary_id(context_course::instance($course->id), 'en', 'de'));
        $this->assertEquals('GLOBAL-ID',
            glossary_sync::resolve_deepl_glossary_id(context_system::instance(), 'en', 'de'));
        $this->assertNull(
            glossary_sync::resolve_deepl_glossary_id(context_system::instance(), 'en', 'es'));
    }
}
