# Tasks

## Meta

Dies ist das operative Zentrum des Projekts.

Es enthaelt:

- neue Beobachtungen
- Tasks (`taskXX`)
- offene Klaerungen (`qXX`)
- aktive Arbeit
- Verifikations-Schritte

Jede Session beginnt hier.

---

## Neu

Unstrukturierter Input landet zuerst hier und wird danach in Task, Frage, Feature oder Bug ueberfuehrt.

---

## Klaerung benoetigt

### q01 Lokaler Moodle-Root fuer Runtime-Verifikation

Linked: `task02`, `test01`
Asked-by: KI
Status: answered

**Frage**
Welcher lokale Moodle-Checkout soll fuer Installation, PHPUnit und UI-Verifikation verwendet werden?

**Warum relevant**
Das Plugin liegt lokal vor, aber Runtime-Checks brauchen einen Moodle-Root, in den das Plugin als `filter/translations` eingebunden wird.

**Antwort**
Naheliegender lokaler Moodle-Root fuer die Runtime-Pruefung:
`/Users/moskaliuk/Documents/Code/Lernhive/runtime/moodle52/moodle/public`

---

### q02 Werden Aktivitaetstitel/Ueberschriften uebersetzt?

Linked: `feat01`, `test15`
Asked-by: PO
Status: answered

**Frage**
Aus einer frueheren Version kam Feedback, dass Ueberschriften von Aktivitaeten nicht uebersetzt werden. Gilt das noch?

**Analyse**
Moodle ruft Filter auf `format_string()` (Namen, Titel, Abschnitts-/Aktivitaetsueberschriften) nur auf, wenn der Filter in `Website-Administration > Plugins > Textfilter > Filter verwalten` auf **"Inhalt und Ueberschriften"** steht. Default ist "Inhalt", dann laufen Titel nie durch den Filter. Das ist die wahrscheinlichste Ursache.

Zusaetzliche, im Code begruendete Grenzen:
- Kein Inline-Edit-Button auf Titeln: `format_string()` strippt HTML; der Inline-Editor (Span + Zero-Width-Zeichen) deaktiviert das Strippen nur im Inline-Modus (`filter_translations_after_config`).
- Titel werden ueber den generierten Hash gematcht (`md5(trim($name))`); eine Uebersetzung greift nur bei exakt passendem Quelltext (manuell angelegt oder per DeepL erzeugt).
- Die Kurssteuerung muss den Kurs ueberhaupt freigeben (sonst `skiptranslations()` true).

**Antwort**
Ja, Aktivitaetstitel koennen uebersetzt werden, wenn Moodle den Filter auf `Inhalt und Ueberschriften` stellt. Zusaetzlich wurde ein Hash-Mismatch behoben: Moodle escaped Titel vor `format_string()` als z. B. `&amp;`, der Export hasht aber den Rohwert `&`. `classes/text_filter.php` normalisiert jetzt String-Stage-Text fuer die Hash-Ermittlung.

---

## Tasks

### Vorlage

```text
### taskXX Titel
Status:    open | in_progress | done
Feature:   featXX
Prioritaet: P0 | P1 | P2 | P3
Linked:    bugXX, qXX, testXX

Ziel
...

Schritte
1. ...
2. ...

Erwartetes Ergebnis
...

Done-Checkliste
- [ ] 01-features.md aktualisiert (falls Verhalten geaendert)
- [ ] 02-user-doc.md aktualisiert (falls UX geaendert)
- [ ] 03-dev-doc.md aktualisiert (immer bei Code-Aenderung)
- [ ] testXX in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off
```

### Prioritaeten

| Stufe | Bedeutung |
| --- | --- |
| P0 | Blocker |
| P1 | Kern-Feature oder aktuelle Iteration |
| P2 | wichtig, aber nicht blockierend |
| P3 | Backlog |

---

## In Progress

Naechster empfohlener Schritt: `task02 Lokale Moodle-Verifikation einrichten` (Code vorbereitet, es fehlt nur der Runtime-Lauf gegen moodle52).

Letzter abgeschlossener Schritt: `task19 Empfohlene Kurssteuerung als Default` (Custom Fields mit Tag-Fallback ist Default und Upgrade-Migration; Runtime-Ausfuehrung haengt an passender Moodle/PHP-Umgebung).

---

## Open

### task02 Lokale Moodle-Verifikation einrichten

Status:    next
Feature:   feat01
Prioritaet: P1
Linked:    q01, test01, test02

