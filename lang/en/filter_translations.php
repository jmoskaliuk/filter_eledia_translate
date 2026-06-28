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
 * @copyright 2023, Tina John <johnt.22.tijo@gmail.com> 
 */

defined('MOODLE_INTERNAL') || die();

$string['any'] = 'Any';
$string['bulkdeleteconfirmation'] = 'Are you absolutely sure you want to completely delete these translations?';
$string['cachingmode'] = 'Caching mode';
$string['cachingmode_desc'] = 'For sites with a relatively small number of courses and high volume of users we recommend Application level, for high number of courses we recommend Session, to minimise caching we recommend Request.';
$string['cachedef_translatedtext_1'] = 'Translated text (Request level)';
$string['cachedef_translatedtext_2'] = 'Translated text (Session level)';
$string['cachedef_translatedtext_4'] = 'Translated text (Application level)';
$string['cannottranslatelang'] = 'Translation in the selected language is disabled.';
$string['cannottranslatepage'] = 'This page cannot be translated.';
$string['casesensitive'] = 'Case sensitive';
$string['cleanuptranslationissues'] = 'Cleanup old translation issues records';
$string['clihelptext_copytranslations'] = 'This tool will copy over translations matching the hash of a content and save it under the found hash of the content for each specified table/field. USE WITH EXTREME CARE.
To execute the task run this command again specifying a mode e.g:
php cli/copy_translations.php --mode=listcolumns

Valid modes are:
process - Actually do it...
dryrun - See how many records will be affected. It does not write any changes to the database.
listcolumns - List the tables and columns that will be impacted

Suggested use is to list out the columns that will could be modified:
php filter/translations/cli/copy_translations.php --mode=listcolumns > /Users/moodleadmin/cols.json

Edit the file using a text editor to remove any that should be ignored.
Then run to process those columns:

php filter/translations/cli/copy_translations.php --mode=process --file=/Users/moodleadmin/cols.json

You can do a dryrun to check how many records will be affected when this process runs.
php filter/translations/cli/copy_translations.php --mode=dryrun --file=/Users/moodleadmin/cols.json
';
$string['clihelptext_insertspans'] = 'This tool will append translation hashes to all rich text fields found in the database, USE WITH EXTREME CARE.
To execute the task run this command again specifying a mode e.g:
php cli/insert_spans.php --mode=listcolumns

Valid modes are:
process - Actually do it...
listcolumns - List the tables and columns that will be impacted

Suggested use is to list out the columns that will could be modified:
php filter/translations/cli/insert_spans.php --mode=listcolumns > /Users/moodleadmin/cols.json

Edit the file using a text editor to remove any that should be ignored.
Then run to process those columns:

php filter/translations/cli/insert_spans.php --mode=process --file=/Users/moodleadmin/cols.json
';
$string['clihelptext_migrate_filter_fulltranslate'] = 'This tool will copy translations from the filter_fulltranslate plugin into the filter_translations plugin.
Any entries previously copied using this tool will be duplicated.
To execute the task run this command again specifying --confirm e.g:
php cli/migrate_filter_fulltranslate.php --confirm';
$string['clihelptext_removeduplicatehashes'] = 'This tool will find and remove duplicate translation hashes found in each specified table/field. USE WITH EXTREME CARE.
To execute the task run this command again specifying a mode e.g:
php cli/remove_duplicate_hashes.php --mode=listcolumns

Valid modes are:
process - Actually do it...
dryrun - See how many records will be affected. It does not write any changes to the database.
listcolumns - List the tables and columns that will be impacted

Suggested use is to list out the columns that will could be modified:
php filter/translations/cli/remove_duplicate_hashes.php --mode=listcolumns > /Users/moodleadmin/cols.json

Edit the file using a text editor to remove any that should be ignored.
Then run to process those columns:

php filter/translations/cli/remove_duplicate_hashes.php --mode=process --file=/Users/moodleadmin/cols.json

You can do a dryrun to check how many records will be affected when this process runs.
php filter/translations/cli/remove_duplicate_hashes.php --mode=dryrun --file=/Users/moodleadmin/cols.json
';
$string['columndefinition'] = 'Tables/columns to check';
$string['columndefinition_desc'] = 'Scheduled tasks are used to conduct periodic maintenance and syncing of translations.<br>
    The default configuration includes Quiz intro text and Question Bank rich-text fields such as question text, general feedback, answers, answer feedback, hints and combined feedback for common question types.<br>
    Enter the tables/columns to check in JSON format, eg:
    {
        "label": [
            "intro"
        ]
    }<br>
    You can get the table/columns list by running:<br>
    <pre>php filter/translations/cli/remove_duplicate_hashes.php --mode=listcolumns</pre>
    Scheduled tasks for this plugin are disabled by default. You can enable them from the <a href="tool/task/scheduledtasks.php">Scheduled tasks</a> page.
