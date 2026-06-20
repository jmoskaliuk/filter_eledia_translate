# Qualitaet

## Meta

Dieses Dokument erfasst Bugs und Tests.

Es enthaelt:

- Bugs (`bugXX`) mit Severity
- Tests (`testXX`) mit Bezug auf Akzeptanzkriterien
- lokale Verifikationslage

---

## Bugs

### Severity-Skala

| Severity | Bedeutung | Reaktion |
| --- | --- | --- |
| S1 | Kritisch: Kernfunktion kaputt, Datenverlust, Sicherheitsluecke | Sofortiger Hotfix |
| S2 | Schwer: Feature unbrauchbar, kein guter Workaround | laufende Iteration |
| S3 | Mittel: eingeschraenkt, Workaround vorhanden | naechstes Release |
| S4 | Gering: kosmetisch oder Edge Case | Backlog |

### Vorlage

```text
### bugXX Kurztitel
Feature:  featXX
Severity: S1 | S2 | S3 | S4
Status:   open | in_progress | fixed | wontfix
Linked:   taskXX, testXX

Beschreibung
...

Reproduktion
1. ...
2. ...

Erwartet
...

Tatsaechlich
...
```

### Aktueller Bug-Stand

### bug01 Glossary import duplicate natural key warning

Feature: feat07
Severity: S2
Status: fixed
Linked: task08, test10

**Beschreibung**
Der Glossar-CSV-Import nutzte `get_record_select()` fuer den natuerlichen Schluessel aus Quellphrase, Quellsprache, Zielsprache und Kurs-Scope. Wenn lokal bereits mehrere passende Glossarzeilen vorhanden waren, erzeugte Moodle die Notice `mdb->get_record() found more than one record!`.

**Reproduktion**
1. Mehr als einen Glossar-Eintrag mit gleicher Quellphrase, Sprachrichtung und gleichem Kurs-Scope anlegen.
2. CSV mit derselben Kombination importieren.

**Erwartet**
Der Import aktualisiert vorhandene Treffer ohne Runtime-Notice.

**Tatsaechlich**
Behoben: Der Import liest mehrere Treffer mit `get_records_select()` und aktualisiert alle passenden bestehenden Eintraege. Regressionstest: `test14`.

### bug02 Glossary-Sync-Reads ohne Mehrfachtreffer-Schutz

Feature: feat07
Severity: S3
Status: fixed
Linked: task12, test13

**Beschreibung**
`glossary_sync::get_state()` und `resolve_deepl_glossary_id()` nutzten `get_record(_select)` ohne `IGNORE_MULTIPLE`. Ohne DB-Constraint konnten doppelte `filter_translations_glossync`-Zeilen die Notice `found more than one record` ausloesen.

**Tatsaechlich**
Behoben: Reads auf `IGNORE_MULTIPLE` umgestellt; zusaetzlich Unique-Index `scope_course_lang` (scope, courseid, sourcelanguage, targetlanguage) in `install.xml` und Upgrade-Step `2026061500` mit vorheriger Dedup-Bereinigung. Hinweis: Bei `courseid IS NULL` (global) sind doppelte Zeilen DB-seitig nicht vollstaendig ausgeschlossen (NULL-Distinct), daher bleibt der `IGNORE_MULTIPLE`-Schutz die primaere Absicherung. Vollstaendige DB-Eindeutigkeit fuer global wuerde `courseid = 0` statt NULL erfordern (Folge-Task moeglich).

### bug03 DeepL-API-Key im Klartext

Feature: feat04
Severity: S3
Status: fixed
Linked: task12

**Tatsaechlich**
Behoben: `deepl_apikey` nutzt jetzt `admin_setting_configpasswordunmask` statt `admin_setting_configtext`.

### bug04 Maturity widerspricht Dev-Release

Feature: rel01
Severity: S4
Status: fixed
Linked: task12

**Tatsaechlich**
Behoben: `maturity` von `MATURITY_STABLE` auf `MATURITY_BETA`, Release `2.1.0-dev` -> `2.1.0-beta`.

### bug05 Course-Custom-Fields unsichtbar

Feature: feat06
Severity: S3
Status: fixed
Linked: task12, test09

**Beschreibung**
`course_customfields::create_field()` legte die Felder mit `visibility = NOTVISIBLE` an, wodurch sie im Kursformular nicht sichtbar waren.

