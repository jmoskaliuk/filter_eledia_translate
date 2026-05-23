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

## Kurssteuerung

Die Kurssteuerung ist in `classes/course_translation_policy.php` gekapselt. `classes/text_filter.php` fragt diese Policy in `skiptranslations()` und `skiplanguage()` ab.

Konfigurierbare Quellen:

- `tags`: Legacy-Verhalten. Ein Kurs muss den Aktivierungs-Tag tragen, standardmaessig `deepl`; die aktuelle Sprache muss ebenfalls als Kurs-Tag vorhanden sein.
- `customfields`: Moodle Course Custom Fields sind fuehrend.
- `customfields_fallback_tags`: Course Custom Fields werden zuerst verwendet; wenn keine Policy-Werte vorhanden sind, greift Legacy-Tag-Verhalten.

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

Ausfuehrung aus dem Moodle-Root:

```bash
vendor/bin/phpunit --testsuite filter_translations_testsuite
```

Fallback, falls die Testsuite lokal nicht registriert ist:

```bash
vendor/bin/phpunit filter/translations/tests
```

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

### Course-level translation control (`feat06`)

Implementiert ueber `classes/course_translation_policy.php`, Admin-Settings in `settings.php`, Cache-Definition `coursepolicy` in `db/caches.php` und die Policy-Aufrufe in `classes/text_filter.php`.

Die Implementierung nutzt Moodle Course Custom Fields ueber die Tabellen `customfield_category`, `customfield_field` und `customfield_data`, weil diese Felder bereits im Moodle-Kursformular erscheinen und keine eigene Kursformular-Erweiterung noetig ist.

`classes/course_customfields.php` legt die empfohlenen Course Custom Fields ueber `core_course\customfield\course_handler` idempotent an. Der Helper wird ueber `setupcoursefields.php`, `db/install.php` und den Upgrade-Step `2026052300` genutzt.

### Glossary management baseline (`feat07`)

Das Datenmodell ist als Tabelle `filter_translations_glossary` und Persistent `classes/glossary_entry.php` angelegt. Die Pflege-UI besteht aus `manageglossary.php`, `editglossaryentry.php`, `classes/manageglossary_filterform.php`, `classes/manageglossary_table.php` und `classes/glossary_entry_form.php`.

Die Seite ist ueber die Plugin-Einstellungen und das Uebersetzungsmenue erreichbar. DeepL-v3-Synchronisation sowie CSV-Import/Export sind Folgeaufgaben.

## Technische Constraints

- Das Plugin benoetigt Moodle-Bootstrap; isolierte Ausfuehrung ausserhalb von Moodle ist nur begrenzt moeglich.
- Database Schema Aenderungen muessen ueber `db/install.xml` und nach Release ueber `db/upgrade.php` gepflegt werden.
- UI-Ausgaben sollen Moodle Output, Mustache und AMD verwenden.
- Provider-APIs koennen Netzwerkfehler, Kosten und Rate Limits verursachen.
- Bulk-Operationen koennen produktive Inhalte veraendern.
