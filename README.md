<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>Enocean Module mit erweitertem Funktionsumfang</h1>
	<h2>Grundsätzliches</h2>
	Die Module haben denselben Funktionsumfang, wie die Grundmodule von Symcon.
	Der Befehlsaufruf ändert sich allerdings. Der Prefix <b><i>ENO</i></b> wird durch den Prefix <b><i>MEF</i></b> ersetzt. Der Rest des Aufrufs bleibt inklusiver der zu übergebenden Parameter identisch.
	<h2>EltakoShutter</h2>
	Das Modul wurde für den <b>FSB14</b> entwickelt, sollte aber auch für den <b>FSB61</b> und den <b>FSB71</b> einsetzbar sein.<br>
	Das Modul kann zum exakten Anfahren einer bestimmten Rollladenposition konfiguriert werden.
	<h2>EEP D2-05-00</h2>
	Das Modul wurde für den <b>NodOn SIN-2-RS-01 Shutter</b> entwickelt, ist aber auch mit allen weiteren <b>EEP D2-05-00 kompartiblen Shutter-Modulen</b> einsetzbar.<br>
	Das Modul unterstützt folgende Funktionen:
	<ol>
		<li>Rolladenposition prozentgenau anfahren</li>
		<li>Kalibrier- und Alarmwerte setzen</li>
	</ol>
	Die Winkelverstellung von Jalousien wird noch nicht unterstützt.
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V1.00 &nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Grundversion für Eltako Shutter</td>
	  </tr>
	  <tr>
		<td>V1.01</td>
		<td>Variablen werden aktualisiert</td>
	  </tr>
	  <tr>
		<td>V1.02</td>
		<td>ShutterMoveTo eingefügt</td>
	  </tr>
	  <tr>
		<td>V1.03</td>
		<td>Fix: ShutterMoveTo Fahrzeit up</td>
	  </tr>
	  <tr>
		<td>V1.04</td>
		<td>Umstellung von EAO auf MEF</td>
	  </tr>
	  <tr>
		<td></td>
		<td>Neu: Kalibrierfunktion</td>
	  </tr>
	  <tr>
		<td>V1.05</td>
		<td>Neu: Bedienung auf %-Werte erweitert, Variable motion entfallen</td>
	  </tr>
	  <tr>
		<td>V1.06</td>
		<td>Fix: ShutterMoveTo Sicherstellen, dass Rollladen vorher gestoppt wurde</td>
	  </tr>
	  <tr>
		<td></td>
		<td>Neu: ShutterStepUp/Down</td>
	  </tr>
	  <tr>
		<td></td>
		<td>Neu: Einlernen</td>
	  </tr>
	  <tr>
		<td></td>
		<td>Neu: Slider-Steuerung</td>
	  </tr>
	  <tr>
		<td>V1.07</td>
		<td>Fix: ShutterMoveTo Berechnungsfehler beim Öffnen</td>
	  </tr>
	  <tr>
		<td>V1.08</td>
		<td>Neu: Vereinfachte Kalibrierung</td>
	  </tr>
	  <tr>
		<td></td>
		<td>Fix: Bei der Modulauswahl heißt der Hersteller jetzt "More Enocean Features"</td>
	  </tr>
	  <tr>
		<td>V1.09</td>
		<td>Fix: undefined variable DownTime</td>
	  </tr>
	  <tr>
		<td>V1.10</td>
		<td>Fix: ReceiveDataFilter lässt ungewünschte Daten durch</td>
	  </tr>
	  <tr>
		<td>V2.00</td>
		<td>Neu: EEP D2-05-00 für z.B. Nodon Shutter-Modul</td>
	  </tr>
	  <tr>
		<td>V2.01</td>
		<td>Fix: Kalibrierfehler Eltako Shutter<br>
			Fix: Sporadischer Fehler beim Anfahren von Positionen (Eltako Shutter)</td>
	  </tr>
	  <tr>
		<td>V3.00</td>
		<td>Neu: EEP A5-38-08 Erweiterung für Rollläden<br>
			Fix: Umstellung SetValue</td>
	  </tr>
	  <tr>
		<td>V3.01</td>
		<td>Fix: Filterproblem bei 64-bit Windows-Systemen</td>
	  </tr>
	</table>
  </body>
</html>

