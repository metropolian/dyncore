<?php

	$CurDir = getcwd();	
	chdir( dirname(__FILE__) );

    require_once("dynlogging.php");
	
	require_once("dynfilters.php");
	require_once("dyndatabase2.php");
	require_once("dynutils.php");
	require_once("dyndbmanager.php");

	chdir( $CurDir );	
?>