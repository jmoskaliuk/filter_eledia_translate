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

/**
 * Default table/column configuration for translation maintenance tasks.
 *
 * @package filter_translations
 */
class column_definition {
    /**
     * Default rich-text columns that should receive translation spans.
     *
     * @return array
     */
    public static function default_columns(): array {
        return [
            'quiz' => ['intro'],
            'question' => ['questiontext', 'generalfeedback'],
            'question_answers' => ['answer', 'feedback'],
            'question_hints' => ['hint'],
            'question_calculated_options' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_ddimageortext' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_ddmarker' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'question_ddwtos' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'question_gapselect' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_match_options' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_match_subquestions' => ['questiontext'],
            'qtype_multichoice_options' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_ordering_options' => ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'],
            'qtype_aitext' => ['graderinfo', 'responsetemplate'],
        ];
    }

    /**
     * Get the default configuration as pretty JSON for the admin setting.
     *
     * @return string
     */
    public static function default_json(): string {
        return json_encode(self::default_columns(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Merge default columns into existing JSON without dropping custom entries.
     *
     * @param string|false|null $json existing setting value
     * @return string|null merged JSON, or null when the existing JSON is invalid
     */
    public static function merge_defaults($json): ?string {
        if (empty($json)) {
            return self::default_json();
        }

        $columns = json_decode($json, true);
        if (!is_array($columns)) {
            return null;
        }

        foreach (self::default_columns() as $table => $defaultcolumns) {
            if (!isset($columns[$table]) || !is_array($columns[$table])) {
                $columns[$table] = $defaultcolumns;
                continue;
            }

            $columns[$table] = array_values(array_unique(array_merge($columns[$table], $defaultcolumns)));
        }

        return json_encode($columns, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
