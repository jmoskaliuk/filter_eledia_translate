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

use core\persistent;

/**
 * Persistent object for glossary terminology.
 *
 * @package filter_translations
 */
class glossary_entry extends persistent {
    /** Table name. */
    public const TABLE = 'filter_translations_glossary';

    /** Draft terminology. */
    public const STATUS_DRAFT = 10;

    /** Reviewed terminology. */
    public const STATUS_REVIEWED = 20;

    /** Approved terminology that can be used for provider sync. */
    public const STATUS_APPROVED = 30;

    /** Archived terminology. */
    public const STATUS_ARCHIVED = 40;

    /**
     * Define persistent properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'sourcephrase' => [
                'type' => PARAM_RAW,
            ],
            'targetphrase' => [
                'type' => PARAM_RAW,
            ],
            'sourcelanguage' => [
                'type' => PARAM_TEXT,
            ],
            'targetlanguage' => [
                'type' => PARAM_TEXT,
            ],
            'contextid' => [
                'type' => PARAM_INT,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'status' => [
                'type' => PARAM_INT,
                'choices' => [
                    self::STATUS_DRAFT,
                    self::STATUS_REVIEWED,
                    self::STATUS_APPROVED,
                    self::STATUS_ARCHIVED,
                ],
                'default' => self::STATUS_DRAFT,
            ],
            'priority' => [
                'type' => PARAM_INT,
                'default' => 100,
            ],
            'casesensitive' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'wholeword' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
            'notes' => [
                'type' => PARAM_RAW,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'deeplglossaryid' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
        ];
    }

    /**
     * Status options for forms and filters.
     *
     * @return string[]
     */
    public static function status_options(): array {
        return [
            self::STATUS_DRAFT => get_string('glossarystatus_draft', 'filter_translations'),
            self::STATUS_REVIEWED => get_string('glossarystatus_reviewed', 'filter_translations'),
            self::STATUS_APPROVED => get_string('glossarystatus_approved', 'filter_translations'),
            self::STATUS_ARCHIVED => get_string('glossarystatus_archived', 'filter_translations'),
        ];
    }
}
