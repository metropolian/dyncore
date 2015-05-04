<?php
	/************************************************************************************************************
	 * 
     * DYNAMIC
     * dynuser - User Managements and Data Settings 
     *
     * 
     * by Northern Mongalian (C) DML System 2009     *
     *   
     ************************************************************************************************************/

	class DynUserData
	{
		public $IsLogged = false;
		public $UserID = null;
		public $UniqueID = null;
		public $UserInfo = null;		
		
		function GetInfo()
		{			
			if (is_null($this->UserInfo))
			{
				return $this->Reload();
			}
			return $this->UserInfo;
		}
		
		function Reload()
		{
			if (strlen($this->UniqueID) > 0)
			{
				$TrRow = DynDb_SelectTable(DynDb_SqlParameters(
					"Select UserID, LogonSig from dyn_users where (UniqueID = @UID)",
					"@UID", $this->UniqueID
					), true);
					
				if ($TrRow['UserID'] > 0)
				{
					$this->UserID = (int)$TrRow['UserID'];
					$UserInfo['UserID'] = $this->UserID;
					$UserInfo['UniqueID'] = $this->UniqueID;
					
					$TrUser = DynDb_SelectTable(DynDb_SqlParameters(
						"Select Key, Value from dyn_users where (UserID = @UserID)",
						"@Username", $Username,
						"@Password", $EncPassword
						), false);
						
					$this->UserInfo = array_merge($UserInfo, $TrUser);
					
				
					$this->SetInfo("TEST", "VALUE");
				
					return $this->UserInfo;
	
				}
			}
			return null;
		}
		
		function Logon_Uid($Uid)
		{
		
		}
		
		function Logon($Username, $Password, $ExpireTime = 3600)
		{
			$IsLogged = true;
			
			$EncPassword = DynUser_EncPassword($Password);
			
			$TrUser = DynDb_SelectTable(DynDb_SqlParameters(
				"Select UserID,UniqueID from dyn_users where (Username = @Username) and (Password = @Password)",
				"@Username", $Username,
				"@Password", $EncPassword
				), true);
			
			if (count($TrUser) > 0)
			{
				$LogonDate = DynDb_SqlDate();
				$LogonSig = crc32( $LogonDate );
				
				DynDb_Update("dyn_users", 
					"LogonDate", $LogonDate,
					"LogonSig", $LogonSig,
				
					DynDb_SqlParameters(
					" where (UserID = @UserID)",
					"@UserID", $TrUser['UserID']
				)); 
				
				
		
				$this->UniqueID = $TrUser['UniqueID'];
				
				
				$CookieExprTime = time() + $ExpireTime;				
				setcookie("dyn_uid", $this->UniqueID, $CookieExprTime );
				setcookie("dyn_sig", $LogonSig, $CookieExprTime);
				
			
				$this->IsLogged = true;
				
				$this->GetInfo();
				
				return 1;		
			}
			
			return 0;
			
		}
		
		function Users_Count()
		{
			return DynDb_SelectValue( "Select Count(UserID) From dyn_users", -1);			
			
		}
		
		function Create($Username, $Password, $UserInfo)
		{
			$Unique = strrev(crc32($Username));
			
			$PwdToken = sha1($Password);
			
			$IsFirstUser = ($this->Users_Count() == 0);  
			
			$Res = DynDb_Insert("dyn_users", 
				"UniqueID", $Unique, 
				"Username", $Username, 
				"Password", $PwdToken, 
				"PermissionID", 0,
				"Flags", (($IsFirstUser) ? 1 : 0),
				"CreateDate", DynDb_SqlDate(),
				"LogonDate", DynDb_SqlDate()
			);
			
			if ($Res == 0)
				return -1;
			
			return 1;
		}
		
		function Logoff()
		{
			$IsLogged = false;
			
			$CookieExprTime = time() - 1;
			
			setcookie("dyn_uid", "", $CookieExprTime );
			setcookie("dyn_sig", "", $CookieExprTime);
			
			$this->UserInfo = null;			
			return true;			
		}
		
		
		
		function SetInfo($Key, $Value)
		{
			if ($this->UserID > 0)
			{
				$Cond = DynDb_SqlParameters(" Where (`UserID` = @UserID) and (`Key` = @Key) ",
						"@UserID", $this->UserID,
						"@Key", $Key				
					);

					
				$Rows = DynDb_SelectValue("SELECT Count(`UserID`) FROM dyn_userinfo " . $Cond, 0 );
				
				if ($Rows > 0)
					$Res = DynDb_Update("dyn_userinfo", "Value", $Value, "Key", $Key, $Cond);
				else
					$Res = DynDb_Insert("dyn_userinfo", "UserID", $this->UserID, "Value", $Value, "Key", $Key );
			}
			return 0;			
		}
		

	}
	
	
	$DynCurrentUser = new DynUserData();
	
	


	function DynUser_EncPassword($Inp)
	{
		return sha1($Inp);
	}
	
	function DynUser_EncUserID($Inp)
	{
		return md5($Inp);
	}
	
	
	
	function DynUser_Logon($Username, $Password, $LogonTime = 3600)
	{	global $DynCurrentUser;

		return $DynCurrentUser->Logon($Username, $Password, $LogonTime);
	}
	
	function DynUser_Logoff()
	{	global $DynCurrentUser;
		return $DynCurrentUser->Logoff();
	}
		
	
	function DynUser_GetInfo()
	{	global $DynCurrentUser;
		$DynCurrentUser->UniqueID = ReadCOOKIE("dyn_uid");
		
		return $DynCurrentUser->GetInfo();
	}
	



	function DynUser_Create($Username, $Password)
	{	global $DynCurrentUser;
		return $DynCurrentUser->Create($Username, $Password, null);
		}
	
	function DynUser_SetInfo($UserVars)
	{	global $DynCurrentUser;
	
		$DynCurrentUser->UserID;
				
		foreach ($UserVars as $Key => $Val)
		{
			if (($Key == "UserID") || ($Key == "UniqueID"))
				continue;
				
			$DynCurrentUser->SetInfo($Key, $Val);
				
		}
		///DynData
	}
	
	function DynDatabase_QueryKeyValue($Query, $FieldKey, $FieldVal)
	{
		$HTable = DynDatabase_Exec($Query);
		$RowCount = DynDatabase_TableRowsCount($HTable);
		$Res = array();
		if ($RowCount > 0 )
		{				
			for ($Index = 0; $Index < $RowCount; $Index++)
			{
				$CurRow = DynDatabase_TableReadRow($HTable, 2);
				
				$Key = $CurRow[$FieldKey];
				
				if (IsStrNotEmpty( $Key ))
				{
					$Res[$Key] = $CurRow[$FieldVal];
				}
			}

			//print_r($Res);
		}	
		return $Res;		
	}
	
	function DynUser_GetInformation($UserID)
	{
		$HTable = DynDatabase_Exec("SELECT [userid], [username], [password], [permissions], [flags] FROM [dyn_users] Where (UserID = '$UserID') ");
		if (DynDatabase_TableRowsCount($HTable) > 0 )
		{
			$UserRow = DynDatabase_TableReadRow($HTable);
			$UserID = $UserRow[0];
						
			$Res = DynDatabase_QueryKeyValue("SELECT [Key], [Value], [Flags] FROM [dyn_userinfo] Where (UserID = $UserID)", "Key", "Value");
			
			$Res['USERNAME'] = $UserRow[1];
			$Res['FLAGS'] = $UserRow[3];
			
			return $Res;
		}
		return null;
	}
	
	function DynUser_CheckUsername($Username)
	{		
		$HTable = DynDatabase_Exec("SELECT [userid] FROM [dyn_users] Where (Username = '$Username')");		
		$Res = 0;
		
		if (DynDatabase_TableRowsCount($HTable) > 0 )
		{
			$ResRow = DynDatabase_TableReadRow($HTable);
			return ($ResRow[0] > 0);
		}
		return $Res;
	}
	
	function DynUser_FindUserID($Username, $Password)
	{		
		$Password = DynUser_EncPassword($Password);
		$HTable = DynDatabase_Exec("SELECT [userid] FROM [dyn_users] Where (Username = '$Username') and (Password = '$Password')");		
		$Res = 0;
		
		if (DynDatabase_TableRowsCount($HTable) > 0 )
		{
			$ResRow = DynDatabase_TableReadRow($HTable);
			return $ResRow[0];
		}
		return $Res;
	}
	
	
	function DynUser_CheckLogon($CookieVars)
	{
		$Username = $CookieVars['USERNAME'];
		$HTable = DynDatabase_Exec("SELECT [userid] FROM [dyn_users] Where (Username = '$Username')");		
		$Res = 0;		
		if (DynDatabase_TableRowsCount($HTable) > 0 )
		{
			$ResRow = DynDatabase_TableReadRow($HTable);
			$UserID = $ResRow[0];
			
			if ($CookieVars['USERID'] == DynUser_EncUserID($UserID))
			{
				$Res = DynUser_GetInformation($UserID);
				$Res['USERID'] = $UserID;
				return $Res;
			}
				
		}
		return null;		
	}
	
	
	
	
	
	
	
	
	
	

	
	function DynUser_MakeInstanceInfo($UserID, $PageInstance, $ModuleName, $BlockName, $Instance, $Parent = 0, $StRow = 0, $MaxRow = 0)
	{		
		$Res['USERID'] = $UserID;
		$Res['PAGEINSTANCE'] = $PageInstance;
		$Res['MODULENAME'] = DynText_ToStorage( $ModuleName );
		$Res['BLOCKNAME'] = DynText_ToStorage( $BlockName );
		$Res['INSTANCE'] = DynText_ToStorage( $Instance );
		$Res['PARENT'] = $Parent;
		$Res['PAGEINSTANCE_STARTROW'] = $StRow;
		$Res['PAGEINSTANCE_COUNTROW'] = $MaxRow;
		return  $Res;		
	}
	
	
	
	// DynUser_GetUserData - Get Data From DYN_USERS by PageInstance / Blockname / Instance / Key
	//
	// Return Array
	// Example Result:
	// USERNAME	ID 	UserID 	PageInstance 	BlockName 	Instance 	Type 	Key 	Value 	CreateDate 	ModifyDate 	Flags
	function DynUser_GetUserData($InstanceInfo, $Key, $MaxRows = 0, $Additional = "" )
	{
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		
		$Query = "SELECT (Select [Username] From [dyn_users] Where [dyn_users].[UserID] = [dyn_userdata].[UserId]) as [Username], [dyn_userdata].* FROM [dyn_userdata] ";		
		$Cond = GetStrCombineParamsArgs(" and ", 
					($PageInstance != ""), "[PageInstance] = $PageInstance ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = $Instance ",
					($Key != ""), "[Key] = '$Key' ");
		if ($Cond != "")
			$Query .= "WHERE " . $Cond;			
		if ($Additional != "")
			$Query .= " " . $Additional . " ";		
		if ($MaxRows > 1)
			$Query .= " LIMIT " . $MaxRows;			
			
		$Table = DynDatabase_SelectTable($Query, ($MaxRows == 1) );
				
		return $Table;		
	}
	
	/*  DynUser_GetUserDataValue - Get Data From DYN_USERS output as Value only
	 * 
	 * 
	 */
	
	function DynUser_GetUserDataValue($InstanceInfo, $Key, $Def )
	{
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		
		$Query = "SELECT [Value] FROM [dyn_userdata] ";				
		$Cond = GetStrCombineParamsArgs(" and ", 
					($PageInstance != ""), "[PageInstance] = $PageInstance ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = $Instance ",
					($Key != ""), "[Key] = '$Key' ");
		if ($Cond != "")
			$Query .= "WHERE " . $Cond;			
		$Res = DynDatabase_SelectValue($Query, $Def);
		return $Res;
	}
	
	function DynUser_CountUserData($UserID, $PageInstance, $ModuleName, $BlockName, $Instance, $Type, $Key)
	{
		$Query = "SELECT Count([Key]) FROM [dyn_userdata] ";
		$Cond = GetStrCombineParamsArgs(" and ", 
					($PageInstance != ""), "[PageInstance] = $PageInstance ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = $Instance ",
					($Type != ""), "[Type] = '$Type' ",
					($Key != ""), "[Key] = '$Key' ");
		if ($Cond != "")
			$Query .= "WHERE " . $Cond;
			
		$Res = DynDatabase_SelectValue( $Query, 0);
		return $Res;
	}
	
	function DynUser_SetUserData($Mode, $InstanceInfo, $Type, $Key, $Value)
	{	
		$UserID = $InstanceInfo['USERID'];
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		
		$Value = DynText_ToStorage($Value);
		
		if ($UserID <= 0)
			return -1;
		if ($PageInstance <= 0)
			return -2;
		if (IsStrEmpty($Type))
			return -4;
		if (IsStrEmpty($Key))
			return -5;

		switch ($Mode)
		{
			case 1: // UPDATE FIELDS IF NOT EXISTS THEN CREATE NEW
			{
				$UserRows = DynUser_CountUserData($UserID, $PageInstance, $ModuleName, $BlockName, $Instance, $Type, $Key);			
				if ($UserRows > 0)
				{
					$Res = DynDatabase_UpdateExec("dyn_userdata",
						"Value",  $Value,
						"ModifyDate", "GetDate()",
						" Where ([PageInstance] = '$PageInstance') and " . 
						" ([UserID] = '$UserID') and " .
						" ([ModuleName] = '$ModuleName') and " . 
						" ([BlockName] = '$BlockName') and " . 
						" ([Instance] = '$Instance') and " .
						" ([Key] = '$Key') "
					);
					
					if ($Res > 0)
						return 2;
				} 
				else
				{
					$Res = DynUser_SetUserData(2, $InstanceInfo, $Type, $Key, $Value);					
					if ($Res > 0)
						return 1;
				}
					
			}

			case 2: // CREATE NEW FIELDS
				{
					if (DynDatabase_InsertExec("dyn_userdata",
						"UserID", $UserID, 
						"PageInstance",  $PageInstance,
						"ModuleName", $ModuleName,
						"BlockName", $BlockName,
						"Instance",  $Instance,
						"Type", $Type, 
						"Key", $Key,
						"Value", $Value,
						"CreateDate", "GetDate()"							
					) > 0)
						return 1;			

					return 4;
				}
								
			case 4: // UPDATE INTEGER
				{
					$Res = DynUser_GetUserDataValue($InstanceInfo, $Key, 0 ) + $Value;		
					
					$Res = DynUser_SetUserData(1, $InstanceInfo, "INT", $Key, $Res);
					
					if ($Res > 0)
						return  $Res;
					
				}
		}
		return 0;		
	}
	
	/*  DynUser_GetUserDataVars - Get dyn_userdata into Array Structure Reference by key
	 * 
	 *  Result Examples: 
	 *    Array
          (
            [KEY_TITLE] => Array
                (...)

            [KEY_ARTICLE] => Array
                (...)
          )
	 * 
	 */
	function DynUser_GetUserDataVars($InstanceInfo, $Keys)
	{
		$Res = null;
		
		if (is_array($Keys))
		{
			foreach ($Keys as $Key)
				$Res[$Key] = DynUser_GetUserDataVars($InstanceInfo, $Key);
			
		}
		else
		{
			$Rec = DynUser_GetUserData($InstanceInfo, $Keys, 1);
			if ($Rec !== null)
				$Res = $Rec;
		}
		return $Res;
	}
	
	
	function DynUser_GetUserData_ByPageInstance($InstanceInfo, $Keys)
	{
		$UserID = $InstanceInfo['USERID'];
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		$Parent = $InstanceInfo['PARENT'];
		$StRow = $InstanceInfo['PAGEINSTANCE_STARTROW'];
		$MaxRow = $InstanceInfo['PAGEINSTANCE_COUNTROW'];
		
		$PageInstanceSearch = $InstanceInfo['PAGEINSTANCE_SEARCH'];
		$PageInstanceOrder = $InstanceInfo['PAGEINSTANCE_ORDER'];
		
		if ($PageInstanceSearch != "")
			$PageInstanceSearch = " and ($PageInstanceSearch) ";
		
		$Res = null;
		
		$Limit = ($MaxRow > 0) ? "LIMIT $MaxRow OFFSET $StRow " : ""; 
		
		$Query = "SELECT (Select [Username] From [dyn_users] " . 
			"Where [dyn_users].[UserID] = [dyn_userdata].[UserId]) as [Username], dyn_userdata.* FROM [dyn_userdata] " .
		 	"INNER JOIN	(SELECT PageInstance FROM `dyn_userinstance` WHERE ([ModuleName] = '$ModuleName') and ([BlockName] = '$BlockName') and ([Instance] = $Instance) and ([Parent] = $Parent) " . $PageInstanceSearch . 
			"$Limit ) as B ON [dyn_userdata].[PageInstance] = B.[PageInstance] "
			;

		$CondKeys = GetStrCombineParams($Keys, "[Key] ='", "'", " or ");
		$Cond = GetStrCombineParamsArgs(" and ", 
					($PageInstance != ""), "[PageInstance] = '$PageInstance' ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = '$Instance' ",
					($CondKeys != ""), "($CondKeys)");
					
		if ($Cond != "")
			$Query .= "WHERE " . $Cond ;
		$Query .= " ORDER BY [PageInstance], [Key]";
		if ($PageInstanceOrder != "")
			$Query .= ", $PageInstanceOrder";
					
		$Table = DynDatabase_SelectTable($Query, false);
		
		if ($Table !== null)
		{
			$CurRow = null;		
			$LastPageInstance = 0;
			for($Index = 0; $Index < count($Table); $Index++)
			{				
				$CurPageInstance = $Table[$Index]['PageInstance'];
				
				if ($LastPageInstance != $CurPageInstance)
				{
					if ($CurRow !== null)
						$Res[] = $CurRow;
					$CurRow = array();
					$LastPageInstance = $CurPageInstance;
				}

				//if ($CurPageInstance == $LastPageInstance)
				{
					$Key = $Table[$Index]['Key'];
					if ($Key)
					{					
						if (!isset($CurRow[$Key]))
							$CurRow[$Key] = $Table[$Index];
						else
						{
							if (is_array($CurRow[$Key]))
							{	
								if (!is_array( $TemRow) )
								{
									$TemRow = $CurRow[$Key]; 
									$CurRow[$Key] = null;  
									$CurRow[$Key] = array($TemRow);
								} 
								$CurRow[$Key][] = $Table[$Index];
							}
							else 
							{
								$CurRow[$Key] = $Table[$Index];
							}
							
						}
					}	
										
		//print_r($CurRow);
				}
			}
			if ($CurRow !== null)
			 	$Res[] = $CurRow;
		}
		
		//print_r($Res);
				
		return $Res;		
	}
	
	
	
	
	function DynUser_CreatePageInstance($InstanceInfo, $Title, $Desc, $Tags = "", $Category = "", $Group = "")
	{
		$UserID = $InstanceInfo['USERID'];
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		$Parent = $InstanceInfo['PARENT'];
		
		$MetaTitle =  DynText_MakeMetaLink($Title);				
		
		$PageInstance = DynDatabase_InsertExec("dyn_userinstance",
						"UserID", $UserID,
						"ModuleName", $ModuleName,
						"BlockName", $BlockName,
						"Instance", $Instance, 
						"Parent", $Parent,
						"Title", $Title,
						"Desc", $Desc,
						"Meta", $MetaTitle,
						"Tags", $Tags,
						"Category", $Category,
						"Group", $Group
						);
						
		$InstanceInfo['PAGEINSTANCE'] = $PageInstance;
		return $InstanceInfo;
	}

	function DynUser_UpdatePageInstance($InstanceInfo, $Title, $Desc, $Tags = "", $Category = "", $Group = "")
	{
		$UserID = $InstanceInfo['USERID'];
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		$Parent = $InstanceInfo['PARENT'];
		
		$MetaTitle =  DynText_MakeMetaLink($Title);				
		
		return DynDatabase_UpdateExec("dyn_userinstance",
						"ModuleName", $ModuleName,
						"BlockName", $BlockName,
						"Instance", $Instance, 
						"Parent", $Parent,
						"Title", $Title,
						"Desc", $Desc,
						"Meta", $MetaTitle,
						"Tags", $Tags,
						"Category", $Category,
						"Group", $Group,
						" Where ([PageInstance] = '$PageInstance') "
						);
	}
	
	function DynUser_DeletePageInstance($InstanceInfo)
	{
		$UserID = $InstanceInfo['USERID'];
		$PageInstance = $InstanceInfo['PAGEINSTANCE']; 
		$ModuleName  = $InstanceInfo['MODULENAME'];
		$BlockName = $InstanceInfo['BLOCKNAME'];
		$Instance = $InstanceInfo['INSTANCE'];
		
		$Query = "DELETE FROM [dyn_userinstance] ";		
		$Cond = GetStrCombineParamsArgs(" and ",
					($PageInstance != ""), "[PageInstance] = $PageInstance ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = $Instance "
					);
		if ($Cond != "")
			$Query .= "WHERE " . $Cond;			
		
		if (DynDatabase_Exec($Query))
		{
			$Query = "DELETE FROM [dyn_userdata] ";		
			if ($Cond != "")
				$Query .= "WHERE " . $Cond;			
		
			DynDatabase_Exec($Query);
			return true;			
		}
		return false;
	}
	
	
	
	function DynUser_GetPageInstanceInfo($UserID, $PageInstance, $ModuleName, $BlockName, $Instance)
	{
		$Query = "SELECT (Select [Username] From [dyn_users] Where [dyn_users].[UserID] = [dyn_userinstance].[UserId]) as [Username], [dyn_userinstance].* FROM [dyn_userinstance] ";		
		$Cond = GetStrCombineParamsArgs(" and ", 
					($PageInstance != ""), "[PageInstance] = $PageInstance ",
					($ModuleName != ""), "[ModuleName] = '$ModuleName' ",
					($BlockName != ""), "[BlockName] = '$BlockName' ",
					($Instance != ""), "[Instance] = $Instance "
					);
		if ($Cond != "")
			$Query .= "WHERE " . $Cond;			
		
		$Table = DynDatabase_SelectTable($Query, 1 );

			$InstanceInfo['USERID'] = $Table['UserID'];
			$InstanceInfo['PAGEINSTANCE'] = $Table['PageInstance'];
			$InstanceInfo['MODULENAME'] = $Table['ModuleName'];
			$InstanceInfo['BLOCKNAME'] = $Table['BlockName'];
			$InstanceInfo['INSTANCE'] = $Table['Instance'];
		return $InstanceInfo;
	}

	
	
	
	
	
	
	
	
	
	function DynUser_StoreFile($FileInfo, $DestDir)
	{
		if (isset($FileInfo))
		{
			$Tmp_Name = $FileInfo['tmp_name'];
			$FName = $FileInfo['name'];
		
			$DestFullname = DynUser_GetPrivateFullname($DestDir, $FName );
						
			if (move_uploaded_file($Tmp_Name, $DestFullname) !== false)
				return $DestFullname;		
		}
		return "";		
	}
	
	function DynUser_DeleteFile($DestFName)
	{
		if (file_exists($DestFName))
		unlink($DestFName);
	}
	
	function DynUser_GetPrivateDestDir()
	{		
		return "userfiles/" . strtolower(DynVar_Get("USER.USERNAME"));		
	}
	
	function DynUser_GetPrivateFullname($DestDir, $SrcFName)
	{
		if (!is_dir($DestDir))
			mkdir($DestDir);
			
		$FName = GetStrBefore($SrcFName, ".", false);
		$FExt = GetStrAfter($SrcFName, ".", true);
		$FAlias = 0;
		$DestName = $FName . $FExt;			
		
		do
		{
			if ($FAlias > 0)
				$DestName = $FName . " " . $FAlias . $FExt;  			
			$DestFullname = $DestDir . "/" . DynText_MakeMetaLink( $DestName );
			$FAlias++;
			
		} while( file_exists($DestFullname) );
		
		return $DestFullname;
	}
	
?>