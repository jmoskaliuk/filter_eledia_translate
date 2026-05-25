# Features

## Meta

Dieses Dokument definiert, was das Plugin tun soll.

Es enthaelt:

- Feature-Definitionen (`featXX`)
- gewuenschtes Verhalten
- Akzeptanzkriterien
- Non-Goals
- Releases (`relXX`)

Quelle der Wahrheit fuer gewuenschtes Verhalten.

---

## Produkt-Uebersicht

### Zweck

`filter_translations` ermoeglicht, nutzergenerierte Moodle-Inhalte in andere Sprachen zu uebertragen, ohne den Ursprungstext pro Sprache duplizieren zu muessen.

### Kernkonzepte

- **Translation Hash:** MD5-basierter Schluessel, der Quellinhalt und Uebersetzungen verbindet.
- **Translation Span:** HTML-Element mit `data-translationhash`, das Inhalte stabil referenzierbar macht.
- **Manual Translation:** Von berechtigten Nutzern gepflegte Uebersetzung.
- **Automatic Translation:** Uebersetzung durch Reverse-Language-String-Lookup oder DeepL.
- **Translation Issue:** Protokollierter Hinweis auf fehlende oder veraltete Uebersetzung.

### Constraints

- Das Plugin laeuft innerhalb von Moodle und folgt Moodle-Filter-, Capability-, Cache-, Task- und Persistent-APIs.
- Automatische Provider duerfen nur genutzt werden, wenn sie explizit konfiguriert sind.
- Bulk-Skripte und Scheduled Tasks koennen gespeicherte Inhalte veraendern und muessen vorsichtig eingesetzt werden.

---

## Features

### feat01 Content translations filter

**Ziel**
Moodle-Inhalte werden beim Rendern in die bevorzugte Sprache des Nutzers uebersetzt, wenn eine passende Uebersetzung vorhanden ist.

**Verhalten**

- Das Plugin priorisiert die Sprache des Nutzers und passende Parent-Sprachen.
- Es sucht Uebersetzungen ueber `data-translationhash`, generierten Inhaltshash und `lastgeneratedhash`.
- Stale automatische Uebersetzungen werden nicht wiederverwendet.
- Spracheigennamen werden nicht uebersetzt.

**Akzeptanzkriterien**

- `feat01.AC01`
  Given: Eine deutsche Uebersetzung existiert fuer den gefundenen Hash.
  When: Ein Nutzer mit Sprache `de` den Inhalt oeffnet.
  Then: Der deutsche Ersatztext wird gerendert.

- `feat01.AC02`
  Given: Nur eine stale automatische Uebersetzung existiert.
  When: Der Quelltext geaendert wurde.
  Then: Die stale automatische Uebersetzung wird nicht als finaler Ersatz verwendet.

**Non-Goals**

- Kein Live-Editing im Filter selbst.
- Keine Garantie, dass automatische Provider verfuegbar oder kostenfrei sind.

---

### feat02 Translator editing UI

**Ziel**
Berechtigte Nutzer koennen Uebersetzungen im Moodle-Kontext finden, bearbeiten und verwalten.

**Verhalten**

- Nutzer mit `filter/translations:edittranslations` sehen eine Uebersetzeransicht.
- Translatierbare Inhalte erhalten ein Bearbeitungssymbol.
- Verwaltungsseiten listen Uebersetzungen und ermoeglichen Bearbeitung, Import und Export.

**Akzeptanzkriterien**

- `feat02.AC01`
  Given: Ein Nutzer hat die Edit-Capability.
  When: Er die Uebersetzeransicht aktiviert.
  Then: Bearbeitungsicons erscheinen neben translatierbaren Inhalten.

- `feat02.AC02`
  Given: Eine bestehende Uebersetzung.
  When: Ein berechtigter Nutzer sie bearbeitet und speichert.
  Then: Die neue Uebersetzung wird beim naechsten Rendering verwendet.

---

### feat03 Translation issue logging

**Ziel**
Fehlende und veraltete Uebersetzungen koennen fuer redaktionelle Nacharbeit protokolliert werden.

**Verhalten**

- Missing- und stale-Issues werden nur geloggt, wenn die entsprechenden Einstellungen aktiv sind.
- Logging respektiert ausgeschlossene Sprachen.
- `logdebounce` verhindert wiederholte identische Eintraege in kurzer Zeit.
- Alte Issues koennen per Scheduled Task bereinigt werden.

**Akzeptanzkriterien**

