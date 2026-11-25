# Tipp-Auswertung für Webmaster (by Marcus)

Individuelle Auswertung der LMO-Tippspielligen für die eigene Homepage. Im Admin-Skript wählt der Webmaster aus,
welche Ligen gewertet werden und wie die Tabelle aussieht (Farben, Schrift, Spalten, Ausrichtung). Das Ergebnis
wird in einer Konfigurationsdatei gespeichert und mit `auswert2.php` angezeigt oder per `include` in die
Homepage eingebunden.

Autor: Marcus ([BC Erlbach](http://www.bcerlbach.de/)), Version 1.8.5, hier für **PHP 8** und die aktuelle
LMO-Version überarbeitet.

## Voraussetzungen

- LMO (PHP-8-Version) mit eingerichtetem und ausgewertetem Tippspiel (Addon `tipp`)
- Die Datei `gesamt.aus` (Tipp-Admin: „Tippspiel neu auswerten“). Sie wird nur gelesen, ein Backup ist nicht nötig.

## Dateien

| Datei                | Zweck                                                                  |
|----------------------|------------------------------------------------------------------------|
| `auswert_admin.php`  | Einstellungen und Ligenauswahl (nur für den LMO-Voll-Admin)            |
| `auswert2.php`       | zeigt die Auswertung an (auch per `include` einbindbar)                |
| `auswert_funk.php`   | Hilfsfunktionen (Prüfen/Bereinigen der Einstellungen)                  |
| `auswert.cfg`        | gespeicherte Einstellungen                                             |
| `AENDERUNGEN_PHP8.txt` | Änderungen gegenüber Version 1.8.5                                   |

## Installation

1. Alle PHP-Dateien und `auswert.cfg` in das **LMO-Hauptverzeichnis** kopieren (dorthin, wo `init.php` liegt).
2. `auswert.cfg` beschreibbar machen (CHMOD 666 bzw. für den Webserver-Benutzer beschreibbar).
3. Im LMO-Adminbereich als **Voll-Admin anmelden** und danach `auswert_admin.php` aufrufen.
4. Ligen markieren, Einstellungen wählen und **„Auswahl speichern“** klicken. Dabei wird `auswert.cfg` erstellt.
5. Ergebnis ansehen mit `auswert2.php`.
6. `auswert_admin.php` danach am besten vom Server löschen oder sperren.

### In die Homepage einbinden

```php
<?php include 'lmo/auswert2.php'; ?>
```

(Pfad zur Datei entsprechend anpassen, das Einbinden funktioniert auch von außerhalb des LMO-Ordners.)

## Einstellungen im Admin-Skript

- Seitentitel, Schrift, Schriftgröße, Hintergrund-, Tabellenkopf-, Tabellen- und Fehlerfarben
- Anzahl der angezeigten Tipper (`-1` = alle), Tipper ohne Tipp anzeigen
- Spalten ein-/ausblenden: Spiele, Quote/Punkte pro Spiel, Joker, Punkte, Team
- Spaltenüberschriften, Zellenabstände, Klammern, Punkt hinter der Platzierung, Überschrift
- Ausrichtung je Spalte (links, zentriert, rechts)

## Sicherheit

- `auswert_admin.php` ist nur für den angemeldeten **Voll-Admin** des LMO erreichbar (vorher konnte jeder die
  Konfiguration überschreiben). Ohne Anmeldung erscheint „Zugriff verweigert“.
- Das Formular ist mit einem Sicherheits-Token gegen fremde Aufrufe (CSRF) geschützt.
- Alle Werte werden vor dem Speichern und bei der Ausgabe geprüft (Farben, Schrift, Zahlen, Ausrichtung, Text)
  und HTML-sicher ausgegeben, auch wenn `auswert.cfg` von einer älteren Version stammt.
- Der Pfad zur Auswertungsdatei darf nur innerhalb des LMO-Ordners liegen.

## Änderungen für PHP 8

Die ausführliche Liste steht in [AENDERUNGEN_PHP8.txt](AENDERUNGEN_PHP8.txt). Kurz:

- `$_POST[title]` usw. -> `$_POST['title']` (vorher Fatal Error beim Speichern), nicht definierte Variablen und
  Array-Schlüssel behoben, nicht angehakte Kontrollkästchen erzeugen keine Warnungen mehr
- Absturz (TypeError) bei leerer Zeile am Ende der `auswert.cfg` behoben, Sortierung am Listenende abgesichert
- Tippmodus und Pfad zur `gesamt.aus` kommen aus der Konfiguration des Tipp-Addons statt fest eingebaut
- Leere Tabellenzeile (`<tr> <td >`) vor jedem Tipper entfernt (ungültiges HTML)
- PSR-12-formatiert (php-cs-fixer), UTF-8, einheitlich LF-Zeilenenden

## Lizenz

GNU General Public License, Version 2 oder höher – siehe [LICENSE](LICENSE).
Die Urheberhinweise im Quelltext dürfen nicht entfernt oder geändert werden.
