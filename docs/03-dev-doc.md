# Entwickler-Dokumentation

## Meta

Dieses Dokument beschreibt, wie das Plugin tatsaechlich implementiert ist.

Quelle der Wahrheit fuer den technischen Ist-Zustand.

---

# System-Uebersicht

## Architektur

`filter_translations` ist ein Moodle Filter Plugin. Es wird im Moodle-Verzeichnis unter `filter/translations` installiert und greift in den Moodle-Textfilter-Flow ein.

Hauptebenen:

- **Filter Boundary:** `filter.php` verarbeitet Moodle-Textausgaben.
- **Domain Logic:** Klassen unter `classes/` verwalten Uebersetzungsauswahl, Persistenz und Issues.
- **Provider Layer:** `classes/translationproviders/` kapselt automatische Uebersetzungsquellen.
- **Admin/UI Layer:** PHP-Seiten, Tabellenklassen, Forms, Mustache Templates und AMD JavaScript.
- **Maintenance Layer:** CLI-Skripte und Scheduled Tasks.
- **Persistence:** XMLDB-Tabellen aus `db/install.xml`, Moodle Persistent API und Moodle Cache API.

## Installation fuer Entwicklung

Das Repository muss im Moodle-Checkout als Plugin-Pfad verfuegbar sein:

```bash
export MOODLE_ROOT=/path/to/moodle
ln -s /Users/moskaliuk/Documents/Code/filter_eledia-translate "$MOODLE_ROOT/filter/translations"
cd "$MOODLE_ROOT"
php admin/cli/upgrade.php
```

## Kernkomponenten

| Komponente | Zweck |
| --- | --- |
| `filter.php` | Moodle-Filterklasse und Einstieg in die Textverarbeitung. |
| `classes/translator.php` | Auswahl der besten Uebersetzung, Provider-Fallback, Issue Logging. |
| `classes/text_filter.php` | Hilfslogik fuer Textverarbeitung. |
| `classes/translation.php` | Persistent Model fuer aktive Uebersetzungen. |
| `classes/translation_issue.php` | Persistent Model fuer fehlende oder stale Uebersetzungen. |
| `classes/translationproviders/*` | Reverse Lookup und DeepL. |
| `index.php` | Zentrales Setup-Dashboard fuer Status, Konfiguration und Workflow-Links. |
| `onboarding.php` | Gefuehrter Admin-Workflow fuer Erstkonfiguration und Setup-Checks. |
| `classes/*_table.php` | Moodle-Tabellen fuer Verwaltungsseiten. |
| `classes/*_form.php` und `classes/form/*` | Moodle Forms fuer Filter, Import und Export. |
| `classes/task/*` | Scheduled Task Implementierungen. |
| `classes/event/*` | Moodle Events fuer Translation CRUD. |
| `amd/src/*` | Browserinteraktionen fuer Translation UI und Diff Rendering. |
| `templates/*` | Mustache Templates fuer UI-Fragmente. |

## Datenmodell

### `filter_translations`

Aktive Uebersetzungen.

Wichtige Felder:

- `md5key`: gefundener Hash aus Inhalt oder Span.
- `lastgeneratedhash`: Hash des Quelltexts, fuer den die Uebersetzung zuletzt erzeugt wurde.
- `targetlanguage`: Zielsprachen-Code.
- `contextid`: Moodle-Kontext.
- `rawtext`: Ursprungstext.
- `substitutetext`: Uebersetzung.
- `substitutetextformat`: Moodle-Textformat.
- `translationsource`: manuell oder automatisch.

### `filter_translation_issues`

Protokollierte fehlende oder veraltete Uebersetzungen.

Wichtige Felder:

- `issue`: Issue-Typ.
- `url`: Seite, auf der das Issue erkannt wurde.
- `md5key`: Lookup Hash.
- `targetlanguage`: Zielsprache.
- `contextid`: Moodle-Kontext.
- `generatedhash`: aktueller Quelltexthash.
- `translationid`: referenzierte Uebersetzung oder `0`.

### `filter_translations_history`

Historische Uebersetzungsaenderungen, wenn History Logging aktiv ist.

## Datenfluss: Filter Rendering

1. Moodle ruft den Filter fuer Textausgabe auf.
2. `course_translation_policy` entscheidet anhand des Filter-Kontexts, ob Uebersetzung und aktuelle Sprache erlaubt sind.
3. Der Filter sucht Translation Hashes oder bildet einen Hash aus dem Quelltext.
4. `translator` ermittelt priorisierte Sprachen.
5. Bestehende Uebersetzungen werden nach Sprache und Hash bewertet.
6. Stale automatische Uebersetzungen werden verworfen.
7. Falls noetig versucht der Translator automatische Quellen.
8. Missing oder stale Issues werden geloggt, wenn aktiviert.
9. Der Ersatztext oder der Ursprungstext wird an Moodle zurueckgegeben.

