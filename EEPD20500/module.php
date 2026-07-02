<?php
	class mEnOceanFEEPD20500 extends IPSModuleStrict
	{
		#=====================================================================================
		public function Create(): void
		#=====================================================================================
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString("ReturnID", "00000000");

			$this->RegisterPropertyString("BaseData", '{
				"DataID":"{70E3075F-A35D-4DEB-AC20-C929A156FE48}",
				"Device":210,
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
				"DataByte3":0,
				"DataByte2":0,
				"DataByte1":0,
				"DataByte0":0
			}');

			#	ListenTimer
			$this->RegisterTimer('ListenTimer', 0, 'IPS_RequestAction($_IPS["TARGET"], "Listen", -1);');
			$this->SetBuffer('Listen', 0);
		}

		#=====================================================================================
		public function Destroy(): void
		#=====================================================================================
		{
			//Never delete this line!
			parent::Destroy();

		}

		#=====================================================================================
		public function ApplyChanges(): void
		#=====================================================================================
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterVariableInteger('Position', $this->Translate('Position'), "~Shutter");
			$this->EnableAction('Position');	
			
			#	Filter setzen
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
		
		#=====================================================================================
		public function ReceiveData(string $JSONString): string
		#=====================================================================================
		{
			$this->SendDebug("Received", $JSONString, 0);
			$data = json_decode($JSONString);

			if($this->GetReturnID($data, array(210)))return '';

			switch($data->Device) {
				case "210":
					$position = $data->DataByte3;
					$this->SendDebug("Received Position", $position."%", 0);
					$this->SetValue("Position", $position);	
					break;
				default:
					$this->LogMessage("Unknown Message", KL_ERROR);
			}
			return '';
		}
		
		#=====================================================================================
		public function RequestAction(string $Ident, mixed $Value): void
		#=====================================================================================
		{
			switch($Ident) {
				case "Position":
					$this->ShutterMoveTo($Value);
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
		
		#=====================================================================================
		public function ShutterMoveTo(int $position): void
		#=====================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataByte0 = 1;
			$data->DataByte3 = $position;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function ShutterStop(): void
		#=====================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 1;
			$data->DataByte0 = 2;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function ShutterMoveDown(): void
		#=====================================================================================
		{
			$this->ShutterMoveTo(100);
		}
		
		#=====================================================================================
		public function ShutterMoveUp(): void
		#=====================================================================================
		{
			$this->ShutterMoveTo(0);
		}
		
		#=====================================================================================
		public function UpdatePosition(): void
		#=====================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 1;
			$data->DataByte0 = 3;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function SetAlarmAction(int $action): void
		#=====================================================================================
		{
			if($action < 0 || $action >3) $action = 7;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 5;
			$data->DataByte0 = 5;
			$data->DataByte1 = $action;
			$data->DataByte3 = 255;
			$data->DataByte4 = 127;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function SetRunTime(int $milliseconds): void
		#=====================================================================================
		{
			$milliseconds = (int)($milliseconds/10);
			if($milliseconds < 500 || $milliseconds >32767) $milliseconds = 32767;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 5;
			$data->DataByte0 = 5;
			$data->DataByte1 = 7;
			$data->DataByte3 = $milliseconds%256;
			$data->DataByte4 = (int)($milliseconds/256);
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function TeachIn(): void
		#=====================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->Device = 212;
			$data->DataLength = 7;
			$data->DataByte0 = 210;
			$data->DataByte1 = 5;
			$data->DataByte2 = 0;
			$data->DataByte3 = 0;
			$data->DataByte4 = 70;
			$data->DataByte5 = 1;
			$data->DataByte6 = 145;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}
		
		#=====================================================================================
		public function TeachOut(): void
		#=====================================================================================
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->Device = 212;
			$data->DataLength = 7;
			$data->DataByte0 = 210;
			$data->DataByte1 = 5;
			$data->DataByte2 = 0;
			$data->DataByte3 = 0;
			$data->DataByte4 = 70;
			$data->DataByte5 = 1;
			$data->DataByte6 = 161;
			$data->DestinationID = $this->GetID();
			$this->SendData(json_encode($data));
		}

		#=====================================================================================
		protected function SendData($data): void
		#=====================================================================================
		{
			$this->SendDataToParent($data);
			$this->SendDebug("Sended", $data, 0);
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
	}
