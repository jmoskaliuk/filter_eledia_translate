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
use core_customfield\field_controller;

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
        if (self::field_exists($handler, $languagesshortname)) {
            $messages[] = get_string('coursefieldexists', 'filter_translations', $languagesshortname);
        } else {
            self::create_field($handler, $category, [
                'type' => 'textarea',
                'shortname' => $languagesshortname,
                'name' => get_string('coursefieldlanguages_name', 'filter_translations'),
                'description' => get_string('coursefieldlanguages_field_desc', 'filter_translations'),
                'configdata' => [
                    'defaultvalue' => '',
                    'defaultvalueformat' => FORMAT_PLAIN,
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

        $categoryid = $handler->create_category(self::CATEGORY_NAME);
        return $handler->get_category_from_array(
            $handler->get_categories_with_fields(),
            $categoryid,
            $handler->get_component(),
            $handler->get_area(),
            $handler->get_itemid()
        );
    }

    /**
     * Check whether a course custom field shortname already exists.
     *
     * @param course_handler $handler
     * @param string $shortname
     * @return bool
     */
    private static function field_exists(course_handler $handler, string $shortname): bool {
        foreach ($handler->get_categories_with_fields() as $category) {
            foreach ($category->get_fields() as $field) {
                if ($field->get('shortname') === $shortname) {
                    return true;
                }
            }
        }

        return false;
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
            'visibility' => course_handler::NOTVISIBLE,
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
