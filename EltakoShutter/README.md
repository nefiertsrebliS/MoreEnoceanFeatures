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
	<h3>Kalibrierung</h3>
	Das Kalibriermenü wird in der Modulansicht mit <b><i>KALIBRIEREN</i></b> aufgerufen.<br><br>
	<b><i>Fahrzeit Schließen (0-100%)</i></b>
	<ol>
		<li>Rolladen <b>vollständig</b> öffnen</li>
		<li>Kalibrierung starten</li>
		<li>Sobald der Rollladen vollständig geschlossen ist, <b><i>KALIBRIEREN</i></b> drücken. Je exakter Sie den Zeitpunkt treffen, um so exakter ist die Kalibrierung</li>
	</ol>
	Durch den Vorgang wird der Wert von <b><i>Fahrzeit Schließen (0-100%)</i></b> überschrieben. Zu Feinkalibrierung können Sie den Wert in der Modulansicht händisch anpassen.<br><br>
	<b><i>Fahrzeit Öffnen (100-0%)</i></b>
	<ol>
		<li>Rolladen <b>vollständig</b> schließen</li>
		<li>Kalibrierung starten</li>
		<li>Sobald der Rollladen vollständig geöffnet ist, <b><i>KALIBRIEREN</i></b> drücken. Je exakter Sie den Zeitpunkt treffen, um so exakter ist die Kalibrierung</li>
	</ol>
	Durch den Vorgang wird der Wert von <b><i>Fahrzeit Öffnen (100-0%)</i></b> überschrieben. Zu Feinkalibrierung können Sie den Wert in der Modulansicht händisch anpassen.<br><br>
	<b><i>Wickelfaktor</i></b><br><br>
	Der Wickelfaktor berücksichtigt die Tatsache, dass die Rolle des Rollladens beim Aufrollen größer wird. Dadurch steigt die Geschwindigkeit des Öffnungsvorgangs.
	Je größer der Wickelfaktor desto größer der Effekt.<br>
	Bei Raffstoren gibt es diesen Effekt nicht. Hier ist beim  <i>Wickelfaktor</i> der Wert 1 einzutragen.<br>
	<ol>
		<li>Rolladen komplett öffnen</li>
		<li><b><i>50% SCHLIESSEN</i></b> drücken. Der Rollladen sollte sich jetzt zu 50% schließen</li>
		<li>Ist der Rollladen zu weit geschlossen, den 2.Schritt mit einem größeren Wickelfaktor wiederholen</li>
		<li>Ist der Rollladen zu weit geöffnet, den 2.Schritt mit einem kleineren Wickelfaktor wiederholen</li>
		<li>Passt die Position, sollten zur Kontrolle die Schritte 1 und 2 wiederholt werden</li>
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
	  <tr>
		<td>4.&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><b><i>MEF_ShutterMoveUpEx/MEF_ShutterMoveDownEx($ID, $seconds)</i></b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>x Sekunden öffnen/schließen</td>
	  </tr>
	  <tr>
		<td>5.&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><b><i>MEF_SetSlatAngle($ID, $angle)</i></b>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td>Anfahren des gewählten Lamellenwinkels</td>
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
		<td>V2.01</td>
		<td>Fix: Kalibrierfehler<br>
			Fix: Sporadischer Fehler beim Anfahren von Positionen</td>
	  </tr>
	  <tr>
		<td>V3.01</td>
		<td>Fix: Filterfehler bei 64-bit-Systemen</td>
	  </tr>
	  <tr>
		<td>V3.02</td>
		<td>Neu: Anfahren von Lamellenwinkeln</td>
	  </tr>
      <tr>
		<td>V3.03</td>
		<td>Neu: Option Lamellenverstellung ohne Verfahrweg</td>
	  </tr>
      <tr>
		<td>V3.04</td>
		<td>Fix: Ident slatangle wurde nicht gefunden</td>
	  </tr>
		<td>V3.12</td>
		<td>Fix: newValue-Error</td>
	  </tr>
	  <tr>
		<td>V3.14</td>
		<td>Neu: Shutter aktualisiert die Position über Laufzeit<br>
			Fix: Anpassung ID-Berechnung auf IPS 6.3</td>
	  </tr>
	</table>
  </body>
</html>