**Ziel**
Das Plugin lokal in einen Moodle-Checkout einbinden und Installation sowie PHPUnit verifizieren.

**Schritte**
1. Repository als `filter/translations` in den dokumentierten Moodle-Root einbinden.
2. `php admin/cli/upgrade.php` ausfuehren.
3. PHPUnit initialisieren, falls noetig.
4. Plugin-Test-Suite ausfuehren.
5. Glossar-UI, CSV Import/Export und DeepL-Sync-Preview im Browser pruefen.
6. Optional echten DeepL-v3-Sync mit API-Key ausfuehren.

**Erwartetes Ergebnis**
Installation, Upgrade, Kern-UI und automatisierte Tests sind lokal reproduzierbar dokumentiert.

**Aktueller Stand**
Nach `task12`/`task13` sind Schema (inkl. Unique-Index `scope_course_lang`), Settings (API-Key als Passwortfeld), Glossar-UI, CSV-Import/Export (jetzt ueber `glossary_importer`), DeepL-v3-Sync und neue PHPUnit-Tests im Repo. Version auf `2026061500` / `2.1.0-beta` gebumpt. Offen ist ausschliesslich der Runtime-Lauf gegen moodle52.

**Runtime-Befehle (auf dem Mac, nicht in der KI-Sandbox)**
```bash
MOODLE=/Users/moskaliuk/Documents/Code/Lernhive/runtime/moodle52/moodle/public
ln -sfn "$(pwd)" "$MOODLE/filter/translations"   # Plugin einbinden
php "$MOODLE/admin/cli/upgrade.php"               # Upgrade-Step 2026061500 ausfuehren
php "$MOODLE/admin/tool/phpunit/cli/init.php"     # PHPUnit-Testumgebung (nach Schema-Aenderung noetig)
cd "$MOODLE" && vendor/bin/phpunit filter/translations/tests
```
Alternativ ueber die eLeDia-Pipeline: `bash deploy.sh --source . --phpunit-init` und `moodle-plugin-ci phpunit --fail-on-warning`.

**Done-Checkliste**
- [ ] 01-features.md aktualisiert (nicht erforderlich, falls nur Setup)
- [ ] 02-user-doc.md aktualisiert (Hinweis "Inhalt und Ueberschriften", siehe q02)
- [x] 03-dev-doc.md aktualisiert
- [ ] test01/test02/test12/test13/test14 in 05-quality.md gruen
- [ ] PO Sign-off

---

## Verifikation nach Deploy

- Glossar-Import in Moodle erneut mit der Datei testen, die zuvor `mdb->get_record() found more than one record!` ausgeloest hat.
- Glossarverwaltung pruefen: Paginierung sichtbar bei mehr als 100 Treffern; `Create glossary entry` steht oben neben `DeepL glossary sync`.
- Optional DeepL-Sync-Preview oeffnen und einen echten Sync mit Test-Key ausfuehren.

---

## Done

Erledigte Tasks bleiben als Historie erhalten.

### task19 Empfohlene Kurssteuerung als Default

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat06
Prioritaet: P1
Linked:    test12, test23

**Ziel**
Der empfohlene Steuerungsmodus soll standardmaessig `Course custom fields, then legacy tags` sein, damit Kursfelder fuehrend werden und bestehende Tag-Kurse weiter funktionieren.

**Schritte**
1. Runtime-Default in `course_translation_policy` auf `customfields_fallback_tags` setzen.
2. Admin-Setting und Onboarding-Default anpassen.
3. Install-/Upgrade-Pfad so erweitern, dass bestehende `tags`-Konfigurationen auf den Fallback-Modus migrieren.
4. PHPUnit-Regressionstest und DevFlow aktualisieren.

**Erwartetes Ergebnis**
Neue Installationen und bestehende Upgrades verwenden Course Custom Fields zuerst und Legacy-Tags nur noch als Rueckfall.

**Aktueller Stand**
Implementiert mit Versionssprung `2026062000`. PHPUnit-Test erweitert; lokale Ausfuehrung bleibt wegen passender Moodle/PHP-Testumgebung offen.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test23 dokumentiert
- [ ] PHPUnit-Lauf in PHP-8.4-/Moodle-Umgebung gruen
- [ ] PO Sign-off

### task18 Behat-Regressionstests fuer UI-Workflows

Status:    done (Tests angelegt), Ausfuehrung geblockt durch lokale PHP-Version
Feature:   feat02, feat07, feat10
Prioritaet: P1
Linked:    test20, test21, test22

