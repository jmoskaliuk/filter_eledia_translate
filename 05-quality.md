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

Keine lokalen Bugs dokumentiert.

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
Schema, Persistent, Listenansicht, Filterformular, Scope-Dropdown, Editor, CSV Import/Export, DeepL-v3-Sync und Navigationslinks sind angelegt und syntaktisch gueltig. Runtime-Pruefung in Moodle und ein echter DeepL-API-Sync bleiben offen.

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
