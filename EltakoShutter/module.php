<?php
	class EltakoShutter extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyInteger("DeviceID", 0);
			$this->RegisterPropertyString("ReturnID", "");
			$this->RegisterPropertyFloat("DownTime", 1.0);
			$this->RegisterPropertyFloat("UpTime", 1.0);
			$this->RegisterPropertyFloat("RollFactor", 1.0);
			$this->RegisterPropertyFloat("StepTime", 0.1);
			$this->RegisterPropertyFloat("SlatTurnTime", 0.0);
			$this->RegisterPropertyBoolean("TurnWithoutTravel", false);
			$this->SetBuffer("Calibrate", "false");

			$this->RegisterPropertyString("BaseData", '{
				"DataID":"{70E3075F-A35D-4DEB-AC20-C929A156FE48}",
				"Device":165,
				"Status":0,
				"DeviceID":0,
				"DestinationID":-1,
				"DataLength":4,
				"DataByte12":0,
				"DataByte11":0,
				"DataByte10":0,
				"DataByte9":0,
				"DataByte8":0,
				"DataByte7":0,
				"DataByte6":0,
				"DataByte5":0,
				"DataByte4":0,
				"DataByte3":0,
				"DataByte2":0,
				"DataByte1":0,
				"DataByte0":0
			}');


			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");

