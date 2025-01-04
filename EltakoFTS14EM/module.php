<?php
class EltakoFTS14EM extends IPSModule
{

	#================================================================================================
	public function Create()
	#================================================================================================
	{
		#	Never delete this line!
		parent::Create();
		$this->RegisterPropertyString('ReturnID', '00000000');
		$this->RegisterPropertyInteger('ButtonType', 0);
		$this->RegisterPropertyInteger('LongPressDetectionTime', 250);

		#	ListenTimer
		$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
		$this->SetBuffer('Listen', 0);

		for($pos = 0; $pos < 10; $pos++){
			#	LongPressTimer
			$this->RegisterTimer('Button'.$pos, 0, 'IPS_RequestAction($_IPS["TARGET"], "LongPress", "'.$pos.'");');
		}

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

		$this->SendDebug('ButtonType', $this->ReadPropertyInteger('ButtonType'), 0);

		if (!IPS_VariableProfileExists('ButtonStatus.EM')) {
			IPS_CreateVariableProfile('ButtonStatus.EM', 1);
			IPS_SetVariableProfileIcon('ButtonStatus.EM', 'Switch');
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 0, $this->Translate('released'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 1, $this->Translate('short pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 2, $this->Translate('double pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 3, $this->Translate('long pressed'), '',-1);
			IPS_SetVariableProfileValues('ButtonStatus.EM', 0, 3, 1);
		}

		if (!IPS_VariableProfileExists('ContactState.EM')) {
			IPS_CreateVariableProfile('ContactState.EM', 1);
			IPS_SetVariableProfileIcon('ContactState.EM', 'Switch');
			IPS_SetVariableProfileAssociation('ContactState.EM', 0, $this->Translate('open'), '',0x00FF00);
			IPS_SetVariableProfileAssociation('ContactState.EM', 1, $this->Translate('closed'), '',-1);
			IPS_SetVariableProfileValues('ContactState.EM', 0, 1, 1);
		}

		if (!IPS_VariableProfileExists('MotionState.EM')) {
			IPS_CreateVariableProfile('MotionState.EM', 1);
			IPS_SetVariableProfileIcon('MotionState.EM', 'Motion');
			IPS_SetVariableProfileAssociation('MotionState.EM', 0, $this->Translate('no motion'), '',-1);
			IPS_SetVariableProfileAssociation('MotionState.EM', 1, $this->Translate('motion'), '',0x0000FF);
			IPS_SetVariableProfileValues('MotionState.EM', 0, 1, 1);
		}

		if($this->ReadPropertyInteger('ButtonType') <  2)$profile = 'ButtonStatus.EM';
		if($this->ReadPropertyInteger('ButtonType') == 2)$profile = 'ContactState.EM';
		if($this->ReadPropertyInteger('ButtonType') == 3)$profile = 'MotionState.EM';
		$this->SendDebug("Profile", $profile, 0);

		for($pos = 0; $pos < 10; $pos++){
			#	Register Variables
			$this->RegisterVariableInteger('Button'.$pos, $this->Translate('Input').' '.($pos+1), $profile, $pos+1);
		}

		#	Filter setzen
		$this->SetFilter();
	}

	#================================================================================================
	public function ReceiveData($JSONString)
	#================================================================================================
	{
		$this->SendDebug("Received", $JSONString, 0);
		$data = json_decode($JSONString);

		#	Tür-Fenster-Kontakt
		if($this->ReadPropertyInteger('ButtonType') == 2){
			if($this->GetReturnID($data, array(8 => 0, 9 => 1)))return;
			$pos = $data->DeviceID - $this->GetID();
			if($pos == 15) $pos = 9;

			$this->SendDebug("Button".$pos, $data->DataByte0, 0);
			if($data->DataByte0 == 8) $this->SetValue('Button'.$pos, 0);
			if($data->DataByte0 == 9) $this->SetValue('Button'.$pos, 1);

		#	Bewegungsmelder
		}elseif($this->ReadPropertyInteger('ButtonType') == 3){
			if($this->GetReturnID($data, array(15 => 0, 13 => 1)))return;
			$pos = $data->DeviceID - $this->GetID();
			if($pos == 15) $pos = 9;

			$this->SendDebug("Button".$pos, $data->DataByte0, 0);
			if($data->DataByte0 == 15) $this->SetValue('Button'.$pos, 0);
			if($data->DataByte0 == 13) $this->SetValue('Button'.$pos, 1);

		#	Taster
		}else{
			if($this->GetReturnID($data, array(16 => 2, 48 => 0, 80 => 3, 112 => 1)))return;

			$Position = array(16 => 0, 48 => 1, 80 => 0, 112 => 1);
			$pos = $data->DeviceID - $this->GetID();
			if($pos == 15) $pos = 9;
			$this->SendDebug("Button".$pos, $data->DataByte0, 0);
	
			switch($data->DataByte0) {
				case 0:
					#	Taste losgelassen
					if($this->ReadPropertyInteger('ButtonType') == 1){
						$this->SetBuffer('Button'.$pos, 0);
						if($this->GetTimerInterval('Button'.$pos) === 0) $this->SetValue('Button'.$pos, 0);
	
						$this->SetBuffer('Button'.($pos-1), 0);
						if($this->GetTimerInterval('Button'.($pos-1)) === 0) $this->SetValue('Button'.($pos-1), 0);
					}else{
						$this->SetBuffer('Button'.$pos, 0);
						if($this->GetTimerInterval('Button'.$pos) === 0) $this->SetValue('Button'.$pos, 0);
					}
					break;
				default:
					#	Taste gedrückt
					if($this->ReadPropertyInteger('ButtonType') == 1) $pos -= $Position[$data->DataByte0];
	
					$this->SetBuffer('Button'.$pos, 1);
					if($this->GetTimerInterval('Button'.$pos) > 0){
						$this->SetTimerInterval('Button'.$pos, 0);
						$this->SendDebug('Button'.$pos, "DoublePress detected", 0);
						$this->SetValue('Button'.$pos, 2);
					}else{
						$this->SetTimerInterval('Button'.$pos, $this->ReadPropertyInteger('LongPressDetectionTime'));
					}
			}
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
		}else{
			$this->SendDebug('Button'.$Position, "ShortPress detected", 0);
			$this->SetValue('Button'.$Position, 1);
			IPS_Sleep(100);
			$this->SetValue('Button'.$Position, 0);
		}
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
			$ID = $data->DeviceID - $data->DeviceID%16 + 1;
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
		if($this->ReadPropertyInteger('ButtonType') == 1){
			$filter = sprintf('.*\"DeviceID\":(%s|%s|%s|%s|%s),.*', $BaseID+1, $BaseID+3, $BaseID+5, $BaseID+7, $BaseID+15);
		}else{
			$filter = sprintf('.*\"DeviceID\":(%s|%s|%s|%s|%s|%s|%s|%s|%s|%s),.*', $BaseID, $BaseID+1, $BaseID+2, $BaseID+3, $BaseID+4, $BaseID+5, $BaseID+6, $BaseID+7, $BaseID+8, $BaseID+15);
		}
		$this->SendDebug('Filter', $filter, 0);
		$this->SetReceiveDataFilter($filter);
	}

	#=====================================================================================
	private function GetID() 
	#=====================================================================================
	{
		$ID = hexdec($this->ReadPropertyString("ReturnID"));
		if(IPS_GetKernelVersion() < 6.3){
			if($ID & 0x80000000)$ID -=  0x100000000;
		}
		return($ID);
	}
	 
	#=====================================================================================
	public function GetConfigurationForm()
	#=====================================================================================
	{
		$form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
		$form['elements'][2]['visible'] = ($this->ReadPropertyInteger('ButtonType') < 2);
		return json_encode($form);
	}
}
?>
