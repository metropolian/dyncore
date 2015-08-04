<?php
/**
* DynDbManager
*
* Database instance manager
*
* @package  DynCore
* @author   Metro Kobchaipuk <metropolian@live.com>
* @version  $Revision: 1.0 $
*/

	$DynDBs = array();

    if (is_array($Configs['db']))
        foreach($Configs['db'] as $Name => $C)
            $DynDBs[$Name] = new DynDb(
                $C['type'], $C['host'], $C['user'], $C['pass'], $C['name'] );

    $DB = reset($DynDBs);

	function GetDB($Instance = 'main')
	{	global $DynDBs;
     
		return $DynDBs[$Instance];
	}

?>