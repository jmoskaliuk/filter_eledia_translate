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
 * @package filter_translations
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace filter_translations;

use moodle_url;
use table_sql;
use html_writer;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tablelib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Table to list and manage translation issues.
 */
class managetranslations_table extends table_sql {
    /**
     * @var array
     */
    private $languages = null;

    /**
     * Set up the table, manage filters etc.
     *
     * @param $filterparams
     * @param $sortcolumn
     * @param $download
     * @throws \coding_exception
     */
    public function __construct($filterparams, $sortcolumn, $download) {
        global $DB, $PAGE, $CFG, $OUTPUT;

        parent::__construct('managetranslations_table');

        $this->languages = get_string_manager()->get_list_of_translations();

        $this->filterparams = $filterparams;

        $headers = [];
        $columns = [];

        $canbulkdelete = has_capability('filter/translations:bulkdeletetranslations', $PAGE->context);
        if ($canbulkdelete && empty($download)) {
            $mastercheckbox = new \core\output\checkbox_toggleall('translations-table', true, [
                'id' => 'select-all-translations',
                'name' => 'select-all-translations',
                'label' => get_string('selectall'),
                'labelclasses' => 'sr-only',
                'classes' => 'm-1',
                'checked' => false,
            ]);

            $headers[] = $OUTPUT->render($mastercheckbox);
            $columns[] = 'select';
        }

        $columns = array_merge($columns, [
            'md5key',
            'targetlanguage',
            'translationsource',
            'rawtext',
            'substitutetext',
            'usermodified',
        ]);
        $headers = array_merge($headers, [
            get_string('hash', 'filter_translations'),
            get_string('targetlanguage_short', 'filter_translations'),
            get_string('translationsource', 'filter_translations'),
            get_string('rawtext', 'filter_translations'),
            get_string('substitutetext', 'filter_translations'),
            get_string('translatedby', 'filter_translations'),
        ]);

        if (empty($download)) {
            $columns[] = 'actions';
            $headers[] = get_string('actions');
        }

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->no_sorting('select');
        $this->sort_default_column = $sortcolumn;

        $wheres = [];
        $params = [];

        if (!empty($this->filterparams->rawtext)) {
            $params['rawtext'] = '%' . $DB->sql_like_escape($this->filterparams->rawtext) . '%';
            $wheres[] = $DB->sql_like('t.rawtext', ':rawtext', false);
        }

        if (!empty($this->filterparams->substitutetext)) {
            $params['substitutetext'] = '%' . $DB->sql_like_escape($this->filterparams->substitutetext) . '%';
            $wheres[] = $DB->sql_like('t.substitutetext', ':substitutetext', false);
        }

        if (!empty($this->filterparams->targetlanguage)) {
            $params['targetlanguage'] = $this->filterparams->targetlanguage;
            $wheres[] = 't.targetlanguage = :targetlanguage';
        } else if (!has_capability('filter/translations:editsitedefaulttranslations', $PAGE->context)) {
            $params['targetlanguage'] = $CFG->lang;
            $wheres[] = 't.targetlanguage <> :targetlanguage';
        }

        if (!empty($this->filterparams->hash)) {
            $params['hash'] = $this->filterparams->hash;
            $params['hash2'] = $this->filterparams->hash;
            $wheres[] = '(t.lastgeneratedhash = :hash OR t.md5key = :hash2)';
        }

        if (!empty($this->filterparams->usermodified)) {
            $params['userid'] = $this->filterparams->usermodified;
            $wheres[] = '(t.usermodified = :userid)';
        }

        if (empty($wheres)) {
            $wheres[] = '1=1';
        }

        $userfieldsapi = \core_user\fields::for_name()->including('username', 'deleted');
        $userfields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $this->set_sql('t.id, t.md5key, t.targetlanguage, t.translationsource, t.rawtext, t.substitutetext, ' .
                't.usermodified, t.contextid, ' . $userfields,
                '{filter_translations} t LEFT JOIN {user} u on t.usermodified = u.id',
            implode(' AND ', $wheres),
            $params);
    }

    /**
     * Generate the select column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_select($row) {
        global $OUTPUT;

        if ($this->is_downloading()) {
            return;
        }

        $checkbox = new \core\output\checkbox_toggleall('translations-table', false, [
            'classes' => 'translationcheckbox m-1',
            'id' => 'translation' . $row->id,
            'name' => 'translationid[]',
            'value' => $row->id,
            'checked' => false,
            'label' => get_string('selectitem', 'moodle', $row->id),
            'labelclasses' => 'accesshide',
        ]);

        return $OUTPUT->render($checkbox);
    }

    /**
     * Truncate and deHTML the raw text.
     *
     * @param $row
     * @return string
     */
    public function col_rawtext($row) {
        if ($this->is_downloading()) {
            return $row->rawtext;
        }

        return shorten_text(strip_tags($row->rawtext));
    }

    /**
     * Truncate and deHTML the subtitute text.
     *
     * @param $row
     * @return string
     */
    public function col_substitutetext($row) {
        if ($this->is_downloading()) {
            return $row->substitutetext;
        }

        return shorten_text(strip_tags($row->substitutetext));
    }

