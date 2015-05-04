<?php


	class DynFilterCollection
	{
		public $Filters = array();
		
		function Add($F, $V = null) // DynFilter object
		{
			if ( is_string($F) )
			{
				$this->Filters[$F] = new DynFilter($F, $V);
			}
			else			
			if ( is_a($F, 'DynFilter') )
			{			
				$Name = strtolower( $F->Name );
				$this->Filters[$Name] = $F;
			}
		}
		
		
		function Apply($Src, $FilterString)
		{
			if ( empty($FilterString) )
				return null;
				
			$Names = explode(",", strtolower( $FilterString ));
			if ( is_array($Src) )
			{
				foreach($Src as $K => $V)
					$Src[$K] = $this->Apply($V, $FilterString);
				return $Src;
			}			
			
			foreach($Names as $Name)
			{			
				if (empty($Name))
					break;
					
				if ( isset($this->Filters[ $Name ]) )
				{
					$Src = $this->Filters[ $Name ]->Apply($Src);									
				}
			}
			return $Src;
		
		}
		
		function GetValues($Name)
		{
			if ( isset($this->Filters[ $Name ]) )
			{
				if ($this->Filters[$Name]->Type == 'array')
					return $this->Filters[$Name]->Value;	
			}
			return null;
		}		
		
	}
	
	class DynFilter
	{
		public $Type;
		public $Name;
		public $Value;
		
		function __construct($FilterName, $FilterValue, $FilterType = '') 
		{   
			$this->Type = $FilterType;			
			if ( is_array($FilterValue) )
				$this->Type = 'array';
			else
			if ( function_exists($FilterValue) )
				$this->Type = 'func';
			else 
			if ( is_string($FilterValue ) )
			{
				if ( $FilterValue[0] == '/' )
					$this->Type = 'regex' ;
			}
				
			$this->Name = $FilterName;
			$this->Value = $FilterValue;
		}
		
		function Apply($Src)
		{
			switch($this->Type)
			{
			case 'func' :
				$Func = $this->Value;			
				return $Func($Src);
			case 'regex' :
				preg_match( $this->Value, $Src, $Res );
				return $Res[0];
			case 'array' :
				if (isset($this->Value[$Src]))
					return $this->Value[$Src];
			}
			return null;
		}
		
	}
	
	function DynFilter( $Src, $FilterString )
	{	global $Filters;
		return $Filters->Apply($Src, $FilterString);
	}
	
	function Filter( $Src, $FilterString )
	{
		return DynFilter( $Src, $FilterString );
	}

?>