<?php
	class EnoceanButtonEmulator extends IPSModuleStrict
	{
		private const BaseData      = '{
			"DataID":"{70E3075F-A35D-4DEB-AC20-C929A156FE48}",
			"Device":246, 
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
		public function Create(): void
		#================================================================================================
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger('DeviceID', 0);
			$this->RegisterPropertyBoolean('LeftSide', false);
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
		public function RequestAction($Ident, $Value): void
		#================================================================================================
		{
			switch($Ident) {
				case "FreeDeviceID":
					if($this->FreeDeviceID() > 0) $this->UpdateFormField('DeviceID', 'value', $this->FreeDeviceID());
					break;
				default:
					throw new Exception("Invalid Ident");
			}
		}

		#================================================================================================
		public function PressUp(): void
		#================================================================================================
		{
			$this->SendState($this->ReadPropertyBoolean('LeftSide')?48:112);
		}

		#================================================================================================
		public function ShortPressUp(): void
		#================================================================================================
		{
			$this->SendState($this->ReadPropertyBoolean('LeftSide')?48:112);
			IPS_Sleep(150);
			$this->SendState(0);
		}

		#================================================================================================
		public function PressDown(): void
		#================================================================================================
		{
			$this->SendState($this->ReadPropertyBoolean('LeftSide')?16:80);
		}

		#================================================================================================
		public function ShortPressDown(): void
		#================================================================================================
		{
			$this->SendState($this->ReadPropertyBoolean('LeftSide')?16:80);
			IPS_Sleep(150);
			$this->SendState(0);
		}

		#================================================================================================
		public function Release(): void
		#================================================================================================
		{
			$this->SendState(0);
		}

		#================================================================================================
		protected function SendState(int $State): void
		#================================================================================================
		{
			$data = json_decode(self::BaseData);

			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte0 = $State;

			$this->SendDataToParent(json_encode($data));
			$this->SendDebug("Transmit", $State, 0);
		}

		#================================================================================================
		protected function FreeDeviceID(): int
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