**Ziel**
Die zuletzt fehleranfaelligen UI-Pfade erhalten Behat-Abdeckung.

**Schritte**
1. Behat-Szenarien fuer Glossar-Anlage und Glossarfilter anlegen.
2. Behat-Szenarien fuer Onboarding-Basics und Speichern zentraler Settings anlegen.
3. JavaScript-Behat-Szenarien fuer Navbar-Translate-Dropdown und Inline-Translation-Toggle anlegen.
4. Lokale Ausfuehrbarkeit pruefen und Blocker dokumentieren.

**Erwartetes Ergebnis**
Regressions wie ein nicht klickbarer Translate-Button oder nicht speicherbare Glossar-Eintraege werden automatisiert auffindbar.

**Aktueller Stand**
Feature-Dateien liegen unter `tests/behat/`. Lokaler `php admin/tool/behat/cli/init.php` scheitert mit PHP 8.5.5, weil Moodle-Lock-Abhaengigkeiten aktuell PHP bis 8.4 erlauben. Ausfuehrung soll in der Docker-/CI-Umgebung mit PHP 8.4 erfolgen.

**Done-Checkliste**
- [ ] 01-features.md aktualisiert (nicht erforderlich, Test-only)
- [ ] 02-user-doc.md aktualisiert (nicht erforderlich, Test-only)
- [x] 03-dev-doc.md aktualisiert
- [x] test20/test21/test22 dokumentiert
- [ ] Behat-Lauf in PHP-8.4-Umgebung gruen
- [ ] PO Sign-off

### task17 Setup-Onboarding-Workflow

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat10
Prioritaet: P1
Linked:    test19

**Ziel**
Administratoren werden durch alle wesentlichen Erstkonfigurationen des Filter-Plugins gefuehrt.

**Schritte**
1. Admin-Externalpage fuer `filtertranslationsonboarding` registrieren.
2. `onboarding.php` mit Schritten fuer Filter, Kurssteuerung, Provider, Logging, Glossar und Abschluss-Checks anlegen.
3. Setup-Dashboard und Plugin-Settings mit Onboarding-Link ergaenzen.
4. Spracheintraege und DevFlow-Doku aktualisieren.

**Erwartetes Ergebnis**
Die zentrale Erstkonfiguration inklusive DeepL API kann ueber einen gefuehrten Workflow erledigt werden.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test19 dokumentiert
- [ ] PO Sign-off

### task16 Zentrale Setup-Seite und Aktivitaetscontent-Export dokumentieren

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat01, feat05, feat08, feat09
Prioritaet: P1
Linked:    bug06, bug07, bug08, test15, test16, test17, test18

**Ziel**
Die aktuelle Iteration aus Titel-Fix, Cache-Fix, erweitertem Export und Setup-Dashboard ist im DevFlow nachvollziehbar.

**Schritte**
1. `feat08` fuer die zentrale Setup-Seite dokumentieren.
2. `feat09` fuer erweiterte Aktivitaetscontent-Exports dokumentieren.
3. User-Doku fuer Titel/Ueberschriften, Dashboard und Export-Abdeckung ergaenzen.
4. Dev-Doku fuer `index.php`, Exportfelder, Hash-Normalisierung und Cache-Key ergaenzen.
5. Tests/Bugs in `05-quality.md` aktualisieren.

**Erwartetes Ergebnis**
Neue Sessions koennen den aktuellen Stand ohne Chat-Kontext aus dem DevFlow ableiten.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test15/test16/test17/test18 in 05-quality.md dokumentiert
- [ ] PO Sign-off

### task15 Zentrales Setup-Dashboard

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat08
Prioritaet: P1
Linked:    test18

**Ziel**
Eine zentrale Setup- und Einstiegsseite fuer das Filter-Plugin bereitstellen.

**Schritte**
1. `index.php` mit Status-Kacheln, Konfigurationsuebersicht und Workflow-Links anlegen.
2. Admin-Externalpage in `settings.php` registrieren.
3. Button von der bestehenden Settings-Seite zur Setup-Seite ergaenzen.
4. Capabilities fuer sichtbare Aktionen beruecksichtigen.

**Erwartetes Ergebnis**
Admins und Uebersetzungsmanager finden die relevanten Plugin-Workflows auf einer Seite.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test18 dokumentiert
- [ ] PO Sign-off

### task14 Aktivitaetscontent-Export erweitern und Titel/Cache fixen

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat01, feat05, feat09
Prioritaet: P1
Linked:    bug06, bug07, bug08, test15, test16, test17

