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
 * Thin convenience wrapper around the LernHive plugin shell for the
 * content-translations setup/maintenance pages.
 *
 * @package    filter_translations
 * @copyright  2026 eLeDia GmbH / LernHive
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_translations\output;

use local_lernhive\output\plugin_page;
use local_lernhive\output\plugin_shell;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Centralises the shell header used by every translations sub-page so they
 * stay visually consistent and the "home" link always returns to the
 * dashboard (index.php).
 */
final class shell {

    /**
     * Static-only helper.
     */
    private function __construct() {
    }

    /**
     * Queue the shell stylesheet. Must run BEFORE $OUTPUT->header().
     */
    public static function require_css(): void {
        global $PAGE;
        $PAGE->requires->css(new moodle_url('/local/lernhive/styles.css'));
    }

    /**
     * Open the shell + content area for a translations sub-page. Call this
     * AFTER $OUTPUT->header().
     *
     * @param string $tagline Short context label shown next to the plugin name.
     * @param string $subtitle One-line description under the title.
     * @param string $modifier One of the plugin_page::MODIFIER_* constants.
     */
    public static function open(
        string $tagline,
        string $subtitle,
        string $modifier = plugin_page::MODIFIER_FULL
    ): void {
        plugin_page::open([
            'name' => get_string('pluginname', 'filter_translations'),
            'tagline' => $tagline,
            'subtitle' => $subtitle,
            'homeurl' => (new moodle_url('/filter/translations/index.php'))->out(false),
            'homelabel' => get_string('shell_backtodashboard', 'filter_translations'),
        ], $modifier);
        plugin_shell::content_open();
    }

    /**
     * Close the content area + shell. Call this BEFORE $OUTPUT->footer().
     */
    public static function close(): void {
        plugin_shell::content_close();
        plugin_page::close();
    }
}