**Tatsaechlich**
Behoben: Sichtbarkeit auf `course_handler::VISIBLETEACHERS`. Runtime-Bestaetigung im Kursformular folgt mit `task02`.

### bug06 Aktivitaetstitel mit HTML-Entities matchen Export-Hash nicht

Feature: feat01
Severity: S2
Status: fixed
Linked: task14, test15

**Beschreibung**
Moodle escaped Strings vor `format_string()`; ein Titel `Research & Development` erreicht den Filter als `Research &amp; Development`. Der Export erzeugt den Hash aber aus dem Rohwert. Dadurch konnte eine vorhandene Uebersetzung fuer den exportierten Titel nicht gefunden werden.

**Tatsaechlich**
Behoben: `classes/text_filter.php` decodiert nur fuer `stage = string` HTML-Entities vor der Hash-Generierung. Regressionstest: `test_filter_string_uses_unescaped_hash()`.

### bug07 Cache-Key ignoriert gefundenen Translation Hash

Feature: feat01
Severity: S3
Status: fixed
Linked: task14, test16

**Beschreibung**
Der Filter erzeugte immer einen `generatedhash`; der Cache-Key nutzte deshalb mit `$generatedhash ?? $foundhash` faktisch nie den eingebetteten `data-translationhash`.

**Tatsaechlich**
Behoben: Der Cache-Key nutzt jetzt `foundhash ?? generatedhash`. Regressionstest: `test_cached_hash_translation_does_not_apply_to_plain_text()`.

### bug08 HTML-Block-Titel nutzt falschen Kontext im Export

Feature: feat05
Severity: S3
Status: fixed
Linked: task14, test17

**Beschreibung**
Der Export von HTML-Block-Titeln nutzte `$cm->context->id` aus der vorherigen Aktivitaetsschleife statt `$blockinstance->context->id`.

**Tatsaechlich**
Behoben: Blocktitel und Blocktext verwenden nun denselben Block-Kontext.

---

## Tests

### Vorlage

```text
### testXX Kurztitel
Feature:            featXX
Akzeptanzkriterium: featXX.ACyy
Typ:                manuell | automatisiert
Status:             pending | pass | fail | blocked
Letzter Lauf:       YYYY-MM-DD

Schritte
1. ...

Erwartetes Ergebnis
...

Beobachtetes Ergebnis
...
```

### test01 Plugin installiert in Moodle

Feature:            feat01
Akzeptanzkriterium: feat01.AC01
Typ:                manuell
Status:             pending
Letzter Lauf:       2026-05-23
Linked:             q01, task02

**Schritte**
1. Repository als `filter/translations` in Moodle einbinden.
2. `php admin/cli/upgrade.php` ausfuehren.
3. Plugin in `Site administration > Plugins > Filters > Manage filters` aktivieren.

**Erwartetes Ergebnis**
Moodle erkennt und installiert `filter_translations` ohne Fehler.

**Beobachtetes Ergebnis**
Moodle-Root ist dokumentiert. Upgrade-Lauf ist noch nicht ausgefuehrt.

### test02 PHPUnit Plugin-Suite

Feature:            feat01
Akzeptanzkriterium: feat01.AC01, feat01.AC02
Typ:                automatisiert
Status:             pending
Letzter Lauf:       2026-05-23
Linked:             q01, task02

**Schritte**
1. Aus Moodle-Root ausfuehren: `vendor/bin/phpunit --testsuite filter_translations_testsuite`.
2. Falls Testsuite nicht registriert ist: `vendor/bin/phpunit filter/translations/tests`.

**Erwartetes Ergebnis**
Vorhandene Tests fuer Filter, Translator, Caching, Events, Issues und Language Strings laufen gruen.

**Beobachtetes Ergebnis**
Moodle-Root ist dokumentiert. PHPUnit-Lauf ist noch nicht ausgefuehrt.

### test03 Translation issue logging

Feature:            feat03
Akzeptanzkriterium: feat03.AC01, feat03.AC02
Typ:                automatisiert
Status:             pending
Letzter Lauf:       2026-05-23

**Schritte**
1. `tests/translationissue_test.php` ausfuehren.
2. Logging-Settings und Debounce-Verhalten pruefen.

**Erwartetes Ergebnis**
Missing- und stale-Issues werden nur entsprechend Konfiguration und Debounce gespeichert.

### test04 Automatic provider fallback