**Ziel**
Aktivitaetstitel und weitere Aktivitaetsinhalte sollen verlaesslicher uebersetzbar/exportierbar sein.

**Schritte**
1. String-Stage-Hash fuer `format_string()` normalisieren.
2. Cache-Key auf `foundhash ?? generatedhash` korrigieren.
3. `processexport.php` fuer Assignment, Choice, Feedback, Glossary, Workshop und Question Bank erweitern.
4. HTML-Block-Titel mit Block-Kontext exportieren.
5. Regressionstests fuer Titel-Hash und Cache-Key ergaenzen.

**Erwartetes Ergebnis**
Titel mit HTML-Entities matchen exportierte Uebersetzungen; Hash-basierte Cache-Treffer laufen nicht in Klartext ohne Hash; mehr Aktivitaetsinhalte erscheinen im Export.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test15/test16/test17 dokumentiert
- [ ] PO Sign-off

### task13 PHPUnit fuer Neubau

Status:    done (Code), Ausfuehrung haengt an task02
Feature:   feat06, feat07
Prioritaet: P1
Linked:    test12, test13, test14

**Ziel**
Die bisher ungetesteten Neubau-Komponenten erhalten PHPUnit-Abdeckung.

**Schritte**
1. `tests/course_translation_policy_test.php` (Tags, Custom Fields, Fallback, leere Sprachliste, Site-Kontext).
2. `tests/glossary_sync_test.php` (Sprach-Mapping, Gruppierung nur approved, leere/ungueltige Gruppe ohne Netzwerk, Glossary-ID-Aufloesung Kurs vor global).
3. `tests/glossary_importer_test.php` (Create/Update, Dubletten-Update als Regression fuer bug01, Kurs-Scope, Validierungs-Skips).

**Erwartetes Ergebnis**
Policy, Sync und Import sind durch PHPUnit abgesichert; bug01 hat einen Regressionstest.

**Aktueller Stand**
Tests geschrieben und im Repo. Lokale Ausfuehrung steht aus (kein Moodle-Bootstrap in der KI-Sandbox) und erfolgt mit `task02`.

**Done-Checkliste**
- [x] 03-dev-doc.md aktualisiert
- [ ] test12/test13/test14 in 05-quality.md gruen (nach task02)
- [ ] PO Sign-off

### task12 S3-Fixes aus dem Review

Status:    done
Feature:   feat04, feat06, feat07
Prioritaet: P1
Linked:    bug02, bug03, bug04, bug05

**Ziel**
Die im Review identifizierten S3-Punkte abraeumen.

**Schritte**
1. `bug02` Glossary-Sync-Reads auf `IGNORE_MULTIPLE` + Unique-Index `scope_course_lang` (install.xml + Upgrade-Step mit Dedup).
2. `bug03` DeepL-API-Key auf `admin_setting_configpasswordunmask`.
3. `bug04` `maturity` auf `MATURITY_BETA`, Release `2.1.0-beta`.
4. `bug05` Course-Custom-Fields-Sichtbarkeit auf `VISIBLETEACHERS`.
5. Version auf `2026061500` bumpen, XMLDB-Version angleichen.

**Erwartetes Ergebnis**
Robustere Sync-Reads, kein Klartext-Key, konsistente Maturity, sichtbare Kursfelder.

**Aktueller Stand**
Implementiert. Upgrade-Step und Index werden mit `task02` runtime-verifiziert.

**Done-Checkliste**
- [x] 03-dev-doc.md aktualisiert
- [x] bug02-bug05 in 05-quality.md aktualisiert
- [ ] PO Sign-off

### task11 Glossar-Import-Dublettenfix und Toolbar-Polish

Status:    done
Feature:   feat07
Prioritaet: P1
Linked:    bug01, test10

**Ziel**
Der Glossar-Import soll auch bei vorhandenen Dubletten stabil laufen, und die wichtigsten Glossar-Aktionen sollen oben sichtbar sein.

**Schritte**
1. Runtime-Notice beim Import analysieren.
2. Einzelrecord-Lookup durch Mehrfachtreffer-sicheren Lookup ersetzen.
3. Vorhandene passende Dubletten beim Import gemeinsam aktualisieren.
4. `Create glossary entry` in die obere Toolbar neben `DeepL glossary sync` verschieben.
5. DevFlow und Qualitaetsdokumentation aktualisieren.

**Erwartetes Ergebnis**
CSV-Import laeuft ohne Moodle-Notice bei mehrfach vorhandenen fachlichen Schluesseln. Admins finden Create und Sync direkt oben in der Glossarverwaltung.

