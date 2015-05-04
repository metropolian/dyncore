<?php

	define("TEXTVALID_CHARLOWER",  1);
	define("TEXTVALID_CHARUPPER",  2);
	define("TEXTVALID_CHAR",  4);
	define("TEXTVALID_DEFAULT",  255);
	define("TEXTVALID_NUMERIC",  8);
	define("TEXTVALID_MULTILINE",  16);
	

	
	function DynText_RegExPos($RegEx, $Text)
	{
		preg_match($RegEx, $Text, $Res, PREG_OFFSET_CAPTURE);
			
		if (is_array($Res))
		{
			if (isset($Res[0]))
			{		
				return $Res[0][1];
			}
		}
		return false;		
	}

	function DynText_IsValid($Text, $AllowEmpty, $Flags)
	{
		$Text = trim($Text, " ");
		
		if (!$AllowEmpty)
			if (strlen($Text) <= 0)
				return 0;
				
		$Res = 0;
		
		//echo ".$Text............";
		
		if (($Flags & TEXTVALID_MULTILINE) == 0)
		{
			if (strpos($Text, "\r") !== false)
				$Res = 1;
			if (strpos($Text, "\n") !== false)
				$Res = 1;
		}

		
		if (($Flags & TEXTVALID_CHARLOWER) == 0)
		{
			if (DynText_RegExPos("/[A-Z]/", $Text) !== false)
				$Res = 1;
		}

		if (($Flags & TEXTVALID_CHARUPPER) == 0)
		{
			if (DynText_RegExPos("/[a-z]/", $Text) !== false)
				$Res = 1;
		}
		
		if (($Flags & TEXTVALID_NUMERIC) == 0)
		{
			if (DynText_RegExPos("/\d/", $Text) !== false)
				$Res = 1;
		}
		

		return $Res;
	}
	
	function DynText_ToStorage($Text)
	{
		return mysql_real_escape_string($Text);
	}
	
	function DynText_ToHtml($Text)
	{
		return htmlentities($Text, ENT_QUOTES);		
	}
	
	function DynText_MakeMetaLink($Src)
	{
		return
		str_replace(array(" ", "\r\n", "\r", "\n", "_", ":", "@", "$", "!", "^", "*", "#", "+"), "-", 
			trim(
				strtolower($Src)
				)
				);	
	}
	
	
	function DynText_DateReformat($Inp, $Type)
	{
		
	}
	
	/*
	
	header("Content-type: text/plain");
	

	
	echo " 1 result: " . DynText_IsValid("" , false, 0) . "<br>\r\n";
	echo " 2 result: " . DynText_IsValid(" sdfsd\r\n \r\n \r\n " , false, TEXTVALID_MULTILINE | TEXTVALID_CHAR) . "<br>\r\n";
	echo " 3 result: " . DynText_IsValid(" sadfsd\r\n \r\n a csf's " , false, TEXTVALID_CHAR) . "<br>\r\n";
	echo " 4 result: " . DynText_IsValid("adsfs" , false, TEXTVALID_CHARLOWER) . "<br>\r\n";
	echo " 5 result: " . DynText_IsValid("ADSFF " , false, TEXTVALID_CHARUPPER) . "<br>\r\n";
	echo " 6 result: " . DynText_IsValid("adsfs" , false, TEXTVALID_CHARUPPER) . "<br>\r\n";
	echo " 7 result: " . DynText_IsValid("ADSFF " , false, TEXTVALID_CHARLOWER) . "<br>\r\n";
	echo " 8 result: " . DynText_IsValid("ADSFsdafsdfF " , false, TEXTVALID_CHAR) . "<br>\r\n";
	echo " 9 result: " . DynText_IsValid(" \r\n \r\n adsfs" , false, TEXTVALID_CHARUPPER) . "<br>\r\n";
	echo "10 result: " . DynText_IsValid(" \r\n \r\n ADSFF " , false, TEXTVALID_CHARLOWER) . "<br>\r\n";
	echo "11 result: " . DynText_IsValid(" asf121ds" , false, TEXTVALID_NUMERIC) . "<br>\r\n";
	echo "12 result: " . DynText_IsValid(" fdsf D 45 " , false, 0) . "<br>\r\n";
	*/
?>