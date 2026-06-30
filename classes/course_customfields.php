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

use core_course\customfield\course_handler;
use core_customfield\category_controller;
use core_customfield\field_controller;
use core_component;

/**
 * Creates the recommended course custom fields for translation control.
 *
 * @package filter_translations
 */
class course_customfields {
    /** Category name for the generated course custom fields. */
    private const CATEGORY_NAME = 'eLeDia Translation';

    /**
     * Ensure the configured course custom fields exist.
     *
     * @return string[] Human-readable result messages.
     */
    public static function ensure(): array {
        $handler = course_handler::create();
        $category = self::get_or_create_category($handler);
        $messages = [];

        $enabledshortname = self::enabled_field_shortname();
        if (self::field_exists($handler, $enabledshortname)) {
            $messages[] = get_string('coursefieldexists', 'filter_translations', $enabledshortname);
        } else {
            self::create_field($handler, $category, [
                'type' => 'checkbox',
                'shortname' => $enabledshortname,
                'name' => get_string('coursefieldenabled_name', 'filter_translations'),
                'description' => get_string('coursefieldenabled_field_desc', 'filter_translations'),
                'configdata' => [
                    'checkbydefault' => 0,
                ],
            ]);
            $messages[] = get_string('coursefieldcreated', 'filter_translations', $enabledshortname);
        }

        $languagesshortname = self::languages_field_shortname();
        $languagesfield = self::field_record_by_shortname($languagesshortname);
        if ($languagesfield) {
            if (self::language_selector_available() && $languagesfield->type !== 'languageselect') {
                self::convert_languages_field_to_selector((int)$languagesfield->id);
            }
            $messages[] = get_string('coursefieldexists', 'filter_translations', $languagesshortname);
        } else {
            self::create_field($handler, $category, [
                'type' => self::languages_field_type(),
                'shortname' => $languagesshortname,
                'name' => get_string('coursefieldlanguages_name', 'filter_translations'),
                'description' => get_string('coursefieldlanguages_field_desc', 'filter_translations'),
                'configdata' => [
                    'defaultvalue' => '',
                ],
            ]);
            $messages[] = get_string('coursefieldcreated', 'filter_translations', $languagesshortname);
        }

        return $messages;
    }

    /**
     * Get or create the target category.
     *
     * @param course_handler $handler
     * @return \core_customfield\category_controller
     */
    private static function get_or_create_category(course_handler $handler) {
        foreach ($handler->get_categories_with_fields() as $category) {
            if ($category->get('name') === self::CATEGORY_NAME) {
                return $category;
            }
        }

        return category_controller::create($handler->create_category(self::CATEGORY_NAME), null, $handler);
    }

    /**
     * Check whether a course custom field shortname already exists.
     *
     * @param course_handler $handler
     * @param string $shortname
     * @return bool
     */
    private static function field_exists(course_handler $handler, string $shortname): bool {
        return self::field_by_shortname($handler, $shortname) !== null;
    }