Feature:            feat04
Akzeptanzkriterium: feat04.AC01, feat04.AC02, feat04.AC03, feat04.AC04
Typ:                automatisiert
Status:             pending
Letzter Lauf:       2026-05-23

**Schritte**
1. `tests/translator_test.php` ausfuehren.
2. Provider-Konfigurationen fuer Reverse Lookup und DeepL pruefen.

**Erwartetes Ergebnis**
Provider-Fallback folgt der dokumentierten Reihenfolge.

### test05 CLI help smoke

Feature:            feat05
Akzeptanzkriterium: feat05.AC01
Typ:                manuell
Status:             pending
Letzter Lauf:       2026-05-23

**Schritte**
1. Aus Moodle-Root die CLI-Skripte mit `--help` ausfuehren.
2. Sicherstellen, dass keine Datenveraenderung ohne explizite Bestaetigung passiert.

**Erwartetes Ergebnis**
CLI-Skripte sind bedienbar und risikoarm pruefbar.

### test06 DevFlow-Dokumentation vorhanden

Feature:            rel01
Akzeptanzkriterium: rel01 documentation baseline
Typ:                manuell
Status:             pass
Letzter Lauf:       2026-05-23
Linked:             task01

**Schritte**
1. Pruefen, dass `00-master.md` bis `05-quality.md` vorhanden sind.
2. Pruefen, dass README auf diese Dateien verweist.
3. Pruefen, dass Feature-, User-, Dev-, Task- und Quality-Perspektiven getrennt sind.

**Erwartetes Ergebnis**
Das Repository folgt der eLeDia.OS_DevFlow-Vorgabe.

**Beobachtetes Ergebnis**
DevFlow-Dateien wurden im Repository-Root angelegt und README wurde aktualisiert.

### test07 Course-level translation policy syntax check

Feature:            feat06
Akzeptanzkriterium: feat06.AC01, feat06.AC02, feat06.AC03, feat06.AC04
Typ:                automatisiert
Status:             partial
Letzter Lauf:       2026-05-23
Linked:             task03

**Schritte**
1. `php -l classes/course_translation_policy.php` ausfuehren.
2. `php -l classes/text_filter.php` ausfuehren.
3. `php -l settings.php` ausfuehren.

**Erwartetes Ergebnis**
Die neue Policy-Klasse, Filter-Integration und Admin-Settings sind syntaktisch gueltig.

**Beobachtetes Ergebnis**
Syntax-Checks sind gruen. Vollstaendige Moodle-Runtime-Verifikation bleibt bis zur Klaerung des lokalen Moodle-Roots blockiert.

### test08 DeepL-only provider pipeline syntax check

Feature:            feat04
Akzeptanzkriterium: feat04.AC02, feat04.AC03, feat04.AC04
Typ:                automatisiert
Status:             partial
Letzter Lauf:       2026-05-23
Linked:             task04

**Schritte**
1. `php -l classes/translator.php` ausfuehren.
2. `php -l classes/translationproviders/deepltranslate.php` ausfuehren.
3. `php -l settings.php` ausfuehren.
4. Vollstaendigen PHP-Lint ueber das Plugin ausfuehren.

**Erwartetes Ergebnis**
Google ist nicht mehr Teil der aktiven Provider-Pipeline; DeepL-Provider und Settings sind syntaktisch gueltig.

**Beobachtetes Ergebnis**
Syntax-Checks und kompletter PHP-Lint sind gruen. Vollstaendige Moodle-Runtime-/HTTP-Verifikation bleibt bis zur Klaerung des lokalen Moodle-Roots und Test-DeepL-Credentials offen.

### test09 Course Custom Fields auto setup

Feature:            feat06
Akzeptanzkriterium: feat06 follow-up
Typ:                automatisiert + manuell
Status:             partial
Letzter Lauf:       2026-05-23
Linked:             task05

**Schritte**
1. Neue Installation oder Upgrade ausfuehren.
2. Pruefen, dass Kursfelder angelegt oder sauber gemeldet werden.
3. Kursformular oeffnen und Felder pruefen.

**Erwartetes Ergebnis**
Admin kann Kurssteuerung ohne manuelles Anlegen der Feldstruktur verwenden.

**Beobachtetes Ergebnis**
Helper, Setup-Seite, Install-Hook und Upgrade-Hook sind syntaktisch gueltig. Runtime-Pruefung in Moodle bleibt offen.