- `feat03.AC01`
  Given: `logmissing` ist aktiv und keine Uebersetzung existiert.
  When: Der Inhalt in einer nicht ausgeschlossenen Sprache gerendert wird.
  Then: Ein Missing-Issue wird gespeichert.

- `feat03.AC02`
  Given: Ein identisches Issue wurde kuerzlich geloggt.
  When: Der Inhalt erneut gerendert wird.
  Then: Es entsteht kein Duplikat innerhalb des Debounce-Fensters.

---

### feat04 Automatic translation providers

**Ziel**
Das Plugin kann fehlende Uebersetzungen optional ueber DeepL automatisch erzeugen oder aktualisieren.

**Verhalten**

- Reverse Language String Lookup wird zuerst versucht.
- DeepL wird genutzt, wenn `deepl_enable` aktiv ist.
- DeepL-Anfragen pruefen HTTP-Status und API-Fehler robust.
- DeepL kann optional HTML Tag Handling, Source Language und Glossary ID verwenden.
- Administratoren koennen die DeepL-Konfiguration ueber eine Testseite pruefen.
- Provider-Fehler koennen Backoff-Verhalten ausloesen, wenn konfiguriert.

**Akzeptanzkriterien**

- `feat04.AC01`
  Given: Reverse Lookup findet eine passende Moodle-Sprachzeichenkette.
  When: Eine Uebersetzung fehlt.
  Then: Die Uebersetzung wird aus der Sprachzeichenkette erstellt.

- `feat04.AC02`
  Given: DeepL ist aktiv.
  When: Reverse Lookup keine Uebersetzung findet.
  Then: DeepL wird als automatischer Provider versucht.

- `feat04.AC03`
  Given: DeepL liefert einen API- oder HTTP-Fehler.
  When: Backoff aktiviert ist.
  Then: DeepL wird temporaer pausiert und der Filter rendert ohne Fatal Error weiter.

- `feat04.AC04`
  Given: Eine DeepL Glossary ID ist konfiguriert.
  When: Eine Source Language fuer die Anfrage bekannt ist.
  Then: Die Anfrage enthaelt `glossary_id` und `source_lang`.

**Non-Goals**

- Keine Provider-Konfiguration ausserhalb von Moodle.
- Kein paralleler Multi-Provider-Abgleich.
- Google Translate ist keine aktive Provider-Option.

---

### feat05 Bulk maintenance, import and export

**Ziel**
Administratoren koennen bestehende Inhalte und Uebersetzungen migrieren, bereinigen, importieren und exportieren.

**Verhalten**

- CLI-Skripte unter `cli/` unterstuetzen Migration, Span-Insertion, Hash-Bereinigung und Kopieren von Uebersetzungen.
- Scheduled Tasks koennen ausgewaehlte Bulk-Operationen wiederkehrend ausfuehren.
- Import- und Exportseiten unterstuetzen Uebersetzungsdaten-Transfer.

**Akzeptanzkriterien**

- `feat05.AC01`
  Given: Ein Administrator ruft ein CLI-Skript mit `--help` auf.
  When: Das Skript startet.
  Then: Es zeigt eine nutzbare Hilfe statt ungefragt Daten zu veraendern.

- `feat05.AC02`
  Given: Uebersetzungsdaten sollen zwischen Umgebungen bewegt werden.
  When: Export und Import genutzt werden.
  Then: Uebersetzungen koennen nachvollziehbar uebertragen werden.

**Non-Goals**

- Bulk-Operationen sind kein Ersatz fuer ein Backup.
- Keine automatische Produktionsausfuehrung ohne explizite Admin-Konfiguration.

---

### feat06 Course-level translation control

**Ziel**
Die Entscheidung, ob ein Kurs uebersetzt wird und welche Zielsprachen erlaubt sind, soll ueber Moodle-Kurseinstellungen steuerbar sein statt ausschliesslich ueber hart codierte Kurs-Tags.

**Verhalten**

- Administratoren waehlen global, ob Kurssteuerung ueber Legacy-Tags, Course Custom Fields oder Course Custom Fields mit Tag-Fallback erfolgt.
- Bei Course Custom Fields aktiviert ein konfigurierbares Kursfeld die Uebersetzung.
- Ein zweites konfigurierbares Kursfeld kann erlaubte Zielsprachen als Moodle-Sprachcodes enthalten.
- Leeres Zielsprachenfeld bedeutet: alle Sprachen sind erlaubt, sofern die Kursuebersetzung aktiviert ist.
- Legacy-Tags bleiben als Fallback verfuegbar, damit bestehende Kurse weiter funktionieren.

