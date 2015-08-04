<?php

	class DynUserData
	{
        public $TbUserData = 'userdata';
		public $User;
        public $Data = array();
		
		function __construct($Src)
		{
            $this->User = $Src;
            $this->Reload();
		}
		
		function ID()
		{
			return $this->User->ID();
		}
		
        function Get($Key, $Def = null)
        {
            if ( isset( $this->Data[$Key]) )
                return $this->Data[$Key];
            return $Def;
        }
        
        function Set($Key, $Value)
        {
            $this->Data[$Key] = $Value;
        }
		
	
		function Reload()
		{
			$Id = $this->User->ID();
			if ($Id > 0)
			{
				$UserDB = User_GetDB();
                
                $res = $UserDB->Select("SELECT * FROM {$this->TbUserData} WHERE user_id = @Id", array("@Id" => $Id));
				if (is_array($res))
				{                   
                    foreach ($res as $R)
                    {
                        $K = $R['name'];
                        $this->Data[$K] = $R['value'];
                        
                    }
					return true;
				}
			}
			return false;			
		}
		
		function Update()
		{	
			$Id = $this->User->ID();
			if ($Id > 0)
			{                
				$UserDB = User_GetDB();
                
                foreach ($this->Data as $K => $V) 
                {
                    $P = array('@Id' => $Id, '@Name' => $K);
                    $Row = array('name' => $K, 'value' => $V);
                    $Cond = "(user_id = @Id) AND (name = @Name)";
                    
                    $exists = $UserDB->SelectValue(
                        "SELECT COUNT(1) FROM {$this->TbUserData} WHERE {$Cond}", $P);
                    
                    if ($exists > 0)                    
                        $res = $UserDB->Update($this->TbUserData, 
                            $Row, 
                            $Cond, $P);                    
                    else
                    {
                        $Row['user_id'] = $Id;
                        $res = $UserDB->Insert($this->TbUserData, $Row);
                    }
                    
                }
                
				return true;
			}
			return false;			
		}		
        
        
        
	}



?>