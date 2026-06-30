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
use core_tag_tag;

/**
 * Tests for the course translation policy (feat06 acceptance criteria).
 *
 * @covers \filter_translations\course_translation_policy
 */
class course_translation_policy_test extends advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * The request-scoped policy cache must be cleared between mutations within a test.
     */
    private function purge_policy_cache(): void {
        \cache::make('filter_translations', 'coursepolicy')->purge();
    }

    /**
     * Write a course custom field value directly, mirroring what the policy reads.
     *
     * @param int $fieldid
     * @param int $courseid
     * @param string $value
     * @param int $intvalue
     */
    private function set_customfield_value(
        int $fieldid,
        int $courseid,
        string $value,
        int $intvalue,
        string $charvalue = ''
    ): void {
        global $DB;

        $now = time();
        $record = (object)[
            'fieldid' => $fieldid,
            'instanceid' => $courseid,
            'intvalue' => $intvalue,
            'charvalue' => $charvalue,
            'value' => $value,
            'valueformat' => FORMAT_PLAIN,
            'contextid' => context_course::instance($courseid)->id,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $existing = $DB->get_record('customfield_data', ['fieldid' => $fieldid, 'instanceid' => $courseid]);
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('customfield_data', $record);
        } else {
            $DB->insert_record('customfield_data', $record);
        }
    }

    public function test_site_context_is_always_enabled(): void {
        $policy = course_translation_policy::for_context(context_system::instance());
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));
    }

    public function test_tags_control_source(): void {
        set_config('coursecontrolsource', course_translation_policy::CONTROL_TAGS, 'filter_translations');
        set_config('coursetagenabled', 'deepl', 'filter_translations');

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        // Without the enable tag the course is not translated.
        $this->purge_policy_cache();
        $this->assertFalse(course_translation_policy::for_context($context)->translation_enabled());

        // With the enable tag plus a language tag the policy allows that language only.
        core_tag_tag::set_item_tags('core', 'course', $course->id, $context, ['deepl', 'de']);
        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));
        $this->assertFalse($policy->language_enabled('es'));
    }

    public function test_customfields_control_source(): void {
        global $DB;

        set_config('coursecontrolsource', course_translation_policy::CONTROL_CUSTOMFIELDS, 'filter_translations');
        course_customfields::ensure();

        $enabledfieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'eledia_translate_enabled']);
        $langfieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'eledia_translate_languages']);
        $this->assertNotEmpty($enabledfieldid);
        $this->assertNotEmpty($langfieldid);
        $this->assertSame('languageselect',
            $DB->get_field('customfield_field', 'type', ['id' => $langfieldid]));

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        // feat06.AC01: not enabled -> no translation.
        $this->purge_policy_cache();
        $this->assertFalse(course_translation_policy::for_context($context)->translation_enabled());

        // feat06.AC02 / AC03: enabled with restricted languages de, fr.
        $this->set_customfield_value($enabledfieldid, $course->id, '1', 1);
        $this->set_customfield_value($langfieldid, $course->id, '', 0, 'de,fr');

        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));
        $this->assertTrue($policy->language_enabled('fr'));
        $this->assertFalse($policy->language_enabled('es'));
    }

    public function test_legacy_language_textarea_field_is_converted_to_selector(): void {
        global $DB;

        set_config('coursecontrolsource', course_translation_policy::CONTROL_CUSTOMFIELDS, 'filter_translations');
        course_customfields::ensure();

        $langfield = $DB->get_record('customfield_field', ['shortname' => 'eledia_translate_languages'], '*', MUST_EXIST);
        $langfield->type = 'textarea';
        $langfield->configdata = json_encode([
            'required' => 0,
            'uniquevalues' => 0,
            'visibility' => 2,
            'defaultvalue' => 'de, es',
            'defaultvalueformat' => FORMAT_PLAIN,
        ]);
        $DB->update_record('customfield_field', $langfield);

        $enabledfieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'eledia_translate_enabled']);
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        $this->set_customfield_value($enabledfieldid, $course->id, '1', 1);
        $this->set_customfield_value((int)$langfield->id, $course->id, 'de, fr', 0);

        course_customfields::ensure();
        $convertedfield = $DB->get_record('customfield_field', ['id' => $langfield->id], '*', MUST_EXIST);
        $this->assertSame('languageselect', $convertedfield->type);
        $convertedconfig = json_decode($convertedfield->configdata, true);
        $this->assertSame('de,es', $convertedconfig['defaultvalue']);

        $converteddata = $DB->get_record('customfield_data', [
            'fieldid' => $langfield->id,
            'instanceid' => $course->id,
        ], '*', MUST_EXIST);
        $this->assertSame('de,fr', $converteddata->charvalue);

        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->language_enabled('de'));
        $this->assertTrue($policy->language_enabled('fr'));
        $this->assertFalse($policy->language_enabled('es'));
    }

    public function test_customfields_empty_language_list_allows_all(): void {
        global $DB;

        set_config('coursecontrolsource', course_translation_policy::CONTROL_CUSTOMFIELDS, 'filter_translations');
        course_customfields::ensure();

        $enabledfieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'eledia_translate_enabled']);
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $this->set_customfield_value($enabledfieldid, $course->id, '1', 1);

        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));
        $this->assertTrue($policy->language_enabled('es'));
    }

    public function test_customfields_fallback_to_tags(): void {
        // feat06.AC04: no custom field values -> legacy tag behaviour applies.
        set_config('coursecontrolsource', course_translation_policy::CONTROL_CUSTOMFIELDS_FALLBACK_TAGS,
            'filter_translations');
        set_config('coursetagenabled', 'deepl', 'filter_translations');
        course_customfields::ensure();

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        core_tag_tag::set_item_tags('core', 'course', $course->id, $context, ['deepl', 'de']);
        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));
        $this->assertFalse($policy->language_enabled('es'));
    }

    public function test_default_control_source_uses_customfields_with_tag_fallback(): void {
        global $DB;

        unset_config('coursecontrolsource', 'filter_translations');
        set_config('coursetagenabled', 'deepl', 'filter_translations');
        course_customfields::ensure();

        $enabledfieldid = $DB->get_field('customfield_field', 'id', ['shortname' => 'eledia_translate_enabled']);
        $this->assertNotEmpty($enabledfieldid);

        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        core_tag_tag::set_item_tags('core', 'course', $course->id, $context, ['deepl', 'de']);
        $this->purge_policy_cache();
        $policy = course_translation_policy::for_context($context);
        $this->assertTrue($policy->translation_enabled());
        $this->assertTrue($policy->language_enabled('de'));

        $this->set_customfield_value($enabledfieldid, $course->id, '0', 0);

        $this->purge_policy_cache();
        $this->assertFalse(course_translation_policy::for_context($context)->translation_enabled());
    }
}