**Aktueller Stand**
Implementiert und nach GitHub gepusht mit Commit `3eb669b Fix glossary import duplicate handling`.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test10/bug01 in 05-quality.md aktualisiert
- [ ] PO Sign-off

### task01 DevFlow an Repository anpassen

Status:    done
Feature:   rel01
Prioritaet: P1
Linked:    test06

**Ziel**
Die lokale Projektdokumentation folgt der eLeDia.OS_DevFlow-Struktur und beschreibt den aktuellen Ist-Stand des Moodle-Filter-Plugins.

**Schritte**
1. DevFlow-Vorgabe aus `jmoskaliuk/eLeDia.OS_DevFlow` sichten.
2. Root-Dateien `00-master.md` bis `05-quality.md` anlegen.
3. Bestehende Plugin-Features, Bedienung, Implementierung, Tasks und Qualitaet dokumentieren.
4. README auf DevFlow-Dateien verlinken.

**Erwartetes Ergebnis**
Neue Sessions koennen ueber `00-master.md` starten und finden Status, Features, Doku und Qualitaetslage ohne Chat-Kontext.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test06 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task10 DeepL Glossary Sync implementieren

Status:    done
Feature:   feat07
Prioritaet: P2
Linked:    test10

**Ziel**
Freigegebene Glossarbegriffe werden kontrolliert mit DeepL-v3-Glossaries synchronisiert.

**Schritte**
1. Sync-Tabelle fuer Scope/Sprachpaar/DeepL-ID anlegen.
2. DeepL-v3-Client fuer Glossary Endpunkte implementieren.
3. Admin-Seite fuer Preview, Sync und Fehleranzeige bauen.
4. Approved-Eintraege pro Scope und Sprachpaar als TSV serialisieren.
5. DeepL-ID beim Uebersetzen passend zur Kurs-/Global-Policy auswaehlen.

**Erwartetes Ergebnis**
Administration kann lokale Glossare kontrolliert nach DeepL synchronisieren und DeepL nutzt die passende Glossary ID bei automatischen Uebersetzungen.

**Aktueller Stand**
Sync-Tabelle, DeepL-v3-Client, Sync-Service, Admin-Preview, Einzel-Sync und DeepL-Provider-Kopplung sind implementiert. Runtime-Pruefung gegen eine echte DeepL-Konfiguration bleibt offen.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test10 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task09 DeepL Glossary Sync vorbereiten

Status:    done
Feature:   feat07
Prioritaet: P2
Linked:    test10

**Ziel**
Freigegebene Glossarbegriffe sollen spaeter kontrolliert mit DeepL Glossaries synchronisiert werden koennen.

**Schritte**
1. DeepL Glossary API fuer Create/List/Delete/Entries gegen offizielle Doku pruefen.
2. Mapping fuer Sprachrichtungen und freigegebene Eintraege definieren.
3. Sync-Status und DeepL Glossary IDs pro Sprachpaar festlegen.
4. Fehler- und Rate-Limit-Verhalten dokumentieren.

**Erwartetes Ergebnis**
Ein technischer Plan fuer DeepL Glossary Sync liegt vor, ohne die aktuelle Pflege-UI zu blockieren.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test10 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task08 Glossar CSV Import/Export ergaenzen

Status:    done
Feature:   feat07
Prioritaet: P2
Linked:    test10

**Ziel**
Glossarbegriffe koennen fuer redaktionelle Pflege exportiert und wieder importiert werden.

**Schritte**
1. CSV-Feldschema definieren.
2. Export fuer gefilterte Glossarlisten bereitstellen.
3. Import mit Validierung und Konfliktverhalten implementieren.
4. Testfaelle fuer Pflichtfelder, Sprachcodes und Statuswerte dokumentieren.

**Erwartetes Ergebnis**
Redaktion kann Terminologie ausserhalb von Moodle pflegen und kontrolliert zurueckspielen.

**Aktueller Stand**
Export und Import sind in der Glossarverwaltung verlinkt. Import legt neue Eintraege an und aktualisiert bestehende Eintraege mit gleicher Quellphrase, Sprachrichtung und gleichem Scope. Seit `task11` werden auch bestehende Dubletten zu diesem Schluessel gemeinsam aktualisiert.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test10 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task06 Glossar-Datenmodell und Pflege-UI entwerfen

Status:    done
Feature:   feat07
Prioritaet: P1
Linked:    test10

