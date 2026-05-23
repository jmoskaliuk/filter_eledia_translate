# Benutzer-Dokumentation

## Meta

Dieses Dokument beschreibt, wie Nutzer mit dem Plugin interagieren.

Quelle der Wahrheit fuer sichtbares Verhalten.

---

## Zielgruppen

- **Lernende und normale Nutzer:** sehen uebersetzte Inhalte, ohne die technische Logik zu bedienen.
- **Uebersetzer / Redakteure:** pflegen Uebersetzungen im Moodle-Kontext.
- **Administratoren:** installieren, konfigurieren, importieren, exportieren und ueberwachen das Plugin.

---

## Produkt-Nutzung

### Content translations filter (`feat01`)

**Was tut es?**
Der Filter ersetzt Inhalte beim Anzeigen durch eine passende Uebersetzung, wenn fuer die Nutzersprache eine Uebersetzung existiert.

**Wann nutze ich es?**
Immer dann, wenn Moodle-Kursinhalte fuer mehrsprachige Zielgruppen bereitgestellt werden sollen.

**Bedienung**

1. Administrator aktiviert den Filter unter `Site administration > Plugins > Filters > Manage filters`.
2. Nutzer waehlen oder verwenden ihre bevorzugte Moodle-Sprache.
3. Moodle rendert Inhalte mit passenden Uebersetzungen.

**Erwartetes Ergebnis**
Nutzer sehen uebersetzte Inhalte in ihrer Sprache, sofern passende Uebersetzungen vorhanden sind.

**Hinweise**

- Wenn keine passende Uebersetzung existiert, bleibt der Ursprungstext sichtbar.
- Die Qualitaet automatischer Uebersetzungen haengt vom konfigurierten Provider ab.

---

### Translator editing UI (`feat02`)

**Was tut es?**
Berechtigte Nutzer koennen Uebersetzungen direkt aus dem Moodle-Kontext heraus bearbeiten.

**Wann nutze ich es?**
Wenn Inhalte manuell uebersetzt, korrigiert oder aktualisiert werden sollen.

**Bedienung**

1. Nutzer mit `filter/translations:edittranslations` oeffnen eine Seite mit translatierbaren Inhalten.
2. Die Uebersetzeransicht wird aktiviert.
3. Neben translatierbaren Inhalten erscheinen Bearbeitungssymbole.
4. Ein Symbol oeffnen, Uebersetzung pflegen und speichern.

**Erwartetes Ergebnis**
Die gespeicherte Uebersetzung wird fuer passende Nutzer und Sprache verwendet.

**Hinweise**

- Rechte werden ueber Moodle-Capabilities gesteuert.
- Companion-Editor-Plugins fuer Atto oder TinyMCE koennen den Workflow ergaenzen.

---

### Translation issue logging (`feat03`)

**Was tut es?**
Das Plugin kann fehlende oder veraltete Uebersetzungen protokollieren.

**Wann nutze ich es?**
Wenn Redaktion oder Administration sehen wollen, wo Uebersetzungen fehlen oder nach Quelltextaenderungen erneuert werden muessen.

**Bedienung**

1. Administrator aktiviert `logmissing` und/oder `logstale` in den Plugin-Einstellungen.
2. Nutzer besuchen Moodle-Seiten mit uebersetzbaren Inhalten.
3. Administratoren pruefen die Seite zur Verwaltung von Translation Issues.

**Erwartetes Ergebnis**
Fehlende oder veraltete Uebersetzungen werden als Issues sichtbar.

**Hinweise**

- `logdebounce` reduziert wiederholte Eintraege.
- Ausgeschlossene Sprachen werden nicht protokolliert.

---

### Automatic translation providers (`feat04`)

**Was tut es?**
Das Plugin kann Uebersetzungen automatisch erzeugen, wenn Provider aktiviert sind.

**Wann nutze ich es?**
Wenn initiale Uebersetzungen schneller bereitstehen sollen und eine manuelle Nachbearbeitung akzeptiert oder geplant ist.

**Bedienung**

1. Administrator oeffnet die Plugin-Einstellungen.
2. Reverse-Language-String-Lookup oder DeepL aktivieren.
3. API-Endpunkt und API-Key fuer externe Provider eintragen.
4. Optional Source Language, HTML Tag Handling und Glossary ID fuer DeepL konfigurieren.
5. Inhalte in einer Ziel-Sprache anzeigen lassen.

