<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  
	<title>MoreEnoceanFeatures/README.md at master · silberstreifen/MoreEnoceanFeatures</title>
    <meta name="description" content="Symcon MoreEnoceanFeatures Modules.">

  </head>

  <body>
	<h1>Enocean Module mit erweitertem Funktionsumfang</h1>
	<h2>Grundsätzliches</h2>
	Die Module haben denselben Funktionsumfang, wie die Grundmodule von Symcon.
	Der Befehlsaufruf ändert sich allerdings. Der Prefix <b><i>ENO</i></b> wird durch den Prefix <b><i>MEF</i></b> ersetzt. Der Rest des Aufrufs bleibt inklusiver der zu übergebenden Parameter identisch.
	<h2>EltakoShutter</h2>
	Das Modul wurde für den <b>FSB14</b> entwickelt, sollte aber auch für den <b>FSB61</b> und den <b>FSB71</b> einsetzbar sein.<br>
	Das Modul kann zum exakten Anfahren einer bestimmten Rollladenposition konfiguriert werden.
	<h3>Kalibrierung</h3>
	<b><i>Fahrzeit Schließen (0-100%)</i></b>
	<ol>
		<li>Rolladen komplett öffnen</li>
		<li>Bei <b><i>Fahrzeit Schließen (0-100%)</i></b> die ungefähre Schließzeit (Startwert am besten zu klein) eingeben</li>
		<li><b><i>98% SCHLIESSEN</i></b> drücken. Der Rollladen sollte sich jetzt nahezu vollständig schließen</li>
		<li>Ist der Rollladen noch zu weit geöffnet, die Schritte 2 und 3 mit einer erhöhten Fahrzeit wiederholen</li>
		<li>Ist der Rollladen zu weit geschlossen, die Schritte 1 bis 3 mit einer verringerten Fahrzeit wiederholen</li>
	</ol><br> 	
	<b><i>Fahrzeit Öffnen (100-0%)</i></b>
	<ol>
		<li>Rolladen komplett schließen</li>
		<li>Bei <b><i>Fahrzeit Öffnen (100-0%)</i></b> die ungefähre Fahrzeit eingeben. Ein guter Startwert ist die <i>Fahrzeit Schließen (0-100%)</i>.</li>
		<li><b><i>2% SCHLIESSEN</i></b> drücken. Der Rollladen sollte sich jetzt nahezu vollständig öffnen</li>
		<li>Ist der Rollladen noch zu weit geschlossen, die Schritte 2 und 3 mit einer erhöhten Fahrzeit wiederholen</li>
		<li>Ist der Rollladen zu weit geöffnet, die Schritte 1 bis 3 mit einer verringerten Fahrzeit wiederholen</li>
	</ol><br> 	
	<b><i>Wickelfaktor</i></b><br><br>
	Der Wickelfaktor berücksichtigt die Tatsache, dass die Rolle des Rollladens beim Aufrollen größer wird. Dadurch steigt die Geschwindigkeit des Öffnungsvorgangs. 
	Je größer der Wickelfaktor desto größer der Effekt.<br>
	Bei Raffstoren gibt es diesen Effekt nicht. Hier ist beim  <i>Wickelfaktor</i> der Wert 1 einzutragen.<br>
	<ol>
		<li>Rolladen komplett öffnen</li>
		<li><b><i>50% SCHLIESSEN</i></b> drücken. Der Rollladen sollte sich jetzt zu 50% schließen</li>
		<li>Ist der Rollladen zu weit geschlossen, die Schritte 1 und 2 mit einem größeren Wickelfaktor wiederholen</li>
		<li>Ist der Rollladen zu weit geöffnet, die Schritte 1 und 2 mit einem kleineren Wickelfaktor wiederholen</li>
	</ol><br> 	
	<h3>Zusätzliche PHP-Befehle</h3>
	<table>
	  <tr>
		<td>1.&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><b><i>MEF_ShutterMoveTo($ID, $Position)</i></b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Anfahren der gewählten Position</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>MEF_ShutterStepUp($ID)</i></b></td>
		<td>Einen Schritt öffnen (siehe Einstellung <i>Schrittdauer in sec:</i>)</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>MEF_ShutterStepDown($ID)</i></b></td>
		<td>Einen Schritt schließen (siehe Einstellung <i>Schrittdauer in sec:</i>)</td>
	  </tr>
	</table>
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V1.00 &nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Grundversion</td>
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
	</table>
  </body>
</html>

