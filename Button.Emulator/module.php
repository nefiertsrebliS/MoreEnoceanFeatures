<?php
	class EnoceanButtonEmulator extends IPSModule
	{

#================================================================================================
		public function Create() 
#================================================================================================
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString('SendID', '00000000');
		
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
			$this->SendState(70);
		}

#================================================================================================
		public function ShortPressUp()
#================================================================================================
		{
			$this->SendState(70);
			IPS_Sleep(150);
			$this->SendState(0);
		}

#================================================================================================
		public function PressDown()
#================================================================================================
		{
			$this->SendState(50);
		}

#================================================================================================
		public function ShortPressDown()
#================================================================================================
		{
			$this->SendState(50);
			IPS_Sleep(150);
			$this->SendState(0);
		}

#================================================================================================
		public function Release()
#================================================================================================
		{
			$this->SendState(0);
		}

		protected function SendState(int $State)
		{
			$SendID   = $this->ReadPropertyString("SendID");
			$this->SendDebug("Send to ".$SendID, $State, 0);
			$Device = str_split($SendID, 2);
			$string = "0B 05 00 00 00 00 00 00 00 00 30";
			$array = explode(" ", $string);
			$array[2] = $State;
		    $array[6] = $Device[0];
		    $array[7] = $Device[1];
		    $array[8] = $Device[2];
		    $array[9] = $Device[3];
			
			$checksumcalc = 0;
			$SendText = chr(hexdec("A5")).chr(hexdec("5A"));
			foreach($array as $hex){
				$checksumcalc += hexdec($hex);
				$SendText .= chr(hexdec($hex));
			}
			$checksumcalc = sprintf("%04X",$checksumcalc);
			$SendText .= chr(hexdec(str_split($checksumcalc, 2)[1]));

			$Port = IPS_GetInstance(IPS_GetInstance($this->InstanceID)['ConnectionID'])['ConnectionID'];
			SPRT_SendText( $Port, $SendText );
			return;
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
?>
