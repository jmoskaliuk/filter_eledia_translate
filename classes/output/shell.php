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
 * Standalone plugin shell for the content-translations setup/maintenance pages.
 *
 * @package    filter_translations
 * @copyright  2026 eLeDia GmbH / LernHive
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_translations\output;

use context_system;
use html_writer;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Centralises the shell header used by every translations sub-page so they
 * stay visually consistent and the "home" link always returns to the
 * dashboard (index.php).
 */
final class shell {
    /** Default plugin shell width. */
    public const MODIFIER_DEFAULT = 'default';

    /** Reading shell width for forms and wizards. */
    public const MODIFIER_READING = 'reading';

    /** Editing shell width; currently equivalent to reading. */
    public const MODIFIER_EDITING = 'editing';

    /** Wider shell width for operational dashboards. */
    public const MODIFIER_WIDE = 'wide';

    /** Full-width shell. */
    public const MODIFIER_FULL = 'full';

    /** @var bool Tracks whether the shell is open. */
    private static bool $opened = false;

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
        $PAGE->requires->css(new moodle_url('/filter/translations/styles.css'));
    }

    /**
     * Open the shell + content area for a translations sub-page. Call this
     * AFTER $OUTPUT->header().
     *
     * @param string $tagline Short context label shown next to the plugin name.
     * @param string $subtitle One-line description under the title.
     * @param string $modifier One of the shell::MODIFIER_* constants.
     */
    public static function open(string $tagline, string $subtitle, string $modifier = self::MODIFIER_DEFAULT): void {
        self::open_page([
            'name' => get_string('pluginname', 'filter_translations'),
            'tagline' => $tagline,
            'subtitle' => $subtitle,
            'homeurl' => (new moodle_url('/filter/translations/index.php'))->out(false),
            'homelabel' => get_string('shell_backtodashboard', 'filter_translations'),
            'sectionnav' => self::section_nav(),
        ], $modifier);
        self::content_open();
    }

    /**
     * Open the standalone shell with custom header data.
     *
     * @param array $headerdata Header render data.
     * @param string $modifier Width modifier.
     */
    public static function open_page(array $headerdata, string $modifier = self::MODIFIER_DEFAULT): void {
        if (self::$opened) {
            throw new \coding_exception('filter_translations shell opened twice without close().');
        }

        $shellclass = 'lh-plugin-shell';
        if ($modifier === self::MODIFIER_READING || $modifier === self::MODIFIER_EDITING) {
            $shellclass .= ' lh-plugin-shell--reading';
        } else if ($modifier === self::MODIFIER_WIDE) {
            $shellclass .= ' lh-plugin-shell--wide';
        } else if ($modifier === self::MODIFIER_FULL) {
            $shellclass .= ' lh-plugin-shell--full';
        }

        echo html_writer::start_div($shellclass);
        echo self::header($headerdata);
        self::$opened = true;
    }

    /**
     * Open the standard shell content area.
     *
     * @param string $classes CSS classes to apply.
     */
    public static function content_open(string $classes = 'lh-plugin-content-area'): void {
        echo html_writer::start_div($classes);
    }

    /**
     * Close the standard shell content area.
     */
    public static function content_close(): void {
        echo html_writer::end_div();
    }

    /**
     * Build the Zone-A plugin navigation shared by every shell page.
     *
     * @return string Rendered navigation markup.
     */
    public static function section_nav(): string {
        $context = context_system::instance();
        $items = [
            [
                'url' => new moodle_url('/filter/translations/index.php'),
                'label' => get_string('navdashboard', 'filter_translations'),
                'icon' => 'fa-th-large',
                'match' => ['index.php', ''],
                'show' => has_capability('filter/translations:edittranslations', $context),
            ],
            [
                'url' => new moodle_url('/filter/translations/pluginsettings.php'),
                'label' => get_string('navsetup', 'filter_translations'),
                'icon' => 'fa-sliders-h',
                'match' => ['pluginsettings.php', 'onboarding.php', 'setupcoursefields.php', 'testdeepl.php'],
                'show' => has_capability('moodle/site:config', $context),
            ],
            [
                'url' => new moodle_url('/filter/translations/managetranslations.php'),
                'label' => get_string('translations', 'filter_translations'),
                'icon' => 'fa-language',
                'match' => ['managetranslations.php', 'edittranslation.php'],
                'show' => has_capability('filter/translations:edittranslations', $context),
            ],
            [
                'url' => new moodle_url('/filter/translations/managetranslationissues.php'),
                'label' => get_string('navproblems', 'filter_translations'),
                'icon' => 'fa-exclamation-triangle',
                'match' => ['managetranslationissues.php'],
                'show' => has_capability('filter/translations:edittranslations', $context),
            ],
            [
                'url' => new moodle_url('/filter/translations/manageglossary.php'),
                'label' => get_string('navglossary', 'filter_translations'),
                'icon' => 'fa-book',
                'match' => [
                    'manageglossary.php',
                    'editglossaryentry.php',
                    'manageglossarysync.php',
                    'glossaryimport.php',
                ],
                'show' => has_capability('filter/translations:edittranslations', $context),
            ],
            [
                'url' => new moodle_url('/filter/translations/import.php'),
                'label' => get_string('navtransfer', 'filter_translations'),
                'icon' => 'fa-exchange',
                'match' => ['import.php', 'export.php'],
                'show' => has_capability('filter/translations:bulkimporttranslations', $context) ||
                    has_capability('filter/translations:exporttranslations', $context),
            ],
        ];

        $html = html_writer::start_tag('nav', [
            'class' => 'lh-plugin-section-nav filter-translations-shell-nav',
            'aria-label' => get_string('pluginname', 'filter_translations'),
        ]);
        foreach ($items as $item) {
            if (empty($item['show'])) {
                continue;
            }
            $attributes = ['class' => 'lh-plugin-section-nav__item'];
            if (self::nav_item_is_current($item['match'])) {
                $attributes['aria-current'] = 'page';
            }
            $html .= html_writer::link($item['url'],
                html_writer::tag('i', '', ['class' => 'fa ' . $item['icon'], 'aria-hidden' => 'true']) .
                html_writer::span($item['label']),
                $attributes
            );
        }
        $html .= html_writer::end_tag('nav');

        return $html;
    }

    /**
     * Close the content area + shell. Call this BEFORE $OUTPUT->footer().
     */
    public static function close(): void {
        self::content_close();
        self::close_page();
    }

    /**
     * Close the shell wrapper after a manually managed content area.
     */
    public static function close_page(): void {
        if (self::$opened) {
            echo html_writer::end_div();
            self::$opened = false;
        }
    }

    /**
     * Check whether any configured script name matches the current page.
     *
     * @param array $matches Script basenames that should mark a nav item active.
     * @return bool
     */
    private static function nav_item_is_current(array $matches): bool {
        $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
        return in_array($script, $matches, true);
    }

    /**
     * Render Zone A/B header markup.
     *
     * @param array $data Header render data.
     * @return string
     */
    private static function header(array $data): string {
        $headerclass = 'lh-plugin-header';
        if (!empty($data['sectionnav'])) {
            $headerclass .= ' lh-plugin-header--has-section-nav';
        }
        if (!empty($data['headerclass'])) {
            $headerclass .= ' ' . $data['headerclass'];
        }

        $title = html_writer::span(
            (!empty($data['homeurl'])
                ? html_writer::link($data['homeurl'], $data['name'], [
                    'class' => 'lh-plugin-header__home',
                    'aria-label' => $data['homelabel'] ?? $data['name'],
                    'title' => $data['homelabel'] ?? $data['name'],
                ])
                : s($data['name'])) .
            html_writer::span('|', 'lh-plugin-header__sep') .
            html_writer::span($data['tagline'] ?? '', 'lh-plugin-header__tagline'),
            'lh-plugin-header__name-text'
        );

        $actions = '';
        foreach ($data['ctas'] ?? [] as $cta) {
            $actions .= html_writer::link($cta['url'],
                (!empty($cta['fa']) ? html_writer::tag('i', '', ['class' => 'fa ' . $cta['fa'], 'aria-hidden' => 'true']) : '') .
                html_writer::span($cta['label']),
                ['class' => 'lh-btn-' . ($cta['modifier'] ?? 'outline')]
            );
        }
        if (!empty($data['createurl'])) {
            $actions .= html_writer::link($data['createurl'],
                html_writer::tag('i', '', ['class' => 'fa fa-plus', 'aria-hidden' => 'true']),
                [
                    'class' => 'lh-plugin-header__action lh-plugin-header__action--create',
                    'aria-label' => $data['createlabel'] ?? get_string('add'),
                    'title' => $data['createlabel'] ?? get_string('add'),
                ]
            );
        }
        foreach ($data['headeractionicons'] ?? [] as $action) {
            $actions .= html_writer::link($action['url'],
                html_writer::tag('i', '', ['class' => 'fa ' . $action['faicon'], 'aria-hidden' => 'true']),
                [
                    'class' => trim('lh-plugin-header__action ' . ($action['modifierclass'] ?? '')),
                    'aria-label' => $action['label'],
                    'title' => $action['label'],
                ]
            );
        }

        $html = html_writer::start_div($headerclass);
        $html .= html_writer::tag('div',
            html_writer::tag('div',
                html_writer::tag('div', $title, ['class' => 'lh-plugin-header__name']),
                ['class' => 'lh-plugin-header__title-block']
            ) .
            html_writer::tag('div', $actions, ['class' => 'lh-plugin-header__actions']),
            ['class' => 'lh-plugin-header__row']
        );
        $html .= $data['sectionnav'] ?? '';
        $html .= html_writer::end_div();

        if (!empty($data['actionicons'])) {
            $actionhtml = '';
            foreach ($data['actionicons'] as $action) {
                $actionhtml .= html_writer::link($action['url'],
                    html_writer::tag('i', '', ['class' => 'fa ' . $action['faicon'], 'aria-hidden' => 'true']) .
                    html_writer::span($action['label'], 'sr-only'),
                    [
                        'class' => trim('lh-icon-action ' . ($action['modifierclass'] ?? '')),
                        'aria-label' => $action['label'],
                        'title' => $action['label'],
                    ]
                );
            }
            $html .= html_writer::tag('div',
                html_writer::tag('nav', $actionhtml, [
                    'class' => 'lh-plugin-infobar__actions',
                    'aria-label' => get_string('actions'),
                ]),
                ['class' => 'lh-plugin-infobar lh-plugin-infobar--actions']
            );
        } else if (!empty($data['stats'])) {
            $stats = '';
            foreach ($data['stats'] as $stat) {
                $stats .= html_writer::span(
                    (!empty($stat['faicon']) ? html_writer::tag('i', '', [
                        'class' => 'fa ' . $stat['faicon'],
                        'aria-hidden' => 'true',
                    ]) : '') .
                    (!empty($stat['value']) ? html_writer::tag('strong', $stat['value']) : '') .
                    s($stat['label']),
                    'lh-plugin-infobar__stat'
                );
            }
            $html .= html_writer::tag('div',
                html_writer::tag('div', $stats, ['class' => 'lh-plugin-infobar__stats']),
                ['class' => 'lh-plugin-infobar']
            );
        }

        return $html;
    }
}