';
$string['columndefinitionfileerror'] = 'Missing or invalid column definition file';
$string['columndefinitionjsonerror'] = 'Invalid column definition json';
$string['context'] = 'Context';
$string['copytranslations'] = 'Copy translations';
$string['coursecontrol'] = 'Course translation control';
$string['coursecontrol_desc'] = 'Controls whether courses are translated from Moodle course custom fields, legacy course tags, or both.';
$string['coursecontrolsource'] = 'Course control source';
$string['coursecontrolsource_customfields'] = 'Course custom fields only';
$string['coursecontrolsource_customfields_fallback_tags'] = 'Course custom fields, then legacy tags';
$string['coursecontrolsource_desc'] = 'Choose where the filter reads course-level translation settings from. Course custom fields, then legacy tags is the recommended default for visible course settings while preserving existing tagged courses.';
$string['coursecontrolsource_tags'] = 'Legacy course tags only';
$string['coursefieldenabled'] = 'Course custom field for enabling translation';
$string['coursefieldenabled_desc'] = 'Shortname of a Moodle course custom field that enables translation for the course. Recommended type: checkbox. Default: eledia_translate_enabled.';
$string['coursefieldenabled_field_desc'] = 'Enables eLeDia content translation for this course.';
$string['coursefieldenabled_name'] = 'Enable content translation';
$string['coursefieldcreated'] = 'Course custom field "{$a}" has been created.';
$string['coursefieldexists'] = 'Course custom field "{$a}" already exists.';
$string['coursefieldlanguages'] = 'Course custom field for target languages';
$string['coursefieldlanguages_desc'] = 'Shortname of a Moodle course custom field containing the allowed target languages. The setup helper creates this as a searchable language multi-select field. Leave empty on a course to allow all languages when translation is enabled. Default: eledia_translate_languages.';
$string['coursefieldlanguages_field_desc'] = 'Allowed Moodle target languages for this course. Leave empty to allow all languages when translation is enabled.';
$string['coursefieldlanguages_name'] = 'Content translation target languages';
$string['courseid'] = 'Course ID';
$string['coursetagenabled'] = 'Legacy course tag for enabling translation';
$string['coursetagenabled_desc'] = 'Course tag that enables translation when tag-based control is used. Default: deepl.';
$string['createglossaryentry'] = 'Create glossary entry';
$string['createtranslation'] = 'Create translation';
$string['current'] = 'Current';
$string['deleteissuesconfirmation'] = 'Are you absolutely sure you want to completely delete these entires?';
$string['deleteselected'] = 'Delete selected translations';
$string['deleteselectedentries'] = 'Delete selected entries';
$string['diff'] = 'Diff';
$string['dashboardconfiguration'] = 'Configuration';
$string['dashboardcreateglossary_desc'] = 'Add a single terminology entry for global or course-specific translation.';
$string['dashboardexportglossary_desc'] = 'Download glossary terminology as CSV.';
$string['dashboardexport_desc'] = 'Export missing course translations to CSV.';
$string['dashboardglossary_desc'] = 'Create, review, import, export and sync controlled terminology.';
$string['dashboardimportglossary_desc'] = 'Upload glossary terminology from CSV.';
$string['dashboardimport_desc'] = 'Import translated CSV files into the translation store.';
$string['dashboardissues_desc'] = 'Review missing and stale translations collected while users browse courses.';
$string['dashboardpendingsyncgroups'] = 'Pending glossary sync groups';
$string['dashboardstatus'] = 'Status overview';
$string['dashboardsync_desc'] = 'Preview and synchronise approved glossary entries with DeepL glossaries.';
$string['dashboardtranslations_desc'] = 'Search, create and edit stored content translations.';
$string['dashboardworkflows'] = 'Workflows';
$string['editglossaryentry'] = 'Edit glossary entry';
$string['edittranslation'] = 'Edit translation';
$string['edittranslationsbutton'] = 'Edit translation';
$string['excludelang'] = 'Languages to exclude from translation';
$string['excludelang_desc'] = 'List of languages to entirely exclude from translation.';
$string['export'] = 'Export';
$string['exportglossary'] = 'Export glossary';
$string['exporttranslations'] = 'Export translations';
$string['fieldrequired'] = 'Field "{$a}" is not allowed. The allowed fields are: "md5key, rawtext, substitutetext, targetlanguage, contextid".';
$string['fieldsmismatch'] = 'Please check the fields in the CSV file. The required fields are: "md5key, rawtext, substitutetext, targetlanguage, contextid"';
$string['fieldwrongorder'] = 'Field "{$a}" is in an incorrect order. The fields order is: "md5key, rawtext, substitutetext, targetlanguage, contextid".';
$string['filetoimport'] = 'File to import';
$string['filetoimport_help'] = 'Browse for and select the CSV file on your computer which contains the translations to import.';
$string['filtername'] = 'Content translations';
$string['filteroptions'] = 'Filter options';
$string['foundhash'] = 'Found hash';
$string['generatedhash'] = 'Generated (content) hash';
$string['hash'] = 'Hash';
$string['importsummary'] = 'Summary of import';
$string['importtranslations'] = 'Import translations';
$string['linenumber'] = 'Line number';
$string['processedcount'] = 'Lines in file: {$a}';
$string['selectcourse'] = 'Select a course...';
$string['errorselectcourse'] = 'Please select a course.';
$string['skippedcount'] = 'Lines skipped: {$a}';
$string['reason'] = 'Reason';
$string['reasonimportskipped1'] = 'Language not available on site.';
$string['reasonimportskipped2'] = 'Translation record for this hash already exists.';
$string['reasonimportskipped3'] = 'Translation data is incomplete.';
$string['insertspans'] = 'Insert translation spans tags';
$string['issue'] = 'Status';
$string['issue_10'] = 'Stale';
$string['issue_20'] = 'Missing';
$string['languagestringreverse'] = 'Reverse look up language strings';
$string['languagestringreverse_enable'] = 'Enable reverse look up language strings';
$string['logdebounce'] = 'Debounce log duration';
$string['logging'] = 'Logging';
$string['logexcludelang'] = 'Languages to exclude from log';
$string['logexcludelang_desc'] = 'List of languages to skip from logging into missing translations table.';
$string['loghistory'] = 'Keep translation history';
$string['logmissing'] = 'Log missing translations';
$string['logstale'] = 'Log stale translations';
$string['manageglossary'] = 'Manage glossary';
$string['managetranslationissues'] = 'Manage pending translations';
$string['managetranslations'] = 'Manage translations';
$string['md5key'] = 'Translation hash key';
$string['notes'] = 'Notes';
$string['nohash'] = 'No translation hash key found';
$string['notranslation'] = 'No translation found';
$string['old'] = 'Old content';
$string['openworkflow'] = 'Open';
$string['onboardingcheck_course'] = 'Course control has been selected';
$string['onboardingcheck_deeplsource'] = 'DeepL glossary usage has a source language';
$string['onboardingcheck_filter'] = 'Filter is globally enabled';
$string['onboardingcheck_headings'] = 'Filter applies to content and headings';
$string['onboardingcheck_provider'] = 'At least one automatic provider is enabled';
$string['onboardingcourse_desc'] = 'Choose how courses opt into translation and where target languages are maintained.';
$string['onboardingdeeplkey_desc'] = 'Leave empty to keep the currently saved API key.';
$string['onboardingfilter_desc'] = 'Enable the Moodle text filter and decide whether activity names and headings should be translated.';
$string['onboardingfilter_enable'] = 'Enable the Content translations filter globally';
$string['onboardingfilter_headings'] = 'Apply the filter to content and headings';
$string['onboardingfilter_headings_desc'] = 'Required for translating activity titles, section names and other format_string() output.';
$string['onboardingfinish_desc'] = 'Review the setup checks. You can return to any step above to adjust the configuration.';
$string['onboardingglossary_desc'] = 'Create or import terminology, then synchronise approved entries with DeepL glossaries when needed.';
$string['onboardingintro'] = 'This wizard walks through the required setup for the content translations filter.';
$string['onboardinglogging_desc'] = 'Configure how missing or stale translations are collected for editorial follow-up.';
$string['onboardingprovider_desc'] = 'Configure automatic translation providers. DeepL requires an API key; glossary usage also requires a source language.';
$string['onboardingstep_course'] = 'Course control';
$string['onboardingstep_filter'] = 'Filter';
$string['onboardingstep_finish'] = 'Finish';
$string['onboardingstep_glossary'] = 'Glossary';
$string['onboardingstep_logging'] = 'Logging';
$string['onboardingstep_provider'] = 'DeepL and providers';
$string['onboardingtitle'] = 'Content translations onboarding';
$string['pluginname'] = 'Content translations';
$string['pluginsettings'] = 'Plugin settings';
$string['pluginsetup'] = 'Content translations setup';
$string['pluginsetup_desc'] = 'Central setup and maintenance page for the content translations filter.';
$string['shell_backtodashboard'] = 'Content translations';
$string['shell_tagline'] = 'Overview';
$string['priority'] = 'Priority';
$string['privacy:metadata'] = 'The content translations plugin stores translation and glossary data.';
$string['rawtext'] = 'Original content';
$string['rawhtml'] = 'Original HTML';
$string['replaceduplicatehashes'] = 'Replace duplicate hashes';
$string['sameasrawcontent'] = 'Translated text is the same as original content';
$string['sameasrawcontentmessage'] = 'Please confirm if translated text is supposed to be the same as the original content, by ticking the checkbox above.';
$string['saveandcontinue'] = 'Save and continue';
$string['scheduledtasksheading'] = 'Maintenance scheduled tasks';
$string['showperfdata'] = 'Show performance data in footer';
$string['sourcephrase'] = 'Source phrase';
$string['sourcelanguage'] = 'Source language';
$string['staletranslation'] = 'Translation was created based on different source text. Please update the translation.';
$string['status'] = 'Status';
$string['startinlinetranslation'] = 'Start in-line translation';
$string['stopinlinetranslation'] = 'Stop in-line translation';
$string['substitutetext'] = 'Translated content';
$string['targetphrase'] = 'Target phrase';
$string['targetlanguage'] = 'Translation language';
$string['translate_none'] = 'Translate - no translation exists';
$string['translate_stale'] = 'Translate - translation needs updating';
$string['translate_good'] = 'Translate - translation is up to date';
$string['translatedby'] = 'Translated by';
$string['translation'] = 'Translation';
$string['translations'] = 'Translations';
$string['translationcreated'] = 'Content translation created';
$string['translationdeleted'] = 'Content translation deleted';
$string['translationupdated'] = 'Content translation updated';
$string['translationalreadyexists'] = 'Translation cannot be saved. A Translation for language "{$a}" already exists.';
$string['translationissuesinpagemissing'] = 'Missing on this page';
$string['translationissuesincoursemissing'] = 'Missing in this course';
$string['translationissuesinpagemissingall'] = 'All missing translations';
$string['translationissuesinpagestale'] = 'Stale on this page';
$string['translationissuesincoursestale'] = 'Stale in this course';
$string['translationissuesinpagestaleall'] = 'All stale translations';
$string['translationdetails'] = 'Translation details';
$string['translationid'] = 'Translation ID';
$string['translationissues'] = 'Translation issues';
$string['translations:bulkdeletetranslations'] = 'Bulk delete translations';
$string['translations:bulkimporttranslations'] = 'Bulk import translations';
$string['translations:deletetranslations'] = 'Delete translations';
$string['translations:edittranslationhashkeys'] = 'Edit hash keys';
$string['translations:editsitedefaulttranslations'] = 'Edit site default language translations';
$string['translations:edittranslations'] = 'Edit translations';
$string['translations:exporttranslations'] = 'Export translations';
$string['unknownformtype'] = 'Unknown form type';
$string['unknowncolumn'] = 'Unknown column or table';
$string['untranslatedpages'] = 'Pages to leave untranslated';
$string['untranslatedpages_desc'] = 'One per line.';
$string['url'] = 'Page';
$string['userid'] = 'User ID';
$string['wholeword'] = 'Whole word only';
$string['exportdescription'] = "<p>Export a CSV file with the missing translations of the selected course.</p>
                                <p>You can then translate the content using automated translation tools such as matecat.com, before importing the translations back into the course.</p>
                                <p>Important notes:</p>
                                <ul>
                                  <li>Only missing translations will be exported.</li>
                                  <li>Importing this file into a different Moodle site is currently not supported.</li>
                                </ul>";
