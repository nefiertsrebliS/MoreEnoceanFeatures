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
		$this->RegisterPropertyBoolean('ButtonType', false);
		$this->RegisterPropertyInteger('LongPressDetectionTime', 250);

		if (!IPS_VariableProfileExists('ButtonStatus.EM')) {
			IPS_CreateVariableProfile('ButtonStatus.EM', 1);
			IPS_SetVariableProfileIcon('ButtonStatus.EM', 'Switch');
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 0, $this->Translate('released'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 1, $this->Translate('short pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 2, $this->Translate('double pressed'), '',-1);
			IPS_SetVariableProfileAssociation('ButtonStatus.EM', 3, $this->Translate('long pressed'), '',-1);
			IPS_SetVariableProfileValues('ButtonStatus.EM', 0, 3, 1);
		}

		for($pos = 0; $pos < 10; $pos++){
			#	Register Variables
			$this->RegisterVariableInteger('Button'.$pos, $this->Translate('Button').' '.($pos+1), 'ButtonStatus.EM', $pos+1);
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

		$this->SendDebug('ButtonType', $this->ReadPropertyBoolean('ButtonType')?"true":"false", 0);

		#	Filter setzen
		$BaseID = (int)hexdec($this->ReadPropertyString('ReturnID'));
		if($this->ReadPropertyBoolean('ButtonType')){
			$filter = sprintf('.*\"DeviceID\":(%s|%s|%s|%s|%s),.*', $BaseID+1, $BaseID+3, $BaseID+5, $BaseID+7, $BaseID+9);
		}else{
			$filter = sprintf('.*\"DeviceID\":(%s|%s|%s|%s|%s|%s|%s|%s|%s|%s),.*', $BaseID, $BaseID+1, $BaseID+2, $BaseID+3, $BaseID+4, $BaseID+5, $BaseID+6, $BaseID+7, $BaseID+8, $BaseID+9);
		}

		$this->SendDebug('Filter', $filter, 0);
		$this->SetReceiveDataFilter($filter);
	}

	#================================================================================================
	public function ReceiveData($JSONString)
	#================================================================================================
	{
		$this->SendDebug("Receive", $JSONString, 0);
		$data = json_decode($JSONString);

		$Position = array(16 => 0, 48 => 1, 80 => 0, 112 => 1);
		$BaseID = (int)hexdec($this->ReadPropertyString('ReturnID'));
		$pos = $data->DeviceID - $BaseID;
		$this->SendDebug("Button".$pos, $data->DataByte0, 0);

		switch($data->DataByte0) {
			case 0:
				#	Taste losgelassen
				if($this->ReadPropertyBoolean("ButtonType")){
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
				if($this->ReadPropertyBoolean("ButtonType")) $pos -= $Position[$data->DataByte0];

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

	#================================================================================================
	public function RequestAction($Ident, $Value) 
	#================================================================================================
	{
		switch($Ident) {
			case "LongPress":
				$this->LongPress($Value);
				break;
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
}
?>
