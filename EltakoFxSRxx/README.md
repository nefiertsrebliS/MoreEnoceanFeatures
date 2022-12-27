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
	<h2>Eltako Schaltaktoren FxSRxx Serie (ähnlich EEP A5-38-08)</h2>
	Das Modul wurde für <b>Eltako FSR Schaltaktoren</b> entwickelt (z.B. FSR14-2X/4X, F4SR14-LED, FSR71,..)<br>
	<br>
	Das Modul unterstützt folgende Funktionen:
	<ol>
		<li>Einlernen in den Aktor als GFVS</li>
		<li>Schaltzustand mit absoluter Priorität blockieren, so kann das Schalten mit eingelernten Funktastern unterbunden werden</li>
	</ol>
	<h3>Hardware einlernen</h3>
	Das Einlernen geschieht wie folgt:
	<ol>
		<li>Geräte-ID im neuen Symcon-Modul eintragen</li>
		<li>Gerät in den Einlernmodus versetzen</li>
		<li>Einlerntaste im Symcon-Modul drücken</li>
	</ol>
	<br>
	<h3>Mögliche PHP-Befehle</h3>
	<table>
	  <tr>
		<td>1.&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><b><i>MEF_SwitchNormal($ID, $switch)</i></b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Ein-/Ausschalten (ohne blockieren)</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>MEF_SwitchBlocking($ID, $switch)</i></b></td>
		<td>Ein-/Ausschalten mit blockieren von Tastern</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>MEF_TeachIn($ID)</i></b></td>
		<td>Lerntelegram (GFVS) senden</td>
	  </tr>
	</table>
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V3.16</td>
		<td>Grundversion</td>
	  </tr>
	</table>
  </body>
</html>