#			Fehlende Profile erzeugen
			if (!IPS_VariableProfileExists('ShutterMoveStop.MEF')) {
				IPS_CreateVariableProfile('ShutterMoveStop.MEF', 1);
				IPS_SetVariableProfileIcon('ShutterMoveStop.MEF', 'Shutter');
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', -2, '<<', '',0xFF9900);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', -1, '<', '',0xFF9900);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 0, 'o', '',0xF60909);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 1, '>', '',0xFF9900);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 2, '>>', '',0xFF9900);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 25, '25', '',-1);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 50, '50', '',-1);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 75, '75', '',-1);
				IPS_SetVariableProfileAssociation('ShutterMoveStop.MEF', 98, '98', '',-1);
			}

			if (!IPS_VariableProfileExists('ShutterMoveTime.MEF')) {
				IPS_CreateVariableProfile('ShutterMoveTime.MEF', 2);
				IPS_SetVariableProfileIcon('ShutterMoveTime.MEF', 'Clock');
				IPS_SetVariableProfileDigits('ShutterMoveTime.MEF', 1);
				IPS_SetVariableProfileText('ShutterMoveTime.MEF', '', 's');
			}
		}

		public function Destroy(){
		    //Never delete this line!
		    parent::Destroy();

		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterVariableInteger("action", $this->Translate("Action"), "ShutterMoveStop.MEF");
			$this->RegisterVariableInteger("position", $this->Translate("Position"), "~Shutter");
			$this->RegisterVariableFloat("movetime", $this->Translate("Travel time"), "ShutterMoveTime.MEF");

			$this->EnableAction("action");
			$this->EnableAction("position");

			if($this->ReadPropertyFloat("SlatTurnTime") > 0){
				$this->RegisterVariableInteger("slatangle", $this->Translate("Slat Angle"), "~Intensity.100");
				$this->EnableAction("slatangle");
				$this->UpdateFormField("TurnWithoutTravel", "visible", true);
			}else{
				$this->UnRegisterVariable("slatangle");
				$this->UpdateFormField("TurnWithoutTravel", "visible", false);
			}

#			Filter setzen
			$ID = hexdec($this->ReadPropertyString("ReturnID"));
			if($ID & 0x80000000)$ID -=  0x100000000;
			$this->SendDebug("DeviceID", (int)$ID, 0);
			$this->SetReceiveDataFilter(".*\"DeviceID\":".(int)$ID.",.*");
		}

		public function ReceiveData($JSONString)
		{
			$this->SendDebug("Receive", $JSONString, 0);
			$data = json_decode($JSONString);
#			$this->SendDebug("Kalibrierung", $JSONString, 0);

#			Kalibrieren, wenn eingeleitet
			if($this->GetBuffer("Calibrate")=="true"){
				$this->SetBuffer("Calibrate", "false");

		        switch($data->Device) {
		            case "165":
						$dt = ((int)$data->DataByte2 + (int)$data->DataByte3 * 255)/10;
						switch($data->DataByte1) {
							case 1:
								IPS_SetProperty ($this->InstanceID, "UpTime", $dt);
								IPS_ApplyChanges ($this->InstanceID);
								break;
							case 2:
								IPS_SetProperty ($this->InstanceID, "DownTime", $dt);
								IPS_ApplyChanges ($this->InstanceID);
								break;
							default:
						}
						$this->SetValue("action", 0);
		                break;
		            default:
						$this->LogMessage("Kalibrierung fehlgeschlagen", KL_ERROR);
		        }

			}

            switch($data->Device) {

#				ShutterDevice liefert Richtung und Fahrzeit
                case "165":
					$dt = ((int)$data->DataByte2 + (int)$data->DataByte3 * 255) / 10;
					$DownTime = $this->ReadPropertyFloat("DownTime");
					$UpTime = $this->ReadPropertyFloat("UpTime");
					$RollFactor = $this->ReadPropertyFloat("RollFactor");
					if($this->ReadPropertyFloat("SlatTurnTime") > 0)$oldAngle = $this->GetValue("slatangle");

					switch($data->DataByte1) {
						case 1:
#							Lamellenwinkel bei Jalousien ermitteln
							if($this->ReadPropertyFloat("SlatTurnTime") > 0){
								$newAngle = round($oldAngle - $dt/$this->ReadPropertyFloat("SlatTurnTime") * 100);
								$this->SetValue("slatangle", ($newAngle < 0)?0:$newAngle);

#								Lamellenwinkel und Verfahren getrennt?
								if($this->ReadPropertyBoolean("TurnWithoutTravel")){
									$TurnTime = round(($oldAngle - $this->GetValue("slatangle")) * $this->ReadPropertyFloat("SlatTurnTime") / 100 ,1);
									$dt = ($dt > $TurnTime)? $dt - $TurnTime : 0;
									$DownTime -= $this->ReadPropertyFloat("SlatTurnTime");
									$UpTime -= $this->ReadPropertyFloat("SlatTurnTime");
								}
							}

#							neue Fahrzeit für 0 bis aktuelle Position ermitteln
							$dt = $dt / $UpTime * $DownTime;
							$newValue = $this->GetValue("movetime") - $dt;
							if($newValue < 0)$newValue = 0;
							$this->SetValue("movetime", $newValue);
						    break;
						case 2:
#							Lamellenwinkel bei Jalousien ermitteln
							if($this->ReadPropertyFloat("SlatTurnTime") > 0){
								$newAngle = round($oldAngle + $dt/$this->ReadPropertyFloat("SlatTurnTime") * 100);
								$this->SetValue("slatangle", ($newAngle > 100)?100:$newAngle);

#								Lamellenwinkel und Verfahren getrennt?
								if($this->ReadPropertyBoolean("TurnWithoutTravel")){
									$TurnTime = round(($this->GetValue("slatangle") - $oldAngle) * $this->ReadPropertyFloat("SlatTurnTime") / 100 ,1);
									$dt = ($dt > $TurnTime)? $dt - $TurnTime : 0;
									$DownTime -= $this->ReadPropertyFloat("SlatTurnTime");
									$UpTime -= $this->ReadPropertyFloat("SlatTurnTime");
								}
							}

#							neue Fahrzeit für 0 bis aktuelle Position ermitteln
							$newValue = $this->GetValue("movetime") + $dt;
							if($newValue > $DownTime)$newValue = $DownTime;
							$this->SetValue("movetime", $newValue);
						    break;
						default:
					}

#					Korrekturfaktor berücksichtigt höhere Geschwindigkeit bei aufgewickelter (dicker) Rolle
					$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $newValue;

#					neue Position ermitteln
					$newPosition = round($newValue / $DownTime * 100 * $Factor);
					$this->SetValue("position", $newPosition);

					$this->SetValue("action", 0);
                    break;

#				SwitchDevice liefert Richtung und Endposition
                case "246":
					switch($data->DataByte0) {
						case 1:
							$this->SetValue("action", -1);
						    break;
						case 2:
							$this->SetValue("action", 1);
						    break;
						case 80:
							$this->SetValue("action", 0);
							$this->SetValue("position", 100);
							$DownTime = $this->ReadPropertyFloat("DownTime");
							if($this->ReadPropertyBoolean("TurnWithoutTravel"))$DownTime -= $this->ReadPropertyFloat("SlatTurnTime");
							$this->SetValue("movetime", $DownTime);
							if($this->ReadPropertyFloat("SlatTurnTime") > 0)$this->SetValue("slatangle", 100);
						    break;
						case 112:
							$this->SetValue("action", 0);
							$this->SetValue("position", 0);
							$this->SetValue("movetime", 0);
							if($this->ReadPropertyFloat("SlatTurnTime") > 0)$this->SetValue("slatangle", 0);
						    break;
						default:
					}
                    break;
                default:
            }
		}

        public function RequestAction($Ident, $Value)
		{
            switch($Ident) {
                case "action":
					switch($Value) {
						case -2:
							$this->ShutterMoveUp();
						    break;
						case -1:
							$this->ShutterStepUp();
						    break;
						case 0:
							$this->ShutterStop();
						    break;
						case 1:
							$this->ShutterStepDown();
						    break;
						case 2:
							$this->ShutterMoveDown();
						    break;
						default:
							$this->ShutterMoveTo($Value);
					}
                    break;
                case "position":
					$this->ShutterMoveTo($Value);
                    break;
                case "slatangle":
					$this->SetSlatAngle($Value);
                    break;
                default:
                    throw new Exception("Invalid Ident");
            }
        }

        public function Learn()
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte3 = 255;
			$data->DataByte2 = 248;
			$data->DataByte1 = 13;
			$data->DataByte0 = 128;
			$this->SendData(json_encode($data));
        }

        public function ShutterCalibrate()
		{
			$this->SetBuffer("Calibrate", "true");
			$this->ShutterStop();
        }

        public function ShutterStop()
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte0 = 8;
			$this->SendData(json_encode($data));
        }

        public function ShutterMoveDown()
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte1 = 2;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
        }

        public function ShutterMoveUp()
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte1 = 1;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
        }

        public function ShutterMoveDownEx(float $movetime)
		{
			$movetime *= 10;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte3 = floor($movetime/255);
			$movetime -= $data->DataByte3 * 255;
			$data->DataByte2 = round($movetime);
			$data->DataByte1 = 2;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
			return;
        }

        public function ShutterMoveUpEx(float $movetime)
		{
			$movetime *= 10;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte3 = floor($movetime/255);
			$movetime -= $data->DataByte3 * 255;
			$data->DataByte2 = round($movetime);
			$data->DataByte1 = 1;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
			return;
        }

        public function ShutterStepUp()
		{
			$this->ShutterMoveUpEx($this->ReadPropertyFloat("StepTime"));
        }

        public function ShutterStepDown()
		{
			$this->ShutterMoveDownEx($this->ReadPropertyFloat("StepTime"));
        }

        public function ShutterMoveTo(int $position)
		{
#			Sicherstellen, dass der Aktor aktuell nicht fährt
			if($this->GetValue("action")<>0) $this->ShutterStop();
			for($i=0; $i<200; $i++){
				if($this->GetValue("action")==0){
					$dt = time() - IPS_GetVariable($this->GetIDForIdent("action"))['VariableChanged'];
					if($dt > 4) break;
				}
				IPS_Sleep(100);
			}
			if($this->GetValue("action")<>0){
				$this->LogMessage("Keine Rückmeldung vom Aktor!", KL_ERROR);
                echo"EltakoShutter".chr(10)."Keine Rückmeldung vom Aktor!";
				return;
			}

			if($position == $this->GetValue("position"))return;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");

#			Zeit für Zielposition holen
			$DownTime = $this->ReadPropertyFloat("DownTime");
			$UpTime = $this->ReadPropertyFloat("UpTime");
			$RollFactor = $this->ReadPropertyFloat("RollFactor");

			for($i = 0; $i < $DownTime*100; $i++){
				$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $i/100;
				$newPosition = $i/100 / $DownTime * 100 * $Factor;
				if($newPosition >= $position)break;
			}
			$newTime = $i/100;

#			Zeit für aktuelle Position holen
			$oldTime = $this->GetValue("movetime");

#			Movetime und Direction bestimmen
			$moveTime = $newTime - $oldTime;

			if($moveTime > 0){
				$this->ShutterMoveDownEx(round($moveTime,1));
			}elseif($moveTime < 0){
				$moveTime = abs($moveTime * $UpTime / $DownTime);
				$this->ShutterMoveUpEx(round($moveTime,1));
			}else{
				return;
			}
        }

        public function SetSlatAngle(int $angle)
		{

#			Abbrechen, wenn Wendedauer nicht gesetzt
			if($this->ReadPropertyFloat("SlatTurnTime") == 0)return;
			$Wait = (int)(($this->ReadPropertyFloat("UpTime") + 5)*10);

#			Sicherstellen, dass der Aktor aktuell nicht fährt
#			if($this->GetValue("action")<>0) $this->ShutterStop();
			for($i=0; $i<$Wait; $i++){
				if($this->GetValue("action")==0){
					$dt = time() - IPS_GetVariable($this->GetIDForIdent("action"))['VariableChanged'];
					if($dt > 3) break;
				}
				IPS_Sleep(100);
			}
			if($this->GetValue("action")<>0){
				$this->LogMessage("Keine Rückmeldung vom Aktor!", KL_ERROR);
                echo"EltakoShutter".chr(10)."Keine Rückmeldung vom Aktor!";
				return;
			}

			if($angle == $this->GetValue("slatangle"))return;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$moveTime = round(($angle - $this->GetValue("slatangle")) / 100 * $this->ReadPropertyFloat("SlatTurnTime") ,1);

			if ($moveTime > 0){
			}

			if($moveTime > 0){
				$this->ShutterMoveDownEx($moveTime);
			}elseif($moveTime < 0){
				$this->ShutterMoveUpEx(-$moveTime);
			}else{
				return;
			}
        }

		protected function SendData($data)
		{
			$this->SendDataToParent($data);
			$this->SendDebug("Send", $data, 0);
		}

		protected function SendDebug($Message, $Data, $Format)
		{
			if (is_array($Data))
			{
			    foreach ($Data as $Key => $DebugData)
			    {
						$this->SendDebug($Message . ":" . $Key, $DebugData, 0);
			    }
			}
			else if (is_object($Data))
			{
			    foreach ($Data as $Key => $DebugData)
			    {
						$this->SendDebug($Message . "." . $Key, $DebugData, 0);
			    }
			}
			else
			{
			    parent::SendDebug($Message, $Data, $Format);
			}
		}
	}
