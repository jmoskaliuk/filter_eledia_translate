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

use html_writer;
use moodle_url;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tablelib.php');

/**
 * Glossary management table.
 *
 * @package filter_translations
 */
class manageglossary_table extends table_sql {
    /** @var string[] */
    private $languages;

    /** @var string[] */
    private $courses;

    /**
     * Constructor.
     *
     * @param \stdClass $filterparams
     * @param string $sortcolumn
     */
    public function __construct(\stdClass $filterparams, string $sortcolumn) {
        global $DB, $PAGE;

        parent::__construct('filter_translations_glossary_table');

        $this->languages = get_string_manager()->get_list_of_translations(true);
        $this->courses = [];
        $courses = $DB->get_records_select('course', 'id > :siteid', ['siteid' => SITEID], '', 'id, fullname');
        foreach ($courses as $course) {
            $this->courses[$course->id] = format_string($course->fullname);
        }

        $columns = ['sourcephrase', 'targetphrase', 'sourcelanguage', 'targetlanguage', 'courseid', 'status', 'priority', 'actions'];
        $headers = [
            get_string('sourcephrase', 'filter_translations'),
            get_string('targetphrase', 'filter_translations'),
            get_string('sourcelanguage_short', 'filter_translations'),
            get_string('targetlanguage_short', 'filter_translations'),
            get_string('courseid', 'filter_translations'),
            get_string('status', 'filter_translations'),
            get_string('priority', 'filter_translations'),
            get_string('actions'),
        ];

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true, $sortcolumn);
        $this->pageable(true);
        $this->no_sorting('actions');

        $wheres = [];
        $params = [];

        if (!empty($filterparams->sourcephrase)) {
            $params['sourcephrase'] = '%' . $DB->sql_like_escape($filterparams->sourcephrase) . '%';
            $wheres[] = $DB->sql_like('g.sourcephrase', ':sourcephrase', false);
        }

        if (!empty($filterparams->targetphrase)) {
            $params['targetphrase'] = '%' . $DB->sql_like_escape($filterparams->targetphrase) . '%';
            $wheres[] = $DB->sql_like('g.targetphrase', ':targetphrase', false);
        }

        if (!empty($filterparams->sourcelanguage)) {
            $params['sourcelanguage'] = $filterparams->sourcelanguage;
            $wheres[] = 'g.sourcelanguage = :sourcelanguage';
        }

        if (!empty($filterparams->targetlanguage)) {
            $params['targetlanguage'] = $filterparams->targetlanguage;
            $wheres[] = 'g.targetlanguage = :targetlanguage';
        }

        if (!empty($filterparams->status)) {
            $params['status'] = $filterparams->status;
            $wheres[] = 'g.status = :status';
        }

        if ((int)$filterparams->courseid === -1) {
            $wheres[] = 'g.courseid IS NULL';
        } else if (!empty($filterparams->courseid)) {
            $params['courseid'] = $filterparams->courseid;
            $wheres[] = 'g.courseid = :courseid';
        }

        if (empty($wheres)) {
            $wheres[] = '1=1';
        }

        $this->set_sql('g.*', '{filter_translations_glossary} g', implode(' AND ', $wheres), $params);
        $this->define_baseurl($PAGE->url);
    }

    /**
     * Source phrase column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_sourcephrase($row): string {
        return shorten_text(strip_tags($row->sourcephrase), 80);
    }

    /**
     * Target phrase column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_targetphrase($row): string {
        return shorten_text(strip_tags($row->targetphrase), 80);
    }

    /**
     * Source language column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_sourcelanguage($row): string {
        return $this->language_tag($row->sourcelanguage);
    }

    /**
     * Target language column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_targetlanguage($row): string {
        return $this->language_tag($row->targetlanguage);
    }

    /**
     * Compact language cell with full language name as tooltip.
     *
     * @param string $language
     * @return string
     */
    private function language_tag(string $language): string {
        $label = $this->languages[$language] ?? $language;
        $code = strtoupper(str_replace('_', '-', $language));

        return html_writer::tag('span', s($code), [
            'class' => 'lh-plugin-tag',
            'title' => $label,
        ]);
    }

    /**
     * Status column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_status($row): string {
        $statuses = glossary_entry::status_options();
        return $statuses[$row->status] ?? (string)$row->status;
    }

    /**
     * Course column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_courseid($row): string {
        if (empty($row->courseid)) {
            return get_string('glossaryscope_global', 'filter_translations');
        }

        return $this->courses[$row->courseid] ?? (string)$row->courseid;
    }

    /**
     * Actions column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_actions($row): string {
        $label = get_string('edit');
        return html_writer::div(
            html_writer::link(new moodle_url('/filter/translations/editglossaryentry.php', [
                'id' => $row->id,
                'returnurl' => $this->baseurl->out(false),
            ]),
                html_writer::tag('i', '', ['class' => 'fa fa-pencil', 'aria-hidden' => 'true']) .
                html_writer::span($label, 'sr-only'),
                [
                    'class' => 'lh-icon-action',
                    'aria-label' => $label,
                    'title' => $label,
                ]
            ),
            'filter-translations-table-actions'
        );
    }
}
