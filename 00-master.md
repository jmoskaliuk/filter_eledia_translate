# eLeDia.OS — Master

> Zentraler Eintrittspunkt für jede KI- oder Entwickler-Session in diesem Repository.

---

## 1. Projekt-Meta

- **Name:** filter_eledia_translate
- **Moodle-Komponente:** `filter_translations`
- **Plugin-Pfad:** `filter/translations`
- **Ziel:** Uebersetzung von nutzergenerierten Moodle-Inhalten ueber einen Textfilter, inklusive manueller Uebersetzungen, optionaler automatischer Provider, Hash-basierter Wiedererkennung und Verwaltungsoberflaechen.
- **Tech Stack:** Moodle Filter Plugin, PHP, Moodle Persistent API, XMLDB, Mustache, AMD JavaScript, PHPUnit.
- **Mindestversion:** Moodle 4.1.13 laut `version.php`.
- **Repository:** https://github.com/jmoskaliuk/filter_eledia_translate

---

## 2. Session-Start

1. Dieses Dokument lesen.
2. `04-tasks.md` lesen und offene `taskXX` / `qXX` pruefen.
3. Fuer Verhalten `01-features.md` pruefen.
4. Fuer sichtbare Bedienung `02-user-doc.md` pruefen.
5. Fuer Implementierung `03-dev-doc.md` pruefen.
6. Fuer Tests, Bugs und Risiken `05-quality.md` pruefen.
7. Bei Moodle-Themen die DevFlow-Vorgabe `Skills/moodle-dev.md` aus `eLeDia.OS_DevFlow` als Rahmen verwenden.
8. Bei Unklarheit nicht raten, sondern `qXX` in `04-tasks.md` anlegen.

---

## 3. Datei-System

| Datei | Zweck |
| --- | --- |
| `01-features.md` | Gewuenschtes Verhalten, Features, Akzeptanzkriterien, Releases |
| `02-user-doc.md` | Benutzerperspektive und Bedienung |
| `03-dev-doc.md` | Technischer Ist-Zustand |
| `04-tasks.md` | Operative Tasks, offene Fragen, Verifikation |
| `05-quality.md` | Bugs, Tests, Qualitaetsstatus |
| `README.md` | Oeffentliche Kurzbeschreibung und Einstieg |

---

## 4. ID-System

| Praefix | Bedeutung | Ablage |
| --- | --- | --- |
| `featXX` | Feature | `01-features.md` |
| `taskXX` | Task | `04-tasks.md` |
| `qXX` | Offene Frage | `04-tasks.md` |
| `bugXX` | Bug | `05-quality.md` |
| `testXX` | Test / Verifikation | `05-quality.md` |
| `adrXX` | Architekturentscheidung | dieses Dokument |
| `relXX` | Release | `01-features.md` |

Beispielkette:

```text
feat03 -> task07 -> bug04 -> test12 -> adr02
```

---

## 5. Workflow

```text
Idee -> Feature -> Task -> Implementierung -> Test -> (Bug -> Fix) -> Done -> Doku-Sync
```

Ein Feature ist erst done, wenn Intent, Bedienung und Implementierung konsistent dokumentiert sind.

---

## 6. Definition of Done

Ein Feature ist done, wenn:

- `01-features.md` Intent und Akzeptanzkriterien beschreibt.
- `02-user-doc.md` sichtbares Verhalten beschreibt, falls Benutzer betroffen sind.
- `03-dev-doc.md` die tatsaechliche Implementierung beschreibt.
- alle verlinkten `taskXX` erledigt sind.
- alle relevanten `testXX` gruen oder bewusst als nicht ausfuehrbar dokumentiert sind.
- keine blockierenden `bugXX` offen sind.
- PO Sign-off erfolgt ist.

### Done-Checkliste pro Task

```text
- [ ] 01-features.md aktualisiert (falls Verhalten geaendert)
- [ ] 02-user-doc.md aktualisiert (falls UX geaendert)
- [ ] 03-dev-doc.md aktualisiert (immer bei Code-Aenderung)
- [ ] testXX in 05-quality.md gruen oder Status dokumentiert
- [ ] PO Sign-off
```

