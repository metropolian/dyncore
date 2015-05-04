<?php 

	function PairedToArray($Paired)
	{
		for($Index = 0; $Index < count($Paired); $Index++)
		{
			if ($Index % 2 == 0)
				$Key = $Paired[$Index];
			else
			{
				$Value = $Paired[$Index];
				$Res[$Key] = $Value;
			}
		}
		return $Res;		
	}
	
	class DynPackRequest
	{
		var $Query;
		
		function DynPackRequest($Src)
		{
			$this->Query = $Src;
		}
		
		function GET($Name, $Def = "", $Filter = null) 
		{
			if (isset($this->Query[$Name]))
			{
				$Value = $this->Query[$Name];
				if (get_magic_quotes_gpc() > 0)
					$Value = stripslashes ( $Value );
			
				if ($Filter != null)
					return DynFilter( $Value, $Filter);
				return $Value;
			}
			return $Def;
		}
		
		function SET($Name, $Val) 
		{
			if ($Val === null)
				unset($this->Query[$Name]);
			else
				$this->Query[$Name] = $Val;
		}
		
		function ArraySET($Array)
		{
			if (is_array($Array))
			{
				foreach($Array as $Key => $Value)
					$this->SET($Key, $Value);
				return true;
			}
			return false;
		}
		
	
		function PairedSET($Paired)
		{
			for($Index = 0; $Index < count($Paired); $Index++)
			{
				if ($Index % 2 == 0)
					$Key = $Paired[$Index];
				else
				{
					$Value = $Paired[$Index];
					$this->SET($Key, $Value);				
				}
			}
		}
		
		function QueryString()
		{
			return http_build_query($this->Query);
		}
		
		function HasQueryString()
		{
			return ( count($this->Query) > 0 );
		}
		
		function BuildQueryString($Vars)
		{
			$NewReq = new DynPackRequest($this->Query);
			if ( $Vars != null) 
			{
				if ( is_array( $Vars ) )
					$NewReq->ArraySET( $Vars );
				else
					$NewReq->PairedSET( func_get_args() );
			}			
			return $NewReq->QueryString();
		}
		
		function BuildURI( $Vars = null )
		{	
			if ( is_array( $Vars ) )
				$QueryString = $this->BuildQueryString( $Vars );
			else
				$QueryString = $this->BuildQueryString( PairedToArray( func_get_args() ) );
				
			if ( strlen($QueryString) > 0 )
			{
				$QueryString = "?" . $QueryString;
			}
			return $_SERVER['SCRIPT_NAME'] . $QueryString;
		}
		
	}
	
	
	/*
	
	if ($_SERVER['REQUEST_METHOD'] == "POST")
	{
		$Request = new DynPackRequest($_GET);		
		//$Request->ArraySET($_POST);
	}
	else */
	$QueryRequest = new DynPackRequest($_GET);
	$Request = new DynPackRequest($_REQUEST);

		
	//var_dump( $Request->BuildURI("a", "b", "c", null) );
	
	//var_dump( $Request->BuildURI( array("page" => null, "a" => "b", "c" => "d") ) );
	
	function RemakeLink()
	{		
		$NewReq = new DynPackRequest(null); 		
		
		return ( $NewReq->BuildURI(  PairedToArray( func_get_args() ) ) );
	}
	
	function MakeLink()
	{	global $QueryRequest;
		
		return ( $QueryRequest->BuildURI(  PairedToArray( func_get_args() ) ) );
	}
	
	//var_dump( MakeLink("a", "c") );
	
	function Redirect($Dest)
	{
		header("Location: " . $Dest);
		
		while( ob_get_level() )
			ob_end_clean();
		
		echo "<html><head><meta http-equiv=\"refresh\" content=\"0; url={$Dest}\"></head>" .
			"<body><a href=\"{$Dest}\">Go to new page.</a></body>";
		
		exit();
	}
	
		
?>