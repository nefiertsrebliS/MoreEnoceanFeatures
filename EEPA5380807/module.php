<?php
class mEnOceanF_EEP_A53808_7 extends IPSModule{

	#================================================================================================
    public function Create()
	#================================================================================================
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterPropertyString("ReturnID", "00000000");

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
        "DataByte3":7,
        "DataByte2":0,
        "DataByte1":0,
        "DataByte0":0
        }');

		#	ListenTimer
		$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
		$this->SetBuffer('Listen', 0);

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

        $this->RegisterVariableInteger('Position', $this->Translate('Position'), "~Shutter");

        # Slider für Position aktivieren
        $this->EnableAction("Position");

        # Filter setzen
		$this->SetFilter();
    }

	#================================================================================================
    public function ReceiveData($JSONString)
	#================================================================================================
    {
        $this->SendDebug("Received", $JSONString, 0);
        $data = json_decode($JSONString);

		if($this->GetReturnID($data, array(165)))return;

        switch($data->Device) {
            case "165": 
                $position = $data->DataByte3;
                $this->SendDebug("Received Position", $position."%", 0);
				$this->SetValue("Position", $position);	

                $status = $data->DataByte1%4;
				if($status > 0){
		            $this->SendDebug("Received Status", $status, 0);
					if (!IPS_VariableProfileExists('ShutterStatus.MEF')) {
						IPS_CreateVariableProfile('ShutterStatus.MEF', 1);
						IPS_SetVariableProfileIcon('ShutterStatus.MEF', 'Power');
						IPS_SetVariableProfileAssociation('ShutterStatus.MEF', 1, $this->Translate('stop'), '',-1);
						IPS_SetVariableProfileAssociation('ShutterStatus.MEF', 2, $this->Translate('open'), '',-1);
						IPS_SetVariableProfileAssociation('ShutterStatus.MEF', 3, $this->Translate('close'), '',-1);
						IPS_SetVariableProfileValues('ShutterStatus.MEF', 1, 3, 1);
					}
				    $this->RegisterVariableInteger('Command', $this->Translate('Command'), 'ShutterStatus.MEF', 0);
				    $this->EnableAction('Command');
					$this->SetValue("Command", $status);	
				}
                break;
            default:
                $this->LogMessage("Unknown Message", KL_ERROR);
        }

    }

	#================================================================================================
    public function RequestAction($Ident, $Value)
	#================================================================================================
    {
        switch($Ident) {
            case "Position":
                $this->ShutterMoveTo($Value);
                break;
            case "Command":
				switch($Value) {
				    case 1:
				        $this->ShutterStop();
				        break;
				    case 2:
				        $this->ShutterMoveUp();
				        break;
				    case 3:
				        $this->ShutterMoveDown();
				        break;
				    default:
				        throw new Exception("Invalid Value");
				}
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
    public function ShutterMoveTo(int $position)
	#================================================================================================
    {
        if($position <0)$position = 0;
        if($position >100)$position = 100;
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 72;
        $data->DataByte2 = $position;
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function ShutterStop()
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 24; 
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function ShutterMoveDown()
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 56; 
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function ShutterMoveUp()
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 40; 
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function UpdatePosition()
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 8; 
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function SetRunTime(int $secondsUp, int $secondsDown)
	#================================================================================================
    {
        if($secondsUp <0)$secondsUp = 0;
        if($secondsUp >255)$secondsUp = 255;
        if($secondsDown <0)$secondsDown = 0;
        if($secondsDown >255)$secondsDown = 255;
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 120;
        $data->DataByte1 = $secondsUp;
        $data->DataByte2 = $secondsDown;
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function TeachIn()
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 128;
        $data->DataByte1 = 255;
        $data->DataByte2 = 71;
        $data->DataByte3 = 224;
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
    }

	#================================================================================================
    public function TeachOut()
	#================================================================================================
    {
        $this->TeachIn();
    }

	#================================================================================================
    protected function SendData($data)
	#================================================================================================
    {
        $this->SendDataToParent($data);
        $this->SendDebug("Sended", $data, 0);
    }

	#================================================================================================
    protected function SendDebug($Message, $Data, $Format)
	#================================================================================================
    {
        if (is_array($Data)){
            foreach ($Data as $Key => $DebugData){
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        }else if (is_object($Data)){
            foreach ($Data as $Key => $DebugData){
                $this->SendDebug($Message . "." . $Key, $DebugData, 0);
            }
        }else{
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
		$ID = $this->GetID();
        $filter = sprintf('.*\"DeviceID\":%s,.*', (int)$ID);
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
