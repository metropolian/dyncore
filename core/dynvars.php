<?php

	/************************************************************************************************************
	 * 
     * DYNAMIC
     * dynvars - Dynamic Variables 
     *
     * 
     * by Northern Mongalian (C) DML System 2009     *
     *   
     ************************************************************************************************************/
 
$DynVars = array();

function Init_DynVariables()
{	
	DynVar_PackInto("GET", ClearMagicQuoteArray( $_GET ));
	DynVar_PackInto("POST", ClearMagicQuoteArray( $_POST ));	
	DynVar_PackInto("SERVER", $_SERVER);
	DynVar_PackInto("ENV", $_ENV);

	DynVar_PackInto("COOKIE", $_COOKIE);	
	DynVar_PackInto("SESSION", $_SESSION);
	
	
}

// Pack KeyValue variable to DynVariable
function DynVar_PackInto($Prefix, $SrcPack, $Upcase = false)
{	global $DynVars;
	if ($SrcPack)
	if (is_array($SrcPack))
	{
		foreach ($SrcPack as $Key => $Val)
		{
			$KeyName = $Prefix . "." .  $Key;
			
			if ($Upcase)
				$KeyName = strtoupper($KeyName);
				

			DynVar_Set($KeyName, $Val);
		}
		return  true;
	}
	return false;
}

function DynVar_GetPack($Prefix)
{	global $DynVars;
	$ResVar = null;
	$Prefix = strtoupper($Prefix);
	foreach ($DynVars as $Key => $Val)
	{
		if (strpos($Key, $Prefix) !== false)
			$ResVar[$Key] = $Val;
	}
	return $ResVar;
}

function DynVar_KeyToLocal($SrcPack)
{
	$ResVar = null;
	if (is_array($SrcPack))
	{
		foreach ($SrcPack as $Key => $Val)
		{
			$Pos = strrpos($Key, ".");
		
			if ($Pos !== false)
			{
				$KeyName = substr($Key, $Pos + 1);
				$ResVar[$KeyName] = $Val;						
			}
		}
	}		
	return $ResVar;
}

function DynVar_GetPackKeyToLocal($Prefix)
{
	return DynVar_KeyToLocal( DynVar_GetPack($Prefix));	
}




function DynVar_SetValue(&$Vars, $VarName, $Value )
{
	if (!is_array($Vars))
		return; 
	//echo $VarName . "\r\n";
	if (IsInString($VarName, "."))
	{
		$HosName = GetStrBefore($VarName, ".", false, false);
		$RemName = GetStrAfter($VarName, ".", false, false);
		
		if ($RemName != "")
		{
			if (!array_key_exists($HosName, $Vars))
				$Vars[$HosName] = array();
			DynVar_SetValue($Vars[$HosName], $RemName, $Value);
		}
	}
	else 
	{
		$Vars[$VarName] = $Value;				
	}
}

function DynVar_GetValue(&$Vars, $VarName, $Def = null  )
{
	if (!is_array($Vars))
		return null; 
	if (IsInString($VarName, "."))
	{
		$HosName = GetStrBefore($VarName, ".", false, false);
		$RemName = GetStrAfter($VarName, ".", false, false);
		
		if ($RemName != "")
		{
			if (array_key_exists($HosName, $Vars))
				return DynVar_GetValue($Vars[$HosName], $RemName, $Def);
		}
	}
	else 
	{		
		if (array_key_exists($VarName, $Vars))
			return $Vars[$VarName];
	}
	return $Def;				
}

function DynVar_Set($VarName, $VarValue)
{	global $DynVars;

	$VarName = strtoupper($VarName);
	
	DynVar_SetValue($DynVars, $VarName, $VarValue);
}

function DynVar_Get($VarName, $Def = null)
{	global $DynVars;

	$VarName = strtoupper($VarName);
	
	return DynVar_GetValue($DynVars, $VarName, $Def);	
}