    /**
     * Find a course custom field controller by shortname.
     *
     * @param course_handler $handler
     * @param string $shortname
     * @return field_controller|null
     */
    private static function field_by_shortname(course_handler $handler, string $shortname): ?field_controller {
        foreach ($handler->get_categories_with_fields() as $category) {
            foreach ($category->get_fields() as $field) {
                if ($field->get('shortname') === $shortname) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Find a course custom field DB record by shortname.
     *
     * @param string $shortname
     * @return \stdClass|null
     */
    private static function field_record_by_shortname(string $shortname): ?\stdClass {
        global $DB;

        $sql = "SELECT f.*
                  FROM {customfield_field} f
                  JOIN {customfield_category} c ON c.id = f.categoryid
                 WHERE c.component = :component
                   AND c.area = :area
                   AND f.shortname = :shortname";
        return $DB->get_record_sql($sql, [
            'component' => 'core_course',
            'area' => 'course',
            'shortname' => $shortname,
        ], IGNORE_MISSING) ?: null;
    }

    /**
     * Convert the legacy textarea language field to the language selector.
     *
     * Existing course values are copied from value to charvalue so the new
     * autocomplete control opens with the same selected language codes.
     *
     * @param int $fieldid
     */
    private static function convert_languages_field_to_selector(int $fieldid): void {
        global $DB;

        $field = $DB->get_record('customfield_field', ['id' => $fieldid], '*', MUST_EXIST);
        $configdata = json_decode((string)$field->configdata, true);
        if (!is_array($configdata)) {
            $configdata = [];
        }

        $configdata['defaultvalue'] = self::normalise_language_value($configdata['defaultvalue'] ?? '');
        unset($configdata['defaultvalueformat']);

        $field->type = 'languageselect';
        $field->configdata = json_encode($configdata);
        $field->timemodified = time();
        $DB->update_record('customfield_field', $field);

        $records = $DB->get_records('customfield_data', ['fieldid' => $fieldid]);
        foreach ($records as $record) {
            $languagevalue = self::normalise_language_value($record->charvalue ?: ($record->value ?? ''));
            if ($languagevalue === (string)$record->charvalue) {
                continue;
            }
            $record->charvalue = $languagevalue;
            $record->timemodified = time();
            $DB->update_record('customfield_data', $record);
        }
    }

    /**
     * Normalise language-code lists for storage in the selector field.
     *
     * @param mixed $value
     * @return string
     */
    private static function normalise_language_value($value): string {
        if (self::language_selector_available()) {
            return implode(',', \customfield_languageselect\data_controller::normalise_language_codes($value));
        }

        $parts = preg_split('/[\s,;|]+/', strtolower((string)$value), -1, PREG_SPLIT_NO_EMPTY);
        $languages = [];
        foreach ($parts as $part) {
            $code = strtolower(trim((string)$part));
            if ($code !== '') {
                $languages[$code] = $code;
            }
        }

        return implode(',', array_values($languages));
    }

    /**
     * Create a custom field in the given category.
     *
     * @param course_handler $handler
     * @param \core_customfield\category_controller $category
     * @param array $data
     * @return field_controller
     */
    private static function create_field(course_handler $handler, $category, array $data): field_controller {
        $configdata = [
            'required' => 0,
            'uniquevalues' => 0,
            'locked' => 0,
            'visibility' => course_handler::VISIBLETOTEACHERS,
            'defaultvalue' => '',
            'defaultvalueformat' => FORMAT_MOODLE,
            'displaysize' => 0,
            'maxlength' => 0,
            'ispassword' => 0,
            'link' => '',
            'linktarget' => '',
            'checkbydefault' => 0,
            'startyear' => 2000,
            'endyear' => 3000,
            'includetime' => 1,
        ];
        $configdata = array_merge($configdata, $data['configdata'] ?? []);

        $record = (object)[
            'name' => $data['name'],
            'shortname' => $data['shortname'],
            'type' => $data['type'],
            'description' => $data['description'] ?? '',
            'descriptionformat' => FORMAT_HTML,
            'sortorder' => 0,
            'configdata' => $configdata,
        ];

        $field = field_controller::create(0, (object)['type' => $record->type], $category);
        $handler->save_field_configuration($field, $record);

        return $field;
    }

    /**
     * Get the best available custom field type for target languages.
     *
     * @return string
     */
    private static function languages_field_type(): string {
        return self::language_selector_available() ? 'languageselect' : 'text';
    }

    /**
     * Whether the optional searchable language custom field plugin is present.
     *
     * @return bool
     */
    private static function language_selector_available(): bool {
        return class_exists('\customfield_languageselect\data_controller') ||
            core_component::get_plugin_directory('customfield', 'languageselect') !== null;
    }

    /**
     * Get the configured enabled field shortname.
     *
     * @return string
     */
    private static function enabled_field_shortname(): string {
        $shortname = get_config('filter_translations', 'coursefieldenabled');
        return empty($shortname) ? 'eledia_translate_enabled' : $shortname;
    }

    /**
     * Get the configured languages field shortname.
     *
     * @return string
     */
    private static function languages_field_shortname(): string {
        $shortname = get_config('filter_translations', 'coursefieldlanguages');
        return empty($shortname) ? 'eledia_translate_languages' : $shortname;
    }
}
