<?php
/*
	array(1) { 
	["product_file"]=> array(5) 
			{ ["name"]=> string(14) "MVG0002842.jpg" 
			  ["type"]=> string(10) "image/jpeg" 
			  ["tmp_name"]=> string(19) "D:\TEMP\phpF100.tmp" 
			  ["error"]=> int(0) 
			  ["size"]=> int(111244) } } */
			  
/*	array(1) { 
	["product_file"]=> array(5) 
		{ 	["name"]=> array(3) 
			{  [0]=> string(14) "MVG0002840.jpg" 
				[1]=> string(14) "MVG0001573.jpg" 
				[2]=> string(14) "MVG0000654.jpg" } 
			["type"]=> array(3) 
			{  [0]=> string(10) "image/jpeg" 
				[1]=> string(10) "image/jpeg" 
				[2]=> string(10) "image/jpeg" } 
			["tmp_name"]=> array(3) 
			{ [0]=> string(19) "D:\TEMP\php5B96.tmp" 
			  [1]=> string(19) "D:\TEMP\php5C23.tmp" 
			  [2]=> string(19) "D:\TEMP\php5D0E.tmp" } 
			 ["error"]=> array(3) 
			 { [0]=> int(0) 
				[1]=> int(0) 
				[2]=> int(0) } 
			["size"]=> array(3) 
			{ [0]=> int(93394) 
			   [1]=> int(132386) 
			   [2]=> int(148032) } } } */

	$StoragePath = dirname(__FILE__);
	$StorageDefaultDir = "media"; 
	
	$ImagePath = "images";
	
	function File_PrepareFullname($Dir, $Name, $AutoCreateFolder = true)
	{			
		$Fullpath = rtrim( $Dir, '/');
	
		if ( $AutoCreateFolder )
		{
			if (! is_dir($Fullpath))
				if (! mkdir($Fullpath, 0777, true))
					return null;
		}
		
		return $Fullpath . "/" . $Name;
	}

	function Get_UploadedFiles($UploadName, $AddInfo = true)
	{
		$Res = array();
		if ( isset( $_FILES[$UploadName] ) )
		{
			$FInfo = $_FILES[$UploadName];
			if (empty($FInfo))
				return null;
			if (is_array($FInfo['name']))
			{			
				foreach ($FInfo['name'] as $Key => $Name) 
				{					
					if (($FInfo['error'][$Key] == UPLOAD_ERR_OK)  &&
						($FInfo['name'][$Key] != ''))
					{
						$Res[] = array(
							"name" => $FInfo['name'][$Key],
							"key" => $Key,
							"size" => $FInfo['size'][$Key],
							"error" => $FInfo['error'][$Key],
							"tmp_name" => $FInfo['tmp_name'][$Key] 
							);
							
					}
				}
			}
			else
			{
				if ($FInfo['error'] == UPLOAD_ERR_OK)
					$Res = array( $FInfo );
					
			}
		}
		
		if (count($Res) > 0)
		{
			foreach($Res as &$R)
			{
				$R['filename'] = (strpos($R['name'], '.') !== false) ? strstr( $R['name'], '.', true ) : $R['name'];
				$R['ext'] = pathinfo($R['name'], PATHINFO_EXTENSION  );	
				
				if ( $AddInfo )
				{
					$FInfo = @getimagesize($R['tmp_name']);
					if ($FInfo)
					{	
						$R['image'] = true;
						$R['mime'] = $FInfo['mime'];
						$R['width'] = $FInfo[0];
						$R['height'] = $FInfo[1];
						$R['imagetype'] = $FInfo[2];
					}
				}
			}
		}
		
		return $Res;
	}
	
	function Detect_ImageFile($Files)
	{
	}
					  
	function Store_UploadedFile($Files, $Path)
	{  	
		$res = array();
		if ( is_array($Files) )
		{
			foreach ($Files as $FInfo)
			{
				 if ($FInfo['error'] == UPLOAD_ERR_OK) 
				 {		
					$Fullname = Get_Fullname( $Path , $FInfo['name']);				
					if ($Fullname != null)
					{
						if ( move_uploaded_file($FInfo['tmp_name'] , $Fullname) );
							$res[] = $Fullname;
					}
				}
			}
		}
		return $res;
	}
	

	
	function Request_StorageFile($FName)
	{
	}
	
	
	
	
?>