function DynVar_Copy($DestVarName, $SrcVarName, $Def = "")
{
	return DynVar_Set($DestVarName, DynVar_Get($SrcVarName, $Def));
}

	


function DynVar_GlobalContent_Add($Type, $Value)
{	global $DynVars;

	$GlobalName = "GLOBAL." . strtoupper($Type);
	
	DynVar_Set( $GlobalName, DynVar_Get($GlobalName) . $Value);
}

function DynVar_GlobalContent_AddLine($Type, $Line, $AddNew = false)
{	global $DynVars;

	$GlobalName = "GLOBAL." . strtoupper($Type);	
	$Line = trim($Line);
	
	$Values = DynVar_Get($GlobalName);
		
	if (isset($Values) && (!$AddNew))
	{
		if (is_array($Values))
			if (array_search($Line, $Values) !== false)
				return;
	}
	
	$Values []= $Line;

	DynVar_Set($GlobalName, $Values);
	
		
}








function DynVar_LocalToURL($SrcPack, $VarNameUppper = true, $Separator = '&' ) 
{
    $Res = "";
    if (is_array($SrcPack)) {
        foreach ($SrcPack as $Key => $Val) {
        	if ($VarNameUppper)
        		$Key = strtoupper($Key);
        	else
        		$Key = strtolower($Key);
            if (is_array($Val)) 
                $Res .= DynVar_LocalToURL($Val, "{$Key}", $Separator);
            else 
                $Res .= "{$Key}=".urlencode($Val) . $Separator;
        }
   		$Res = substr($Res,0,-1);
    }
    return $Res;
}


function DynVar_URLToLocal($Src, $VarNameUpper = true)
{
	if (IsInString($Src, "&"))
		$Vars = split("&", $Src);
	else
		$Vars = array($Src);
	
	$Res = array();
		//echo "*****$Src*************************************************\r\n";
	
	$Index = 0;
	while($Index < count($Vars))
	{		 
		$Val = $Vars[$Index];
		$VarName = urldecode( GetStrBefore($Vars[$Index], "=", false) );		
		$VarValue = urldecode( GetStrAfter($Vars[$Index], "=", false) );
		
		//echo "*****$Val*******$VarName********$VarValue*************\r\n";
		
		DynVar_SetValue($Res, $VarName, $VarValue);
		
		$Index++;		
	}
	return $Res;	
}

function DynVar_PackVars($SrcPack, $NewPack)
{	
/*	$Res = $SrcPack;
	
	if ($SrcPack)
	{
		if (is_array($SrcPack) && is_array($NewPack))
		{
			//$Res = array_merge($SrcPack, $NewPack);			
			foreach ($NewPack as $Key => $Val)
			{
				DynVar_SetValue($Res, strtoupper($Key), $Val);
			}
		}
	}
	return $Res; */
	$Res = $SrcPack;
	if ($SrcPack && $NewPack)
	foreach ($NewPack as $NewKey => $NewVal)
	{			
		if (isset( $Res[$NewKey] ))
		{
			if (is_array($NewVal))
			{
				if (is_array($Res[$NewKey]))
					$Res[$NewKey] = DynVar_PackVars($Res[$NewKey], $NewVal);
				else 
					$Res[$NewKey] = $NewVal;
			}
			else
				$Res[$NewKey] = $NewVal;
		}
		else 
			$Res[$NewKey] = $NewVal;
	}
	return $Res;
	
}



function DynParam($VarName, $DefVarName, $Def = "", $NewVarName = "")
{
	$Res = DynVar_Get($VarName, DynVar_Get($DefVarName, $Def));
	if ($NewVarName != "")
		DynVar_Set($NewVarName, $Res);
	return $Res;
}

function DynParamCmd($VarName, $DefVarName, $Def = "", $NewVarName = "")
{
	$Res = strtoupper( trim( DynVar_Get($VarName, DynVar_Get($DefVarName, $Def)) ) );
	if ($NewVarName != "")
		DynVar_Set($NewVarName, $Res);
	return $Res;
}

?>