Hash-Details:

- `classes/text_filter.php::normalisehashtext()` decodiert HTML-Entities nur fuer Filteraufrufe mit `stage = string`. Dadurch passen Moodle-Titel aus `format_string()` wieder zu den Rohwerten, die der Export gehasht hat.
- Der Cache-Key nutzt `foundhash ?? generatedhash`. Damit bleiben explizit markierte Inhalte per `data-translationhash` getrennt von zufaellig gleichem sichtbarem Text ohne Hash.

## Kurssteuerung

Die Kurssteuerung ist in `classes/course_translation_policy.php` gekapselt. `classes/text_filter.php` fragt diese Policy in `skiptranslations()` und `skiplanguage()` ab.

Konfigurierbare Quellen:

- `tags`: Legacy-Verhalten. Ein Kurs muss den Aktivierungs-Tag tragen, standardmaessig `deepl`; die aktuelle Sprache muss ebenfalls als Kurs-Tag vorhanden sein.
- `customfields`: Moodle Course Custom Fields sind fuehrend.
- `customfields_fallback_tags`: Course Custom Fields werden zuerst verwendet; wenn keine Policy-Werte vorhanden sind, greift Legacy-Tag-Verhalten.

Der Runtime-Default ist `course_translation_policy::DEFAULT_CONTROL_SOURCE = customfields_fallback_tags`. Install- und Upgrade-Step `2026062000` setzen diesen Modus fuer neue Installationen und migrieren bestehende `tags`-Konfigurationen auf den Fallback-Modus.

Default-Custom-Field-Shortnames:

- `eledia_translate_enabled`: Checkbox oder bool-artiges Feld fuer Kursuebersetzung aktiv.
- `eledia_translate_languages`: Text/Textarea mit Moodle-Sprachcodes, getrennt durch Komma, Leerzeichen, Semikolon, Pipe oder Zeilenumbruch.

Leeres Sprachfeld bedeutet: alle Sprachen sind erlaubt, solange `eledia_translate_enabled` aktiv ist.

## Automatische Provider

Provider leben unter `classes/translationproviders/`.

Aktive Provider:

- `languagestringreverse.php`
- `deepltranslate.php`

Die Reihenfolge in `translator.php` ist:

1. Language String Reverse Lookup.
2. DeepL, wenn aktiv.

Google Translate ist nicht mehr Teil der aktiven Provider-Pipeline.

## Admin-Einstellungen

Einstellungen sind in `settings.php` definiert.

`settings.php` registriert zusaetzlich die Admin-Externalpages `filtertranslationsdashboard` und `filtertranslationsonboarding` unter `filtersettings`. Das Dashboard lebt in `index.php`; der gefuehrte Admin-Workflow lebt in `onboarding.php`. Nutzer mit `filter/translations:edittranslations` duerfen das Dashboard oeffnen; Admin-only Links werden dort ueber `moodle/site:config` und `moodle/course:configurecustomfields` ausgeblendet. Das Onboarding benoetigt `moodle/site:config`, weil es globale Filter- und Provider-Einstellungen inklusive DeepL API-Key speichern kann.

Wichtige Gruppen:

- Performance: `showperfdata`, `cachingmode`.
- Ausschluesse: `untranslatedpages`, `excludelang`.
- Logging: `logexcludelang`, `loghistory`, `logmissing`, `logstale`, `logdebounce`.
- Scheduled Tasks: `columndefinition`.
- Provider: Language String Reverse und DeepL.

## Scheduled Tasks

Definitionen stehen in `db/tasks.php`.

| Task | Default | Zweck |
| --- | --- | --- |
| `replace_duplicate_hashes` | disabled | Doppelte Hashes ersetzen. |
| `copy_translations` | disabled | Uebersetzungen unter aktuelle Hashes kopieren. |
| `insert_spans` | disabled | Translation Spans in konfigurierte Inhalte einfuegen. |
| `cleanup_translation_issues` | enabled | Alte Translation Issues bereinigen. |

## CLI-Skripte

| Script | Zweck |
| --- | --- |
| `cli/migrate_filter_fulltranslate.php` | Migration aus `filter_fulltranslate`. |
| `cli/insert_spans.php` | Translation Spans in bestehende Daten einfuegen. |
| `cli/remove_duplicate_hashes.php` | Doppelte Hashes ersetzen. |
| `cli/copy_translations.php` | Passende Uebersetzungen kopieren. |

