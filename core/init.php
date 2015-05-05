<?php

	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	if ($Prev_InitDir)
		return;

	//$Prev_InitDir = getcwd();
	//$InitDir = dirname(__FILE__);	
	//chdir( $InitDir );

	require_once("dyn.php");	

	require_once("requests.php");		
	require_once("htmlbase.php");
	require_once("htmlfunc.php");	

	require_once('users/users.php');


	//require_once("storage/userfile.php");
	
	//chdir( $Prev_InitDir );
	//$Prev_InitDir = null;
	

?>