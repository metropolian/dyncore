<?php

	$DynBlockCallback = DynExecuteBlockCallback;
	$DynBlockFuncCallback = DynExecuteBlockFuncCallback;
	
	$DynBlockRegisters= null;
	$DynBlockCallbackRegisters= null;
	
	function DynBlockRegister($ModuleName, $BlockName, $Func)
	{	global $DynBlockRegisters;
	
		$ModuleName = strtoupper( trim( $ModuleName ));
		$BlockName = strtoupper( trim( $BlockName ));
		
		if (($ModuleName != "") && ($BlockName != "") && is_callable($Func))
		{
			$DynBlockCallbackRegisters[$ModuleName][$BlockName] = $Func;
			return  true;
		}
		return false;
	}
	
	function DynBlockCallbackRegister($Func)
	{	global $DynBlockCallbackRegisters;
		
		if (is_callable($Func))
		{
			$DynBlockCallbackRegisters[] = $Func;
			return true;
		}		
		return false;
	}

	
	
	
	
	function DynExecuteBlockCallback($Module, $BlockName, &$LocalVars)
	{	global $DynBlockRegisters, $DynBlockCallbackRegisters;

		$BlockName = strtoupper( trim( $BlockName ));
				
		$Res = null;		
		if (isset($DynCallbacks[$ModuleName][$BlockName] ))
		{
			$Res = $DynCallbacks[$ModuleName][$BlockName]($Module, $BlockName, &$LocalVars);
			if ($Res !== null)
				return $Res;
		}
		
		
		if (isset($DynBlockCallbackRegisters))
		foreach ($DynBlockCallbackRegisters as $Func)
		{
			$Res = $Func($ModuleName, $BlockName, &$LocalVars);
			if ($Res !== null)
				return  $Res;			
		}
		return $Res;		
	}
	
	
	
	function DynExecuteBlockFuncCallback($Module, $BlockName, &$LocalVars)
	{
		//echo "=$BlockName=======================================";
		if ($BlockName == "SELECTBOX")
		{
			$SelBoxOptions = $LocalVars['OPTIONS'];
			$SelVal = $LocalVars['SELECTED'];
			$Selected =  'value="' . $SelVal . '" selected ';
			$SelSearch = '/value=[\'\"]?' . $SelVal . '[\'\"]?/ims';

			
		//echo "=$SelVal=======================================";
		
			$Res = preg_replace($SelSearch, $Selected , $SelBoxOptions, 1  );
			return $Res;
		}
		
		if (($BlockName == "#") && (is_array($LocalVars)))
		{
			$Gets = DynVar_Get("GET");
			if (is_array($Gets))
				$LocalVars = DynVar_PackVars($Gets , $LocalVars);
				
			
			$ScriptFile = DynVar_Get("SERVER.SCRIPT_NAME") . "?";
			
			$QueryVars = array_change_key_case($LocalVars, CASE_LOWER);
			$Res = http_build_query($QueryVars);
			return $ScriptFile . $Res;			
		}
		
		if ($BlockName == "DATEFORMAT")
		{	
			$DateType = strtoupper( trim( isset( $LocalVars['TYPE'] ) ? $LocalVars['TYPE'] : "DEFAULT" )) ;
			switch ($DateType)
			{
				case "SHORT" : $Format = "d-m-Y h:m"; break;
				case "DATE" : $Format = "d-m-Y"; break;
				case "TIME" : $Format = "h:m:s"; break;
				default: 
					$Format = "d-m-Y h:m:s";
			} 
			$Src = strtotime( $LocalVars['DATE'] );			
			$Res = date($Format, $Src);
			return $Res;
		}
		return null;
	}

?>