    /**
     * Compact hash column.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_md5key($row): string {
        if ($this->is_downloading()) {
            return $row->md5key;
        }

        return html_writer::tag('span',
            html_writer::tag('i', '', ['class' => 'fa fa-eye', 'aria-hidden' => 'true']) .
            html_writer::span($row->md5key, 'sr-only'),
            [
                'class' => 'lh-icon-action filter-translations-hash-eye',
                'title' => $row->md5key,
                'aria-label' => get_string('hash', 'filter_translations') . ': ' . $row->md5key,
            ]
        );
    }

    /**
     * Get the full name for ISO language code.
     *
     * @param $row
     * @return mixed
     */
    public function col_targetlanguage($row) {
        if ($this->is_downloading()) {
            return $row->targetlanguage;
        }

        if (isset($this->languages[$row->targetlanguage])) {
            $label = $this->languages[$row->targetlanguage];
        } else {
            $label = $row->targetlanguage;
        }

        return html_writer::tag('span', strtoupper($row->targetlanguage), [
            'class' => 'lh-plugin-tag',
            'title' => $label,
        ]);
    }

    /**
     * Translation source.
     *
     * @param \stdClass $row
     * @return string
     */
    public function col_translationsource($row): string {
        if ($this->is_downloading()) {
            return $row->translationsource == translation::SOURCE_AUTOMATIC ?
                get_string('translationsource_automatic', 'filter_translations') :
                get_string('translationsource_manual', 'filter_translations');
        }

        if ((int)$row->translationsource === translation::SOURCE_AUTOMATIC) {
            return html_writer::span(get_string('translationsource_automatic', 'filter_translations'),
                'lh-plugin-tag lh-plugin-tag--warning');
        }

        return html_writer::span(get_string('translationsource_manual', 'filter_translations'),
            'lh-plugin-tag lh-plugin-tag--active');
    }

    /**
     * Linked name of the user who last modified the translation.
     *
     * @param $row
     * @return \lang_string|string
     * @throws \moodle_exception
     */
    public function col_usermodified($row) {
        if ($this->is_downloading()) {
            return fullname($row);
        }

        return \html_writer::link(
            new moodle_url('/user/view.php',
            ['id' => $row->usermodified]), fullname($row)
        );
    }

    /**
     * Show actions - currenly just an edit button.
     * @param $row
     * @return string|void
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($row) {
        global $PAGE;

        if ($this->is_downloading()) {
            return;
        }

        $actions = html_writer::link(new moodle_url('/filter/translations/edittranslation.php', [
                'id' => $row->id,
                'contextid' => $row->contextid,
                'targetlanguage' => $row->targetlanguage,
                'returnurl' => $PAGE->url,
            ]),
            html_writer::tag('i', '', ['class' => 'fa fa-pencil', 'aria-hidden' => 'true']) .
            html_writer::span(get_string('edittranslation', 'filter_translations'), 'sr-only'),
            [
                'class' => 'lh-icon-action',
                'aria-label' => get_string('edittranslation', 'filter_translations'),
                'title' => get_string('edittranslation', 'filter_translations'),
            ]
        );

        if ((int)$row->translationsource === translation::SOURCE_AUTOMATIC) {
            $actions .= html_writer::link(new moodle_url('/filter/translations/approvetranslation.php', [
                    'id' => $row->id,
                    'sesskey' => sesskey(),
                    'returnurl' => $PAGE->url,
                ]),
                html_writer::tag('i', '', ['class' => 'fa fa-check', 'aria-hidden' => 'true']) .
                html_writer::span(get_string('approvetranslation', 'filter_translations'), 'sr-only'),
                [
                    'class' => 'lh-icon-action lh-icon-action--primary',
                    'aria-label' => get_string('approvetranslation', 'filter_translations'),
                    'title' => get_string('approvetranslation', 'filter_translations'),
                ]
            );
        }

        return html_writer::div($actions, 'filter-translations-table-actions');
    }

    /**
     * Wrap in a form to power the select checkboxes and related buttons.
     *
     * @return void
     */
    public function wrap_html_start() {
        global $PAGE;

        // Begin the form.
        echo html_writer::start_tag('form', ['method' => 'post', 'id' => 'bulkdeleteform', 'action' => 'action_redir.php']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'returnurl', 'value' => $PAGE->url]);
    }

    /**
     * Finish wrapping the form.
     *
     * @return void
     */
    public function wrap_html_finish() {
        echo html_writer::start_tag('div', ['class' => 'actions my-1']);
        $this->submit_buttons();
        echo html_writer::end_tag('div');
        // Close the form.
        echo html_writer::end_tag('form');
    }

    /**
     * Output the submit button for the bulk delete action.
     */
    protected function submit_buttons() {
        global $PAGE;
        if (has_capability('filter/translations:bulkdeletetranslations', $PAGE->context)) {
            echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'bulkdelete']);

            $deletebuttonparams = [
                'type'  => 'submit',
                'class' => 'lh-btn-outline filter-translations-action-button',
                'id'    => 'deletetranslationsbutton',
                'name'  => 'delete',
                'value' => get_string('deleteselected', 'filter_translations'),
                'data-action' => 'toggle',
                'data-togglegroup' => 'translations-table',
                'data-toggle' => 'action',
                'disabled' => true,
            ];
            echo html_writer::empty_tag('input', $deletebuttonparams);
            $PAGE->requires->event_handler('#deletetranslationsbutton', 'click', 'M.util.show_confirm_dialog',
                    ['message' => get_string('bulkdeleteconfirmation', 'filter_translations')]);
        }
    }

    /**
     * Download the data in the selected format.
     *
     * @param string $format The format to download the report.
     */
    public function download($format) {
        $filename = 'filter_translations_' . userdate(time(), get_string('backupnameformat', 'langconfig'),
                99, false);

        $this->is_downloading($format, $filename, get_string('translations', 'filter_translations'));
        $this->out(100, false);
    }
}
