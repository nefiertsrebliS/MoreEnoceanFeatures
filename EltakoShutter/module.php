<?php
	class EltakoShutter extends IPSModule
	{
		#================================================================================================
		public function Create()
		#================================================================================================
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

			#	ListenTimer
			$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
			$this->SetBuffer('Listen', 0);

			#	UpdateTimer
			$this->RegisterTimer('UpdateTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Update", "");');

			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");

			#	Fehlende Profile erzeugen
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

		#================================================================================================
		public function Destroy()
		#================================================================================================
		{
		    //Never delete this line!
		    parent::Destroy();

		}

		#================================================================================================
		public function ApplyChanges()
		#================================================================================================
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

			#	Position merken
			$this->SetBuffer('Position', $this->GetValue('position'));

			#	Filter setzen
			$this->SetFilter();
		}

		#================================================================================================
		public function ReceiveData($JSONString)
		#================================================================================================
		{
			$this->SendDebug("Receive", $JSONString, 0);
			$data = json_decode($JSONString);
			$this->SetTimerInterval('UpdateTimer', 0);

			if($this->GetReturnID($data, array(165, 246)))return;

			#	Kalibrieren, wenn eingeleitet
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

				#	ShutterDevice liefert Richtung und Fahrzeit
                case "165":
					$dir = ($data->DataByte1 == 1)? -1:1;	# -1: öffnen, 1: schließen
					$dt = $dir * ((int)$data->DataByte2 + (int)$data->DataByte3 * 255) / 10;

					$DownTime = $this->ReadPropertyFloat("DownTime");
					$UpTime = $this->ReadPropertyFloat("UpTime");
					$RollFactor = $this->ReadPropertyFloat("RollFactor");

					# Lamellenwinkel und Position ermitteln
					if($this->ReadPropertyFloat("SlatTurnTime") > 0){
						if($dir >0){
							$dtSlat = (100 - $this->GetValue("slatangle")) / 100 * $this->ReadPropertyFloat("SlatTurnTime");
						}else{
							$dtSlat = -$this->GetValue("slatangle") / 100 * $this->ReadPropertyFloat("SlatTurnTime");
						}

						#	Lamellenwinkel und Position?
						if (abs($dt) > abs($dtSlat)){
							$this->SetValue("slatangle", ($dir > 0)?100:0);
							#	Lamellenwinkel und Verfahren getrennt?
							if($this->ReadPropertyBoolean("TurnWithoutTravel")) $dt -= $dtSlat;
						}else{ 
							$newAngle = round($this->GetValue("slatangle") + $dt/$this->ReadPropertyFloat("SlatTurnTime") * 100);
							$newAngle = ($newAngle > 100)?100:$newAngle;
							$newAngle = ($newAngle < 0)?0:$newAngle;
							$this->SetValue("slatangle", $newAngle); 
							#	Lamellenwinkel und Verfahren getrennt?
							if($this->ReadPropertyBoolean("TurnWithoutTravel")) $dt = 0;
						}  
					}

					#	neue Position ermitteln
					$newValue = $this->GetValue("movetime") + $dt;
					if($newValue < 0)$newValue = 0;
					if($newValue > $DownTime)$newValue = $DownTime;
					$this->SetValue("movetime", $newValue);

					#	Korrekturfaktor berücksichtigt höhere Geschwindigkeit bei aufgewickelter (dicker) Rolle
					$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $newValue;

					#	neue Position ermitteln
					$newPosition = round($newValue / $DownTime * 100 * $Factor);
					$this->SetValue("position", $newPosition); 
					$this->SetBuffer('Position', $newPosition);

					$this->SetValue("action", 0);
                    break;
					
				#	SwitchDevice liefert Richtung und Endposition
                case "246":
					switch($data->DataByte0) {
						case 1:
							$this->SetValue("action", -1);
							$this->SetTimerInterval('UpdateTimer', 1000);
						    break;
						case 2:
							$this->SetValue("action", 1);
							$this->SetTimerInterval('UpdateTimer', 1000);
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

		#================================================================================================
        public function RequestAction($Ident, $Value)
		#================================================================================================
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
				case "FreeDeviceID":
					$this->UpdateFormField('DeviceID', 'value', $this->FreeDeviceID());
					break;
				case "Listen":
					$this->Listen($Value);
					break;
				case "Update":
					$this->Update($Value);
					break;
				case "SetReturnID":
					$this->UpdateFormField('ReturnID', 'value', $Value);
					break;
                default:
                    throw new Exception("Invalid Ident");
            }
        }

		#================================================================================================
        public function Learn()
		#================================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte3 = 255;
			$data->DataByte2 = 248;
			$data->DataByte1 = 13;
			$data->DataByte0 = 128;
			$this->SendData(json_encode($data));
        }

		#================================================================================================
        public function ShutterCalibrate()
		#================================================================================================
		{
			$this->SetBuffer("Calibrate", "true");
			$this->ShutterStop();
        }

		#================================================================================================
        public function ShutterStop()
		#================================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte0 = 8;
			$this->SendData(json_encode($data));
        }

		#================================================================================================
        public function ShutterMoveDown()
		#================================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte1 = 2;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
        }

		#================================================================================================
        public function ShutterMoveUp()
		#================================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte1 = 1;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
        }

		#================================================================================================
        public function ShutterMoveDownEx(float $movetime)
		#================================================================================================
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

		#================================================================================================
        public function ShutterMoveUpEx(float $movetime)
		#================================================================================================
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

		#================================================================================================
        public function ShutterStepUp()
		#================================================================================================
		{
			$this->ShutterMoveUpEx($this->ReadPropertyFloat("StepTime"));
        }

		#================================================================================================
        public function ShutterStepDown()
		#================================================================================================
		{
			$this->ShutterMoveDownEx($this->ReadPropertyFloat("StepTime"));
        }

		#================================================================================================
        public function ShutterMoveTo(int $position)
		#================================================================================================
		{
			#	Sicherstellen, dass der Aktor aktuell nicht fährt
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

			#	Zeit für Zielposition holen
			$DownTime = $this->ReadPropertyFloat("DownTime");
			$UpTime = $this->ReadPropertyFloat("UpTime");
			$RollFactor = $this->ReadPropertyFloat("RollFactor");

			for($i = 0; $i < $DownTime*100; $i++){
				$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $i/100;
				$newPosition = $i/100 / $DownTime * 100 * $Factor;
				if($newPosition >= $position)break;
			}
			$newTime = $i/100;

			#	Zeit für aktuelle Position holen
			$oldTime = $this->GetValue("movetime");

			#	Movetime und Direction bestimmen
			$moveTime = $newTime - $oldTime;

			# Korrektur bei SlatTurnTime und TurnWithoutTravel
			if($this->ReadPropertyFloat("SlatTurnTime") > 0 && $this->ReadPropertyBoolean("TurnWithoutTravel")){
				if($moveTime > 0){
					$moveTime += (100 - $this->GetValue("slatangle")) / 100 * $this->ReadPropertyFloat("SlatTurnTime");
				}else{
					$moveTime -= $this->GetValue("slatangle") / 100 * $this->ReadPropertyFloat("SlatTurnTime");
				}
			}

			# Ausführen
			if($moveTime > 0){
				$this->ShutterMoveDownEx(round($moveTime,1));
			}elseif($moveTime < 0){
				$moveTime = abs($moveTime * $UpTime / $DownTime);
				$this->ShutterMoveUpEx(round($moveTime,1));
			}else{
				return;
			}
        }

		#================================================================================================
        public function SetSlatAngle(int $angle)
		#================================================================================================
		{

			#	Abbrechen, wenn Wendedauer nicht gesetzt
			if($this->ReadPropertyFloat("SlatTurnTime") == 0)return;
			$Wait = (int)(($this->ReadPropertyFloat("UpTime") + 5)*10);

			#	Sicherstellen, dass der Aktor aktuell nicht fährt
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

			if($moveTime > 0){
				$this->ShutterMoveDownEx($moveTime);
			}elseif($moveTime < 0){
				$this->ShutterMoveUpEx(-$moveTime);
			}else{
				return;
			}
        }

		#================================================================================================
		protected function SendData($data)
		#================================================================================================
		{
			$this->SendDataToParent($data);
			$this->SendDebug("Send", $data, 0);
		}

		#================================================================================================
		protected function SendDebug($Message, $Data, $Format)
		#================================================================================================
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

		#================================================================================================
		protected function FreeDeviceID()
		#================================================================================================
		{
			$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
			if($Gateway == 0) return;
			$Devices = IPS_GetInstanceListByModuleType(3);             # alle Geräte
			$DeviceArray = array();
			foreach ($Devices as $Device){
				if(IPS_GetInstance($Device)["ConnectionID"] == $Gateway){
					$config = json_decode(IPS_GetConfiguration($Device));
					if(!property_exists($config, 'DeviceID'))continue;
					if(is_integer($config->DeviceID)) $DeviceArray[] = $config->DeviceID;
				}
			}
		
			for($ID = 1; $ID<=256; $ID++)if(!in_array($ID,$DeviceArray))break;
			return $ID == 256?0:$ID;
		}
		
		#=====================================================================================
		private function Listen($value) 
		#=====================================================================================
		{
			$this->SetReceiveDataFilter('');
			if($value > 0){
				$this->SetBuffer('DeviceIDs','[]');
				$this->UpdateFormField('FoundIDs', 'values', json_encode(array()));
			}
			$this->SetTimerInterval('ListenTimer', 1000);
			$remain = intval($this->GetBuffer('Listen')) + $value;
			if($remain == 0)$this->SetFilter();
			if($remain > 60) $remain = 60;
			$this->UpdateFormField('Remaining', 'current', $remain);
			$this->UpdateFormField('Remaining', 'caption', "$remain / 60s");
			$this->SetBuffer('Listen', $remain);
		}
		
		#=====================================================================================
		private function Update($value) 
		#=====================================================================================
		{
			$Position = $this->GetBuffer("Position");
			$Action = $this->GetValue("action");
			$DownTime = $this->ReadPropertyFloat("DownTime");
			$dt = $this->GetTimerInterval('UpdateTimer') / 1000;
			if($Action < 0){
				$newPosition = $Position - 100 / $DownTime * $dt;
				if($newPosition < 0)$newPosition = 0;
				$this->SetBuffer('Position', $newPosition);
				$this->SetValue("position", round($newPosition)); 
			}elseif($Action > 0){
				$newPosition = $Position + 100 / $DownTime * $dt;
				if($newPosition > 100)$newPosition = 100;
				$this->SetBuffer('Position', $newPosition);
				$this->SetValue("position", round($newPosition)); 
			}
		}
		
		#=====================================================================================
		private function GetReturnID($data, $DataValues) 
		#=====================================================================================
		{
			if($this->GetTimerInterval('ListenTimer') == 0) return false;

			$values = json_decode($this->GetBuffer('DeviceIDs'));
			$Devices = $this->GetDeviceArray();
			if(in_array($data->Device, $DataValues)){
				$ID = $data->DeviceID;
				if($ID <= 0)return true;
				$DeviceID = sprintf('%08X',$ID);
				if(strpos($this->GetBuffer('DeviceIDs'), $DeviceID) === false){
					$values[] = array(
						"ReturnID" => $DeviceID, 
						"InstanceID" => isset($Devices[$DeviceID])?$Devices[$DeviceID]:0 ,
						"rowColor"=>isset($Devices[$DeviceID])?"#C0FFC0":-1
					);
					$this->UpdateFormField('FoundIDs', 'values', json_encode($values));
					$this->SetBuffer('DeviceIDs', json_encode($values));
				}
			}
			return true;
		}

		#=====================================================================================
		private function GetDeviceArray()
		#=====================================================================================
		{
			$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
			if($Gateway == 0) return;
			$Devices = IPS_GetInstanceListByModuleType(3);             # alle Geräte
			$DeviceArray = array();
			foreach ($Devices as $Device){
				if(IPS_GetInstance($Device)["ConnectionID"] == $Gateway){
					$config = json_decode(IPS_GetConfiguration($Device));
					if(!property_exists($config, 'ReturnID'))continue;
					$DeviceArray[strtoupper(trim($config->ReturnID))] = $Device;
				}
			}
			return $DeviceArray;
		}

		#=====================================================================================
		private function SetFilter() 
		#=====================================================================================
		{
			#	ListenTimer ausschalten
			$this->SetTimerInterval('ListenTimer', 0);

			#	Filter setzen
			$ID = hexdec($this->ReadPropertyString("ReturnID"));
			if(IPS_GetKernelVersion() < 6.3){
				if($ID & 0x80000000)$ID -=  0x100000000;
			}
			$filter = sprintf('.*\"DeviceID\":%s,.*', $ID);
			$this->SendDebug('Filter', $filter, 0);
			$this->SetReceiveDataFilter($filter);
		}
	}
