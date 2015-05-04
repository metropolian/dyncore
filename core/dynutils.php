<?php
 
	/* Input Data Functions */
	function ClearMagicQuote( $Inp )
	{
		if (get_magic_quotes_gpc() > 0)
			$Inp = stripslashes ( $Inp );
		return $Inp;
	}
	
	function ClearMagicQuoteArray( $Inps )
	{
		if (is_array($Inps))
		{	
			$Res = $Inps;		
			foreach ($Res as & $Val)
			{		
				$Val = ClearMagicQuote($Val);
			}
			return $Res;
		}
		return $Inp;
	}

	function ReadPOST( $V, $D = "" )
	{		
		return ClearMagicQuote( isset( $_POST[ $V ] ) ? $_POST[ $V ] : $D );
	}

	function ReadGET( $V, $D = "" )
	{		
		return ClearMagicQuote( isset( $_GET[ $V ] ) ? $_GET[ $V ] : $D );
	}
	
	function ReadCOOKIE( $V, $D = "")
	{
		return ClearMagicQuote( isset( $_COOKIE[ $V ] ) ? $_COOKIE[ $V ] : $D );
	}

	function ReadSERVER( $V, $D = "")
	{
		return isset( $_SERVER[ $V ] ) ? $_SERVER[ $V ] : $D ;
	}
	
	
	/* Utility Function */
	
	/* for PHP < 4.3 -------------------------------------------- */
    if (!function_exists("stripos")) 
    {
        function stripos($str, $needle, $offset=0)
       {
           return strpos(strtolower($str), strtolower($needle), $offset);
       }
   	} 
   	

    if (!function_exists("str_ireplace")) 
    {
        function str_ireplace($search, $replace, $subject)
       {
           return str_replace(strtolower($search), strtolower($replace), $subject);
       }
    } 
   
   	if (!function_exists("scandir"))
   	{
       function scandir($dir, $sort = 0)
       {
            if (! is_dir($dir))
                return NULL;        

            $dh  = opendir($dir);
			while (false !== ($filename = readdir($dh))) {
			   $retfiles[] = $filename;
			}
			closedir($dh);
			sort($retfiles);
			return $retfiles;
		}
    }
    
    function str_contains($str, $val)
    {
    	return (strpos($str, $val) !== false);
    }
    
	/* String Management -------------------------------------------- */
    function IsStrEmpty($Src)
    {
    	return strlen( trim($Src) ) <= 0;
    }
    
    function IsStrNotEmpty($Src)
    {
    	return strlen( trim($Src) ) > 0;
    }
    
    function IsInString($Src, $Needle)
    {
    	return ( strpos($Src, $Needle) !== false );
    }
    
    /*  GetStrCombineParams - Combine Parameter  
     * 
     *  Format Example:    $PrefV $Value $SuffV $Spr ...
     * 						(aaaa),(bbbb),(ccccc) 
     * 
     */
    
    function IsStrFilePath($Src)
    {
    	if (strpos("/") === 0)
    		return true;
    		
    	return false;    	
    }
    
    function IsStrFileName($Src)
    {
    	if (strpos($Src, "/") === false)
    		return true;
    		
    	return false;    	
    }
    
	function GetStrCombineParams($Params, $PrefV, $SuffV, $Spr, $StIndex = 0, $Inc = 1, $Max = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = "";
		$Index = $StIndex;
		while($Index < $MaxParams)
		{
			$Res .= $PrefV . $Params[$Index] . $SuffV;
			$Index += $Inc;			
			if ($Index < $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;
	}
	
	/*  --------------------------------------------------------------------------- 
	 *  GetStrCombineKVParams - Combine Key-Value Pair Parameter
	 * 
	 *  Format Example:      $PrefK $Key $SuffK $Mid $PrefV $Value $SuffV $Spr ...
     * 						[keyaaa]=(valueaaa), [keybbb]=(valuebbb), [keyccc]=(valueccc) 
	 * 	 				
	 */
	
	function GetStrCombineKVParams($Params, $PrefK, $SuffK, $Middle, $PrefV, $SuffV, $Spr, $StIndex = 0, $Max = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = "";
		$Index = $StIndex;
		while($Index + 2 <= $MaxParams)
		{
			$Res .= $PrefK . $Params[$Index] . $SuffK;			
			$Index++;
			$Res .= $Middle . $PrefV . $Params[$Index] . $SuffV;			
			$Index++;
			if ($Index + 2 <= $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;
	}
	
	/*  ---------------------------------------------------------------------------
	 * 	GetStrCombineParamsArgs - Combine Parameter by Function Arguments
	 * 
	 *  Format Example:      $PrefK $Key $SuffK $Mid $PrefV $Value $SuffV $Spr ...
     * 						aaaa , bbb , cccc 
	 * 	 				
	 */
	
	function GetStrCombineParamsArgs($Spr)
	{
		$Res = "";
		$Index = 1;
		$Args = func_get_args();		
		$ArgsCount = count($Args);		
		while($Index < $ArgsCount)
		{
			if ($Args[$Index++])
			{
				if ($Res != "")
					$Res .= $Spr;
				$Res .= $Args[$Index];
			}
			$Index++;
		}
		return  $Res;
	}
	
	/*  ---------------------------------------------------------------------------
	 * 	GetStrBefore - Before Token
	 *   
	 * 	 				
	 */
	
	function GetStrBefore($Src, $Before, $Inc = false, $CaseSen = false)
    {
    	if ($CaseSen)
    		$Pos = stripos($Src, $Before);
    	else
    		$Pos = strpos($Src, $Before);
    	    	 
    	if ($Pos !== false)
    	{
    		if ($Inc)
    		{
    	   		$BeforeLen = strlen($Before);
    	   		return substr($Src, 0, $Pos + $BeforeLen);
    		}
    		
    		return substr($Src, 0, $Pos);    		    	    
    	}
    	return "";
    }

    function GetStrAfter($Src, $After, $Inc = false, $CaseSen = false)
    {
    	if ($CaseSen)
    		$Pos = stripos($Src, $After);
    	else
    		$Pos = strpos($Src, $After);
    	    	 
    	if ($Pos !== false)
    	{
    		if (!$Inc)
    		{
    	   		$AfterLen = strlen($After);
    	   		return substr($Src, $Pos + $AfterLen);
    		}
    		
    		return substr($Src, $Pos  );    		    	    
    	}
    	return "";
    }
    
    function GetStrBetween($Src, $StrSt, $StrEn, $Inc = false, $CaseSen = false, $Def = "")
    {
    	if ($CaseSen)
    	{
    		$PosSt = stripos($Src, $StrSt);
   			$PosEn = stripos($Src, $StrEn, $PosSt);
    	}
    	else
    	{
    		$PosSt = strpos($Src, $StrSt);
   			$PosEn = strpos($Src, $StrEn, $PosSt);
    	}
    	
    	if (($PosSt === false) && ($PosEn === false))
    		return $Def;
    	
    	if ($PosEn === false)
    		$PosEn = strlen($Src);

    	if (!$Inc)
    	{
    		if ($PosSt === false)
    			$PosSt = 0;
    		else
    		{
    			$StrStLen = strlen($StrSt);
    			$PosSt += $StrStLen;
    		}
    	}
    	else
    	{
    		$StrEnLen = strlen($StrEn);
    		$PosEn += $StrEnLen;
    	}
    	
    	return substr($Src, $PosSt, $PosEn - $PosSt);
    	
    }
    
    function GetStrBetweenCross($Src, $StrSt, $StrEn, $Inc = false, $CaseSen = false, $Def = "")
    {
    	if ($CaseSen)
    	{
    		$PosSt = stripos($Src, $StrSt);
   			$PosEn = stripos($Src, $StrEn, $PosSt);
    	}
    	else
    	{
    		$PosSt = strpos($Src, $StrSt);
   			$PosEn = strrpos($Src, $StrEn, $PosSt);
    	}
    	
    	if (($PosSt === false) && ($PosEn === false))
    		return $Def;
    	
    	if ($PosEn === false)
    		$PosEn = strlen($Src);

    	if (!$Inc)
    	{
    		if ($PosSt === false)
    			$PosSt = 0;
    		else
    		{
    			$StrStLen = strlen($StrSt);
    			$PosSt += $StrStLen;
    		}
    	}
    	else
    	{
    		$StrEnLen = strlen($StrEn);
    		$PosEn += $StrEnLen;
    	}
    	
    	return substr($Src, $PosSt, $PosEn - $PosSt);    	
    }
    
    /* General Functions */
	function Microtime_Begin()
	{
		return Microtime_GetFloat();		
	}
		
	function Microtime_GetFloat()
	{
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
	}
	
	function Microtime_CatchResult($StTime)
	{
		$EnTime = Microtime_GetFloat();
		return $EnTime - $StTime;		
	}
	
	
    function DefDefault( $V, $D )
	{
		return (isset( $V )) ? $V : $D;
	}

	function ConvLink( $V, $A, $T = "" )
	{
		return "<A " . $T . " HRef=\"$V\">$A</A>";
	}
	
	function GetStrIden( $V )
	{
		$CurVal = 0;
		$Result = 0;
		$MaxLen = strlen( $V );
		
		if ($MaxLen > 0)
		{
	            		for ($Cnt = 0; $Cnt < $MaxLen; $Cnt++)
            			{
                			$CurVal = ord( $V[ $Cnt ] );
                			$Result ^= ((($Result << 3) * $CurVal) + $CurVal);

//				echo $Result . "<br>";
	            		}
	            		$Result *= $MaxLen;
		}
         		return $Result;
		
	}

	function GetStrIdenAsHex( $V )
	{
		return sprintf("%08X", GetStrIden($V) );
	}
	
	/* File Name and Path  Functions */
	function MakeFilePath( $V )
	{
		$Res = $V;
		$VLen = strlen($V);
		if ($VLen > 0)
		{
			if ($V[ $VLen - 1 ] != "/")
		   	$Res .= "/";
		}
		return $Res;				
	}

	function CombineFilePath( $V, $X = "" )
	{
		return MakeFilePath( $V ) . $X ;
	}

	function GetFileName( $V )
	{
		$SpPos = strrpos( $V , "/");
		if ($SpPos !== false)
			return substr( $V, $SpPos + 1 );
		return $V;
	}

	function GetFilePath( $V )
	{
		$SpPos = strrpos( $V , "/");
		if ($SpPos !== false)
			return substr( $V, 0, $SpPos );
		return "";
	}
	
	function GetCurrentDateTime()
	{
		return date('Y-m-d H:i:s');
	}


	/* Convertion Functions */
	function EncQuote($Inp, $EncSingle = true, $EncDouble = true)
	{
		if ($EncDouble)
			$Inp = str_replace('"', "&quot;", $Inp ) ;
		if ($EncSingle)
			$Inp = str_replace("'", "&#039;",  $Inp) ;
		return $Inp;
	}
	
	function EncHtml($Inp)
	{
		return htmlspecialchars($Inp, ENT_QUOTES );
	}

	function EncValues( $Src)
	{
			$MaxArgs = func_num_args();
			if (($MaxArgs < 3) && (strlen( $Src ) == 0))
				return $Src;
		
			$Args = func_get_args();
			
			for ($CntArgs = 1; $CntArgs < $MaxArgs - 1; $CntArgs += 2 )
			{
				$FindStr = $Args [ $CntArgs ] ;
				$RepStr = $Args [ $CntArgs + 1 ] ;
				
				$Src = str_replace( $FindStr, $RepStr, $Src);
			}			
			return $Src;			
	}

	function tis2utf8($tis) 
	{
			for( $i=0 ; $i< strlen($tis) ; $i++ )
			{
	      $s = substr($tis, $i, 1);
	      $val = ord($s);
	      if( $val < 0x80 ){
	         $utf8 .= $s;
	      } elseif ( ( 0xA1 <= $val and $val <= 0xDA ) or ( 0xDF <= $val and $val <= 0xFB ) ){
	         $unicode = 0x0E00 + $val - 0xA0;
	         $utf8 .= chr( 0xE0 | ($unicode >> 12) );
	         $utf8 .= chr( 0x80 | (($unicode >> 6) & 0x3F) );
	         $utf8 .= chr( 0x80 | ($unicode & 0x3F) );
	      }
			}
		return $utf8;
	}
	
	function utf8_to_tis620($string) 
	{
		  $str = $string;
		  $res = "";
		  for ($i = 0; $i < strlen($str); $i++) {
		    if (ord($str[$i]) == 224) {
		      $unicode = ord($str[$i+2]) & 0x3F;
		      $unicode |= (ord($str[$i+1]) & 0x3F) << 6;
		      $unicode |= (ord($str[$i]) & 0x0F) << 12;
		      $res .= chr($unicode-0x0E00+0xA0);
		      $i += 2;
		    } else {
		      $res .= $str[$i];
		    }
		  }
		  return $res;
	}
	
	
	function DynDebug($Val)
	{
		if ($DynDebugOutput)
		{
			if (func_num_args() > 1)
				$Data = implode(func_get_args()) . "\r\n";
			else
				$Data = $Val . "\r\n";
			echo $Data;
			file_put_contents("debug.txt", $Data, FILE_APPEND);
		}
	}

	
	
	function VarDump_String($Var, $TabLv = 0)
	{
		$StrTab = "";
		for ($Cnt = 0; $Cnt < $TabLv; $Cnt++) 
			$StrTab .= "\t";
		
		if (is_null($Var))
			return null;
		if (is_string($Var))
		{
			$Len = strlen($Var);
			
			$Var = str_replace("\r", "{CR}", $Var);
			$Var = str_replace("\n", "{LF}", $Var);
			$Var = str_replace("\0", "{0}", $Var);
			return "(string[$Len]) \t \"$Var\" \r\n";
		}
		if (is_array($Var))
		{
			$Len = count($Var);
			
			if ($Len > 0)
			{
				$Res = "Array[$Len] $StrTab\r\n$StrTab{\r\n";
				foreach($Var as $Key => $Val)
				{
					$Text = VarDump_String($Val, $TabLv + 1);
					$Res .= "$StrTab\t[$Key] => $Text";				
				}
				return $Res . "$StrTab}\r\n\r\n";
			}
			$Res = "Array[$Len] \r\n";			
		}
						
		$Type = gettype($Var);
		return "($Type) $Var \r\n";
	}
	
?>