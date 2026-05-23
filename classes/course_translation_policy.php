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

use cache;
use context;
use context_course;
use core_tag_tag;

/**
 * Resolves whether content translation is enabled for a course context.
 *
 * @package filter_translations
 */
class course_translation_policy {
    /** Use the legacy course tag behaviour. */
    public const CONTROL_TAGS = 'tags';

    /** Use Moodle course custom fields. */
    public const CONTROL_CUSTOMFIELDS = 'customfields';

    /** Use course custom fields first, then fall back to tags. */
    public const CONTROL_CUSTOMFIELDS_FALLBACK_TAGS = 'customfields_fallback_tags';

    /** @var int|null */
    private $courseid;

    /** @var bool|null */
    private $customfieldenabled = null;

    /** @var string[]|null */
    private $customfieldlanguages = null;

    /** @var bool|null */
    private $tagenabled = null;

    /** @var string[]|null */
    private $taglanguages = null;

    /**
     * Constructor.
     *
     * @param int|null $courseid
     */
    private function __construct(?int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Build a policy for a Moodle filter context.
     *
     * @param context $context
     * @return self
     */
    public static function for_context(context $context): self {
        $coursecontext = $context->get_course_context(false);

        if (empty($coursecontext) || $coursecontext->instanceid == SITEID) {
            return new self(null);
        }

        return new self((int)$coursecontext->instanceid);
    }

    /**
     * Whether translation is enabled for the current course.
     *
     * @return bool
     */
    public function translation_enabled(): bool {
        if (empty($this->courseid)) {
            return true;
        }

        switch ($this->control_source()) {
            case self::CONTROL_CUSTOMFIELDS:
                return $this->customfield_enabled();

            case self::CONTROL_CUSTOMFIELDS_FALLBACK_TAGS:
                if ($this->has_customfield_policy()) {
                    return $this->customfield_enabled();
                }
                return $this->tag_enabled();

            case self::CONTROL_TAGS:
            default:
                return $this->tag_enabled();
        }
    }

    /**
     * Whether the current language is enabled for the current course.
     *
     * @param string $language
     * @return bool
     */
    public function language_enabled(string $language): bool {
        if (empty($this->courseid)) {
            return true;
        }

        switch ($this->control_source()) {
            case self::CONTROL_CUSTOMFIELDS:
                return $this->customfield_language_enabled($language);

            case self::CONTROL_CUSTOMFIELDS_FALLBACK_TAGS:
                if ($this->has_customfield_policy()) {
                    return $this->customfield_language_enabled($language);
                }
                return $this->tag_language_enabled($language);

            case self::CONTROL_TAGS:
            default:
                return $this->tag_language_enabled($language);
        }
    }

    /**
     * Get the configured control source.
     *
     * @return string
     */
    private function control_source(): string {
        $source = get_config('filter_translations', 'coursecontrolsource');
        if (empty($source)) {
            return self::CONTROL_TAGS;
        }

        return $source;
    }

    /**
     * Check whether custom field data exists for this course.
     *
     * @return bool
     */
    private function has_customfield_policy(): bool {
        return $this->customfield_raw_value($this->enabled_field_shortname()) !== null ||
            $this->customfield_raw_value($this->languages_field_shortname()) !== null;
    }

    /**
     * Whether translation is enabled according to custom field data.
     *
     * @return bool
     */
    private function customfield_enabled(): bool {
        if ($this->customfieldenabled !== null) {
            return $this->customfieldenabled;
        }

        $value = $this->customfield_raw_value($this->enabled_field_shortname());
        $this->customfieldenabled = $this->normalise_bool($value);

        return $this->customfieldenabled;
    }

    /**
     * Whether a language is enabled according to custom field data.
     *
     * @param string $language
     * @return bool
     */
    private function customfield_language_enabled(string $language): bool {
        if (!$this->customfield_enabled()) {
            return false;
        }

        $languages = $this->customfield_languages();
        if (empty($languages)) {
            return true;
        }

        return in_array(strtolower($language), $languages, true);
    }

    /**
     * Get custom field language values.
     *
     * @return string[]
     */
    private function customfield_languages(): array {
        if ($this->customfieldlanguages !== null) {
            return $this->customfieldlanguages;
        }

        $this->customfieldlanguages = $this->normalise_language_list(
            $this->customfield_raw_value($this->languages_field_shortname())
        );

        return $this->customfieldlanguages;
    }

    /**
     * Whether translation is enabled according to legacy tags.
     *
     * @return bool
     */
    private function tag_enabled(): bool {
        if ($this->tagenabled !== null) {
            return $this->tagenabled;
        }

        $enabledtag = strtolower((string)get_config('filter_translations', 'coursetagenabled'));
        if ($enabledtag === '') {
            $enabledtag = 'deepl';
        }

        $this->tagenabled = in_array($enabledtag, $this->course_tags(), true);

        return $this->tagenabled;
    }

    /**
     * Whether a language is enabled according to legacy tags.
     *
     * @param string $language
     * @return bool
     */
    private function tag_language_enabled(string $language): bool {
        if (!$this->tag_enabled()) {
            return false;
        }

        return in_array(strtolower($language), $this->course_tags(), true);
    }

    /**
     * Get normalised course tag names.
     *
     * @return string[]
     */
    private function course_tags(): array {
        if ($this->taglanguages !== null) {
            return $this->taglanguages;
        }

        $coursecontext = context_course::instance($this->courseid);
        $tags = core_tag_tag::get_tags_by_area_in_contexts('core', 'course', [$coursecontext]);
        $this->taglanguages = [];

        foreach ($tags as $tag) {
            $this->taglanguages[] = strtolower($tag->rawname);
        }

        return $this->taglanguages;
    }

    /**
     * Get a raw course custom field value.
     *
     * @param string $shortname
     * @return string|null
     */
    private function customfield_raw_value(string $shortname): ?string {
        global $DB;

        if (empty($this->courseid) || $shortname === '') {
            return null;
        }

        $cachekey = $this->courseid . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $shortname);
        $cache = cache::make('filter_translations', 'coursepolicy');
        $cached = $cache->get($cachekey);
        if ($cached !== false) {
            return $cached === '__null__' ? null : $cached;
        }

        $sql = "SELECT d.*
                  FROM {customfield_data} d
                  JOIN {customfield_field} f ON f.id = d.fieldid
                  JOIN {customfield_category} c ON c.id = f.categoryid
                 WHERE c.component = :component
                   AND c.area = :area
                   AND f.shortname = :shortname
                   AND d.instanceid = :courseid";
        $params = [
            'component' => 'core_course',
            'area' => 'course',
            'shortname' => $shortname,
            'courseid' => $this->courseid,
        ];
        $record = $DB->get_record_sql($sql, $params, IGNORE_MISSING);

        if (!$record) {
            $cache->set($cachekey, '__null__');
            return null;
        }

        $value = $record->value ?? '';
        if ($value === '' && isset($record->intvalue)) {
            $value = (string)$record->intvalue;
        }

        $cache->set($cachekey, $value);
        return $value;
    }

    /**
     * Normalise a yes/no custom field value.
     *
     * @param string|null $value
     * @return bool
     */
    private function normalise_bool(?string $value): bool {
        if ($value === null) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'yes', 'y', 'true', 'on', 'ja'], true);
    }

    /**
     * Normalise comma, newline, semicolon or pipe separated Moodle language codes.
     *
     * @param string|null $value
     * @return string[]
     */
    private function normalise_language_list(?string $value): array {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $languages = preg_split('/[\s,;|]+/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($languages));
    }

    /**
     * Get the custom field shortname used to enable translation.
     *
     * @return string
     */
    private function enabled_field_shortname(): string {
        $shortname = get_config('filter_translations', 'coursefieldenabled');
        return empty($shortname) ? 'eledia_translate_enabled' : $shortname;
    }

    /**
     * Get the custom field shortname used to list enabled target languages.
     *
     * @return string
     */
    private function languages_field_shortname(): string {
        $shortname = get_config('filter_translations', 'coursefieldlanguages');
        return empty($shortname) ? 'eledia_translate_languages' : $shortname;
    }
}
