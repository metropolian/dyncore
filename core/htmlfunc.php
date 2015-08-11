<?php

	$Current_Html_Form_Attribs = array();
	$Current_Html_Label_Id = '';
	$Current_Html_Label_Data = '';
	

    function Html_Classes($A = null)
    {    
        $Args = func_get_args();
        $res = array();        
        foreach($Args as $A) 
        { 
            if (is_array($A))
            {
                foreach($A as $V)
                    if ($V !== null)
                    $res[] = Html_Classes($V);
            }
            else if ($A != '')
                $res[] = $A;
            
        }
        return implode(' ', $res);
    }

	function Html_Attribs( $A )
	{		
		if (is_array($A))
		{
			$Attrs[] = null;
			foreach($A as $K => $V) 
			{
                if ( is_numeric($K))
                    $Attrs[] = $V;
                else                
                {
                    if ($K == 'class')
                    {
                        if (is_array($V))
                            $V = Html_Classes($V);                        
                        if ($V == '')
                            continue;
                        
                        /*
                        if (is_array($V))
                        {
                            $Vs[] = null;
                            foreach($V as $Va)
                                $Vs[] = trim($Va);
                            $V = trim(implode($Vs, ' '));
                        }
                        else
                            if ($V == '')
                                continue;
                        */
                    }
                
                    $Attrs[] = "{$K}=\"{$V}\"";
                }
			}			
			return trim(implode($Attrs, ' '));
		}
		return $A;
	}
	
	function Html_Element( $TagName, $Attribs = null, $InnerHtml = null )
	{		
        if (in_array($TagName, array('img', 'input', 'br', 'hr', 'link', 'base', 'embed')))
            $C = ' /';
            
		$A = Html_Attribs($Attribs);
		if ( empty( $A ) )
            $res = "<{$TagName}{$C}>";
		else        
            $res = "<{$TagName} {$A}{$C}>";
		
		if ( $InnerHtml !== null  )
			$res .= $InnerHtml . "</{$TagName}>";
		return $res;
	}
	
	
	
	function Html_ClearInfo()
	{	global $DynPageInfos;
	
		$DynPageInfos[] = null;
	}
	
	function Html_SetInfo($Msg)
	{	global $DynPageInfos;
	
		$DynPageInfos[] = $Msg;
	}
	
	function Html_ListInfos( $Attrs = array('id' => 'info', 'class' => 'alert alert-info') )
	{	global $DynPageInfos;
		if ( !is_array( $DynPageInfos ) )
			return;
			
		$res = Html_Element( 'ul', $Attrs );
		foreach($DynPageInfos as $E)
			$res .= "<li>{$E}</li>\r\n";
		$res .= "</ul>\r\n";
		echo $res;
	}
	
	
	function Html_ClearErrors()
	{	global $DynPageErrors;	
		$DynPageErrors = null;
	}
	
	function Html_SetError($Msg)
	{	global $DynPageErrors;
	
		$DynPageErrors[] = $Msg;
	}
	
	function Html_Errors()
	{	global $DynPageErrors;
		return $DynPageErrors;
	}
	
	function Html_HasErrors()
	{	global $DynPageErrors;
		return count($DynPageErrors) > 0;
	}
	
	function Html_ListErrors( $Attrs = array('id' => 'error', 'class' => 'alert alert-danger') )
	{	global $DynPageErrors;
		if ( !is_array( $DynPageErrors ) )
			return;
        $res = Html_Element( 'div', $Attrs);
		$res .= "<ul>\r\n";
		foreach($DynPageErrors as $E)
			$res .= "<li>{$E}</li>\r\n";
		$res .= "</ul></div>\r\n";
		echo $res;
	}
	
	
	
	
	
	
	function Html_Table( $Attrs = array(), $Headers = array() )
	{
		$A = array( 
					'cellspacing' => 0,
					'cellpadding' => 0,
					'border' => 0
				);
				
		$A = array_merge($A, $Attrs);
		$Res = Html_Element('table', $A);
	
		if ( is_array( $Headers )  )
		{	
			if ( count( $Headers ) )
			{
				$Res .= "<tr>";
				foreach($Headers as $H)
					$Res .= "<th>{$H}</th>";
				$Res .= "</tr>";
			}
		}
		
		
		echo $Res;			
	}
	
	function Html_TR( $Values )
	{
		$Res = "<tr>";
		if ( is_array($Values ) )
		{
			foreach($Values as $V)
				$Res .= "<td>{$V}</td>";
		}
		else
			$Res .= "<td>{$Value}</td>";
		$Res .= "</tr>";
		echo $Res;
	}
	
	function Html_Table_End()
	{
		echo "</table>";
	}
	
	
	function Html_Form( $Action, $Method = 'GET', $Attrs = array() )
	{	global $Current_Html_Form_Attribs;
		$A = array( "method" => $Method, "action" => $Action );
		if (strtoupper($Method) == 'POST')
			$A['enctype'] = "multipart/form-data";
		$A = array_merge($A, $Attrs);
		$Tags = Html_Attribs($A);
		$Current_Html_Form_Attribs = $Tags;
		
		$Res = Html_Element('form', $A );
		echo $Res;
	}	
	
	function Html_Form_End()
	{
		$Res = "</form>";
		echo $Res;
		$Current_Html_Form_Attribs = null;
	}
	
	function Html_Form_Group($Text = '', $Attrs = array())
	{
		$Res = "<fieldset>";
		if ($Text != '')
			$Res .= "<legend>{$Text}</legend>\r\n";
		echo $Res;
	}
	
	function Html_Form_Group_End()
	{
		echo "</fieldset>";
	}

	function MakeSelect( $Name, $Tb, $ColValue, $ColName, $DefValue = null )
	{
		$Res = "<select name=\"$Name\" id=\"$Name\">";
		foreach( $Tb as $Tr )
		{
			$Value = $Tr[$ColValue];
			$Name = $Tr[$ColName];
			
			$Def = ($Value == $DefValue) ? "default" : "";

			$Res .= "<option value=\"{$Value}\" {$Def}>{$Name}</option>";
		}
		
		$Res .= "</select>";
		return $Res;
	}
	
	function Html_A( $Href, $Text = '' )
	{
		echo Html_A_Tag( $Href, $Text );
	}
	
	function Html_A_Tag( $Href, $Text = '' )
	{
		if ($Text == '')
			$Text = htmlentities( $Href, ENT_QUOTES );
	
		$Res = "<a href=\"{$Href}\">{$Text}</a>";
		
		return $Res;
	}

	function Html_Label( $Title, $Id = '', $MergeWithInput = true )
	{   global $Current_Html_Label_Id, $Current_Html_Label_Data;
	
		$Res = "<label for=\"{$Id}\" class=\"control-label col-sm-2\">{$Title}</label>";
		if (($Id != '') && ( $MergeWithInput) )
		{
			$Current_Html_Label_Data = $Res;
			$Current_Html_Label_Id = $Id;
		}
		else
			echo $Res;
	}
	
	function Html_Input($Type, $Name, $Value = '', $Id = '', $Ops = '', $Attrs = array())
	{
		echo Html_Input_Tag($Type, $Name, $Value, $Id, $Ops, $Attrs);
	}
	
	function Html_Input_Tag($Type, $Name, $Value = '', $Id = '', $Ops = '', $Attrs = array())
	{	global $Current_Html_Label_Id, $Current_Html_Label_Data;

		if ($Id == '')
			$Id = $Name;
			
		if ($Type == 'date')
		{
			$Type = 'text';
		}
		
		if ($Type == 'int')
		{
			$Attrs['class'] .= 'integer';
			$Type = 'text';
		}
     
		$Attrs['name'] = $Name;

        if ($Id != '')
		  $Attrs['id'] = $Id;
     
        if ($Value !== null)
            $Value = htmlspecialchars($Value, ENT_QUOTES, "UTF-8");
        else
            $Value = '';
			
		$Res = '';
		
        switch($Type)
        {
            case 'checkbox':
            
                if ($Value == $Ops)
                    $Attrs[] = 'checked';
                $Attrs['type'] = 'checkbox';
                $Attrs['value'] = $Ops;
                    
                $Res .= Html_Element('input', $Attrs);
                break;
            
            case 'submit':
                $Attrs['type'] = 'submit';                
                $Text = $Attrs['text'];
                if ($Text == '')
                    $Text = $Value;
                if ($Text === null)
                    $Text = '';
            
                LogWrite('debug', $Attrs);
                $Res .= Html_Element('button', $Attrs, $Text);
                break;
            
            case 'image':
                $Attrs['src'] = $Attrs['value'];
                $Res .= Html_Element('img', $Attrs);
                break;
                
            
            case 'button':                
                $Attrs['type'] = 'submit';
                
                $Res .= Html_Element('button', $Attrs, $Value);
                break;
            
            case 'select':
            
                $Res .= Html_Element('select', $Attrs);
                if ( is_array($Ops) )
                {
                    foreach($Ops as $K => $V)
                    {					
                        $Def = ($Value == $K) ? 'selected' : '';
                        $Res .= "<option value=\"{$K}\" {$Def}>{$V}</option>";
                    }
                }
                $Res .= "</select>";
                break;
        
            case 'textarea':
                        
                $Res .= Html_Element('textarea', $Attrs, $Value);
                break;
            
        
            
            case 'radio':

                if ($Value == $Ops)
                    $Attrs[] = 'checked';
                $Attrs['type'] = 'radio';
                $Attrs['value'] = $Ops;
            
                $Res .= Html_Element('input', $Attrs);
               break; 
            
            default:
                $Attrs['type'] = $Type;
                $Attrs['value'] = $Value;

                $Res .= Html_Element('input', $Attrs);
                break;
		}
	
		$Res .= "\r\n";
		
		return $Res;
	}
	
	
?>