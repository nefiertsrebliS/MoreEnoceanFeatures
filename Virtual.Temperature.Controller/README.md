# Virtual Temperature Controller

Die Instanz emuliert einen Enocean-Temperatur-Regler. Sie kann an einen Enocean- oder Eltako 14-BUS-Heizungsregler angelernt werden und sendet die aktuellen Soll- und Ist-Temperaturen.

## Konfiguration

* Mit der Geräte-ID wird die Sende-ID des Moduls festgelegt. 
* Über die Temperatur-ID kann das Modul mit einer beliebigen Temperatur-Variable aus dem Objektbaum verknüpft werden. Die Ist-Temperatur folgt auf diese Weise der Temperatur der ausgewählten Variable.
* Wenn die Konfiguration abgeschlossen und gespeichert ist, kann der virtuelle Temperatur-Regler über die Lern-Taste an einen lernbereiten Aktor angelernt werden. Dazu vorher den Aktor in den Lernmodus versetzen.

## Mögliche PHP-Befehle

| Nr.	| Befehl  							| Aktion											|
| ------| ----------------------------------|---------------------------------------------------|
| 1.	| MEF_SetActualValue($id, $Value)	| Setzt die Ist-Temperatur auf den Wert *$Value*	|
| 2.	| MEF_SetTargetValue($id, $Value)	| Setzt die Soll-Temperatur auf den Wert *$Value*	|

## Changelog

| Version | Changes								            |
| --------|-------------------------------------------------|
| V3.17   | Basisversion					            	|
