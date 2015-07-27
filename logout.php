<?php
	require_once('init.php');
	
	Html_Init( 'u4shop' );

    User_Logout();
	
	Html_Title('dynamic platform administration');
	Html_Begin();


    Redirect('index.php');


?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="text-center">
                <h1>
                    Log out</h1>
                <h2>
                    404 Not Found</h2>
                <p class="error-details">
                    Sorry, an error has occured, Requested page not found!
                </p>
                <p class="error-actions">
                    <a href="//www.u4shop.com" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span>
                        Take Me Home </a>
                    <a href="//www.u4shop.com" class="btn btn-default"><span class="glyphicon glyphicon-envelope"></span> Contact Support </a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php Html_End(); ?>