**Ziel**
Ein eigenes Glossar-Konzept fuer Terminologie wird als Datenmodell und Pflege-Workflow vorbereitet.

**Schritte**
1. Tabelle und Persistent fuer Glossarbegriffe entwerfen.
2. Felder fuer Sprachrichtung, Kontext/Kurs, Status, Prioritaet und Review definieren.
3. Management-UI fuer Filtern, Anlegen und Bearbeiten ergaenzen.
4. Import/Export und DeepL Glossary Sync als spaetere Tasks abgrenzen.

**Erwartetes Ergebnis**
Glossarbegriffe sind fachlich und technisch getrennt von normalen Inhaltsuebersetzungen und koennen in Moodle gepflegt werden.

**Aktueller Stand**
Schema, Persistent, Admin-Link, Navbar-Link, Listenansicht, Filterformular, Scope-Dropdown und Editor sind angelegt. Import/Export ist ueber `task08` erledigt, DeepL-v3-Sync ueber `task10`.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test10 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task07 DeepL-Verbindungstest in den Settings bereitstellen

Status:    done
Feature:   feat04
Prioritaet: P1
Linked:    test11

**Ziel**
Administratoren koennen die DeepL-Konfiguration direkt aus den Plugin-Einstellungen testen.

**Schritte**
1. Testseite `testdeepl.php` anlegen.
2. Provider um eine speicherfreie Testmethode ergaenzen.
3. Button in Plugin-Settings ergaenzen.

**Erwartetes Ergebnis**
Fehlerhafte DeepL-Konfigurationen werden schneller sichtbar, ohne eine echte Kursuebersetzung ausloesen zu muessen.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test11 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task05 Course Custom Fields automatisch anlegen

Status:    done
Feature:   feat06
Prioritaet: P1
Linked:    test09

**Ziel**
Die empfohlenen Moodle Course Custom Fields fuer Kursuebersetzung werden automatisch oder halbautomatisch angelegt, damit Admins sie nicht manuell suchen und konfigurieren muessen.

**Schritte**
1. Moodle Custom Field API fuer Course Fields pruefen.
2. Install-/Upgrade- oder Admin-Helper definieren.
3. Kategorie `eLeDia Translation` anlegen.
4. Checkbox und Sprachenfeld mit konfigurierten Shortnames anlegen.

**Erwartetes Ergebnis**
Neue Installationen koennen die Kurssteuerung ohne manuelle Custom-Field-Vorarbeit nutzen.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test09 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task04 Google entfernen und DeepL Provider robust machen

Status:    done
Feature:   feat04
Prioritaet: P0
Linked:    test08

**Ziel**
Google Translate wird aus der aktiven Provider-Steuerung entfernt. DeepL wird der einzige externe Provider und verarbeitet Fehler, Source Language, HTML und optionale Glossary ID robuster.

**Schritte**
1. Google aus `translator.php` Provider-Pipeline entfernen.
2. Google-Settings und Performance-Ausgabe entfernen.
3. DeepL-Backoff-Bug korrigieren.
4. DeepL HTTP-Status und API-Fehler pruefen.
5. DeepL-Settings fuer Source Language, HTML Tag Handling und Glossary ID ergaenzen.
6. Syntax-Checks ausfuehren und DevFlow aktualisieren.

**Erwartetes Ergebnis**
Automatische externe Uebersetzungen laufen nur noch ueber DeepL; Fehler schalten nicht versehentlich Google-Config und brechen Rendering nicht fatal ab.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test08 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off

### task03 Course Custom Fields fuer Kurssteuerung verwenden

Status:    done
Feature:   feat06
Prioritaet: P1
Linked:    test07

**Ziel**
Die bisher hart codierte Kurs-Tag-Steuerung wird durch eine konfigurierbare Policy ersetzt, die Moodle Course Custom Fields als Kurs-Einstellung nutzen kann.

**Schritte**
1. Policy-Klasse fuer Kurssteuerung anlegen.
2. `text_filter.php` auf Policy-Abfrage umstellen.
3. Admin-Settings fuer Control Source, Legacy-Tag und Custom Field Shortnames ergaenzen.
4. DevFlow-Doku aktualisieren.

**Erwartetes Ergebnis**
Bestehende Tag-Steuerung bleibt verfuegbar, aber neue Kurse koennen ueber Course Custom Fields gesteuert werden.

**Done-Checkliste**
- [x] 01-features.md aktualisiert
- [x] 02-user-doc.md aktualisiert
- [x] 03-dev-doc.md aktualisiert
- [x] test07 in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off
