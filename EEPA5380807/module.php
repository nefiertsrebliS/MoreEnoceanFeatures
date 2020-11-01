<?php
class mEnOceanF_EEP_A53808_7 extends IPSModule{
    public function Create(){
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

        //Connect to available enocean gateway
        $this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");

    }

    public function Destroy(){
        //Never delete this line!
        parent::Destroy();

    }

    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterVariableInteger('Position', $this->Translate('Position'), "~Shutter");

        # Slider für Position aktivieren
        $this->EnableAction("Position");

        # Filter setzen
        $this->SetReceiveDataFilter(".*\"DeviceID\":".(int)hexdec( $this->ReadPropertyString("ReturnID")).",.*");

    }

    public function ReceiveData($JSONString){
        $this->SendDebug("Received", $JSONString, 0);
        $data = json_decode($JSONString);

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

    public function RequestAction($Ident, $Value){
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
            default:
                throw new Exception("Invalid Ident");
        }
    }

    public function ShutterMoveTo(int $position){
        if($position <0)$position = 0;
        if($position >100)$position = 100;
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 72;
        $data->DataByte2 = $position;
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function ShutterStop(){
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 24; 
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function ShutterMoveDown(){
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 56; 
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function ShutterMoveUp(){
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 40; 
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function UpdatePosition(){
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 8; 
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function SetRunTime(int $secondsUp, int $secondsDown){
        if($secondsUp <0)$secondsUp = 0;
        if($secondsUp >255)$secondsUp = 255;
        if($secondsDown <0)$secondsDown = 0;
        if($secondsDown >255)$secondsDown = 255;
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 120;
        $data->DataByte1 = $secondsUp;
        $data->DataByte2 = $secondsDown;
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function TeachIn(){
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DataByte0 = 128;
        $data->DataByte1 = 255;
        $data->DataByte2 = 71;
        $data->DataByte3 = 224;
        $data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
        $this->SendData(json_encode($data));
    }

    public function TeachOut(){
        $this->TeachIn();
    }

    protected function SendData($data){
        $this->SendDataToParent($data);
        $this->SendDebug("Sended", $data, 0);
    }


    protected function SendDebug($Message, $Data, $Format){
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
}