$string['importdescription'] = "<p>Import a CSV file with the missing translations of a course.</p>
                                <p>Important notes:</p>
                                <ul>
                                  <li>Only missing translations will be uploaded.</li>
                                  <li>Importing translations from other Moodle sites is currently not supported.</li>
                                </ul>";
$string['deepl_apiendpoint'] = 'API Endpoint';
$string['deepl_apikey'] = 'API key';
$string['deepl_backoffonerror'] = 'Back off from erroring API';
$string['deepl_enable'] = 'Use DeepL Translate API';
$string['deepl_glossaryid'] = 'Glossary ID';
$string['deepl_glossaryid_desc'] = 'Optional DeepL glossary ID. DeepL requires a source language when a glossary is used. Configure a source language before enabling a manual or synced glossary.';
$string['deeplglossaryinvalidentries'] = '{$a} glossary entries contain tabs or line breaks and cannot be synced to DeepL TSV.';
$string['deeplglossarymissingid'] = 'DeepL did not return a glossary ID.';
$string['deeplglossarynoentries'] = 'No approved glossary entries found for this scope and language pair.';
$string['deeplglossarynosyncgroups'] = 'No approved glossary entries are available for DeepL sync.';
$string['deeplglossarystatus_error'] = 'Error';
$string['deeplglossarystatus_pending'] = 'Pending';
$string['deeplglossarystatus_synced'] = 'Synced';
$string['deeplglossarysync'] = 'DeepL glossary sync';
$string['deeplglossarysyncerror'] = 'DeepL glossary sync failed: {$a}';
$string['deeplglossarysyncfailed'] = 'DeepL glossary sync failed: {$a}';
$string['deeplglossarysyncpreview'] = 'DeepL glossary sync preview';
$string['deeplglossarysyncsuccess'] = 'DeepL glossary sync completed.';
$string['deeplnotconfigured'] = 'DeepL API key is not configured.';
$string['deepl_sourcelang'] = 'Source language';
$string['deepl_sourcelang_desc'] = 'Optional DeepL source language code, for example EN or DE. Leave empty to let DeepL auto-detect, unless a glossary ID is configured.';
$string['deepl_taghandlinghtml'] = 'Use DeepL HTML tag handling';
$string['deepl_taghandlinghtml_desc'] = 'Send Moodle HTML content to DeepL with tag_handling=html so tags are preserved more reliably.';
$string['deepltest'] = 'Test DeepL connection';
$string['deepltestfailed'] = 'DeepL test failed. Check the API key, endpoint, source language, glossary ID and the web server logs.';
$string['deepltestsuccess'] = 'DeepL test succeeded. Result: {$a}';
$string['deepltranslate'] = 'DeepL Translate';
$string['glossarystatus_approved'] = 'Approved';
$string['glossarystatus_archived'] = 'Archived';
$string['glossarystatus_draft'] = 'Draft';
$string['glossarystatus_reviewed'] = 'Reviewed';
$string['glossarycreatedcount'] = 'Glossary entries created: {$a}';
$string['glossaryfieldsmismatch'] = 'Please check the fields in the CSV file. The required fields are: "sourcephrase, targetphrase, sourcelanguage, targetlanguage, courseid, status, priority, casesensitive, wholeword, notes, deeplglossaryid".';
$string['glossaryfieldwrongorder'] = 'Field "{$a}" is in an incorrect order. The fields order is: "sourcephrase, targetphrase, sourcelanguage, targetlanguage, courseid, status, priority, casesensitive, wholeword, notes, deeplglossaryid".';
$string['glossaryimportinvalidcourse'] = 'Course ID does not exist.';
$string['glossaryimportinvalidlanguage'] = 'Source or target language is not available on this site.';
$string['glossaryimportinvalidpriority'] = 'Priority must be a positive integer.';
$string['glossaryimportinvalidstatus'] = 'Status is invalid. Use 10/draft, 20/reviewed, 30/approved or 40/archived.';
$string['glossaryimportmissingdata'] = 'Source phrase, target phrase, source language and target language are required.';
$string['glossaryscope'] = 'Glossary scope';
$string['glossaryscope_global'] = 'Global / all courses';
$string['glossaryscope_globalonly'] = 'Global entries only';
$string['glossaryupdatedcount'] = 'Glossary entries updated: {$a}';
$string['importglossary'] = 'Import glossary';
$string['importglossarydescription'] = 'Import a glossary CSV with the fields sourcephrase, targetphrase, sourcelanguage, targetlanguage, courseid, status, priority, casesensitive, wholeword, notes and deeplglossaryid. Existing entries with the same source phrase, language pair and scope are updated.';
$string['entries'] = 'Entries';
$string['pending'] = 'Pending';
$string['sync'] = 'Sync';
$string['privacy:exportpath'] = 'Content translations';
$string['privacy:metadata:history'] = 'Stores the edit history for content translations.';
$string['privacy:metadata:history:contextid'] = 'The context where the translation history entry belongs.';
$string['privacy:metadata:history:crud'] = 'The create, update or delete action recorded for the history entry.';
$string['privacy:metadata:history:lastgeneratedhash'] = 'The generated source content hash for the translation history entry.';
$string['privacy:metadata:history:md5key'] = 'The stable translation hash key for the translation history entry.';
$string['privacy:metadata:history:prevrawtext'] = 'The previous original content for the history entry.';
$string['privacy:metadata:history:prevsubstitutetext'] = 'The previous translated content for the history entry.';
$string['privacy:metadata:history:rawtext'] = 'The original content for the history entry.';
$string['privacy:metadata:history:substitutetext'] = 'The translated content for the history entry.';
$string['privacy:metadata:history:targetlanguage'] = 'The target language for the history entry.';
$string['privacy:metadata:history:usermodified'] = 'The user who last modified the history entry.';
$string['privacy:metadata:issues'] = 'Stores missing or stale translation issues.';
$string['privacy:metadata:issues:contextid'] = 'The context where the translation issue was detected.';
$string['privacy:metadata:issues:generatedhash'] = 'The generated source content hash for the issue.';
$string['privacy:metadata:issues:issue'] = 'The translation issue type.';
$string['privacy:metadata:issues:md5key'] = 'The stable translation hash key for the issue.';
$string['privacy:metadata:issues:rawtext'] = 'The original content related to the issue.';
$string['privacy:metadata:issues:targetlanguage'] = 'The target language for the issue.';
$string['privacy:metadata:issues:translationid'] = 'The translation record related to the issue.';
$string['privacy:metadata:issues:url'] = 'The URL where the translation issue was detected.';
$string['privacy:metadata:issues:usermodified'] = 'The user who last modified the issue.';
$string['privacy:metadata:translations'] = 'Stores content translations managed by the filter.';
$string['privacy:metadata:translations:contextid'] = 'The context where the translation belongs.';
$string['privacy:metadata:translations:lastgeneratedhash'] = 'The generated source content hash for the translation.';
$string['privacy:metadata:translations:md5key'] = 'The stable translation hash key.';
$string['privacy:metadata:translations:rawtext'] = 'The original content.';
$string['privacy:metadata:translations:substitutetext'] = 'The translated content.';
$string['privacy:metadata:translations:targetlanguage'] = 'The target language for the translation.';
$string['privacy:metadata:translations:translationsource'] = 'How the translation was created.';
$string['privacy:metadata:translations:usermodified'] = 'The user who last modified the translation.';
$string['privacy:metadata:glossary'] = 'Stores terminology entries used to control translations.';
$string['privacy:metadata:glossary:notes'] = 'Reviewer or editor notes for a glossary entry.';
$string['privacy:metadata:glossary:sourcelanguage'] = 'Source language for a glossary entry.';
$string['privacy:metadata:glossary:sourcephrase'] = 'Source phrase for a glossary entry.';
$string['privacy:metadata:glossary:targetlanguage'] = 'Target language for a glossary entry.';
$string['privacy:metadata:glossary:targetphrase'] = 'Target phrase for a glossary entry.';
$string['privacy:metadata:glossary:usermodified'] = 'The user who last modified a glossary entry.';
$string['privacy:metadata:glossarysync'] = 'Stores DeepL glossary sync state.';
$string['privacy:metadata:glossarysync:courseid'] = 'Course ID for a course-specific synced glossary.';
$string['privacy:metadata:glossarysync:deeplglossaryid'] = 'DeepL glossary ID returned by the DeepL API.';
$string['privacy:metadata:glossarysync:lastsyncerror'] = 'Last sync error returned by local validation or the DeepL API.';
$string['privacy:metadata:glossarysync:sourcelanguage'] = 'Source language for the synced glossary dictionary.';
$string['privacy:metadata:glossarysync:targetlanguage'] = 'Target language for the synced glossary dictionary.';
$string['privacy:metadata:glossarysync:usermodified'] = 'The user who last triggered or updated a glossary sync state.';
$string['setupcoursefields'] = 'Create course translation fields';
