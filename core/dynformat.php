<?php

	require_once('dynfilters.php');

	
	$Formats = new DynFilterCollection();

	function GetFormats()
	{	global $Formats;	 	
	 	return $Formats;
	}

?>