<?php
	class EnoceanVirtTempControl extends IPSModule
	{
		private const BaseData      = '{
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
			"DataByte3":7,
			"DataByte2":0,
			"DataByte1":0,
			"DataByte0":0
		}';

		#================================================================================================
		public function Create() 
		#================================================================================================
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger('DeviceID', 0);
			$this->RegisterPropertyInteger('TemperatureID', 0);

			if (!IPS_VariableProfileExists('Temperature.EM')) {
				IPS_CreateVariableProfile('Temperature.EM', 2);
				IPS_SetVariableProfileIcon('Temperature.EM', 'Temperature');
				IPS_SetVariableProfileValues('Temperature.EM', 0, 40, 1);
				IPS_SetVariableProfileDigits('Temperature.EM', 1);
				IPS_SetVariableProfileText('Temperature.EM', '', ' °C');
			}

			if (!IPS_VariableProfileExists('Temperature.Target.EM')) {
				IPS_CreateVariableProfile('Temperature.Target.EM', 2);
				IPS_SetVariableProfileIcon('Temperature.Target.EM', 'Temperature');
				IPS_SetVariableProfileValues('Temperature.Target.EM', 8, 28, 0.2);
				IPS_SetVariableProfileDigits('Temperature.Target.EM', 1);
				IPS_SetVariableProfileText('Temperature.Target.EM', '', ' °C');
			}

			#	Register Variables
			$this->RegisterVariableFloat('actual', $this->Translate('Actual Temperature'), 'Temperature.EM', 0);
			$this->RegisterVariableFloat('target', $this->Translate('Target Temperature'), 'Temperature.Target.EM', 0);
			$this->EnableAction('target');
			#	LongPressTimer
			$this->RegisterTimer('SendState', 300000, 'IPS_RequestAction($_IPS["TARGET"], "SendState", "");');
		
			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");
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
			if($this->ReadPropertyInteger('TemperatureID') > 0)$this->RegisterMessage ($this->ReadPropertyInteger('TemperatureID'), VM_UPDATE);
			$this->SetTargetValue($this->GetValue('target'));
		}

		#================================================================================================
		public function RequestAction($Ident, $Value)
		#================================================================================================
		{
			switch($Ident) {
				case "FreeDeviceID":
					$this->UpdateFormField('DeviceID', 'value', $this->FreeDeviceID());
					break;
				case "LearnMode":
					$this->SendLearn();
					break;
				case "SendState":
					$this->SendState($this->GetValue('actual'), $this->GetValue('target'));
					break;
				case "target":
				case "MEF_SetTargetValue":
					$this->SetTargetValue($Value);
					break;
				case "MEF_SetActualValue":
					$this->SetActualValue($Value);
					break;
				default:
					throw new Exception("Invalid Ident");
			}
		}
		
		#================================================================================================
		public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
		#================================================================================================
		{
			switch ($Message) {
				case VM_UPDATE:
					switch($SenderID){
						case $this->ReadPropertyInteger('TemperatureID'):
							if($Data[1])$this->SetActualValue($Data[0]);
							break;
						default:
							$this->UnregisterMessage($SenderID, VM_UPDATE);
					}
					break;
			}
		}
		
		#================================================================================================
		public function SetTargetValue(float $Value)
		#================================================================================================
		{
			if($Value < 8)$Value = 8;
			if($Value > 28)$Value = 28;
			$this->SetValue('target', $Value);
			$this->SendState($this->GetValue('actual'), $Value);
		}
		
		#================================================================================================
		public function SetActualValue(float $Value)
		#================================================================================================
		{
			if($Value < 0) $Value = 0;
			if($Value > 40) $Value = 40;
			$this->SetValue('actual', $Value);
			$this->SendState($Value, $this->GetValue('target'));
		}

		#================================================================================================
		protected function SendState($Actual, $Target)
		#================================================================================================
		{
			$data = json_decode(self::BaseData);

			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte0 = 15;
			if($Actual>40)$Actual = 40;
			if($Actual<0)$Actual = 0;
			$data->DataByte1 = round((1 - $Actual / 40) * 255);
			if($Target>40)$Target = 40;
			if($Target<8)$Target = 8;
			$data->DataByte2 = round($Target / 40 * 255);
			$data->DataByte3 = 0;

			$this->SendDataToParent(json_encode($data));
			$this->SendDebug("SendState", 'Actual:'.$Actual.'°C - Target:'.$Target.'°C', 0);
		}

		#================================================================================================
		protected function SendLearn()
		#================================================================================================
		{
			$data = json_decode(self::BaseData);

			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataLength=4;
			$data->DataByte0 = 135;
			$data->DataByte1 = 13;
			$data->DataByte2 = 48;
			$data->DataByte3 = 64;

			$this->SendDataToParent(json_encode($data));
			$this->SendDebug("SendLearn", json_encode($data), 0);
			$this->SendDebug("SendLearn", '0x40300D87', 0);
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
	}
