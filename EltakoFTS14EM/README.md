# Eltako Eingabemodul FTS14EM

Die Instanz wertet Tastersignale des Eingabemoduls FTS14EM nach kurzem, langem Tastendruck und Doppel-Klick aus. 

## Konfiguration

* Mit der Geräte-ID wird die Basis-ID des Eingabemoduls festgelegt. 
* Bei Eltako FTS14EM kann auf dem Gerät festlegt werden, ob die angeschlossenen Geräte als Universal- oder Richtungstaster, Bewegungsmelder oder Tür-Fensterkontakte verwendet werden sollen. Bitte in der Instanz die gleiche Einstellung wählen.
* Bei Verwendung von Tastern kann die Dauer eingestellt werden, ab wann ein Tastendruck als "langer Tastendruck" erkannt wird. Für Kurzhubtasten sind 250ms ein guter Standardwert. Bei klassischen mechanischen Tastern mit langem Hub sollte der Wert auf 500ms gestellt werden.

## Changelog

| Version | Changes								            |
| --------|-------------------------------------------------|
| V3.08   | Basisversion					            	|
| V3.09   | Fix: LongPressDetectionTime			            |
| V3.10   | Fix: Filterproblem bei 64-bit Windows-Systemen  |
| V3.14   | FIX: Anpassung ID-Berechnung auf IPS 6.3        |
| V3.15   | FIX: Button 10 ohne Funktion                    |
| V3.20   | Neu: Bewegungsmelder und Tür-Fenster-Kontakte   |

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