**Erwartetes Ergebnis**
Das Plugin versucht, fehlende Uebersetzungen automatisch zu erzeugen.

**Hinweise**

- Externe Provider koennen Kosten verursachen.
- API-Keys sind sensible Konfiguration.
- Automatische Uebersetzungen sollten fachlich geprueft werden.
- Eine DeepL Glossary ID sollte nur mit passender Source Language verwendet werden.

---

### Bulk maintenance, import and export (`feat05`)

**Was tut es?**
Administratoren koennen Uebersetzungen migrieren, Hashes bereinigen, Spans einfuegen und Daten importieren oder exportieren.

**Wann nutze ich es?**
Bei Migrationen, groesseren Bestandsdaten, Umgebungstransfers oder Wartungsarbeiten.

**Bedienung**

1. Vorher ein Datenbank-Backup erstellen.
2. CLI-Skript zuerst mit `--help` pruefen.
3. Operation in einer Testumgebung ausfuehren.
4. Ergebnis pruefen.
5. Erst danach in produktionsnahen Umgebungen ausfuehren.

**Erwartetes Ergebnis**
Uebersetzungsdaten und Translation Hashes koennen kontrolliert gepflegt oder uebertragen werden.

**Hinweise**

- Bulk-Operationen koennen gespeicherte Inhalte veraendern.
- Scheduled Tasks sind fuer grosse Sites standardmaessig ueberwiegend deaktiviert und muessen bewusst aktiviert werden.

---

### Course-level translation control (`feat06`)

**Was tut es?**
Administratoren koennen festlegen, ob die Uebersetzung eines Kurses weiterhin ueber Kurs-Tags oder ueber Moodle Course Custom Fields gesteuert wird.

**Wann nutze ich es?**
Wenn die Uebersetzungssteuerung sichtbar in den Kurseinstellungen gepflegt werden soll und Tags nicht mehr die einzige Steuerungsquelle sein sollen.

**Bedienung**

1. Moodle Course Custom Fields anlegen:
   - Checkbox mit Shortname `eledia_translate_enabled`
   - Textfeld oder Textarea mit Shortname `eledia_translate_languages`
2. Plugin-Einstellungen oeffnen.
3. Bei Bedarf `Create course translation fields` ausfuehren, um die empfohlenen Kursfelder automatisch anzulegen.
4. Unter `Course translation control` den Control Source waehlen.
5. Im Kurs das Aktivierungsfeld setzen.
6. Optional Zielsprachen als Moodle-Sprachcodes eintragen, z. B. `de, fr, es`.

**Erwartetes Ergebnis**
Der Kurs wird nur uebersetzt, wenn die Kurssteuerung ihn erlaubt. Zielsprachen koennen pro Kurs eingeschraenkt werden.

**Hinweise**

- Bleibt das Sprachfeld leer, sind alle Sprachen erlaubt.
- Der Modus `Course custom fields, then legacy tags` schuetzt bestehende Kurse, die noch mit Tags wie `deepl` und Sprachcodes arbeiten.
- Neue Installationen und Upgrades versuchen, die empfohlenen Kursfelder automatisch anzulegen.

---

### Glossary management (`feat07`)

**Was tut es?**
Administratoren koennen Terminologie getrennt von normalen Inhaltsuebersetzungen pflegen.

**Wann nutze ich es?**
Wenn Fachbegriffe, Produktnamen oder festgelegte Formulierungen konsistent uebersetzt werden sollen.

**Bedienung**

1. Plugin-Einstellungen oder das Uebersetzungsmenue oeffnen.
2. `Manage glossary` waehlen.
3. Nach Quellbegriff, Zielbegriff, Sprache, Glossar-Scope oder Status filtern.
4. Glossarbegriff anlegen oder bearbeiten.
5. Entscheiden, ob der Begriff global fuer alle Kurse oder nur fuer einen Kurs gilt.
6. Status, Prioritaet, Whole-Word-Option und optionale Notizen pflegen.

**Erwartetes Ergebnis**
Terminologie liegt strukturiert vor und kann spaeter fuer DeepL-Glossaries oder redaktionelle Qualitaetssicherung genutzt werden.

**Hinweise**

- `Global / all courses` bedeutet: Der Glossarbegriff gilt kursuebergreifend.
- Ein ausgewaehlter Kurs bedeutet: Der Glossarbegriff ist auf diesen Kurs beschraenkt.
- DeepL-Synchronisation und CSV-Import/Export sind als Folgeaufgaben vorgesehen.
