# Artikelimport mit PHP

## Voraussetzungen und Start

PHP 8.1 oder neuer (verwendet: PHP 8.4). Keine externen Bibliotheken erforderlich.

```bash
php import.php artikel.csv ausgabe.json
```

Die gültigen Artikel stehen in `ausgabe.json`, die Fehler mit Zeilennummer und Grund in `ausgabe.json.errors.json`. Bestehende Ausgabedateien werden überschrieben. Eingabe- und Ausgabedatei müssen unterschiedlich sein.

Preise werden auf zwei Nachkommastellen gerundet und als JSON-Zahlen gespeichert; beispielsweise kann `12.90` als `12.9` erscheinen. Die Zeilenzählung setzt einen CSV-Datensatz pro physischer Dateizeile voraus.

## Wie bin ich vorgegangen?

Zuerst habe ich die Aufgabenstellung gelesen. Danach habe ich die CSV-Datei geöffnet und die Zeilen selbst durchgesehen. Bevor ich Code geschrieben habe, wollte ich verstehen, welche Daten ich korrigieren und welche Datensätze ich überspringen muss.

Dabei sind mir folgende Punkte aufgefallen:

* Die Spalten sind durch Semikolons (`;`) getrennt.
* Die Preise sind unterschiedlich geschrieben (Komma, Punkt, Leerzeichen, Euro-Zeichen).
* Bei einem Artikel fehlt die SKU.
* Ein Preis steht als `kostenlos` in der Datei, ein anderer Preis ist leer.
* Im Lagerbestand kommen `0`, negative Werte und leere Felder vor. `0` ist ein gültiger Wert, deshalb durfte ich ihn nicht mit leeren oder fehlerhaften Werten verwechseln.
* `PS-1002` kommt zweimal vor. Im späteren Datensatz haben sich Artikelname, Preis und Lagerbestand geändert.

Nach dieser Durchsicht habe ich mich entschieden, zuerst die CSV-Datei einzulesen und danach die Prüfungen Schritt für Schritt zu ergänzen. Die Artikel, die die Prüfungen für SKU, Preis und Lagerbestand bestehen, habe ich gesammelt und in eine JSON-Datei geschrieben. Das Feld EAN habe ich als Text beibehalten.

## Meine Entscheidungen

* Bei negativem oder nicht ganzzahligem Lagerbestand wird der Artikel übersprungen und der Grund in die Fehlerdatei geschrieben.
* Wenn dieselbe SKU mehrfach vorkommt, verwende ich den letzten gültigen Datensatz. Ist ein späterer Datensatz fehlerhaft, bleibt der vorherige gültige Artikel erhalten.
* Bei der Anzahl der übersprungenen Datensätze zähle ich neben den fehlerhaften Datensätzen auch die Duplikate mit, die durch einen späteren Datensatz ersetzt wurden.

Außerdem prüfe ich die Anzahl der Spalten. Hat eine Zeile zu wenige oder zu viele Felder, soll das Programm nicht komplett abbrechen, sondern diese Zeile als Fehler festhalten.

## Aufbau des Codes

Ich habe den Code auf zwei Dateien aufgeteilt:

* `import.php`: Nimmt die Namen der Eingabe- und Ausgabedateien über die Kommandozeile entgegen, prüft die Anzahl der Argumente, erzeugt ein `ArticleImporter`-Objekt und startet den Import. Ausnahmen bei Dateioperationen oder der JSON-Umwandlung werden abgefangen und als Fehlermeldung ausgegeben.
* `src/ArticleImporter.php`: Liest die CSV-Datei, prüft die Artikel, sortiert die gültigen Datensätze nach SKU und speichert die Ergebnisse.

Die Aufgaben innerhalb der Klasse habe ich auf mehrere Methoden verteilt:

* `import()` steuert den gesamten Ablauf.
* `validateArticle()` prüft und bereinigt SKU, Preis und Lagerbestand.
* `addError()` speichert die Zeilennummer und den Fehlergrund.
* `writeJson()` schreibt die Artikel und die Fehlerliste in getrennte JSON-Dateien.

Ich habe den Code so aufgeteilt, damit er leichter nachvollziehbar ist und ich bei Änderungen an einer Prüfung nur im jeweiligen Abschnitt arbeiten muss.

## Manuelle Tests

Ich habe das Skript mit der mitgelieferten CSV-Datei und veränderten
Testdaten geprüft. Dabei habe ich fehlende SKUs, ungültige Preise,
leere und negative Bestände sowie doppelte SKUs getestet.

Die JSON-Ausgabe, das Fehlerprotokoll und die Zusammenfassung auf
der Konsole habe ich mit den erwarteten Ergebnissen verglichen.

## Was ich mit mehr Zeit gemacht hätte

Ich hätte zusätzliche Prüfungen für leere Zeilen und für Sonderfälle in anderen CSV-Dateien ergänzt.

Außerdem hätte ich die Lesbarkeit des Codes weiter verbessert. Die Schritte zur Bereinigung des Preises hätte ich in eine eigene Methode `normalizePrice()` ausgelagert.

Zusätzlich hätte ich automatisierte Tests für folgende Fälle geschrieben: fehlende SKU, verschiedene Preisformate, leerer und negativer Lagerbestand, fehlende Spalten und doppelte SKUs.

## Verwendete Hilfsmittel

Da ich seit etwa einem Jahr in einem anderen Bereich arbeite, habe ich Codex genutzt, um meine PHP-Kenntnisse aufzufrischen und einige technische Details der Aufgabe schneller zu verstehen. Dabei habe ich die Erklärungen und Codevorschläge verwendet und bin Schritt für Schritt vorgegangen.

Da ich schon früher CSV-Dateien eingelesen und Daten bereinigt habe, war mir die grundsätzliche Logik der Aufgabe vertraut. Ich musste vor allem die PHP-Syntax und die Verwendung einiger Funktionen wiederholen.

Weil ich mich beim Schreiben auf Deutsch noch nicht sicher genug fühle, habe ich außerdem Unterstützung bei der Übersetzung und sprachlichen Überarbeitung dieser README ins Deutsche in Anspruch genommen.
