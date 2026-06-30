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

use filter_translations\edittranslationform;
use filter_translations\translation;
use filter_translations\unifieddiff;
use filter_translations\output\shell;

require_once(__DIR__ . '../../../config.php');

$id = optional_param('id', null, PARAM_INT);
$contextid = optional_param('contextid', null, PARAM_INT);
$generatedhash = optional_param('generatedhash', null, PARAM_TEXT);
$foundhash = optional_param('foundhash', null, PARAM_TEXT);
$targetlanguage = optional_param('targetlanguage', '', PARAM_TEXT);
$rawtext = optional_param('rawtext', null, PARAM_RAW);
$returnurl = optional_param('returnurl', new moodle_url('/filter/translations/managetranslations.php'), PARAM_URL);

if (empty($id)) {
    $title = get_string('createtranslation', 'filter_translations');
} else {
    $title = get_string('edittranslation', 'filter_translations');
}

if (empty($contextid)) {
    $context = context_system::instance();
} else {
    $context = context::instance_by_id($contextid);
}

require_login();

require_capability('filter/translations:edittranslations', $context);

$url = new moodle_url('/filter/translations/edittranslation.php');

$PAGE->set_context($context);

$coursecontext = $PAGE->context->get_course_context(false);
if (!empty($coursecontext)) {
    $PAGE->set_course(get_course($coursecontext->instanceid));
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading('');

$persistent = null;
if (empty($id)) {
    if ($targetlanguage == '') {
        $targetlanguage = current_language();
    }

    $persistent = new translation();
    $persistent->set('md5key', empty($foundhash) ? $generatedhash : $foundhash);
    $persistent->set('targetlanguage', $targetlanguage);
    $persistent->set('substitutetext', $rawtext);
} else {
    $persistent = new translation($id); // Load translation record for this id.
    if (!empty($foundhash)) {
        $persistent->set('md5key', $foundhash); // Use the foundhash.
    }

    $url->param('id', $id);

    if ($targetlanguage == '') {
        $targetlanguage = $persistent->get('targetlanguage');
    }

    if ($rawtext === null) {
        $rawtext = $persistent->get('rawtext');
    }

    if ($persistent->get('targetlanguage') == $CFG->lang) {
        require_capability('filter/translations:editsitedefaulttranslations', $context);
    }
}
$persistent->set('contextid', $contextid);

$istranslationstale = !empty($generatedhash) && !empty($persistent->get('id')) &&
    $persistent->get('lastgeneratedhash') !== $generatedhash;
if (!empty($generatedhash)) {
    $persistent->set('lastgeneratedhash', $generatedhash);
}

$showdiff = false;
$old = false;
if (!empty($rawtext) && !empty($persistent->get('rawtext')) && $rawtext != $persistent->get('rawtext')) {
    $PAGE->requires->js_call_amd('filter_translations/diffrenderer', 'init',
        ['changeset' => unifieddiff::generatediff($persistent->get('rawtext'), $rawtext)]);
    $showdiff = true;
    $old = $persistent->get('rawtext');
}

if (!empty($rawtext)) {
    $persistent->set('rawtext', $rawtext);
}

$sourceishtml = (string)$rawtext !== strip_tags((string)$rawtext);
$formtype = edittranslationform::FORMTYPE_RICH;

$form = new edittranslationform($url->out(false),
    [
        'persistent' => $persistent,
        'formtype' => $formtype,
        'showdiff' => $showdiff,
        'old' => $old,
        'sourceishtml' => $sourceishtml,
        'returnurl' => $returnurl,
    ]);

if ($data = $form->get_data()) {
    if (!empty($data->deletebutton) && has_capability('filter/translations:deletetranslations', $context)) {
        $persistent->delete();
        redirect($returnurl);
    }

    $persistent->from_record($form->filter_data_for_persistent($data));

    if ($formtype !== edittranslationform::FORMTYPE_RICH) {
        $persistent->set('substitutetext', $data->substitutetext_plain);
    }

    if ($formtype == edittranslationform::FORMTYPE_RICH) {
        $data = file_postupdate_standard_editor($data, 'substitutetext', $form->get_substitute_text_editoroptions(), $context,
            'filter_translations',
            'substitutetext', $persistent->get('id'));

        $persistent->set('substitutetext', $data->substitutetext);
        $persistent->set('substitutetextformat', $data->substitutetextformat);
        // $persistent->update();
    }

    // Before saving, ensure we are not overwriting existing translation.
    $record = $DB->get_record('filter_translations',
            ['targetlanguage' => $targetlanguage, 'md5key' => $persistent->get('md5key')]
        );

    if (!$record) {
        // Nothing found, OK to add new entry.
        $persistent->set('id', 0); // Unset id.
        $persistent->create();
    } else if ($record->id == $persistent->get('id') && $record->targetlanguage == $persistent->get('targetlanguage')) {
        // Updating the translation in the same language, OK to update.
        $persistent->update();
    } else {
        notice(get_string('translationalreadyexists', 'filter_translations', $targetlanguage), $returnurl);
    }

    if (!empty($data->submitnextbutton)) {
        $nextrecords = $DB->get_records_select('filter_translations', 'id > :id', ['id' => $persistent->get('id')],
            'id ASC', 'id, contextid, targetlanguage', 0, 1);
        if (!empty($nextrecords)) {
            $nextrecord = reset($nextrecords);
            redirect(new moodle_url('/filter/translations/edittranslation.php', [
                'id' => $nextrecord->id,
                'contextid' => $nextrecord->contextid,
                'targetlanguage' => $nextrecord->targetlanguage,
                'returnurl' => $returnurl,
            ]));
        }
    }

    redirect($returnurl);
}
$form->set_data(['returnurl' => $returnurl, 'targetlanguage' => $targetlanguage]);

$PAGE->requires->js(new moodle_url('/filter/translations/lib/diff2html.js'));
$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/diff2html/bundles/css/diff2html.min.css'));

shell::require_css();
echo $OUTPUT->header();
shell::open($title, get_string('dashboardtranslations_desc', 'filter_translations'),
    shell::MODIFIER_EDITING);

if ($istranslationstale) {
    echo html_writer::div(get_string('staletranslation', 'filter_translations'), 'alert alert-warning');
}

echo html_writer::tag('h2', get_string('translation', 'filter_translations'),
    ['class' => 'filter-translations-settings-heading']);
echo html_writer::start_tag('section', ['class' => 'filter-translations-settings-card filter-translations-edit-card']);
echo html_writer::start_div('filter-translations-settings-card__body filter-translations-form-card');
$form->display();
echo html_writer::end_div();
echo html_writer::end_tag('section');

shell::close();
echo $OUTPUT->footer();