### test10 Glossary baseline

Feature:            feat07
Akzeptanzkriterium: feat07.AC01, feat07.AC02, feat07.AC03, feat07.AC04
Typ:                automatisiert + manuell
Status:             partial
Letzter Lauf:       2026-05-23
Linked:             task06

**Schritte**
1. Glossar-Datenmodell pruefen.
2. Pflege-UI mit Filterung pruefen.
3. Import/Export und Reviewstatus pruefen.
4. DeepL-v3-Sync-Implementierung gegen offizielle API-Dokumentation und Syntax pruefen.

**Erwartetes Ergebnis**
Terminologie kann getrennt von Inhaltsuebersetzungen gepflegt werden.

**Beobachtetes Ergebnis**
Schema, Persistent, Listenansicht mit Moodle-Paginierung, Filterformular, Scope-Dropdown, Editor, CSV Import/Export, DeepL-v3-Sync und Navigationslinks sind angelegt und syntaktisch gueltig. Ein Runtime-Fehler beim Import mit mehrfach vorhandenen natuerlichen Schluesseln wurde als bug01 behoben. Ein echter DeepL-API-Sync bleibt offen.

### test11 DeepL settings test page

Feature:            feat04
Akzeptanzkriterium: feat04.AC03, feat04.AC04
Typ:                automatisiert + manuell
Status:             partial
Letzter Lauf:       2026-05-23
Linked:             task07

**Schritte**
1. `php -l testdeepl.php` ausfuehren.
2. `php -l classes/translationproviders/deepltranslate.php` ausfuehren.
3. Runtime-Test mit gueltigem DeepL-Key aus den Plugin-Settings starten.

**Erwartetes Ergebnis**
DeepL-Testseite meldet Erfolg oder Konfigurationsfehler ohne gespeicherte Uebersetzung zu erzeugen.

**Beobachtetes Ergebnis**
Syntax ist gueltig. Runtime-Test bleibt bis zu Moodle-Root und Test-Credentials offen.

### test12 Course translation policy (PHPUnit)

Feature:            feat06
Akzeptanzkriterium: feat06.AC01, feat06.AC02, feat06.AC03, feat06.AC04
Typ:                automatisiert
Status:             pending
Letzter Lauf:       -
Linked:             task13

**Schritte**
1. `vendor/bin/phpunit filter/translations/tests/course_translation_policy_test.php`.

**Erwartetes Ergebnis**
Tags-, Custom-Fields- und Fallback-Steuerung sowie leere Sprachliste und Site-Kontext verhalten sich wie in feat06 beschrieben.

**Beobachtetes Ergebnis**
Test geschrieben (`tests/course_translation_policy_test.php`). Noch nicht ausgefuehrt (haengt an task02).

### test13 Glossary sync logic (PHPUnit)

Feature:            feat07
Akzeptanzkriterium: feat07.AC04
Typ:                automatisiert
Status:             pending
Letzter Lauf:       -
Linked:             task13, bug02

**Schritte**
1. `vendor/bin/phpunit filter/translations/tests/glossary_sync_test.php`.

**Erwartetes Ergebnis**
Sprach-Mapping, Gruppierung nur approved, leere/ungueltige Gruppe ohne Netzwerk und Glossary-ID-Aufloesung (Kurs vor global) stimmen.

**Beobachtetes Ergebnis**
Test geschrieben (`tests/glossary_sync_test.php`). Noch nicht ausgefuehrt (haengt an task02).

### test14 Glossary importer (PHPUnit)

Feature:            feat07
Akzeptanzkriterium: feat07.AC03
Typ:                automatisiert
Status:             pending
Letzter Lauf:       -
Linked:             task13, bug01

**Schritte**
1. `vendor/bin/phpunit filter/translations/tests/glossary_importer_test.php`.

**Erwartetes Ergebnis**
Create/Update, gemeinsames Update vorhandener Dubletten (Regression bug01), getrennter Kurs-Scope und Validierungs-Skips funktionieren.

**Beobachtetes Ergebnis**
Test geschrieben (`tests/glossary_importer_test.php`). Noch nicht ausgefuehrt (haengt an task02).

### test15 Aktivitaetstitel werden uebersetzt

