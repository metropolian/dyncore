<?php

    require_once('init.php');

    Html_Init( 'u4shop' );
    Html_Title('dynamic platform administration');
    Html_Begin();

    if ($Request->GET('func', '') == 'auth')
    {
        $User = User_ByLogin(
            $Request->GET('username', '', 'trim'),
            $Request->GET('password'));
        
        if ($User)
        {
            if (! $User->VerifyAccessToken())
                $User->GetAccessToken(true);            
            User_SetCookies($User);
            Redirect('index.php');
        }
        else
        {
            Html_SetError('Invalid User.');
        }
        
        

    }
    $FLogin = new DynForm('', 'POST', 'vertical');
    $FLogin->AddData('func', 'auth');

    $FLogin->Label('Username');
    $FLogin->AddInput('text', 'username', '');

    $FLogin->Label('Password');
    $FLogin->AddInput('password', 'password', '');

    $FLogin->AddButton('button', 'Sign In', array('class' => 'btn btn-primary btn-block btn-center'));
     
    //$DB = User_GetDB();
    //$DB->ShowLogs(); 

?>

<div class="container">
    <div class="text-center">

            <div class="col-md-offset-1 col-md-4">
                <h2 class="form-signin-heading">Log in</h2>
                <p class="text-muted">sign in with U4SHOP ID</p>
                <br>
                <?php Html_ListErrors(); ?>
                <?php 
                
                    $FLogin->Render();
                
                ?>
            </div>

            <div class="col-md-4 col-md-offset-2">
                <h2 class="form-signin-heading">Join</h2>

                <small class="create-account text-muted">Dont have a U4SHOP ID or social network account? <a class="btn btn-primary btn-block btn-center" href="register.php">Free Account Sign Up !!</a> </small>

                <br>
                <small class="text-muted">or Connect U4SHOP with your favorite social network</small>
                <br>
                <br>


                <div class="text-center">
                    <div class="btn-login fb-bg">
                        <div class="fb-icon-bg"></div>
                    </div>  
                    <div class="btn-login twi-bg">
                        <div class="twi-icon-bg"></div>
                    </div>  
                    <div class="btn-login g-bg">
                        <div class="g-icon-bg"></div>
                    </div>
                </div>


            </div>


    </div>
</div>
<?php Html_End(); ?>