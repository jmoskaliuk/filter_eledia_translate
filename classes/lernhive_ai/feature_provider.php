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
 * LernHive AI feature provider for filter_translations.
 *
 * @package    filter_translations
 * @copyright  2026 eLeDia GmbH / LernHive
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_translations\lernhive_ai;

defined('MOODLE_INTERNAL') || die();

use local_lernhive_ai\feature\descriptor;
use local_lernhive_ai\feature\feature_provider as feature_provider_contract;
use moodle_url;

/**
 * Registers the translation workflow with the LernHive AI Suite.
 *
 * The descriptor deliberately uses the roadmap id `translate` so the
 * live filter plugin replaces the AI Suite's coming-soon tile.
 */
final class feature_provider implements feature_provider_contract {
    #[\Override]
    public static function get_descriptors(): array {
        return [
            new descriptor(
                id: 'translate',
                component: 'filter_translations',
                name: get_string('feature_translate_name', 'local_lernhive_ai'),
                description: get_string('feature_translate_desc', 'local_lernhive_ai'),
                launchurl: new moodle_url('/filter/translations/index.php'),
                icon: 'languages',
                capability: 'filter/translations:edittranslations',
                configurl: new moodle_url('/admin/settings.php', ['section' => 'filtersettingtranslations']),
                comingsoon: false,
                detaildescription: get_string('feature_translate_detail', 'local_lernhive_ai'),
            ),
        ];
    }
}