Feature:            feat01
Akzeptanzkriterium: feat01.AC01 (Ueberschriften)
Typ:                manuell
Status:             partial
Letzter Lauf:       2026-06-20
Linked:             q02, bug06, task14

**Schritte**
1. Filter `translations` in "Filter verwalten" auf "Inhalt und Ueberschriften" setzen.
2. Fuer einen aktivierten Kurs eine Uebersetzung des Aktivitaetstitels anlegen (passender Hash) oder DeepL aktivieren.
3. Kurs in Zielsprache oeffnen und pruefen, ob der Aktivitaetstitel uebersetzt erscheint.

**Erwartetes Ergebnis**
Mit "Inhalt und Ueberschriften" werden Aktivitaetstitel via `format_string()` durch den Filter uebersetzt.

**Beobachtetes Ergebnis**
Code-Fix und PHPUnit-Regressionstest fuer Entity-Hashing ergaenzt. Manuelle Runtime-Pruefung in moodle52 bleibt offen.

### test16 Cache-Key fuer eingebettete Hashes

Feature:            feat01
Akzeptanzkriterium: feat01.AC01
Typ:                automatisiert
Status:             pending
Letzter Lauf:       -
Linked:             bug07, task14

**Schritte**
1. `vendor/bin/phpunit filter/translations/tests/filter_test.php --filter test_cached_hash_translation_does_not_apply_to_plain_text`.

**Erwartetes Ergebnis**
Eine Cache-Translation fuer Text mit `data-translationhash` wird nicht auf denselben sichtbaren Text ohne Hash angewendet.

**Beobachtetes Ergebnis**
Test geschrieben. Noch nicht ausgefuehrt (haengt an task02). Syntax-Check fuer `tests/filter_test.php` war gruen.

### test17 Erweiterter Aktivitaetscontent-Export

Feature:            feat09
Akzeptanzkriterium: feat09.AC01, feat09.AC02
Typ:                manuell
Status:             pending
Letzter Lauf:       -
Linked:             task14, bug08

**Schritte**
1. Kurs mit Assignment, Choice, Feedback, Glossary, Workshop und Quiz/Question Bank anlegen.
2. Missing-Translation-Export fuer eine Zielsprache starten.
3. CSV pruefen.

**Erwartetes Ergebnis**
Redaktionelle Inhalte aus den genannten Aktivitaeten erscheinen im CSV; Forum/Wiki und Paketinhalte bleiben bewusst ausgenommen.

**Beobachtetes Ergebnis**
Code syntaktisch gueltig. Runtime-Pruefung in moodle52 bleibt offen.

### test18 Zentrales Setup-Dashboard

Feature:            feat08
Akzeptanzkriterium: feat08.AC01, feat08.AC02
Typ:                manuell
Status:             pending
Letzter Lauf:       -
Linked:             task15

**Schritte**
1. Als Admin `filter/translations/index.php` oeffnen.
2. Status-Kacheln, Konfigurationsuebersicht und Workflow-Links pruefen.
3. Als Rolle mit `filter/translations:edittranslations`, aber ohne `moodle/site:config`, oeffnen.
4. Sichtbarkeit der Admin-only Buttons pruefen.

**Erwartetes Ergebnis**
Dashboard ist erreichbar, zeigt Status korrekt und blendet Aktionen passend zur Capability aus.

**Beobachtetes Ergebnis**
Code syntaktisch gueltig. Runtime-Pruefung in moodle52 bleibt offen.

### test19 Setup-Onboarding-Workflow

Feature:            feat10
Akzeptanzkriterium: feat10.AC01, feat10.AC02
Typ:                manuell
Status:             pending
Letzter Lauf:       -
Linked:             task17

**Schritte**
1. Als Admin `filter/translations/onboarding.php` oeffnen.
2. Jeden Schritt speichern und die Weiterleitung zum Folgeschritt pruefen.
3. Im Filter-Schritt globales Aktivieren und `Content and headings` setzen.
4. Im Provider-Schritt DeepL API-Endpunkt und API-Key speichern und anschliessend `testdeepl.php` ausfuehren.
5. Abschluss-Checks pruefen.

**Erwartetes Ergebnis**
Der Wizard speichert die jeweiligen Moodle-Settings korrekt, zeigt keine Fehler und markiert erledigte Pflichtpunkte im Abschluss.

**Beobachtetes Ergebnis**
Code syntaktisch gueltig. Runtime-Pruefung in moodle52 bleibt offen.