Bulk-Skripte immer zuerst mit `--help` und auf einer Datenkopie pruefen.

## Tests

Vorhandene Tests:

- `tests/filter_test.php`
- `tests/translator_test.php`
- `tests/translatorcaching_test.php`
- `tests/translationissue_test.php`
- `tests/events_test.php`
- `tests/languagestrings_test.php`
- `tests/course_translation_policy_test.php` (feat06, Tags/Custom Fields/Fallback)
- `tests/glossary_sync_test.php` (feat07, Sprach-Mapping, Gruppierung, ID-Aufloesung)
- `tests/glossary_importer_test.php` (feat07, Import inkl. bug01-Regression)

Behat-Tests:

- `tests/behat/manage_glossary.feature` prueft Glossar-Anlage und Sprachfilter.
- `tests/behat/onboarding.feature` prueft Onboarding-Schritte und Speichern zentraler Settings.
- `tests/behat/inline_translation.feature` prueft den Navbar-Translate-Dropdown und den Inline-Translation-Toggle.

Ausfuehrung aus dem Moodle-Root:

```bash
vendor/bin/phpunit --testsuite filter_translations_testsuite
```

Fallback, falls die Testsuite lokal nicht registriert ist:

```bash
vendor/bin/phpunit filter/translations/tests
```

Behat aus dem Moodle-Root:

```bash
php admin/tool/behat/cli/init.php
vendor/bin/behat --tags='@filter_translations'
```

Hinweis: Die lokale Shell muss eine mit Moodle kompatible PHP-Version nutzen. Der bekannte Moodle-5.2-Docker-Stand nutzt PHP 8.4; Homebrew PHP 8.5.5 ist fuer die aktuellen Composer-Lock-Abhaengigkeiten zu neu.

## Feature-Implementierung

### Content translations filter (`feat01`)

Implementiert ueber `filter.php`, `classes/translator.php`, `classes/text_filter.php` und die Translation-Persistents. PHPUnit-Abdeckung liegt vor allem in `tests/filter_test.php` und `tests/translator_test.php`.

### Translator editing UI (`feat02`)

Implementiert ueber `managetranslations.php`, `edittranslation.php`, `classes/managetranslations_table.php`, Forms, Mustache Templates und AMD Module.

### Translation issue logging (`feat03`)

Implementiert in `translator::checkforandlogissue()` und `classes/translation_issue.php`, mit Verwaltungsseite `managetranslationissues.php` und Cleanup Task.

### Automatic translation providers (`feat04`)

Implementiert ueber Providerklassen unter `classes/translationproviders/` und die Fallbacklogik in `classes/translator.php`.

DeepL ist der einzige externe Provider. Relevante Optionen sind API-Endpunkt, API-Key, Backoff, Source Language, HTML Tag Handling und optional eine Glossary ID.

### Bulk maintenance, import and export (`feat05`)

Implementiert ueber CLI-Skripte, Scheduled Tasks, `import.php`, `export.php`, `processexport.php` und Formular-Klassen unter `classes/form/`.

`processexport.php` exportiert neben Kurs-, Abschnitts-, Aktivitaetsnamen und vorhandenen Page/Book/Lesson-Pfaden auch:

- `assign.activity` inklusive Pluginfile-Rewrite fuer `mod_assign/activityattachment`
- `choice_options.text`
- `feedback.page_after_submit`, Item-Name/Label, Label-HTML und Multichoice-Optionen
- `glossary_entries.concept` und `definition`
- `workshop.instructauthors`, `instructreviewers`, `conclusion`
- Question-Bank-Felder fuer Fragetext, allgemeines Feedback, options feedback, answers, answer feedback und hints
- HTML-Block-Titel und -Text mit Block-Kontext

Forum und Wiki sind bewusst nicht erweitert. H5P/SCORM/IMSCP-Paketinhalte werden nicht geparst.

### Course-level translation control (`feat06`)

Implementiert ueber `classes/course_translation_policy.php`, Admin-Settings in `settings.php`, Cache-Definition `coursepolicy` in `db/caches.php` und die Policy-Aufrufe in `classes/text_filter.php`.

Die Implementierung nutzt Moodle Course Custom Fields ueber die Tabellen `customfield_category`, `customfield_field` und `customfield_data`, weil diese Felder bereits im Moodle-Kursformular erscheinen und keine eigene Kursformular-Erweiterung noetig ist.

