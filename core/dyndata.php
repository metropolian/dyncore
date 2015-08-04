<?php 
/**
* DynDataStorage 
*
* Caching Data
*
* @package  DynCore
* @author   Metro Kobchaipuk <metropolian@live.com>
* @version  $Revision: 1.0 $
*/

    class DynDataStorage
    {
        public $Type;
        public $Name;
        public $Data = array();
        public $DataExpire = array();
        
        function __construct($Type = 'file', $Name = 'default')
        {
            $this->Type = $Type;
            $this->Name = $Name;
        }
        
        
        function LoadFile($FName)
        {            
            $F = @file_get_contents($FName);            
            if ($F)
            {
                $this->Data = json_decode($F);
                return true;
            }
            return false;            
        }
        
        function SaveFile($FName)
        {
            return @file_put_contents($FName, json_encode($this->Data));
        }
        
        
        function Get($K)
        {
            return $this->Data[$K];    
        }
        
        function Set($K, $V)
        {
            $this->Data[$K] = $V;
        }
    }




    $Data = new DynDataStorage();

    $Data->LoadFile(__DIR__ . '/data/default');

    var_dump($Data);$Data->Get('hello');

    var_dump($Data->Data);

    $Data->SaveFile(__DIR__ . '/data/default');
    

?>