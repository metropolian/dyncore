<?php

	$func = $Request->GET('func');

	$Users = array();
	$CurrentUser = null;
		

	if (!defined(DYNUSER_TOKEN_NAME))
		define(DYNUSER_TOKEN_NAME, 'dynuid');

	if (!defined(DYNUSER_PASSWORD_TYPE))
		define(DYNUSER_PASSWORD_TYPE, 'sha256');

	if (!defined(DYNUSER_TOKEN_TYPE))
		define(DYNUSER_TOKEN_TYPE, 'sha256');

	class DynUser
	{
        public $TbUser = 'users';
        
        public $DataKeys = array(
            'user_id', 'username', 'password', 'access_token', 'access_token_time',
            'user_type', 'user_title', 'user_email', 
            'created_time', 'accessed_time', 'updated_time',
            'user_permissions', 'user_options', 'user_status', 
            'language', 'timezone', 
            'first_name', 'mid_name', 'last_name',
            'flags',
        );
        
		public $Data = array();
		
		function __construct($Src)
		{
			if ( is_numeric($Src) )
				$this->Data['user_id'] = $Src;
			if ( is_array($Src) )
				$this->Data = $Src;
		}        
		
		function ID()
		{
			return intval( $this->Data['user_id'] );
		}
		
		function Title()
		{
			$res = $this->Data['user_title'];
			if ($res == '')
				$res = $this->Data['username'];
			return $res;
		}
        
        
        function Get($Key, $Def = null)
        {
            if ( isset( $this->Data[$Key]) )
                return $this->Data[$Key];
            return $Def;
        }
        
        function Set($Key, $Value)
        {
            $this->Data[$Key] = $Value;
        }
        
		function GetAccessToken($New = false)
		{	
			if ($New)
			{
				$Username = $this->Data['username'];				
				$time = User_GetDB()->Date();
				$token = User_EncryptToken($time, $Username);
				$res = $this->Update(array(
					'access_token_time' => $time,
					'access_token' => $token
				));
				
				if ($res)
				{
					$this->Data['access_token_time'] = $time;
					$this->Data['access_token'] = $token;
					return $token;
				}
				return null;
			}
			return $this->Data['access_token'];
		}
		
		function VerifyAccessToken()
		{
			$Username = $this->Data['username'];
			$time = $this->Data['access_token_time'];
			$token = $this->Data['access_token'];			
			$check_token = User_EncryptToken($time, $Username);
			return ($token == $check_token) ;
		}
		
		function Reload()
		{
			$Id = $this->ID();			
			if ($Id > 0)
			{
				$UserDB = User_GetDB();
				$res = $UserDB->SelectRow("SELECT * FROM users WHERE user_id = @Id LIMIT 1", array("@Id" => $Id));
				if ($res)
				{
					$this->Data = $res;
					return true;
				}
			}
			return false;			
		}
		
		function Update($V = null)
		{	
			$Id = $this->ID();			
			if ($Id > 0)
			{
                if ($V == null)
                    $V = $this->Data;
                
                if (is_array($V))
                {
				    unset($V['user_id']);                
                    foreach ($V as $K => $Val) 
                        $this->Data[$K] = $Val;
                }
                
				$UserDB = User_GetDB();
				return $UserDB->Update('users', $V, " user_id = @Id", array("@Id" => $Id));
			}
			return false;			
		}
        
        
        
	}







    /* general function ------------------------------------------------------------------------
    */

	function User_GetDB()
	{	global $UserDB;
	
	 	if ($UserDB == null)
			$UserDB = GetDB('user');
        if ($UserDB == null)
        {
            echo 'No UserDB defined.';
            exit;
        }
	 	return $UserDB;	 
	}


	function User_EncryptPassword( $password )
	{
		return hash( DYNUSER_PASSWORD_TYPE, $password );
	}

	function User_EncryptToken( $time, $Username )
	{
		return hash_hmac(DYNUSER_TOKEN_TYPE, date('Y-m-d H:i:s', $time), $Username);			
	}




	function User_GetCurrent()
	{	global $CurrentUser;
	 
		if (is_a($CurrentUser, 'DynUser'))
			return $CurrentUser;
	 
	 	$User = User_ByCookies();
	 	if ($User)
			$CurrentUser = $User;
	 	return $CurrentUser;
	}



	function User_SetCookies( $User, $LifeTime = 86400, $SetAsCurrent = true )
	{	global $CurrentUser;
	 
		$Id = $User->ID();
		if ($Id > 0 )
		{
			$token = $User->GetAccessToken();
			$expire = time() + $LifeTime;
            
			setcookie(DYNUSER_TOKEN_NAME, $token, $expire);
			if ($SetAsCurrent)
				$CurrentUser = $User;
			return true;
		}
		return false;
	}

	function User_ClearCookies()
	{
		setcookie(DYNUSER_TOKEN_NAME, '', time() - 1);
	}

    function User_Logout()
    {   global $CurrentUser;
        User_ClearCookies();
        $CurrentUser = null;
    }

	function User_ByCookies()
	{
		if ( !isset($_COOKIE[DYNUSER_TOKEN_NAME]))
			return null;
		
		$token = $_COOKIE[DYNUSER_TOKEN_NAME];
		return User_ByToken($token);		
	}

	function User_ByToken( $token )
	{   global $Users;
		$token = substr(trim($token), 0, 128);
		if ($token == '')
			return;
        
        if (is_array($Users))
            foreach($Users as $User)
                if ($User->Get('access_token') == $token)
                    return $User;
		
	 	$UserDB = User_GetDB();
		$res = $UserDB->SelectRow("SELECT * FROM users WHERE (access_token = @token) LIMIT 1", array( '@token' => $token ));		
		if ( $res )
		{
			$User = new DynUser($res);
			User_SetCache($User);
			return $User;
		}
		return null;		
	}

	function User_ByLogin( $username, $password )
	{	
	 	$UserDB = User_GetDB();
		$username = substr(trim($username), 0, 128);
		$password = User_EncryptPassword( substr(trim($password), 0, 128) );
		
		$res = $UserDB->SelectRow("SELECT * FROM users WHERE (username = @username) AND (password = @password) LIMIT 1",
					array(	'@username' => $username,
							'@password' => $password
					));		
		if ( $res )
		{
			$User = new DynUser($res);
			User_SetCache($User);
			return $User;
		}
		return null;
	}

	function User_SetCache( $User )
	{	global $Users;	 
	  	$User_Id = $User->ID();
	 	if ($User_Id > 0)
			$Users[$User_Id] = $User;
	 	return $User;
	}

	function User_ById( $Id )
	{	global $Users;
		if ( isset($Users[$Id]) )
			return $Users[$Id];
		
		$User = new DynUser($Id);
		if ($User->Reload())
		{
			User_SetCache($User);
			return $User;
		}
		return null;		
	}

?>