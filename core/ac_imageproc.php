<?php
	
	ini_set("memory_limit","128M");

	function image_load_file($FName, $FExt = "")
	{
		if (!is_file($FName))
			return null;
			
		if ($FExt == "")
		{
			$FInfo = pathinfo($FName);		
			$FExt = $FInfo['extension'];
		}		
	
		if (($FExt == "jpg") || ($FExt == "jpeg") || ($FExt == "image/jpg") || ($FExt == "image/jpeg"))
			$Res = imagecreatefromjpeg($FName);
		else if ($FExt == "png" || ($FExt == "image/png"))
			$Res = imagecreatefrompng($FName);
		else if ($FExt == "gif" || ($FExt == "image/gif"))
			$Res = imagecreatefromgif($FName);
		else
		{		
			$Info = getimagesize($FName) ;
			if ($Info)
				$Res = image_load_file($FName, $Info['mime']);
		}			
		return $Res;
	}
	
	function image_create_rescale($HSrc, $W, $H, $SaveRatio = 0)
	{		
		if (($W <= 0) || ($H <= 0) )
			return null;
		
		if ($SaveRatio == 2 )
		{
			$SrcW = imagesx($HSrc);
			$SrcH = imagesy($HSrc);		
			$W = $SrcW / max($SrcW / $W, $SrcH / $H) ;
			$H = $SrcH / max($SrcW / $W, $SrcH / $H) ;
			$SaveRatio = 0;
		}
		
		$HDest = imagecreatetruecolor($W, $H);		
		if ( image_render($HDest, $HSrc, $W, $H, $SaveRatio) )		
			return $HDest;
		return null;
	}
	
	function image_render($HDest, $HSrc, $W, $H, $SaveRatio = 0)
	{
		if ( ! $HDest )
			return null;
			
		$SrcW = imagesx($HSrc);
		$SrcH = imagesy($HSrc);		
				
		if ( ($SrcW <= 0) || ($SrcH <= 0) )
			return null;			
		if ( ($W <= 0) || ($H <= 0) )
			return null;
			
		if ($SaveRatio > 0 )
		{
			$XW = $SrcW / max($SrcW / $W, $SrcH / $H) ;
			$XH = $SrcH / max($SrcW / $W, $SrcH / $H) ;
			$StX = ($W * 0.5) - ($XW * 0.5) ;
			$StY = ($H * 0.5) - ($XH * 0.5) ;
		}
		else
		{
			$XW = $W;
			$XH = $H;
		}
			
		$BgColor = imagecolorallocatealpha($HDest, 255, 255, 255, 127);		
		imagefill($HDest, 0, 0, $BgColor);
		imagecolortransparent($HDest, $BgColor );
		imagealphablending($HDest, false);
		imagesavealpha($HDest, true);	
		$Res = imagecopyresampled($HDest, $HSrc, $StX, $StY, 0, 0, $XW, $XH, $SrcW, $SrcH);
		return $Res;
	}	
	
	function image_render_overlay($HDest, $HSrc, $StX, $StY, $SrcW = 0, $SrcH = 0)
	{
		if ($SrcW <= 0)
			$SrcW = imagesx($HSrc);
		if ($SrcH <= 0)
			$SrcH = imagesy($HSrc);
		
		$DestW = imagesx($HDest);
		$DestH = imagesy($HDest);

		if ($StX < 0)
			$StX = $DestW - $SrcW - (-$StX);
		if ($StY < 0)
			$StY = $DestH - $SrcH - (-$StY);
	
		imagecopyresampled($HDest, $HSrc, $StX, $StY, 0, 0, $SrcW, $SrcH, $SrcW, $SrcH);
		return $HDest;
	}
	
	
	function image_rotate($HSrc, $Angle )
	{
		$Res = imagerotate ($HSrc, $Angle, imagecolorallocatealpha($HSrc,0 , 0, 0, 127), 1 );
		imagealphablending($Res, false);
		imagesavealpha($Res, true); 		
		return $Res;
	}
	
	function image_create_rescale_file($SrcFName, $DestFName, $W, $H, $SaveRatio)
	{
		$HSrc = image_load_file($SrcFName);
		if ($HSrc)
		{
			if ( ($W > 0) && ($H > 0) )
				$HDest = image_create_rescale($HSrc, $W, $H, $SaveRatio);			
			else
				$HDest = $HSrc;				
				
			if ($HDest)
				return image_save_file($HDest, $DestFName);
		}
		return null;
	}

	
	function image_save_file($HSrc, $FName, $Type = "")
	{
		if ( ! $HSrc)		
			return null;
			
		if ($Type == "")
		{
			$FInfo = pathinfo($FName);
			$Type = $FInfo['extension'];			
		}
		
		
		if ($Type == "png")
			$Res = imagepng( $HSrc, $FName );
		else if (($Type == "jpg") || ($Type == "png"))
			$Res = imagejpeg( $HSrc, $FName );
		else if (($Type == "gif") )
			$Res = imagegif( $HSrc, $FName );
		if ($Res)
			return $HSrc;
		return null;
	}
	
	
	
	function image_process_filter($HDest, $Filter, $Div, $Offset)
	{
		if (imageistruecolor($HDest))
		{			
			imageconvolution($HDest, $Filter, $Div, $Offset);
			
			imagealphablending($HDest, false);
			imagesavealpha($HDest, true); 		

			return true;
		}
		return false;		
		
	}
	
	function image_process_grassian($HDest)
	{
		$Filter = array( array(1, 2, 1),
				array(2, 4, 2),
				array(1, 2, 1)); 
		return image_process_filter($HDest, $Filter, 16, 0);
	}
	
	function image_process_blur($HDest)
	{
		$Filter = array( array(1, 1, 1),
				array(1, 1, 1),
				array(1, 1, 1)); 
		return image_process_filter($HDest, $Filter, 9 , 0);
	}
	
	function image_process_sharpen($HDest)
	{
		$Filter = array( array(-1, -1, -1),
				array(-1, 16, -1),
				array(-1, -1, -1)); 
		return image_process_filter($HDest, $Filter, 8 , 0);
	}
	
	function ImageProcess_TintColor($HDest, $Red, $Green, $Blue)
	{
		imagefilter($HDest, IMG_FILTER_COLORIZE, $Res * 255, $Green * 255, $Blue * 255);		
	}

	function image_textout($HDest, $Size, $Angle, $StX, $StY, $Color, $FontName, $Text, $Align = 'left')
	{	
		if (!is_file($FontName))
			return false;
	
		$Rect = imagettfbbox($Size, $Angle, $FontName, $Text );
		$FW = $Rect[6];
		$FH = $Rect[1] - $Rect[5];		
		if ($Align == 'right')		
			$FW = $Rect[4] ;
		else
		if ($Align == 'center')
			$FW = ($Rect[4] - $Rect[6]) / 2;
		imagettftext($HDest, $Size, $Angle, $StX - $FW, $StY + $FH, $Color, $FontName, $Text);	
		
	}

	
	/*
	$Res = image_create_rescale_file("wp-logo.png", "larger.png", 900, 200, 1);
	
	if ($Res)
	{
		header("Content-type: image/png");		
		imagepng($Res);
	}  */

?>