`classes/course_customfields.php` legt die empfohlenen Course Custom Fields ueber `core_course\customfield\course_handler` idempotent an. Der Helper wird ueber `setupcoursefields.php`, `db/install.php` und die Upgrade-Steps `2026052300` und `2026062000` genutzt.

### Glossary management baseline (`feat07`)

Das Datenmodell ist als Tabelle `filter_translations_glossary` und Persistent `classes/glossary_entry.php` angelegt. Die Pflege-UI besteht aus `manageglossary.php`, `editglossaryentry.php`, `classes/manageglossary_filterform.php`, `classes/manageglossary_table.php` und `classes/glossary_entry_form.php`.

Die Seite ist ueber die Plugin-Einstellungen und das Uebersetzungsmenue erreichbar. `courseid = null` steht fuer globale Glossarbegriffe, konkrete Kurs-IDs begrenzen einen Eintrag auf einen Kurs. Die UI zeigt dafuer einen Scope-Dropdown statt roher IDs. Die Liste nutzt `table_sql` mit Moodle-Paginierung; aktuell gibt `manageglossary.php` 100 Eintraege pro Seite aus.

CSV Export und Import laufen ueber `glossaryexport.php`, `glossaryimport.php`, `classes/form/glossary_import_form.php` und `templates/glossary_import_summary.mustache`. Die Validierungs- und Upsert-Logik pro Zeile liegt seit `task13` in `classes/glossary_importer.php` (`glossary_importer::import_row()`), damit sie unit-testbar ist; `glossaryimport.php` kuemmert sich nur noch um CSV-Einlesen und die Ergebnis-Zusammenfassung. Der Import nutzt `sourcephrase + sourcelanguage + targetlanguage + courseid` als fachlichen Schluessel: vorhandene Eintraege werden aktualisiert, neue Eintraege werden angelegt. Da historische Daten Dubletten enthalten koennen, verwendet der Import `get_records_select()` und aktualisiert alle passenden bestehenden Eintraege, statt mit `get_record_select()` eine Moodle-Notice bei Mehrfachtreffern auszuloesen.

### DeepL Glossary Sync (`feat07`, `task10`)

Die offizielle DeepL-Dokumentation empfiehlt fuer neue Glossararbeit die v3-Endpunkte. v3 kann ein Glossar mit mehreren Dictionaries, also mehreren Sprachpaaren, verwalten und editieren. v2 bleibt Legacy und sollte nicht mit v3 gemischt werden, weil v3-edited Glossare ueber v2 nicht mehr verlaesslich gelesen oder geloescht werden koennen.

Relevante DeepL-v3-Endpunkte:

- `POST /v3/glossaries` erzeugt ein Glossar mit einer Liste von Dictionaries. Jedes Dictionary enthaelt `source_lang`, `target_lang`, `entries` und `entries_format = tsv`.
- `GET /v3/glossaries` listet Glossare mit Metadaten und Dictionary-Entry-Counts.
- `GET /v3/glossaries/{glossary_id}/entries?source_lang=...&target_lang=...` liest Dictionary-Eintraege.
- `PUT /v3/glossaries/{glossary_id}/dictionaries` ersetzt ein einzelnes Dictionary fuer ein Sprachpaar.
- `PATCH /v3/glossaries/{glossary_id}` kann Glossar-Metadaten oder Dictionary-Inhalte aktualisieren.
- `DELETE /v3/glossaries/{glossary_id}` loescht ein Glossar, `DELETE /v3/glossaries/{glossary_id}/dictionaries?...` loescht nur ein Sprachpaar.

Implementiertes Sync-Modell:

1. Pro Scope wird ein DeepL-Glossar gefuehrt:
   - global: `eLeDia Translation Glossary - global`
   - kursbezogen: `eLeDia Translation Glossary - course {courseid}`
