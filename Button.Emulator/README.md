<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width">
  </head>

  <body>
	<h1>Button-Emulator</h1>
	Die Instanz emuliert einen Enocean-Taster. Der Taster kann in alle durch Taster steuerbaren Enocean-Geräte eingelernt werden. Der Tastendruck kann mittels der unten aufgeführten Befehle ausgelöst werden. 
	<b>Voraussetzung: Eltako-BUS verbunden über ein FGW14 mit IP-Symcon.</b>
	Es werden ESP2-kompatible Befehle gesendet.
	<h2>Mögliche PHP-Befehle</h3>
	<table>
	  <tr>
		<td>1.</td>
		<td><b><i>MEF_PressUp($ID)</i></b></td>
		<td>Taste oben betätigt</td>
	  </tr>
	  <tr>
		<td>2.</td>
		<td><b><i>MEF_ShortPressUp($ID)</i></b></td>
		<td>Taste oben für 150ms betätigt und wieder losgelassen</td>
	  </tr>
	  <tr>
		<td>3.</td>
		<td><b><i>MEF_PressDown($ID)</i></b></td>
		<td>Taste unten betätigt</td>
	  </tr>
	  <tr>
		<td>4.</td>
		<td><b><i>MEF_ShortPressDown($ID)</i></b></td>
		<td>Taste unten für 150ms betätigt und wieder losgelassen</td>
	  </tr>
	  <tr>
		<td>5.</td>
		<td><b><i>MEF_Release($ID)</i></b></td>
		<td>Taste losgelassen</td>
	  </tr>
	</table>
	<h2>Changelog</h2>
	<table>
	  <tr>
		<td>V3.05</td>
		<td>Grundversion</td>
	  </tr>
	</table>
  </body>
</html>