**Akzeptanzkriterien**

- `feat06.AC01`
  Given: Der Control Source ist `Course custom fields`.
  When: Das Aktivierungsfeld im Kurs nicht gesetzt ist.
  Then: Der Filter uebersetzt Kursinhalte nicht automatisch.

- `feat06.AC02`
  Given: Das Aktivierungsfeld ist gesetzt und das Sprachfeld enthaelt `de, fr`.
  When: Ein Nutzer den Kurs mit Sprache `de` oeffnet.
  Then: Der Filter darf Uebersetzungen fuer `de` verwenden.

- `feat06.AC03`
  Given: Das Aktivierungsfeld ist gesetzt und das Sprachfeld enthaelt `de, fr`.
  When: Ein Nutzer den Kurs mit Sprache `es` oeffnet.
  Then: Der Filter uebersetzt fuer `es` nicht.

- `feat06.AC04`
  Given: Der Control Source ist `Course custom fields, then legacy tags`.
  When: Der Kurs keine passenden Custom Field Werte besitzt.
  Then: Die bisherige Tag-Steuerung wird als Fallback verwendet.

**Non-Goals**

- Das Plugin legt die Moodle Course Custom Fields noch nicht automatisch an.
- Keine eigene Kurs-Konfigurationsseite ausserhalb des Moodle-Course-Custom-Fields-Systems.

---

### feat07 Glossary management baseline

**Ziel**
Terminologie soll als eigenes Glossar gepflegt werden koennen, statt nur indirekt ueber vollstaendige Uebersetzungsdatensaetze.

**Verhalten**

- Glossarbegriffe werden getrennt von normalen Inhaltsuebersetzungen modelliert.
- Glossare koennen global oder kursbezogen gepflegt werden.
- Begriffe haben Sprachrichtung, Status, Prioritaet und optionale Review-Information.
- Administratoren koennen Glossarbegriffe ueber eine Management-Seite filtern, anlegen und bearbeiten.
- Die Glossarverwaltung nutzt Moodle-Paginierung fuer groessere Eintragslisten.
- Administratoren koennen gefilterte Glossarlisten als CSV exportieren und CSV-Dateien importieren.
- Beim Import werden bestehende Begriffe gleicher Quellphrase, Sprachrichtung und gleichem Scope aktualisiert; vorhandene Dubletten werden ohne Runtime-Notice gemeinsam aktualisiert.
- Freigegebene Glossare koennen kontrolliert mit DeepL Glossaries synchronisiert werden.
- Fuer DeepL-Sync wird die v3 Glossary API vorgesehen, damit ein Glossar mehrere Sprachpaare als Dictionaries enthalten kann.

**Akzeptanzkriterien**

- `feat07.AC01`
  Given: Ein Glossarbegriff ist freigegeben.
  When: Eine automatische DeepL-Uebersetzung fuer dieselbe Sprachrichtung erzeugt wird.
  Then: Der Begriff kann fuer DeepL-Glossary-Nutzung vorbereitet werden.

- `feat07.AC02`
  Given: Redaktion pflegt Terminologie.
  When: Sie die Glossarverwaltung oeffnet.
  Then: Sie kann nach Begriff, Sprache, Kurs/Kontext und Status filtern.

- `feat07.AC03`
  Given: Eine gepflegte Glossarliste existiert.
  When: Administration die Liste als CSV exportiert oder eine CSV importiert.
  Then: Glossarbegriffe koennen ausserhalb von Moodle gepflegt und kontrolliert zurueckgespielt werden.

- `feat07.AC04`
  Given: Glossarbegriffe sind freigegeben.
  When: Ein DeepL-Sync geplant oder ausgefuehrt wird.
  Then: Nur `approved` Eintraege werden pro Scope und Sprachpaar in DeepL-v3-Dictionaries ueberfuehrt.

**Non-Goals**

- Keine automatische DeepL-v3-Synchronisation ohne ausdruecklichen Admin-Start.
- Kein Ersatz fuer die bestehende Inhaltsuebersetzungsverwaltung.

---

## Releases

### rel01 Current baseline

- **Version:** `2.1.0-dev`
- **Plugin version:** `2026052301`
- **Moodle requires:** `2022112814` / Moodle 4.1.13
- **Status:** DevFlow, DeepL-only Provider, Course Custom Fields, Glossarverwaltung, CSV Import/Export und DeepL-v3-Glossary-Sync sind implementiert. Runtime-Verifikation in Moodle bleibt offen.
