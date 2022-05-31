<?php
class EltakoFTS61BTK extends IPSModule
{
	private const Position      = array("TL" =>["top", "left"], "TR" =>["top", "right"], "BL" =>["bottom", "left"], "BR" =>["bottom", "right"]);
	private const FlashTime		= 700;

	#================================================================================================
	public function Create()
	#================================================================================================
	{
		#	Never delete this line!
		parent::Create();
		$this->RegisterPropertyString('ReturnID', '00000000');
		$this->RegisterPropertyBoolean('AdressTyp', false);
		$this->RegisterPropertyInteger('LongPressDetectionTime', 250);
		$this->RegisterPropertyBoolean('IndicatorLight', false);

		if (!IPS_VariableProfileExists('ButtonStatus.EM')) {
			IPS_CreateVariableProfile('ButtonStatus.EM', 1);
			IPS_SetVariableProfileIcon('ButtonStatus.EM', 'Switch');
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 0, $this->Translate('released'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 1, $this->Translate('short pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 2, $this->Translate('double pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 3, $this->Translate('long pressed'), '',-1);
			IPS_SetVariableProfileValues('ButtonStatus.EM', 0, 3, 1);
		}

		foreach(self::Position as $pos => $text){
			#	Register Variables
			$this->RegisterPropertyInteger('StatusID'.$pos,  0);
			$this->RegisterPropertyString('LedID'.$pos, '00000000');
			$this->RegisterPropertyBoolean('DirectLedS'.$pos, true);
			$this->RegisterPropertyBoolean('DirectLedD'.$pos, true);
			$this->RegisterPropertyBoolean('DirectLedL'.$pos, true);
			$this->RegisterVariableInteger('Button'.$pos, $this->Translate('Button').' '.$this->Translate($text[0]).' '.$this->Translate($text[1]), 'ButtonStatus.EM');
			#	LongPressTimer
			$this->RegisterTimer('Button'.$pos, 0, 'IPS_RequestAction($_IPS["TARGET"], "LongPress", "'.$pos.'");');
			#	StatusTimer
			$this->RegisterTimer("$pos", 0, 'IPS_RequestAction($_IPS["TARGET"], "RenewState", "'.$pos.'");');
			#	LedFlashTimer
			$this->RegisterTimer('LedFlash'.$pos, 0, 'IPS_RequestAction($_IPS["TARGET"], "LedFlash", "'.$pos.'");');
		}

		#	ListenTimer
		$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
		$this->SetBuffer('Listen', 0);

		#	Connect to available enocean gateway
		$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");
	}

	#================================================================================================
	public function Destroy()
	#================================================================================================
	{
		#	Never delete this line!
		parent::Destroy();

	}

	#================================================================================================
	public function ApplyChanges()
	#================================================================================================
	{
		#	Never delete this line!
		parent::ApplyChanges();

		#	Filter setzen
		$this->SetFilter();

		#	Unregister Messages
		foreach($this->GetMessageList() as $ID => $Messages){
			$this->UnregisterMessage ($ID, VM_UPDATE);
		}

		if($this->ReadPropertyBoolean('IndicatorLight')){
			#	Register Messages
			foreach(self::Position as $pos => $text){
				if(@$this->ReadPropertyInteger('StatusID'.$pos) > 0){
					$this->RegisterMessage ($this->ReadPropertyInteger('StatusID'.$pos), VM_UPDATE);
				}
				$this->SendState($pos, false);
			}
		}
	}

	#================================================================================================
	public function ReceiveData($JSONString)
	#================================================================================================
	{
		$this->SendDebug("Receive", $JSONString, 0);
		$data = json_decode($JSONString);
		$this->SendDebug("State", $data->DataByte0, 0);

		if($this->GetReturnID($data, array(16 => 2, 48 => 0, 80 => 3, 112 => 1)))return;

		$DataPosition = array(16 => 2, 48 => 0, 80 => 3, 112 => 1);
		$Position = array_keys(self::Position);

		switch($data->DataByte0) {
			case 0:
				#	Taste losgelassen
				if($this->ReadPropertyBoolean("AdressTyp")){
					$ID = "Button".$Position[$data->DeviceID - $this->GetID()];
					$this->SetBuffer($ID, 0);
					if($this->GetTimerInterval($ID) === 0) $this->SetValue($ID, 0);
			}else{
					foreach($Position as $ID){
						$this->SetBuffer("Button".$ID, 0);
						if($this->GetTimerInterval("Button".$ID) === 0) $this->SetValue("Button".$ID, 0);
					}
				}
				break;
			default:
				#	Taste gedrückt
				$nr = $DataPosition[$data->DataByte0];
				$pos = $Position[$nr];
				$this->SetBuffer("Button".$pos, 1);
				if($this->GetTimerInterval("Button".$pos) > 0){
					$this->SetTimerInterval("Button".$pos, 0);
					$this->SendDebug('Button'.$pos, "DoublePress detected", 0);
					$this->SetValue('Button'.$pos, 2);
					$this->DirectState($pos, 'D');
				}else{
					$this->SetTimerInterval("Button".$pos, $this->ReadPropertyInteger('LongPressDetectionTime'));
				}
		}

	}

	#================================================================================================
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
	#================================================================================================
	{
		switch ($Message) {
			case IM_CHANGESTATUS:
				break;
			case VM_UPDATE:

				foreach(self::Position as $pos => $text){
					if($SenderID == $this->ReadPropertyInteger('StatusID'.$pos)){
						$state = abs(intval($Data[0]));
						$this->SetTimerInterval("$pos", 0);
						$this->SendDebug("VM_UPDATE at ".$pos, $state, 0);
						if($Data[1]){
							IPS_Sleep(150);
							if($state < 2){
								$this->SendState($pos, (bool) $state);
								$this->SetTimerInterval('LedFlash'.$pos, 0);
							}else{
								$this->SendState($pos, true);
								if($this->ReadPropertyBoolean('IndicatorLight'))$this->SetTimerInterval('LedFlash'.$pos, self::FlashTime);
							}
						}
					}
				}
				break;
		}
	}

	#================================================================================================
	public function RequestAction($Ident, $Value) 
	#================================================================================================
	{
		switch($Ident) {
			case "LongPress":
				$this->LongPress($Value);
				break;
			case "RenewState":
				$this->RenewState($Value);
				break;
			case "LedFlash":
				$this->LedFlash($Value);
				break;
			case "Listen":
				$this->Listen($Value);
				break;
			case "SetReturnID":
				$this->UpdateFormField('ReturnID', 'value', $Value);
				break;
			default:
				throw new Exception("Invalid Ident");
		}
	}

	#================================================================================================
	private function LongPress(string $Position)
	#================================================================================================
	{
		$this->SetTimerInterval('Button'.$Position, 0);
		if((bool)$this->GetBuffer('Button'.$Position)){
			$this->SendDebug('Button'.$Position, "LongPress detected", 0);
			$this->SetValue('Button'.$Position, 3);
			$this->DirectState($Position, 'L');
		}else{
			$this->SendDebug('Button'.$Position, "ShortPress detected", 0);
			$this->SetValue('Button'.$Position, 1);
			$this->DirectState($Position, 'S');
			IPS_Sleep(100);
			$this->SetValue('Button'.$Position, 0);
		}
	}

	#================================================================================================
	private function DirectState(string $Position, string $DetectionLabel)
	#================================================================================================
	{
		if(!$this->ReadPropertyBoolean('IndicatorLight'))return;
		$ID = $this->ReadPropertyInteger("StatusID".$Position);
		$this->SendDebug("ReceiveData at Button".$Position, $ID, 0);
		$PositionArray = array_keys(self::Position);
		$nr = array_search($Position, $PositionArray);
		if(!$this->ReadPropertyBoolean('DirectLed'.$DetectionLabel.$PositionArray[$nr]))return;

		$state = true;
		if($ID == 0){
			if($nr > 1){
				$nr -= 2;
			}else{
				$nr += 2;
			}
			$ID = $this->ReadPropertyInteger("StatusID".$PositionArray[$nr]);
			$state = false;
		}
		if($ID <>0){
			$this->SendState($PositionArray[$nr], $state);
			$this->SetTimerInterval($PositionArray[$nr], 2500);
		}
	}

	#================================================================================================
	public function SwitchLED(string $Position, int $state)
	#================================================================================================
	{
		if(!$this->ReadPropertyBoolean('IndicatorLight')){
			echo 'Indicatorlight is not configured in Instance '.$this->InstanceID;
			return;
		}elseif(!array_key_exists($Position, self::Position)){
			echo $Position.' is not a valid position. Please try TL, TR, BL or BR';
			return;
		}
		if($state < 2){
			$this->SetTimerInterval('LedFlash'.$Position, 0);
			$this->SendState($Position, (bool) $state);
		}elseif($this->GetTimerInterval('LedFlash'.$Position) == 0){
			$this->SetTimerInterval('LedFlash'.$Position, self::FlashTime);
			$this->SendState($Position, true);
		}
	}

	#================================================================================================
	private function RenewState(string $TimerID)
	#================================================================================================
	{
		$this->SetTimerInterval($TimerID, 0);
		$state = intval(GetValue($this->ReadPropertyInteger("StatusID".$TimerID)));
		$this->SendDebug("RenewState at $TimerID", $state, 0);
		$this->SwitchLED($TimerID, $state);
	}

	#================================================================================================
	private function LedFlash(string $Position)
	#================================================================================================
	{
		$State = $this->GetBuffer('LedState'.$Position) == "true"?false:true;
		$this->SendState($Position, $State);
	}

	#================================================================================================
	protected function SendState(string $Position, bool $State)
	#================================================================================================
	{
		$this->SetBuffer('LedState'.$Position, $State?"true":"false");
		if ($State) {
			$State = 70;
		}else{
			$State = 50;
		}
		$ReturnId   = $this->ReadPropertyString("LedID".$Position);
		$this->SendDebug($ReturnId, $State, 0);
		$Device = str_split($ReturnId, 2);
		$string = "0B 05 00 00 00 00 00 00 00 00 30";
		$array = explode(" ", $string);
		$array[2] = $State;
		$array[6] = $Device[0];
		$array[7] = $Device[1];
		$array[8] = $Device[2];
		$array[9] = $Device[3];

		$checksumcalc = 0;
		$SendText = chr(hexdec("A5")).chr(hexdec("5A"));
		foreach($array as $hex){
			$checksumcalc += hexdec($hex);
			$SendText .= chr(hexdec($hex));
		}
		$checksumcalc = sprintf("%04X",$checksumcalc);
		$SendText .= chr(hexdec(str_split($checksumcalc, 2)[1]));

		$Port = IPS_GetInstance(IPS_GetInstance($this->InstanceID)['ConnectionID'])['ConnectionID'];
		SPRT_SendText( $Port, $SendText );
		return;
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
	 
	#=====================================================================================
	public function GetConfigurationForm() 
	#=====================================================================================
	{
		$form = json_decode(file_get_contents(__DIR__ . "/form.json"));
		foreach($form->elements as &$element){
			if($element->type == "ExpansionPanel" && strpos(@$element->name, "Led") !== false){
				$element->visible = $this->ReadPropertyBoolean('IndicatorLight');
			}
		}
		return json_encode($form);
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
	private function GetReturnID($data, $DataValues) 
	#=====================================================================================
	{
		if($this->GetTimerInterval('ListenTimer') == 0) return false;

		$values = json_decode($this->GetBuffer('DeviceIDs'));
		$Devices = $this->GetDeviceArray();
		if(isset($DataValues[$data->DataByte0])){
			$ID = $this->ReadPropertyBoolean('AdressTyp')?$data->DeviceID - $DataValues[$data->DataByte0]:$data->DeviceID;
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
		$BaseID = $this->GetID();
		if($this->ReadPropertyBoolean('AdressTyp')){
			$filter = sprintf('.*\"DeviceID\":(%s|%s|%s|%s),.*', $BaseID, $BaseID+1, $BaseID+2, $BaseID+3);
		}else{
			$filter = sprintf('.*\"DeviceID\":%s,.*', $BaseID);
		}
		$this->SendDebug('Filter', $filter, 0);
		$this->SetReceiveDataFilter($filter);
	}

	#=====================================================================================
	private function GetID() 
	#=====================================================================================
	{
		$ID = (int)hexdec($this->ReadPropertyString("ReturnID"));
		if($ID & 0x80000000)$ID -=  0x100000000;
        return($ID);
	}
}
?>
