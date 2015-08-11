<?php
/**
* DynCore initialization file
*
* bootstrap dyncore system with Configs
*
* @package  DynCore
* @author   Metro Kobchaipuk <metropolian@live.com>
* @version  $Revision: 1.0 $
*/

    if (isset($Prev_InitDir))
		return;

	$Prev_InitDir = getcwd();
	$InitDir = dirname(__FILE__);	
	chdir( $InitDir );

	require_once("dyn.php");	

    LogWrite('core', 'start');

    $Error_reporting = $Configs['error_reporting'];
    if ($Error_reporting > 0)
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
    }
    else
    {        
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }

	require_once("requests.php");		
	require_once("htmlmeta.php");		
	require_once("htmlbase.php");
	require_once("htmlfunc.php");

    require_once('html/forms.php');
    require_once('html/lists.php');
    require_once('html/panels.php');


	require_once('users/users.php');
	require_once('users/userdata.php');
    require_once('storage/userfile.php');

    if (is_array($Configs['modules']))
        foreach ($Configs['modules'] as $M)
            if (is_string($M))
                require_once($M);

	
	chdir( $Prev_InitDir );
	$Prev_InitDir = null;
	

?>