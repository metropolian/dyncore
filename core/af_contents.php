<?php
	require_once("af.php");
	
	function LoadContent($Url, $FName = "")
	{
		$useragent= "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $Url); 
        curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        //curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $output = curl_exec($ch);         
        $info = curl_getinfo ($ch);
        curl_close($ch);           
		if ($output)
		{
			if (! empty( $FName ) )
			{
				$hdest = fopen($FName, "w");
				if ($hdest)
				{
					fwrite($hdest, $output);
					fclose($hdest);
				}
			}
			return $output;
		}
		return null;
	}
	
	function Is_FileOlder($FName, $UpdateSecs)
	{
		$current_time = time();
		$local_updated = is_file($FName) ? filemtime($FName) : 0;			
		$diff_time = $current_time - $local_updated;
		return ($diff_time >= $UpdateSecs);
	}
	
	function LoadContentCaching($Url, $FName, $UpdateSecs)
	{
		$current_time = time();
		$local_updated = is_file($FName) ? filemtime($FName) : 0;			
		$diff_time = $current_time - $local_updated;
		if ($diff_time >= $UpdateSecs)
		{
			$local_data = LoadContent($Url, $FName);
		}
		else
		{
			$local_data = null;
			if (is_file($FName))
				$local_data = file_get_contents($FName);		
				
			if ( !$local_data )
				$local_data = LoadContent($Url, $FName);
		}
		return $local_data;
	}
	
	
	function Get_UploadedFiles($UploadName)
	{
		$Res = array();
		if ( isset( $_FILES[$UploadName] ) )
		{
			$FInfo = $_FILES[$UploadName];
			if ( ! is_array($FInfo))
			{
				if ($Error == UPLOAD_ERR_OK) 
			    {
					$Res[] = array(
						"name" => $FInfo['name'],
						"key" => 0,
						"type" => $FInfo['type'],
						"size" => $FInfo['size'],
						"error" => $FInfo['error'],
						"tmp_name" => $FInfo['tmp_name'] 
						);
						
				}
			}
			else
			{	
				if ( ! is_array($FInfo['name']))
				{
					$Res[] = array(
						"name" => $FInfo['name'],
						"key" => 0,
						"size" => $FInfo['size'],
						"error" => $FInfo['error'],
						"type" => $FInfo['type'],
						"tmp_name" => $FInfo['tmp_name'] 
						);
				}
				else
				{				
					foreach ($FInfo['name'] as $Key => $Name) 
					{
						$Tmp_name = $FInfo['tmp_name'][$Key];				
						$FName = $FInfo['name'][$Key];
						$Type = $FInfo['name'][$Key];
						$Size = $FInfo['size'][$Key];
						$Error = $FInfo['error'][$Key];	
						if ($Error == UPLOAD_ERR_OK) 
						{
							$Res[] = array(
								"name" => $FName,
								"key" => $Key,
								"size" => $Size,
								"error" => $Error,
								"type" => $Type,
								"tmp_name" => $Tmp_name 
								);
								
						}
					}
				}
			}
		}
		
		return $Res;

	}
					  
?>