---

## 7. Zusammenarbeit

| Rolle | Wer | Verantwortung |
| --- | --- | --- |
| Product Owner | Mensch | Ziel, Scope, Prioritaeten, Sign-off, Releases |
| Architekt | Mensch, KI beraet | ADRs, groessere Strukturentscheidungen |
| Implementer | KI primaer | Code, Tests, Task-Umsetzung |
| Doc-Sync | KI primaer | Konsistenz von `01` / `02` / `03` |
| QA-Reviewer | Mensch, KI generiert Drafts | Manuelle Verifikation, finale Testbewertung |
| Triage | Mensch | Neue Inputs in Tasks, Bugs oder Fragen ueberfuehren |

Die KI darf alleine:

- freigegebene Tasks umsetzen,
- Doku nach Code-Aenderungen synchronisieren,
- Test-Drafts anlegen,
- Statusberichte liefern.

Die KI muss vorschlagen und warten bei:

- neuen Features,
- Architekturentscheidungen,
- neuen Dependencies,
- Schema-/Datenbankmigrationen,
- Loeschoperationen,
- Releases und Plugin-Submission.

---

## 8. Repo-Hygiene

- Branches: `feat/featXX-kurztitel`, `fix/bugXX-kurztitel`, `task/taskXX-kurztitel`.
- Commits: beginnen mit ID, z. B. `task02: DevFlow-Dokumentation anlegen`.
- PR-Titel: ID plus Klartext.
- PR-Body: Scope, Tests, Done-Checkliste.
- Keine lokalen Artefakte wie `.DS_Store`, `vendor/`, `node_modules/`, Coverage oder generierte AMD-Dateien committen.

---

## 9. Architekturentscheidungen

### adr01 eLeDia.OS_DevFlow als Projektstruktur

- **Datum:** 2026-05-23
- **Status:** beschlossen
- **Kontext:** Das Repository benoetigt eine KI- und menschenlesbare Struktur fuer Features, Benutzer-Doku, Entwickler-Doku, Tasks und Qualitaet.
- **Optionen:** A) lose README-Erweiterung, B) DevFlow-Struktur `00` bis `05`, C) Wiki ausserhalb des Repositories.
- **Entscheidung:** B, DevFlow-Struktur direkt im Repository.
- **Konsequenzen:** Jede relevante Aenderung muss die passende Perspektive aktualisieren. Dokumentation bleibt nah am Code.

### adr02 Bestehende Moodle-Plugin-Architektur bleibt fuehrend

- **Datum:** 2026-05-23
- **Status:** beschlossen
- **Kontext:** Das Projekt ist ein existierendes Moodle-Filter-Plugin mit Komponente `filter_translations`.
- **Optionen:** A) Plugin-Struktur refactoren, B) bestehende Moodle-Struktur dokumentieren und respektieren.
- **Entscheidung:** B.
- **Konsequenzen:** DevFlow beschreibt den Ist-Zustand und erzwingt keinen Strukturumbau. Neue Implementierungen folgen Moodle-Konventionen.

---

## 10. Prompt-Shortcuts

### `#status`

Analysiere `01-features.md`, `04-tasks.md`, `05-quality.md` und gib aus: implementierte Features, offene Tasks, offene Fragen, Bugs, Teststatus, Risiken und die naechsten drei sinnvollen Schritte.

### `#next`

Identifiziere die 1-3 wertvollsten naechsten Tasks mit Grund, Abhaengigkeiten und Risiko.

### `#plan`

Brich ein Feature in Tasks mit Ziel, Schritten, Abhaengigkeiten und erwartetem Ergebnis.

### `#implement`

Setze einen freigegebenen Task um, aktualisiere Tests und synchronisiere DevFlow-Doku.

### `#doc`

Pruefe und aktualisiere `01-features.md`, `02-user-doc.md`, `03-dev-doc.md` nach einer Aenderung.