2. Innerhalb eines Glossars wird pro Sprachpaar ein v3-Dictionary gepflegt.
3. Nur Eintraege mit `status = approved` werden synchronisiert.
4. Sprachcodes werden ueber die bestehende DeepL-Sprachmapping-Logik normalisiert. Moodle-Codes wie `de`, `en`, `fr` werden fuer Glossaries als DeepL-Codes genutzt; regionale Codes muessen vor der Implementierung gegen die DeepL-Glossary-Language-Pairs validiert werden.
5. TSV-Eintraege werden aus `sourcephrase<TAB>targetphrase` gebaut. Zeilenumbrueche und Tabs in Phrasen muessen beim Sync validiert oder abgewiesen werden.
6. Der lokale Sync-Schluessel ist `scope + sourcelanguage + targetlanguage`. Scope ist `global` oder `course:{courseid}`.
7. Die Sync-Tabelle `filter_translations_glossync` speichert `scope`, `courseid`, `sourcelanguage`, `targetlanguage`, `deeplglossaryid`, `contenthash`, `status`, `lastsyncerror`, `timemodified`.
8. Sync wird explizit durch Admins auf `manageglossarysync.php` pro Scope und Sprachpaar gestartet. Automatische Scheduled-Task-Synchronisation kann spaeter folgen.
9. `classes/translationproviders/deepltranslate.php` fragt vor der statisch konfigurierten Glossary ID die Sync-Tabelle ab. Kursbezogene IDs haben Vorrang vor globalen IDs.

### Central setup dashboard (`feat08`)

`index.php` ist eine zentrale, read-only Setup- und Wartungsseite. Sie nutzt Moodle Output APIs und bestehende Workflows statt eigene Speicherlogik einzufuehren.

Die Seite berechnet:

- Gesamtzahl gespeicherter Uebersetzungen aus `filter_translations`
- Gesamtzahl Translation Issues aus `filter_translation_issues`
- Gesamtzahl Glossar-Eintraege aus `filter_translations_glossary`
- ausstehende DeepL-Sync-Gruppen ueber `glossary_sync::groups()`
- Issue-Counts pro `translation_issue`-Status
- Glossar-Counts pro `glossary_entry`-Status

Die Seite unterscheidet drei Rechteebenen:

- `filter/translations:edittranslations`: Zugriff auf Dashboard und operative Uebersetzungs-/Glossarworkflows
- `filter/translations:bulkimporttranslations` / `filter/translations:exporttranslations`: Import-/Export-Workflows
- `moodle/site:config` und `moodle/course:configurecustomfields`: Admin-Settings, Filterverwaltung, Scheduled Tasks, DeepL-Test und Kursfeld-Setup

Fehlerverhalten:

- `400/415`: lokale Eintraege oder Payload sind ungueltig; Sync abbrechen und Fehler sichtbar speichern.
- `401/403`: API-Key oder Plan-Berechtigung fehlerhaft; Sync abbrechen.
- `413`: Dictionary zu gross; Admin muss Scope oder Sprachpaar aufteilen.
- `429/529/503`: temporaere Ueberlastung oder Rate Limit; Backoff nutzen und spaeter erneut versuchen.
- `456`: Quota erreicht; keine weiteren Sync-Versuche bis Admin eingreift.

Quellen: [DeepL v3 Create Glossary](https://developers.deepl.com/api-reference/multilingual-glossaries/create-a-glossary), [DeepL v3 Glossaries Overview](https://developers.deepl.com/api-reference/multilingual-glossaries), [DeepL v2 vs v3 endpoints](https://developers.deepl.com/api-reference/glossaries/v2-vs-v3-endpoints).

### Setup onboarding workflow (`feat10`)

`onboarding.php` ist eine Moodle-Admin-Externalpage mit eigener Schrittsteuerung ueber den URL-Parameter `step`.

Implementierte Schritte:

- `filter`: nutzt `filter_set_global_state()`, `filter_set_applies_to_strings()` und `reset_text_filters_cache()`.
- `course`: schreibt `coursecontrolsource`, `coursetagenabled`, `coursefieldenabled` und `coursefieldlanguages`.
- `provider`: schreibt Reverse-Lookup- und DeepL-Settings; `deepl_apikey` wird nur bei nicht leerer Eingabe ersetzt.
- `logging`: schreibt Missing-/Stale-/History-Logging, `logdebounce` und `untranslatedpages`.
- `glossary`: verlinkt bestehende Glossar-, Import-, Export- und Sync-Seiten.
- `finish`: zeigt lesende Setup-Checks fuer die wichtigsten Konfigurationsrisiken.

Die Seite fuehrt keine eigene Validierung gegen die DeepL API aus; dafuer bleibt `testdeepl.php` verantwortlich.

## Technische Constraints

- Das Plugin benoetigt Moodle-Bootstrap; isolierte Ausfuehrung ausserhalb von Moodle ist nur begrenzt moeglich.
- Database Schema Aenderungen muessen ueber `db/install.xml` und nach Release ueber `db/upgrade.php` gepflegt werden.
- UI-Ausgaben sollen Moodle Output, Mustache und AMD verwenden.
- Provider-APIs koennen Netzwerkfehler, Kosten und Rate Limits verursachen.
- Bulk-Operationen koennen produktive Inhalte veraendern.
