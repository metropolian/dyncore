<?php

	$DynPageTitle = "";

	$DynPageErrors = null;

	$DynTemplate = array(
		'base' => 'templates',
		'path' => 'main',
		'title' => '',
		'content-type' => 'utf-8',
		
		'meta' => array(),
		
		'func' => 'functions.php',
		'head' => '',
		'foot' => '',
		
		'fext' => '.php',
		
		'current_scope' => '',
		'scopes' => array()
	);

	function Html_TemplatePath($FName = '')
	{	global $DynTemplate;
	 
	 	$res = $DynTemplate['base'] . '/' . $DynTemplate['path'];
	 	if ($FName != '')
			$res .= '/' . $FName;
		return $res;
	}

	function Html_Init($TemplateName, $Options = null)
	{	global $DynTemplate;

	 	if ($TemplateName != null)
	 		$DynTemplate['path'] = $TemplateName;
 
	 	if ( is_array( $Options ) )
			foreach($Options as $K => $V)
			{
				$DynTemplate[$K] = $V;
			}
     
        LogWrite('core', 'html_init');
	}



	function Html_Title($Title = null)
	{	global $DynTemplate;	 	
	 	if ($Title != '')
			$DynTemplate['title'] = $Title;
	 	return $DynTemplate['title'];
	}

	function Html_Meta($K = "", $V = "")
	{	global $DynTemplate;
	
		if ($K != "")
		{
			return Html_SetMeta($K, $V);
		}
	
		if (is_array($DynTemplate['meta']))
		{
			$Res = "";	
			foreach ($DynTemplate['meta'] as $Key => $Value)
			{
				if ($Key == "content-type")
					$Res .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$Value\" />\r\n";
				else
					$Res .= "<meta name=\"$Key\" content=\"$Value\" />\r\n";
			}
			return $Res;
		}
		return "";
	}
	
	function Html_Head()
	{	global $DynTemplate;

		if (is_array($DynTemplate['scripts']))
		{
			$JsValue = "";
			$CssValue = "";
			$Res = "";	
			foreach ($DynTemplate['scripts'] as $Script)
			{
				$Src = $Script['src'];
				$Type = $Script['type'];
	
				if ($Script['value'] == "")
				{
					if (StrContains($Type, "js") || StrContains($Type, "java"))						
						$Res .=	"<script type=\"text/javascript\" src=\"" . $Src . "\"></script>\r\n";						
					if (StrContains($Type, "css"))
						$Res .=	"<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $Src . "\" />\r\n";
				}
				else
				{
					if (StrContains($Type, "js") || StrContains($Type, "java"))						
						$JsValue .= $Script['value'];
					if (StrContains($Type, "css"))
						$CssValue .= $Script['value'];
				}
			}
			
			if ($JsValue != "")
				$Res .=	"<script type=\"text/javascript\">\r\n{$JsValue}\r\n</script>\r\n";
			if ($CssValue != "")
				$Res .=	"<style type=\"text/javascript\">\r\n{$CssValue}\r\n</script>\r\n";
			
			return $Res;
		}		
		return "";
	}

	function Html_Render($ScopeName)
	{	global $DynTemplate, $Request, $User, $CurrentUser, $Cache, $Filters;
     
        if (is_array($ScopeName))
        {
            foreach ($ScopeName as $SN) 
            {
                Html_Render($SN);
            }
            return;
        }
     
     
        LogWrite('core', 'html_render ' . $ScopeName);
	 	if ( isset( $DynTemplate['scope'][$ScopeName] ) )
		{
			echo $DynTemplate['scope'][$ScopeName];
		}
		else
		{
			$Fname = Html_TemplatePath( $ScopeName );
			
			if (!is_file($Fname))
			{
				$Fname_ext = $Fname . $DynTemplate['fext'];
				if (is_file($Fname_ext))
					$Fname = $Fname_ext;
			}
            
            if (is_file($Fname))
			     require( $Fname ); 
            else
                echo "Scope missing: {$ScopeName}";
		}
	}
	
	function Html_Begin($ScopeName = '')
	{	global $DynTemplate;
	 
		Html_Buffer_Start();
	 
	 	if ($ScopeName != '')
			$DynTemplate['current_scope'] = $ScopeName;
		else
		{	 
			$fname = $DynTemplate['func'];
			if ( $fname != '')
				Html_Render( $fname );
		}
	}
	
	function Html_End()
	{	global $DynTemplate, $Request;
	 
	 	$contents = Html_Buffer_Finish();
	 
	 	if ($DynTemplate['current_scope'] != null)
		{
			$DynTemplate['scope'][$DynTemplate['current_scope']] = $contents;
			$DynTemplate['current_scope'] = null;
			return $contents;
		}
	 
		Html_Meta("content-type", $DynTemplate['content-type']);
	 
	 	$fname = $DynTemplate['head'];
	 	if ( $fname != '')
	 		Html_Render( $fname );
	 	else
		{	 
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			echo "\r\n";
			echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\r\n";
			echo "<head>\r\n";
			echo Html_Meta();
			echo Html_Head();
			echo "<title>{$Title}</title>\r\n";
			echo "</head>\r\n";
			echo "<body>\r\n";
		}
	 
	 //	var_dump($Request);
	 
	 	echo $contents;
		
	 	$fname = $DynTemplate['foot'];
	 	if ( $fname != '')
	 		Html_Render( $fname );
	 	else
		{
			echo "</body>\r\n";
			echo "</html>";
		}
	}
	
	function Html_SetMeta($Key, $Value)
	{	global $DynTemplate;
		if ($Value === null)
			unset($DynTemplate['meta'][$Key]);
		else
			$DynTemplate['meta'][$Key] = $Value;		
	}
	

	function Html_Style($FName, $Type = "")
	{
		return Html_Script($FName, $Type);
	}
	
	function Html_Script($FName, $Type = "")
	{	global $DynTemplate;
	
		if ($Type == "")
		{
			if ( StrContains($FName, ".js") )
				$Type = "js";
			if ( StrContains($FName, ".css") )
				$Type = "css";
		}
		
		if ( is_array($DynTemplate['scripts']) )
		{
			foreach($DynTemplate['scripts'] as $Script)
			{
				if ($Script['src'] == $FName)
					return false;
			}
		}
		
		$DynTemplate['scripts'][] = array(
			'src' => $FName,
			'type' => $Type);
		return true;
	}
	
	function Html_ScriptData($Data, $Type)
	{	global $DynTemplate;
	 
		$DynTemplate['scripts'][] = array(
			'value' => $Data,
			'type' => $Type);
	}
	
	

	function Html_Buffer_Start()
	{
		ob_start();
	}
	
	function Html_Buffer_Finish()
	{
		$res = ob_get_contents();
		ob_end_clean();
		return $res;
	}
	
?>