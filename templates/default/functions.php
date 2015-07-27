<?php

	Html_Init( 'default', 
	array(
		'head' => 'html_header.php',
		'foot' => 'html_footer.php'
	));

    Html_Script('./css/bootstrap.css');
    Html_Script('./css/bootstrap-theme.css');

    Html_Script('./js/jquery.min.js');
    Html_Script('./js/bootstrap.min.js');

    /* check user login */
	// $User = User_GetCurrent();

?>