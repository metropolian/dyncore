<?php
/**
* DynFilter 
*
* Filtering Data Class
*
* @package  DynCore
* @author   Metro Kobchaipuk <metropolian@live.com>
* @version  $Revision: 1.0 $
*/

	require_once("dynfilters_base.php");
	
	$Filters = new DynFilterCollection();

	$Filters->Add("number", "intval");
	$Filters->Add("int", "intval");
	$Filters->Add("float", "floatval");
	$Filters->Add("intval", "intval");
	$Filters->Add("floatval", "floatval");
	$Filters->Add("trim", "trim");
	$Filters->Add("ltrim", "ltrim");
	$Filters->Add("rtrim", "rtrim");
	$Filters->Add("upcase", "strtoupper");
	$Filters->Add("downcase", "strtolower");
	$Filters->Add("strupper", "strtoupper");
	$Filters->Add("strlower", "strtolower");
	$Filters->Add("plaintext", "html_entity_decode");
	$Filters->Add("stripslash", "stripslashes");
	$Filters->Add("stripslashes", "stripslashes");
	$Filters->Add("crc32", "crc32");
	$Filters->Add("md5", "md5");
	$Filters->Add("sha1", "sha1");
	$Filters->Add("base64", "base64_encode");
	$Filters->Add("debase64", "base64_decode");
	$Filters->Add("url", "urlencode");
	$Filters->Add("urlencode", "urlencode");
	$Filters->Add("urldecode", "urldecode");
	$Filters->Add("strlen", "strlen");
	$Filters->Add("dir", "dirname");
	$Filters->Add("filename", "basename");

	$Filters->Add("noempty", "Filter_NoEmpty");
	//$Filters->Add("parse_url", "parse_url");
	
	// custom function 
	$Filters->Add("html", "Filter_Html");
	$Filters->Add("clean", "Filter_CleanRequest");
	$Filters->Add("numeric", "Filter_Numeric");
	$Filters->Add("graph", "Filter_Graph");
	
	$Filters->Add("domain", "Filter_Domain");
	$Filters->Add("path", "Filter_Path");
	$Filters->Add("querystring", "Filter_Query");

	$Filters->Add("slug", "Filter_Slug");
	
	$Filters->Add("email", '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}/ims', 'regex');
	$Filters->Add("url", '/((http|https|ftp):\/\/)?[A-Z0-9.-]+\.[A-Z]{2,6}/i', 'regex');	
	$Filters->Add("word", '([a-zA-Z]+)', 'regex');
	
/*	$Filters->Add( "data", array('a' => 'AAA', 'b' => 'BBB') );

	var_dump( Filter( array( '', null,
		'test   ', '1', '     email@email.com' ,
		array( 'g a g a  ', '99', ' dsa dds d' )
		), 
		'upcase,trim,slug,noempty') );

	var_dump($Filters);
*/	

	function GetFilters()
	{	global $Filters;
		return $Filters;
	}

	function Filter_Lists()
	{	global $Filters;
		return array_keys( $Filters );
	}
	
	function Filter_Html( $Src )
	{
		return htmlentities( $Src, ENT_QUOTES);
	}
	
	function Filter_NoEmpty( $Src )
	{
		if ( trim($Src) == "" )
			return null;
		return $Src;
	}
	
	function Filter_Numeric( $Src )
	{
		return ( ctype_digit ( $Src ) ) ? $Src : null;
	}

	function Filter_Graph( $Src )
	{
		return ( ctype_graph ( $Src ) ) ? $Src : null;
	}
	
	function Filter_Domain($Src)
	{
		$Info = parse_url($Src);		
		return $Info['host'];
	}

	function Filter_Path($Src)
	{
		$Info = parse_url($Src);		
		return $Info['path'];
	}

	function Filter_Query($Src)
	{
		$Info = parse_url($Src);		
		return $Info['query'];
	}
	
	function Filter_Slug($Src)
	{
		return preg_replace( '/[^a-z^A-Z^0-9]+/ims', "-", $Src);
	}
	
	function Filter_CleanRequest( $Inp )
	{
		if (get_magic_quotes_gpc() > 0)
			$Inp = stripslashes ( $Inp );
		return $Inp;
	}
	
	
	
?>