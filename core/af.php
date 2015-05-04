<?php

	function HtmlEnc($data)
	{
		return htmlspecialchars($data, ENT_QUOTES);
	}
	
	$PrevAFDir = getcwd();	
	$AFDir = dirname(__FILE__);
	chdir( $AFDir );
	
/*
	$ServerName = isset($_SERVER['SERVER_NAME']) ? strtolower($_SERVER['SERVER_NAME']) : "";
	$IsLocal = (($ServerName == "arang.homeip.net") || ($ServerName == "localhost"));

	if ($IsLocal)
	{
		$DbConfig ['SERVER'] = '';
		$DbConfig ['USERNAME'] = 'root';
		$DbConfig ['PASSWORD'] = 'drmg';
		$DbConfig ['DATABASE'] = 'dynamic';
	}
	else
	{
		$DbConfig ['SERVER'] = 'ara1110008363249.db.7724384.hostedresource.com';
		$DbConfig ['USERNAME'] = 'ara1110008363249';
		$DbConfig ['PASSWORD'] = 'DrMg4667';
		$DbConfig ['DATABASE'] = 'ara1110008363249';
	}
*/

	$AFWP = false;
	define('WP_USE_THEMES', false);	


	if ( is_file("../wp-config.php") )
	{
		require_once("../wp-config.php");
		$AFWP = true;
	}
	else
	{
		if ( is_file("../../wp-config.php") )
		{
			require_once("../../wp-config.php");
			$AFWP = true;
		}
	}
	
	require_once("af_contents.php");	
	
	if ($AFWP)
	{

		$wp->init();
		$wp->parse_request();
		$wp->query_posts();
		$wp->register_globals();

	require_once("init.php");		
/*
	if ( is_file("../wp-blog-header.php") )
		require_once("../wp-blog-header.php");
	else
		require_once("../../wp-blog-header.php");
		
	header("HTTP/1.1 200 OK");
*/

/*
	include_once('../wp-config.php');
	include_once('../wp-load.php');
	include_once('../wp-includes/wp-db.php'); 
	include_once('../wp-includes/bookmark.php'); 
	*/


		$wpdb->feeds = $wpdb->prefix.'feeds';
		
		require_once("af_links.php");	
		require_once("af_posts.php");
		require_once("af_tag.php");
		
		require_once("ac_baseconv.php");

	}
	
	chdir( $PrevAFDir );	
?>
