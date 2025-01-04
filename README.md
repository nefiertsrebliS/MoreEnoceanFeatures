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
	Der Befehlsaufruf ändert sich allerdings. Der Prefix <b><i>ENO</i></b> wird durch den Prefix <b><i>MEF</i></b> ersetzt. Der Rest des Aufrufs bleibt inklusiver der zu übergebenden Parameter identisch.<br>
	Die Beschreibung der einzelnen Module sind in der README.md der Module zu finden.
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
	  <tr>
		<td>V3.02</td>
		<td>Neu: Eltako Shutter - Anfahren von Lamellenwinkeln</td>
	  </tr>
	  <tr>
		<td>V3.03</td>
		<td>Neu: Eltako Shutter - Lamellenverstellung ohne Verfahrweg</td>
	  </tr>
      <tr>
		<td>V3.04</td>
		<td>Fix: Ident slatangle wurde nicht gefunden</td>
	  </tr>
      <tr>
		<td>V3.05</td>
		<td>Neu: Eltako FFKB und FTKB-rw</td>
	  </tr>
      <tr>
		<td>V3.06</td>
		<td>Neu: Enocean Button Emulator</td>
	  </tr>
      <tr>
		<td>V3.07</td>
		<td>Neu: Eltako BUS-Taster</td>
	  </tr>
      <tr>
		<td>V3.08</td>
		<td>Neu: Eltako FTS14EM</td>
	  </tr>
      <tr>
		<td>V3.09</td>
		<td>Fix: LongPressDetectionTime</td>
	  </tr>
	  <tr>
		<td>V3.10</td>
		<td>Fix: Filterproblem bei 64-bit Windows-Systemen<br>
			Neu: Button-Emulator funktioniert unabhängig von der Enocean-Hardware</td>
	  </tr>
	  <tr>
		<td>V3.11</td>
		<td>Neu: Suche nach der nächsten freien Sende-ID<br>
			Neu: Suche nach der Geräte/Melde-ID</td>
	  </tr>
	  <tr>
		<td>V3.12</td>
		<td>Neu: Button-Emulator - Tastenseite auswählbar<br>
			Fix: Eltako-Shutter - newValue-Error</td>
	  </tr>
	  <tr>
		<td>V3.13</td>
		<td>Fix: Fehler in ID-Berechnung</td>
	  </tr>
	  <tr>
		<td>V3.14</td>
		<td>Neu: Shutter aktualisiert die Position über Laufzeit<br>
			Fix: Anpassung ID-Berechnung auf IPS 6.3</td>
	  </tr>
	  <tr>
		<td>V3.15</td>
		<td>Fix: FTS14EM Button 10 ohne Funktion</td>
	  </tr>
	  <tr>
		<td>V3.16</td>
		<td>Neu: Eltako FxSRxx von rolf1</td>
	  </tr>
	  <tr>
		<td>V3.17</td>
		<td>Neu: Virtueller Temperatur-Regler</td>
	  </tr>
	  <tr>
		<td>V3.18</td>
		<td>Fix: Shutter stoppt nicht am Fahrzeitende</td>
	  </tr>
	  <tr>
		<td>V3.19</td>
		<td>Fix: MEF_SetRunTime: Parameter count does not match<br>
			Fix: Undefined array key in Module EltakoFTS61BTK</td>
	  </tr>
	  <tr>
		<td>V3.20</td>
		<td>Neu: FTS14EM für Bewegungsmelder und Tür-Fenster-Kontakte</td>
	  </tr>
	</table>
  </body>
</html>
