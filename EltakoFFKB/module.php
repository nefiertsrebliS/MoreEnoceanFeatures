<?php
	class mEnOceanFEltakoFFKB extends IPSModule
	{
		public function Create() 
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString("ReturnID", "00000000");

			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");


			#	Fehlende Profile erzeugen
			if (!IPS_VariableProfileExists('Voltage.MEF')) {
				IPS_CreateVariableProfile('Voltage.MEF', 2);
				IPS_SetVariableProfileIcon('Voltage.MEF', 'Electricity');
				IPS_SetVariableProfileDigits('Voltage.MEF', 2);
				IPS_SetVariableProfileValues('Voltage.MEF', 0, 5, 0.02);
				IPS_SetVariableProfileText('Voltage.MEF', '', ' V');
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

			$this->RegisterVariableBoolean('Contact', $this->Translate('Contact'), "~Window.Reversed");
			$this->RegisterVariableFloat('BatteryVoltage', $this->Translate('Battery Voltage'), "Voltage.MEF");
			$this->RegisterVariableFloat('EnergyStorage', $this->Translate('Energy Storage'), "Voltage.MEF");
			
#			Filter setzen
			$ID = hexdec($this->ReadPropertyString("ReturnID"));
			if($ID & 0x80000000)$ID -=  0x100000000;
			$this->SendDebug("DeviceID", (int)$ID, 0);
			$this->SetReceiveDataFilter(".*\"DeviceID\":".(int)$ID.",.*");

		}
		
		public function ReceiveData($JSONString)
		{
			$this->SendDebug("Received", $JSONString, 0);
			$data = json_decode($JSONString);

	        switch($data->Device) {
	            case "165":
					$this->SetValue('BatteryVoltage', (int)$data->DataByte2 * 0.02);
					$this->SetValue('EnergyStorage', (int)$data->DataByte3 * 0.02);
	                break;
	            case "213":
					switch($data->DataByte0) {
						case 9:
							$this->SetValue('Contact', true);
							break;
						case 8:
							$this->SetValue('Contact', false);
							break;
						default:
					}
	                break;
	            default:
					$this->LogMessage("Unknown Message", KL_ERROR);
	        }
		
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

