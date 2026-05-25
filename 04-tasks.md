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

Naechster empfohlener Schritt: `task02 Lokale Moodle-Verifikation einrichten`.

Letzter abgeschlossener Schritt: `task11 Glossar-Import-Dublettenfix und Toolbar-Polish`.

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
Nach `task11` sind Schema, Settings, Glossar-UI, CSV-Import/Export, Dublettenhandling beim Import und DeepL-v3-Sync syntaktisch gueltig. Offen ist der Moodle-Upgrade-Lauf, UI-Test im Browser und optional ein echter DeepL-Sync mit API-Key.

**Done-Checkliste**
- [ ] 01-features.md aktualisiert (nicht erforderlich, falls nur Setup)
- [ ] 02-user-doc.md aktualisiert (nicht erforderlich)
- [ ] 03-dev-doc.md aktualisiert
- [ ] test01/test02 in 05-quality.md gruen
- [ ] PO Sign-off

---

## Verifikation nach Deploy

- Glossar-Import in Moodle erneut mit der Datei testen, die zuvor `mdb->get_record() found more than one record!` ausgeloest hat.
- Glossarverwaltung pruefen: Paginierung sichtbar bei mehr als 100 Treffern; `Create glossary entry` steht oben neben `DeepL glossary sync`.
- Optional DeepL-Sync-Preview oeffnen und einen echten Sync mit Test-Key ausfuehren.

---

## Done

Erledigte Tasks bleiben als Historie erhalten.

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
