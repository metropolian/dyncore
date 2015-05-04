<?php
/* ----------------------------------------------------- 

     --------------------------------------------------- */

	$CurDir = getcwd();	
	chdir( dirname(__FILE__) );

	require_once("ac_imageproc.php");
	require_once("ac_base32.php");
	
	chdir( $CurDir );	
	
?>