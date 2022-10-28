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
	<h2>EEP D2-05-00</h2>
	Das Modul wurde für den <b>NodOn SIN-2-RS-01 Shutter</b> entwickelt, ist aber auch mit allen weiteren <b>EEP D2-05-00 kompartiblen Shutter-Modulen</b> einsetzbar.<br>
	Das Modul unterstützt folgende Funktionen:
	<ol>
		<li>Rolladenposition prozentgenau anfahren</li>
		<li>Kalibrier- und Alarmwerte setzen</li>
	</ol>
	Die Winkelverstellung von Jalousien wird noch nicht unterstützt.
	<h3>Hardware einlernen</h3>
	Das Einlernen geschieht wie folgt:
	<ol>
		<li>Geräte-ID im neuen Symcon-Modul eintragen</li>
		<li>Gerät in den Einlernmodus versetzen</li>
		<li>Einlerntaste im Symcon-Modul drücken</li>
	</ol>
	<h3>Kalibrierung</h3>
	Die Kalibrierung erfolgt ausschließlich am Gerät.<br>
	<h3>Mögliche PHP-Befehle</h3>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>MEF_ShutterMoveUp($ID)</i></b></td>
		<td>Rollladen Öffnen</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>MEF_ShutterMoveDown($ID)</i></b></td>
		<td>Rollladen schließen</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>MEF_ShutterStop($ID)</i></b></td>
		<td>Rollladen stoppen</td>
	  </tr>
	  <tr>
		<td>4.&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><b><i>MEF_ShutterMoveTo($ID, $Position)</i></b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Anfahren der gewählten Position</td>
	  </tr>
	  <tr>
		<td>5.</td>
		<td><b><i>MEF_UpdatePosition($ID)</i></b></td>
		<td>Position des Rollladens in Symcon aktualisieren</td>
	  </tr>
	  <tr>
		<td>6.</td>
		<td><b><i>MEF_SetAlarmAction($ID, $Action)</i></b></td>
		<td>Verhalten des Gerätes bei Alarmtelegramm einstellen</td>
	  </tr>
	  <tr>
		<td>7.</td>
		<td><b><i>MEF_SetRunTime($ID, $milliseconds)</i></b></td>
		<td>Kalibrierwert für die Rollladen-Fahrzeit an das Gerät senden</td>
	  </tr>
	</table>
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V2.00</td>
		<td>Grundversion</td>
	  </tr>
	  <tr>
		<td>V3.01</td>
		<td>Fix: Filterfehler bei 64-bit-Systemen</td>
	  </tr>
	  <tr>
		<td>V3.14</td>
		<td>Fix: Anpassung ID-Berechnung auf IPS 6.3</td>
	  </tr>
	</table>
  </body>
</html>

