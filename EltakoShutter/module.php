<?
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
			
			$this->RegisterVariableInteger("action", "Aktion", "ShutterMoveStop.MEF");
			$this->RegisterVariableInteger("position", "Position", "~Shutter");
			$this->RegisterVariableFloat("movetime", "Fahrzeit", "ShutterMoveTime.MEF");
			
			$this->EnableAction("action");	
			$this->EnableAction("position");	

#			Falsche Werte abfangen
			if($this->ReadPropertyFloat("DownTime")<1){
				IPS_SetProperty ($this->InstanceID, "DownTime", 1);
				IPS_ApplyChanges ($this->InstanceID);
			}
			if($this->ReadPropertyFloat("UpTime")<1){
				IPS_SetProperty ($this->InstanceID, "UpTime", 1);
				IPS_ApplyChanges ($this->InstanceID);
			}
			if($this->ReadPropertyFloat("RollFactor")<1){
				IPS_SetProperty ($this->InstanceID, "RollFactor", 1);
				IPS_ApplyChanges ($this->InstanceID);
			}
			if($this->ReadPropertyFloat("StepTime")<0.1){
				IPS_SetProperty ($this->InstanceID, "StepTime", 0.1);
				IPS_ApplyChanges ($this->InstanceID);
			}
			
#			Filter setzen
			$this->SetReceiveDataFilter(".*\"DeviceID\":".(int)hexdec($this->ReadPropertyString("ReturnID")).".*");

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
					$dt = (int)$data->DataByte2 + (int)$data->DataByte3 * 255;
					$DownTime = $this->ReadPropertyFloat("DownTime");
					$UpTime = $this->ReadPropertyFloat("UpTime");
					$RollFactor = $this->ReadPropertyFloat("RollFactor");

					switch($data->DataByte1) {
						case 1:
#							neue Fahrzeit für 0 bis aktuelle Position ermitteln
							$dt = $dt / 10 / $UpTime * $DownTime;
							$newValue = $this->GetValue("movetime") - $dt;
							if($newValue < 0)$newValue = 0;
							$this->SetValue("movetime", $newValue);

#							Korrekturfaktor berücksichtigt höhere Geschwindigkeit bei aufgewickelter (dicker) Rolle
							$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $newValue;

#							neue Position ermitteln
							$newPosition = round($newValue / $DownTime * 100 * $Factor);
							$this->SetValue("position", $newPosition);
						    break;
						case 2:
#							neue Fahrzeit für 0 bis aktuelle Position ermitteln
							$dt = $dt / 10;
							$newValue = $this->GetValue("movetime") + $dt;
							if($newValue > $DownTime)$newValue = $DownTime;
							$this->SetValue("movetime", $newValue);

#							Korrekturfaktor berücksichtigt höhere Geschwindigkeit bei aufgewickelter (dicker) Rolle
							$Factor = $RollFactor - ($RollFactor - 1) / $DownTime * $newValue;

#							neue Position ermitteln
							$newPosition = round($newValue / $DownTime * 100 * $Factor);
							$this->SetValue("position", $newPosition);
						    break;
						default:
					}
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
							$this->SetValue("movetime", $this->ReadPropertyFloat("DownTime"));
						    break;
						case 112:
							$this->SetValue("action", 0);
							$this->SetValue("position", 0);
							$this->SetValue("movetime", 0);
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
								IPS_SetProperty ($this->InstanceID, "DownTime", 22.1);
								IPS_ApplyChanges ($this->InstanceID);
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
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte2 = round($movetime*10);
			$data->DataByte1 = 2;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
        }
		
        public function ShutterMoveUpEx(float $movetime) 
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte2 = round($movetime*10);
			$data->DataByte1 = 1;
			$data->DataByte0 = 10;
			$this->SendData(json_encode($data));
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
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");

#			Sicherstellen, dass der Rollladen aktuell nicht fährt
			
			if($this->GetValue("action")<>0) $this->ShutterStop();
			for($i=0; $i<50; $i++){
				if($this->GetValue("action")==0)break;
				IPS_Sleep(100);
			}
			if($this->GetValue("action")<>0){
				$this->LogMessage("Keine Rückmeldung vom Aktor!", KL_ERROR);
                echo"EltakoShutter".chr(10)."Keine Rückmeldung vom Aktor!";
				return;
			}

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
			$moveTime = round($newTime - $oldTime,1);

			if($moveTime > 0){
				$this->ShutterMoveDownEx($moveTime);
			}elseif($moveTime < 0){
				$moveTime = abs($moveTime * $UpTime / $DownTime);
				$this->ShutterMoveUpEx($moveTime);
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
?>
