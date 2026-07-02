<?php
class EltakoFxSRxx extends IPSModuleStrict
{

	#================================================================================================
    public function Create(): void
	#================================================================================================
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyInteger("DeviceID", 0);  // genutzte Sendeadresse (= BaseID + DeviceID) des Gateway 
		$this->RegisterPropertyString("ReturnID", "00000000"); // Adresse des Aktors
        $this->RegisterPropertyBoolean("EnableBlocking", false); //aus der Form.json
        $this->RegisterPropertyBoolean("EnableMeasurement", false); //FSR14M-2x: Messfunktion auswerten
        
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
    }

	#================================================================================================
    public function Destroy(): void
	#================================================================================================
    {
        //Never delete this line!
        parent::Destroy();
    }

	#================================================================================================
    public function ApplyChanges(): void
	#================================================================================================
    {
        //Never delete this line!
        parent::ApplyChanges();

        //Anlegen der Variablen unter der Instanz im Objektbaum 
        $this->RegisterVariableBoolean("StatusVariable", $this->Translate("Status"), "~Switch");
                        
        # Variable aktivieren
        $this->EnableAction("StatusVariable");

        # Messfunktion (FSR14M-2x): Leistungsvariable anlegen
        if($this->ReadPropertyBoolean("EnableMeasurement")){
            $this->RegisterMeasurementProfile();
            $this->RegisterVariableFloat("Power", $this->Translate("Power"), "MEF.Watt", 10);
        }

        # Filter setzen
		$this->SetFilter();
    }

    #================================================================================================
    public function GetCompatibleParents(): string
    #================================================================================================
    {
        return json_encode([
            'type' => 'connect',
            'moduleIDs' => [
                // enocean gateway
                '{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}'
            ]
        ]);
    }

	#================================================================================================
	public function ReceiveData(string $JSONString): string
	#================================================================================================
	{
		$this->SendDebug("Receive", $JSONString, 0);
       	$data = json_decode($JSONString);
		$this->SetTimerInterval('UpdateTimer', 0); 

        if($this->GetReturnID($data, array(165, 246)))return '';

        // FSR14M-2x: Messwert-Telegramm (EEP A5-12-01, RORG A5 = 165)
        if($data->Device == 165){
            if($this->ReadPropertyBoolean("EnableMeasurement")) $this->DecodeMeasurement($data);
            return '';
        }

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
        return '';   
    }


    #================================================================================================
    public function RequestAction(string $Ident, mixed $Value): void
    #================================================================================================
    {
        switch($Ident) {
            case "FreeDeviceID":
                if($this->FreeDeviceID() > 0) $this->UpdateFormField('DeviceID', 'value', $this->FreeDeviceID());
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
            default:
                throw new Exception("Invalid Ident");
        }
    }
	
    #================================================================================================
    public function SwitchNormal(bool $switch): void //Sendet einfaches ein-/ausschalten
	#================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DeviceID = $this->ReadPropertyInteger("DeviceID");
        $data->DataByte3 = 1;
        
        if($switch){ //Ein
            $data->DataByte0 = 9;
        }else{  //Aus
            $data->DataByte0 = 8;
        }
        
        $this->SendData(json_encode($data));
    }
    

    #================================================================================================
    public function SwitchBlocking(bool $switch): void //Sendet ein-/ausschalten mit Sperre des Aktors für Tasterbefehle
    #================================================================================================
    {
        $data = json_decode($this->ReadPropertyString("BaseData"));
        $data->DeviceID = $this->ReadPropertyInteger("DeviceID");
        $data->DataByte3 = 1;
        
        if($switch){ //Ein
            $data->DataByte0 = 13;
        }else{  //Aus
            $data->DataByte0 = 12;
        }
         
        $this->SendData(json_encode($data));
    }

	
	#================================================================================================
    public function TeachIn(): void //Sendet ein TeachIn als "GFVS" an den Aktor
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
    protected function SendData(string $data): void
	#================================================================================================
    {
        $this->SendDataToParent($data);
        $this->SendDebug("Sended", $data, 0);
    }

	#================================================================================================
	private function DecodeMeasurement(object $data): void //Wertet das EEP A5-12-01 Messwert-Telegramm aus
	#================================================================================================
	{
		// EEP A5-12-01 (Eltako FSR14M-2x / FSR61VA / FSVA-230V):
		// DB3..DB1 = 24-Bit-Messwert (DB3 = MSB), DB0 = Steuerbits
		//   DB0_Bit3 = LRN  (1 = Datentelegramm, 0 = Lerntelegramm)
		//   DB0_Bit2 = Inhalt (1 = Augenblicksleistung/W, 0 = Zählerstand/kWh)
		//   DB0_Bit1..0 = Teiler (0=/1, 1=/10, 2=/100, 3=/1000)
		// Der FSR14M-2x sendet ausschließlich Leistung (DB0 = 0x0C -> Wert direkt in W).
		$db0 = $data->DataByte0;

		// Lerntelegramm (0x48080D80) ignorieren -> nur Datentelegramme auswerten
		if(($db0 & 0x08) == 0) return;

		// Nur Leistungstelegramme auswerten (DB0_Bit2 = 1)
		if((($db0 >> 2) & 0x01) != 1){
			$this->SendDebug("Measure", "kein Leistungstelegramm, DB0=".$db0, 0);
			return;
		}

		$raw = ($data->DataByte3 << 16) | ($data->DataByte2 << 8) | $data->DataByte1;
		$divisor = pow(10, $db0 & 0x03);
		$power = $raw / $divisor;

		$this->SetValue("Power", $power);
		$this->SendDebug("Measure", "Leistung = ".$power." W", 0);
	}

	#================================================================================================
	private function RegisterMeasurementProfile(): void //Legt das Leistungsprofil an (unabhängig von Systemprofilen)
	#================================================================================================
	{
		if(!IPS_VariableProfileExists("MEF.Watt")){
			IPS_CreateVariableProfile("MEF.Watt", 2); // 2 = Float
			IPS_SetVariableProfileDigits("MEF.Watt", 0);
			IPS_SetVariableProfileText("MEF.Watt", "", " W");
		}
	}
	 
	#=====================================================================================
	private function Listen(int $value): void
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
	private function GetReturnID(mixed $data, mixed $DataValues): bool
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
    private function GetDeviceArray(): mixed
	#=====================================================================================
    {
		$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
		if($Gateway == 0) return array();
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
	private function SetFilter(): void
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
	private function GetID(): int
	#=====================================================================================
	{
        return hexdec($this->ReadPropertyString("ReturnID"));
	}

	#================================================================================================
	protected function FreeDeviceID(): int  //Ermitteln der nächsten freien Adresse des Gateways
	#================================================================================================
	{
		$Gateway = @IPS_GetInstance($this->InstanceID)["ConnectionID"];
		if($Gateway == 0) return -1;
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
