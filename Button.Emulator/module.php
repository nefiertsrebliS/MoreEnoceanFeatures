<?php
	class EnoceanButtonEmulator extends IPSModule
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
		public function Create() 
#================================================================================================
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyInteger('DeviceID', 0);
		
			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");
		}

#================================================================================================
		public function Destroy(){
#================================================================================================
		    //Never delete this line!
		    parent::Destroy();

		}
    
#================================================================================================
		public function ApplyChanges()
#================================================================================================
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

#================================================================================================
		public function PressUp()
#================================================================================================
		{
			$this->SendState(112);
		}

#================================================================================================
		public function ShortPressUp()
#================================================================================================
		{
			$this->SendState(112);
			IPS_Sleep(150);
			$this->SendState(0);
		}

#================================================================================================
		public function PressDown()
#================================================================================================
		{
			$this->SendState(80);
		}

#================================================================================================
		public function ShortPressDown()
#================================================================================================
		{
			$this->SendState(80);
			IPS_Sleep(150);
			$this->SendState(0);
		}

#================================================================================================
		public function Release()
#================================================================================================
		{
			$this->SendState(0);
		}

#================================================================================================
		protected function SendState(int $State)
#================================================================================================
		{
			$data = json_decode(self::BaseData);

			$data->DeviceID = $this->ReadPropertyInteger("DeviceID");
			$data->DataByte0 = $State;

			$this->SendDataToParent(json_encode($data));
			$this->SendDebug("Transmit", $State, 0);
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
