# Eltako Bustaster

Die Instanz wertet Signale von Enocean-Tastern nach kurzem, langem Tastendruck und Doppel-Klick aus. Bei Tastern des 14er BUS von Eltako  mit Kontroll-LEDs  können die LEDs angesteuert werden. Neben einer verzögerungsfreien Statusanzeige des geschalteten Gerätes können auch Stati anderer Geräte zur Anzeige gebracht werden. Weiter kann man die Kontrollleuchten auch blinken lassen. Zur Installation bitte nach *FTS61BTK* suchen.

## Konfiguration

* Mit der Geräte-ID wird die Basis-ID des Tasters festgelegt. * Bei Eltako BUS-Tastern kann man im PCT14 festlegen, ob diese für jede Taste eine unterschiedliche Adresse verwenden. Die Einstellung der Instanz muss dem entsprechen.
* Für die Auswertung, ob eine Taste länger gehalten wird, sind 250ms ein guter Standardwert. Dieser sollte nur verändert werden, wenn es zu Problemen mit der Erkennung kommt. Dies kann z.B. der Fall sein, wenn Taster schwergängig sind. Diese werden typischerweise nicht so schnell geklickt.
* Für die Eltako BUS-Taster kann man noch wählen, ob diese Kontrollleuchten haben. Wenn ja, wird nach dem Speichern der Änderung ein zusätzliches Menü zur Konfiguration der LEDs sichtbar.

## Konfiguration der Kontrollleuchten (LEDs)

* Zum Ansteuern der LED kann eine beliebige SendeID im HEX-Format festgelegt werden. Es ist allerdings darauf zu achten, dass die vergebene ID noch nirgends im System genutzt wurde. Diese ID dann bitte im PCT14 bei der anzusteuernden LED eintragen.
* Als StatusID bitte im Objektbaum den Status im Objektbaum heraussuchen, der mit der LED angezeigt werden soll. Der Status kann vom Typ BOOL, INTEGER oder FLOAT sein. Bei INTEGER und FLOAT wird der Betrag des Wertes zur Anzeige gebracht. Wobei *1* und *0* *an* und *aus* entsprechen. Werte größer oder gleich *2* werden durch *blinken* dargestellt.
* Zum Schluss kann noch eingestellt werden, ob die LED bei Tastendruck direkt an oder aus geht. Damit das funktioniert, darf der Status nur auf der an- **oder** aus-Seite angezeigt werden.

## Zusätzliche PHP-Befehle

| Befehl                                          | Erläuterung	          |
| ------------------------------------------------|-----------------------|
| *MEF_SwitchLED($InstanzID, $Position, $Value);* | Ein/Ausschalten der LED <br> $Position: wird mit TL, TR, BL oder BR angegeben<br>$Value: 0 - aus, 1 - an, 2 - blinken |

## Changelog

| Version | Changes								|
| --------|-------------------------------------|
| V3.07   | Basisversion						|

## License

MIT License

Copyright (c) 2021 nefiertsrebliS

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
