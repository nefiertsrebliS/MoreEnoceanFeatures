<?php
	class mEnOceanFEEPD20500 extends IPSModule
	{
		public function Create() 
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

			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");

		}

		public function Destroy(){
		    //Never delete this line!
		    parent::Destroy();

		}
    
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterVariableInteger('Position', $this->Translate('Position'), "~Shutter");
			
#			Filter setzen
			$this->SetReceiveDataFilter(".*\"DeviceID\":".(int)hexdec($this->ReadPropertyString("ReturnID")).",.*");

#			Slider für Position aktivieren
			$this->EnableAction("Position");	

		}
		
		public function ReceiveData($JSONString)
		{
			$this->SendDebug("Received", $JSONString, 0);
			$data = json_decode($JSONString);

	        switch($data->Device) {
	            case "210":
					$position = $data->DataByte3;
					$this->SendDebug("Received Position", $position."%", 0);
					SetValue($this->GetIDForIdent("Position"), $position);	
	                break;
	            default:
					$this->LogMessage("Unknown Message", KL_ERROR);
	        }
		
		}
		
        public function RequestAction($Ident, $Value) 
		{
            switch($Ident) {
                case "Position":
					$this->ShutterMoveTo($Value);
                    break;
                default:
                    throw new Exception("Invalid Ident");
            }
        }
		
        public function ShutterMoveTo(int $position) 
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataByte0 = 1;
			$data->DataByte3 = $position;
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function ShutterStop() 
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 1;
			$data->DataByte0 = 2;
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function ShutterMoveDown() 
		{
			$this->ShutterMoveTo(100);
        }
		
        public function ShutterMoveUp() 
		{
			$this->ShutterMoveTo(0);
        }
		
        public function UpdatePosition() 
		{
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 1;
			$data->DataByte0 = 3;
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function SetAlarmAction(int $action) 
		{
			if($action < 0 || $action >3) $action = 7;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 5;
			$data->DataByte0 = 5;
			$data->DataByte1 = $action;
			$data->DataByte3 = 255;
			$data->DataByte4 = 127;
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function SetRunTime(int $milliseconds) 
		{
			$milliseconds = (int)($milliseconds/10);
			if($milliseconds < 500 || $milliseconds >32767) $milliseconds = 32767;
			$data = json_decode($this->ReadPropertyString("BaseData"));
			$data->DataLength= 5;
			$data->DataByte0 = 5;
			$data->DataByte1 = 7;
			$data->DataByte3 = $milliseconds%256;
			$data->DataByte4 = (int)($milliseconds/256);
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function TeachIn() 
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
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }
		
        public function TeachOut() 
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
			$data->DestinationID = (int)hexdec($this->ReadPropertyString("ReturnID"));
			$this->SendData(json_encode($data));
        }

		protected function SendData($data)
		{
			$this->SendDataToParent($data);
			$this->SendDebug("Sended", $data, 0);
		} 


		protected function SendDebug($Message, $Data, $Format)
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

