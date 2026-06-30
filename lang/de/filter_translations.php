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

defined('MOODLE_INTERNAL') || die();

$string['any'] = 'Alle';
$string['approvetranslation'] = 'Uebersetzung freigeben';
$string['backtooverview'] = 'Zurueck zur Uebersicht';
$string['cachingmode'] = 'Cache-Modus';
$string['cachingmode_desc'] = 'Fuer Websites mit wenigen Kursen und vielen Nutzenden wird der Applikations-Cache empfohlen. Bei vielen Kursen wird der Session-Cache empfohlen. Request reduziert die Zwischenspeicherung auf ein Minimum.';
$string['casesensitive'] = 'Gross-/Kleinschreibung beachten';
$string['columndefinition'] = 'Zu pruefende Tabellen und Spalten';
$string['columndefinition_desc'] = 'Geplante Aufgaben nutzen diese Definition fuer regelmaessige Wartung und Synchronisierung von Uebersetzungen.';
$string['context'] = 'Kontext';
$string['coursecontrol'] = 'Kurssteuerung';
$string['coursecontrol_desc'] = 'Legt fest, ob Kurse ueber Kursfelder, bisherige Kurstags oder beide Varianten fuer Uebersetzungen gesteuert werden.';
$string['coursecontrolsource'] = 'Quelle der Kurssteuerung';
$string['coursecontrolsource_customfields'] = 'Nur Kursfelder';
$string['coursecontrolsource_customfields_fallback_tags'] = 'Kursfelder, danach bisherige Kurstags';
$string['coursecontrolsource_desc'] = 'Legt fest, wo der Filter die kursbezogenen Uebersetzungseinstellungen liest.';
$string['coursecontrolsource_tags'] = 'Nur bisherige Kurstags';
$string['coursefieldenabled'] = 'Kursfeld fuer Uebersetzung aktivieren';
$string['coursefieldenabled_desc'] = 'Kurzname des Kursfelds, das Uebersetzung fuer den Kurs aktiviert.';
$string['coursefieldlanguages'] = 'Kursfeld fuer Zielsprachen';
$string['coursefieldlanguages_desc'] = 'Kurzname des Kursfelds mit den erlaubten Zielsprachen.';
$string['createglossaryentry'] = 'Glossareintrag erstellen';
$string['createtranslation'] = 'Uebersetzung erstellen';
$string['coursetagenabled'] = 'Bisheriger Kurstag fuer Uebersetzung';
$string['coursetagenabled_desc'] = 'Kurstag, der Uebersetzung aktiviert, wenn tagbasierte Steuerung verwendet wird.';
$string['dashboardconfiguration'] = 'Konfiguration';
$string['dashboardcreateglossary_desc'] = 'Einen Begriff fuer globale oder kursspezifische Uebersetzungen anlegen.';
$string['dashboardexportglossary_desc'] = 'Glossarbegriffe als CSV herunterladen.';
$string['dashboardexport_desc'] = 'Fehlende Kursuebersetzungen als CSV exportieren.';
$string['dashboardglossary_desc'] = 'Terminologie erstellen, pruefen, importieren, exportieren und synchronisieren.';
$string['dashboardimportglossary_desc'] = 'Glossarbegriffe aus einer CSV-Datei importieren.';
$string['dashboardimport_desc'] = 'Uebersetzte CSV-Dateien in den Uebersetzungsspeicher importieren.';
$string['dashboardissues_desc'] = 'Fehlende und veraltete Uebersetzungen pruefen, die beim Aufruf von Kursen erfasst wurden.';
$string['dashboardpendingsyncgroups'] = 'Ausstehende Glossar-Sync-Gruppen';
$string['dashboardstatus'] = 'Statusuebersicht';
$string['dashboardsync_desc'] = 'Freigegebene Glossareintraege mit DeepL-Glossaren abgleichen.';
$string['dashboardtranslations_desc'] = 'Gespeicherte Inhaltsuebersetzungen suchen, erstellen und bearbeiten.';
$string['dashboardworkflows'] = 'Workflows';
$string['deepl_apiendpoint'] = 'API-Endpunkt';
$string['deepl_apikey'] = 'API-Schluessel';
$string['deepl_backoffonerror'] = 'Fehlerhafte API voruebergehend aussetzen';
$string['deepl_enable'] = 'DeepL Translate API verwenden';
$string['deepl_glossaryid'] = 'Glossar-ID';
$string['deepl_glossaryid_desc'] = 'Optionale DeepL-Glossar-ID. DeepL benoetigt eine Ausgangssprache, wenn ein Glossar verwendet wird.';
$string['deeplglossarysync'] = 'DeepL-Glossar synchronisieren';
$string['deeplglossarynoentries'] = 'Keine freigegebenen Glossareintraege fuer diesen Bereich und dieses Sprachpaar gefunden.';
$string['deeplglossarynosyncgroups'] = 'Es sind keine freigegebenen Glossareintraege fuer die DeepL-Synchronisierung vorhanden.';
$string['deeplglossarysyncerror'] = 'DeepL-Glossarsynchronisierung fehlgeschlagen: {$a}';
$string['deeplglossarysyncfailed'] = 'DeepL-Glossarsynchronisierung fehlgeschlagen: {$a}';
$string['deeplglossarystatus_error'] = 'Fehler';
$string['deeplglossarystatus_pending'] = 'Ausstehend';
$string['deeplglossarystatus_synced'] = 'Synchronisiert';
$string['deeplglossarysyncpreview'] = 'Vorschau DeepL-Glossarsynchronisierung';
$string['deeplglossarysyncsuccess'] = 'DeepL-Glossarsynchronisierung abgeschlossen.';
$string['deepl_sourcelang'] = 'Ausgangssprache';
$string['deepl_sourcelang_desc'] = 'Optionaler DeepL-Sprachcode fuer die Ausgangssprache, zum Beispiel EN oder DE. Leer lassen, wenn DeepL die Sprache automatisch erkennen soll.';
$string['deepl_taghandlinghtml'] = 'DeepL HTML-Tag-Behandlung verwenden';
$string['deepl_taghandlinghtml_desc'] = 'Moodle-HTML-Inhalte mit tag_handling=html an DeepL senden, damit HTML-Tags zuverlaessiger erhalten bleiben.';
$string['deepltranslate'] = 'DeepL Translate';
$string['deepltest'] = 'DeepL-Verbindung testen';
$string['deepltest_confirm_button'] = 'DeepL-Test ausfuehren';
$string['deepltest_confirm_desc'] = 'Fuehrt einen Verbindungstest mit den konfigurierten DeepL-Einstellungen aus. Dabei werden keine Uebersetzungen gespeichert.';
$string['editglossaryentry'] = 'Glossareintrag bearbeiten';
$string['edittranslation'] = 'Uebersetzung bearbeiten';
$string['entries'] = 'Eintraege';
$string['exportglossary'] = 'Glossar exportieren';
$string['exporttranslations'] = 'Uebersetzungen exportieren';
$string['feature_translate_desc'] = 'Kursinhalte in andere Sprachen uebersetzen und Review-, Glossar- und Import-Workflows verwalten.';
$string['feature_translate_detail'] = 'Translate beschleunigt mehrsprachige Kursbereitstellung. Bestehende Moodle-Inhalte koennen markiert, uebersetzt, geprueft, importiert und zentral gepflegt werden, inklusive DeepL-Unterstuetzung bei entsprechender Konfiguration.';
$string['feature_translate_name'] = 'Translate';
$string['filtername'] = 'Inhaltsuebersetzung';
$string['filteroptions'] = 'Filteroptionen';
$string['glossaryscope'] = 'Glossarbereich';
$string['glossarygroups'] = 'Glossar-Gruppen';
$string['glossaryscope_global'] = 'Global / alle Kurse';
$string['glossaryscope_globalonly'] = 'Nur globale Eintraege';
$string['hash'] = 'Hash';
$string['importglossary'] = 'Glossar importieren';
$string['importtranslations'] = 'Uebersetzungen importieren';
$string['languagepair'] = 'Sprachpaar';
$string['languagestringreverse'] = 'Sprachstrings rueckwaerts suchen';
$string['languagestringreverse_enable'] = 'Rueckwaertssuche nach Sprachzeichenfolgen aktivieren';
$string['logging'] = 'Protokollierung';
$string['logdebounce'] = 'Entprellzeit fuer Protokollierung';
$string['logexcludelang'] = 'Von der Protokollierung ausgeschlossene Sprachen';
$string['logexcludelang_desc'] = 'Sprachen, die nicht in der Tabelle fuer fehlende Uebersetzungen protokolliert werden.';
$string['loghistory'] = 'Uebersetzungshistorie behalten';
$string['logmissing'] = 'Fehlende Uebersetzungen protokollieren';
$string['logstale'] = 'Veraltete Uebersetzungen protokollieren';
$string['manageglossary'] = 'Glossar verwalten';
$string['managetranslationissues'] = 'Probleme verwalten';
$string['managetranslations'] = 'Uebersetzungen verwalten';
$string['navglossary'] = 'Glossar';
$string['navdashboard'] = 'Dashboard';
$string['navproblems'] = 'Probleme';
$string['navsetup'] = 'Einstellungen';
$string['navtransfer'] = 'Import/Export';
$string['onboardingcourse_desc'] = 'Legen Sie fest, wie Kurse fuer Uebersetzungen aktiviert werden und wo Zielsprachen gepflegt werden.';
$string['onboardingdeeplkey_desc'] = 'Leer lassen, um den aktuell gespeicherten API-Schluessel beizubehalten.';
$string['onboardingfilter_desc'] = 'Aktivieren Sie den Moodle-Textfilter und legen Sie fest, ob Aktivitaetsnamen und Ueberschriften uebersetzt werden.';
$string['onboardingfilter_enable'] = 'Filter Inhaltsuebersetzungen global aktivieren';
$string['onboardingfilter_headings'] = 'Filter auf Inhalte und Ueberschriften anwenden';
$string['onboardingfilter_headings_desc'] = 'Erforderlich fuer die Uebersetzung von Aktivitaetstiteln, Abschnittsnamen und anderer format_string()-Ausgabe.';
$string['onboardingfinish_desc'] = 'Pruefen Sie die Einrichtung. Sie koennen zu jedem Bereich zurueckkehren und die Konfiguration anpassen.';
$string['onboardingglossary_desc'] = 'Erstellen oder importieren Sie Terminologie und synchronisieren Sie freigegebene Eintraege bei Bedarf mit DeepL-Glossaren.';
$string['onboardingintro'] = 'Dieser Wizard fuehrt durch die noetige Einrichtung der Inhaltsuebersetzung.';
$string['onboardinglogging_desc'] = 'Legen Sie fest, wie fehlende oder veraltete Uebersetzungen fuer die redaktionelle Nachbearbeitung erfasst werden.';
$string['onboardingprovider_desc'] = 'Konfigurieren Sie automatische Uebersetzungsanbieter. DeepL benoetigt einen API-Schluessel; Glossare benoetigen zusaetzlich eine Ausgangssprache.';
$string['onboardingstart_button'] = 'Einrichtung starten';
$string['onboardingstart_desc'] = 'Starten Sie die Einrichtung von Ai Translate und nutzen Sie dafür den Wizard, der Sie durch die Einrichtung begleitet.';
$string['onboardingstart_title'] = 'Ai Translate einrichten';
$string['onboardingstep_course'] = 'Kurssteuerung';
$string['onboardingstep_filter'] = 'Filter';
$string['onboardingstep_finish'] = 'Abschluss';
$string['onboardingstep_glossary'] = 'Glossar';
$string['onboardingstep_logging'] = 'Protokollierung';
$string['onboardingstep_provider'] = 'DeepL und Anbieter';
$string['onboardingtitle'] = 'Inhaltsuebersetzung einrichten';
$string['openworkflow'] = 'Oeffnen';
$string['pending'] = 'Ausstehend';
$string['pluginname'] = 'Inhaltsuebersetzung';
$string['pluginsettings'] = 'Plugin-Einstellungen';
$string['pluginsetup'] = 'Inhaltsuebersetzung einrichten';
$string['pluginsetup_desc'] = 'Zentrale Seite fuer Einrichtung und Wartung der Inhaltsuebersetzung.';
$string['saveandcontinue'] = 'Speichern und weiter';
$string['saveandnext'] = 'Speichern und naechste';
$string['scheduledtasksheading'] = 'Geplante Wartungsaufgaben';
$string['setupcoursefields'] = 'Kursfelder erstellen';
$string['setupcoursefields_confirm_button'] = 'Felder erstellen';
$string['setupcoursefields_confirm_desc'] = 'Erstellt oder aktualisiert die empfohlenen Kursfelder, mit denen Ai Translate aktiviert und Zielsprachen gepflegt werden.';
$string['shell_backtodashboard'] = 'Inhaltsuebersetzung';
$string['shell_tagline'] = 'Ueberblick';
$string['sourcephrase'] = 'Ausgangsbegriff';
$string['sourcelanguage'] = 'Ausgangssprache';
$string['sourcelanguage_short'] = 'Quelle';
$string['status'] = 'Status';
$string['sync'] = 'Synchronisieren';
$string['targetphrase'] = 'Zielbegriff';
$string['targetlanguage'] = 'Zielsprache';
$string['targetlanguage_short'] = 'Ziel';
$string['settingsstatus_check'] = 'Pruefen';
$string['settingsstatus_ready'] = 'Bereit';
$string['translationissues'] = 'Probleme';
$string['translations'] = 'Uebersetzungen';
$string['translationsource'] = 'Quelle';
$string['translationsource_automatic'] = 'Automatisch';
$string['translationsource_manual'] = 'Manuell';
$string['excludelang'] = 'Von Uebersetzung ausgeschlossene Sprachen';
$string['excludelang_desc'] = 'Sprachen, die vollstaendig von der Uebersetzung ausgeschlossen werden.';
$string['showperfdata'] = 'Performance-Daten im Footer anzeigen';
$string['untranslatedpages'] = 'Nicht zu uebersetzende Seiten';
$string['untranslatedpages_desc'] = 'Eine Seite pro Zeile.';
$string['wholeword'] = 'Nur ganze Woerter';
