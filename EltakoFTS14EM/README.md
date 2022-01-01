# Eltako Eingabemodul FTS14EM

Die Instanz wertet Signale des Eingabemoduls FTS14EM nach kurzem, langem Tastendruck und Doppel-Klick aus. 

## Konfiguration

* Mit der Geräte-ID wird die Basis-ID des Eingabemoduls festgelegt. 
* Bei Eltako FTS14EM kann auf dem Gerät festlegt werden, ob die angeschlossenen Tasten aus Universal- oder Richtungstaster verwendet werden sollen. Bitte in der Instanz die gleiche Einstellung wählen.
* Für die Auswertung, ob eine Taste länger gehalten wird, sind 250ms ein guter Standardwert. Dieser sollte nur verändert werden, wenn es zu Problemen mit der Erkennung kommt. Dies kann z.B. der Fall sein, wenn Taster schwergängig sind. Diese werden typischerweise nicht so schnell geklickt.

## Changelog

| Version | Changes								            |
| --------|-------------------------------------------------|
| V3.08   | Basisversion					            	|
| V3.09   | Fix: LongPressDetectionTime			            |
| V3.10   | Fix: Filterproblem bei 64-bit Windows-Systemen  |

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
