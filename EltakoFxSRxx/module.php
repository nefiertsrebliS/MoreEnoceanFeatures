<?php
class EltakoFxSRxx extends IPSModule
{

	#================================================================================================
    public function Create()
	#================================================================================================
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger("DeviceID", 0);  // genutzte Sendeadresse (= BaseID + DeviceID) des Gateway 
		$this->RegisterPropertyString("ReturnID", "00000000"); // Adresse des Aktors
        $this->RegisterPropertyBoolean("EnableBlocking", false); //aus der Form.json
        
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
        //DataID: ???
        //Device: RORG? 165 (A5) -> 4BS -> 4 Byte Communication; 246 (F6) -> RPS Repeated Switch Communication (ähnlich 1BS)
        //Status: ???
        //DestinationID: Adresse des Empfängers?


		#	ListenTimer
		$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
		$this->SetBuffer('Listen', 0);

        #	UpdateTimer
		$this->RegisterTimer('UpdateTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Update", "");');

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

        //Anlegen der Variablen unter der Instanz im Objektbaum 
        $this->RegisterVariableBoolean("StatusVariable", $this->Translate("Status"), "~Switch");
                        
        # Variable aktivieren
        $this->EnableAction("StatusVariable");

        # Filter setzen
		$this->SetFilter();
    }


	#================================================================================================
	public function ReceiveData($JSONString) //Verarbeitet die Rückmeldung des Aktors
	#================================================================================================
	{
		$this->SendDebug("Receive", $JSONString, 0);
       	$data = json_decode($JSONString);
		$this->SetTimerInterval('UpdateTimer', 0); 

        if($this->GetReturnID($data, array(165, 246)))return;

        // Byte0 > 112 = An, 80 = Aus; Setzt den Status anhand der Aktorrückmeldung
        switch($data->DataByte0) {
            case 112:
                $this->SetValue("StatusVariable", boolval(true)); //Statusvariable im Objektbaum
            break;
            case 80:
                $this->SetValue("StatusVariable", boolval(false));
            break;
            default:
                throw new Exception("Invalid Ident");
        }      
    }


    #================================================================================================
    public function RequestAction($Ident, $Value)
    #================================================================================================
    {
        switch($Ident) {
            case "FreeDeviceID":
                $this->UpdateFormField('DeviceID', 'value', $this->FreeDeviceID());
                break;
            case "Listen":
                $this->Listen($Value);
                break;
            case "SetReturnID":
                $this->UpdateFormField('ReturnID', 'value', $Value);
                break;
            case "StatusVariable": //Schalten bei Änderung der Statusvariable berücksichtigt den Schalter "EnableBlocking"
                $blocking = $this->ReadPropertyBoolean("EnableBlocking");
                if(($blocking) && ($Value)) {
                    $this->SwitchBlocking(true);
                }else if (($blocking) && (!$Value)) { 
                    $this->SwitchBlocking(false);
                }else if ((!$blocking) && ($Value)) { 
                    $this->SwitchNormal(true);
                }else {
                    $this->SwitchNormal(false);
                }
                break;
            case "SetReturnID":
				$this->UpdateFormField('ReturnID', 'value', $Value);
				break;
            default:
                throw new Exception("Invalid Ident");
        }
    }
	
    #================================================================================================
    public function SwitchNormal(bool $switch) //Sendet einfaches ein-/ausschalten
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DeviceID = $this->ReadPropertyInteger("DeviceID");
        $data->DataByte3 = 1;
        
        if($switch) //Ein
            {
            $data->DataByte0 = 9;
            }else //Aus
            {
            $data->DataByte0 = 8;
            }
        
        $this->SendData(json_encode($data));
    }
    

    #================================================================================================
    public function SwitchBlocking(bool $switch) //Sendet ein-/ausschalten mit Sperre des Aktors für Tasterbefehle
    #================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DeviceID = $this->ReadPropertyInteger("DeviceID");
        $data->DataByte3 = 1;
        
        if($switch) //Ein
            {
            $data->DataByte0 = 13;
            }else //Aus
            {
            $data->DataByte0 = 12;
            }
         
        $this->SendData(json_encode($data));
    }

	
	#================================================================================================
    public function TeachIn() //Sendet ein TeachIn als "GFVS" an den Aktor
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DeviceID = $this->ReadPropertyInteger("DeviceID");
        $data->DataByte3 = 224;
        $data->DataByte2 = 64;
        $data->DataByte1 = 13;
        $data->DataByte0 = 128;
        $data->DestinationID = $this->GetID();
        $this->SendData(json_encode($data));
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
        $filter = sprintf('.*\"DeviceID\":%s,.*', $ID);
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

	#================================================================================================
	protected function FreeDeviceID()  //Ermitteln der nächsten freien Adresse des Gateways
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
		

}
