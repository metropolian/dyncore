<?php

	$DynDBs = array();

	if (defined('DB_TYPE'))
	{
		$DynDBs['main'] = new DynDb(DB_TYPE, DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);		
		
		$DB = GetDB('main');
	}

	function GetDB($Instance = 'main')
	{	global $DynDBs;
		return $DynDBs[$Instance];
	}

?>