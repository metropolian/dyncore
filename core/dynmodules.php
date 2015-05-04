<?php
	require_once("dynutils.php");
	require_once("dynvars.php");
	
	$DynCurModule = null;
	
	$DynBlockCallback = null;
	$DynBlockFuncCallback = null;
			

	
	class DynModuleEngine
	{
		public $DynModules = array();
		
		public $CurBlock = null;
		public $CurBlockLv = 0;	
		public $CurBlockUID = 0;
		public $CurBlockEID = 0;
		
		public $MaxBlockLv = 20;
		
		
		
		/*--------------------------------------------------------
		 * DynModules_LoadFile
		 * 
		 * Load Dynamic Module file to $DynModules	 * 
		 */
		function LoadFileContent($FName)
		{
			if (file_exists($FName))
				return file_get_contents($FName);
			return "";		
		}
	
		function LoadFileModule($FName)
		{	
		
			// Check Existing File Module	
			$Module = $this->GetModuleByFName($FName);
			if ($Module != null)
				return $Module;
				
			$Content = $this->LoadFileContent($FName);
			$ContentLen = strlen($Content);		
			if ($ContentLen > 0)
			{			
				unset($Module);
				
				$Module = new DynModule();				
				
				// Create Module Structure Data					
				$Module->Name = basename($FName);
				$Module->FileName = $FName;
				$Module->FileData = $Content; 
				$Module->FileSize = $ContentLen; 
				$Module->Elements = null;
							
				// Parse File Data to Elements				
				$Module->ParseModule();
				
				//echo "<! Module Load $FName-------------------------------> \r\n";
				
										
				return $Module;
			}				
			return null;
		}
		
		function LoadAllModules($Dir = "modules", $Ext = ".htm")
		{	
			$Files = scandir($Dir);
			
			foreach ($Files as $FName)
			{
				if (stripos($FName, ".htm") !== false)
				{
					$Fullname = $Dir . "/" . $FName;
					//echo "Load $Fullname \r\n";
					$Module = $this->LoadFileModule( $Fullname );
					
			
					if (isset($Module))
						$this->AddModule($Module);
					
				}
			}
		}
		
		
		function AddModule($NewModule)
		{					
			$NewModuleName = $NewModule->Name;
			
			if ($this->GetModuleByName($NewModuleName) !== null)
				return false;			
			
			$this->DynModules[$NewModuleName] = $NewModule;
			
			return true;
		}
		
		
		function & GetModuleByFName($FName)
		{				
			if (isset($this->DynModules))
			{
				$FName = strtoupper($FName);
				
				foreach($this->DynModules as $Module)
				{
					$CurName = strtoupper($Module->FileName);			
					if ($CurName == $FName)
						return $Module;
				}
			}
			return null;	
		}
			
		
		function & GetModuleByName($ModuleName)
		{			
			$ModuleName = strtoupper($ModuleName);
			foreach($this->DynModules as $Module)
			{
				$CurName = strtoupper($Module->Name);			
				//DynDebug($CurName);
				if ($CurName == $ModuleName)
				{
					return $Module;
				}
			}
			return null;	
		}
		
		function MainOutput($DefModule = "MAIN", $DefBlock = "MAIN")
		{   global $DynVars;
			$Module = $this->GetModuleByName($DefModule);
			//var_dump($Module);
			if ($Module !== null)
				return $this->BlockOutput($Module, $DefBlock, $DynVars);
			return null;
		}
		
		function BlockOutputSearch($DestBlock)
		{	global $DynVars;
		
			echo "--------$DestBlock--------";
			
		/*
			foreach($this->DynModules as $Module)
			{
				$Block = DynModules_BlockByName($Module, $DestBlock);
				if ($Block !== null)
					return DynModules_BlockOutput($Module, $DestBlock, $DynVars);
			} */
			return null;
		}
		
		function BlockModuleNameSearch($DestBlock)
		{	global $DynVars;
		/*	
			foreach($this->DynModules as $Module)
			{
				$Block = DynModules_BlockByName($Module, $DestBlock);
				if ($Block !== null)
					return $Module['NAME'];
			} */
			return null;
		}
		
		
		
	
		
				// DynModules_BlockOutput - Render Block
		
		function BlockOutput(&$Module, $BlockName, $LocalVars)
		{	global $DynBlockCallback ;
	
			if ($Module === null)
				return "";
			if (! isset($Module->Elements))
				return "";
				
			$Res = "";
			$BlockName = strtoupper($BlockName);						
			
			$Element = $Module->BlockByName($BlockName);
					  		
		
			//print_r($RegEx);
					
			/*
			if ($this->CurBlockUID <= 0)
				$this->CurBlockUID = 1; */
		
			if ($DynBlockCallback !== null)
			{				
				$Res = $DynBlockCallback ($Module->Name, $BlockName, $LocalVars);

				if ($Res !== null)
					return $Res;
			}
					
			if ($Element !== null)
			//foreach($Elements as $Element)
			{	
				//if (($Element['_NAME'] == "BLOCK") && (strtoupper( $Element['NAME'] ) == $BlockName) )
				{	
					if ($this->CurBlockLv > $this->MaxBlockLv)
					{
						return "--Nested Block $Module->Name $BlockName Overflow--";
						
					}
			   
					// Entering New Block
					$this->CurBlockLv++;
					$this->CurBlock[$this->CurBlockLv] = null;
									
					//$this->CurBlock[$this->CurBlockLv] = $Element;
					$this->CurBlock[$this->CurBlockLv]['MODULE'] =& $Module;				
					$this->CurBlock[$this->CurBlockLv]['LEVEL'] = $this->CurBlockLv;
					$this->CurBlock[$this->CurBlockLv]['UID'] = ++$this->CurBlockUID;
					
					$Text = $Element['_TEXT'];
					
					
					// Enable Condition /*
					$CondEnable = trim( $Element['ENABLE'] );
					if ($CondEnable != "")
					{
						if (!$this->TestCondition($CondEnable))
						{
							$this->CurBlockLv--;
							return "";
						} 
					} 
					
					// Set Iteration 
					$Iteration = 0;
					$IterationValid = 0;
					
					if ($Element['ITERATION'] > 0)
					{
						$Iteration = 1;
						$this->CurBlock[$this->CurBlockLv]['ITERATION'] = $Iteration;
					}
					 					
					$MapVarName = trim( $Element['MAP'] );
					if ($MapVarName != "")
					{
						if (isset($LocalVars[$MapVarName]))						
							$MapVars = $LocalVars[$MapVarName];
						else
							$MapVars = DynVar_Get($MapVarName); 
																				
						if (!is_null($MapVars))
						{
							
							if (is_array($MapVars))
							{		
								$LocalVars = DynVar_PackVars($LocalVars, $MapVars);
								//$LocalVars = $MapVars;
								$IterationValid = 1;								
							}
							else 
							{
								var_dump($MapVars);
								return "--Mapping $MapVarName is not arrays--";
							}
						}
					}
	
					$this->CurBlock[$this->CurBlockLv]['VARS'] = $LocalVars;
										
					if ($Iteration)
					{						
						if ($IterationValid)
						{					
							foreach ($MapVars as $Key => $Val)
							{										
								$this->CurBlock[$this->CurBlockLv]['KEY'] = $Key;		
								$this->CurBlock[$this->CurBlockLv]['VALUE'] = $Val;	
								$Res .= $this->BlockOutput_GetProcessedText($Text);
							}
						}
					}
					else 
					{
						// Find Block Variable to Evalutate and Get Result
						$Res = $this->BlockOutput_GetProcessedText($Text);
					}
					
					if (DynVar_Get("DYNAMIC.OPTION.TRIMOUTPUT"))
						$Res = trim($Res);
					if (DynVar_Get("DYNAMIC.OPTION.COMPACTLF"))
						$Res = str_replace(array("\r","\n","\r\n"), "", $Res); 
					
					// Leaving New Block
					$this->CurBlockLv--;		
					
					return $Res;
				}
			}	
			return null; // "Cannot Find $BlockName";
			//return "{%" . $Module->Name . " $BlockName%} Not Found";		
		}
		
		
		function BlockOutput_GetProcessedText($Text)
		{
			$RegEx = "/{%[^%]*%}/ims";
			return preg_replace_callback($RegEx, array( &$this, 'BlockOutput_Evaluate'), $Text );
		}
		
		function BlockOutput_Evaluate($Matchs)
		{			
			$Text = $Matchs[0];
			
			$TextLen = strlen($Text);
			
			$Text = trim(substr($Text, 2, $TextLen - 4));
	
			//echo "##$Text##########################################\r\n";
			// Count Extra ID
			$this->CurBlockEID++;
			
			$this->CurModule = $this->CurBlock[$this->CurBlockLv]['MODULE'];
			
			$EvalRes = $this->Evaluate($Text);
			
			if (is_array($EvalRes))
			{
				$Res = "";
				foreach ($EvalRes as $Val )
				{
					$Res .= $Val . ",";
				}
				$EvalRes = "--Array($Res)--";
			}
			return $EvalRes;
		}
		
		function Evaluate($Expr)
		{	global $DynBlockFuncCallback;
		
			$Res = null;
			$CurBlock = $this->CurBlock[$this->CurBlockLv];
				
			if ($Expr == "@VALUE")
				$Res = $CurBlock['VALUE'];
				
			if ($Expr == "@KEY")
				$Res = $CurBlock['KEY'];
			
			if ($Expr == "@NAME")
				$Res = $this->CurModule['NAME'];			
		
			if ($Expr == "@@NAME")
				$Res = $CurBlock['NAME'];
			if ($Expr == "@@LV")
				$Res = $this->CurBlockLv;
			if ($Expr == "@@UID")
				$Res = $CurBlock['UID'] ;
			if ($Expr == "@@EID")
				$Res = $this->CurBlockEID;
	
			//echo "----$Expr--------------------------\r\n";
				//		print_r($CurBlock); */
			
			if ($Res === null)
				if (isset($CurBlock['VALUE']))
				$Res = DynVar_GetValue($CurBlock['VALUE'], $Expr);
				
			// Get Local Variables
			if ($Res === null)
				$Res = DynVar_GetValue($CurBlock['VARS'], $Expr);
			
			// Get Global Variables
			if ($Res === null)
				$Res = DynVar_Get($Expr, null);
							
			if ($Res === null)
			{
				$BlockExpr = $Expr;
						
				{ 	// Run Another Module Block
					$BlockName = $BlockExpr;
					
					if (IsInString($BlockName, "("))
						$BlockName = GetStrBefore($BlockName, "(", false, false);
						
						
					
					$ModuleName = GetStrBefore($BlockName, "::", false, false);								
					if (($ModuleName == "") || ($ModuleName == "@"))
					{
						$Module = $this->CurModule;
						$BlockExpr = "@::" . $BlockExpr;
					}
					else
					{
						$Module = $this->GetModuleByName($ModuleName);
					}
					
					if ($Module === null)
						return "--Module $ModuleName is Invalid--";
						
					
					$BlockName = GetStrAfter($BlockExpr, "::", false, false);
					$LocalVars = null;
					if (IsInString($BlockName, "("))
					{
						$Params = GetStrBetweenCross($BlockName, "(", ")", false);				
											
						$BlockName = GetStrBefore($BlockName, "(", false);
						$ParamsVars = $this->SplitParam($Params, false);
	
						
						// Construct ParamVar to LocalVar
						$LocalVars = array();						
						foreach ($ParamsVars as $Key => $Val)
						{
							$ParamVal = $Val;							
							if ((strpos($ParamVal, "\"") === 0) || (strpos($ParamVal, "'") === 0))
							{
								$Value = trim($Val, "'\"");
								
								DynVar_SetValue($LocalVars, $Key, $Value);
							}
							else
							{
								DynVar_SetValue($LocalVars, $Key, $this->Evaluate($ParamVal) );
							}
						}
							
						//echo "......$BlockName.....$Params..............................\r\n";
							//print_r($ParamsVars);
							
					}
										
					if ($DynBlockFuncCallback !== null)
					{
						$FuncRes = $DynBlockFuncCallback ($this, $BlockName, $LocalVars);
						if ($FuncRes !== null)
							$Res = $FuncRes;
					}

					if (IsInString($BlockName, "~"))
					{
						$FuncName = substr($BlockName, 1);
						
						if (function_exists($FuncName))
						{						
							return call_user_func($FuncName);
						}
						else 
						{
							echo "--Func $FuncName is invalid--";
						}
					}
					else					
					if (($BlockName == "?") && (is_array($LocalVars)))
					{					
						//print_r($LocalVars);									
						if (array_key_exists("DEFAULT", $LocalVars))
						{
							$OnValue = $LocalVars['DEFAULT'];							
								
							if ($OnValue !== null)
							{
								if (strlen($OnValue) > 0)
									$Res = $OnValue;
								else
									$Res = $LocalVars['ELSE'];
							}
							else
								$Res = $LocalVars['ELSE'];
						}
						
						if (array_key_exists("IF", $LocalVars))
						{
								
							if ($this->TestCondition($LocalVars['IF']))
								$Res = $LocalVars['TRUE'];
							else
								$Res = $LocalVars['FALSE'];
						} 
						
						if (array_key_exists("CASE", $LocalVars))
						{
						//	echo "..................................................";
							//print_r($LocalVars);
							$Case = $LocalVars['CASE'];
							if (isset($LocalVars[$Case]))
								$Res = $LocalVars[$Case];
							else
								$Res = $LocalVars['ELSE'];
						}
						
						if (array_key_exists("CALL", $LocalVars))
						{						
							$Res = $this->Evaluate($LocalVars['CALL']);						
						}
						 
					}
					else
					
					
					if ($Res === null)
					{				
						
						$CurBlock['VARS'] = DynVar_PackVars( $CurBlock['VARS'], $LocalVars  );
						
						//echo "##$BlockName##########################################\r\n";
			
						/*
						// Process Parameters
						if (IsInString($BlockName, "("))
						{
							
						//echo "=====$BlockName=====$Params=================================\r\n";
							//print_r($ParamsVars);
							
							
							
								if (is_array($CurBlock['VARS']))						
									$CurBlock['VARS'] = array_merge_recursive( $CurBlock['VARS'], $LocalVars  );
							else
								$CurBlock['VARS'] = $LocalVars;  
							
							//print_r($CurBlock['VARS']);
							
							//$Block = DynModules_BlockByName($Module, $BlockName);						
						} */
											
						$Res = $this->BlockOutput($Module, $BlockName, $CurBlock['VARS']);
						//$Res = "$ModuleName::$BlockName ERROR";
						//if ($Res === null)
							
					}
				}
			}
			
			return $Res;	
			return "{%%($Expr) => \r\n $Res \r\n%%}";
		}
		
		function TestCondition($Expr)
		{			
		 	$Expr = trim($Expr);	 	
		 		
		 	if (($Expr[0] == "!") || ($Expr[0] == "~"))
		 	{
		 		$NotRes = true;
		 		$Expr = substr($Expr, 1);
		 	}
		 	
		 	$UExpr = strtoupper($Expr);
		 	if (($UExpr == "TRUE") || ($UExpr > 0))
		 		return true;
	
		 	$CurBlock = $this->CurBlock[$this->CurBlockLv];
			
			// Get Local Variables
		 	$Res = DynVar_GetValue($CurBlock['VARS'], $Expr);
			
			// Get Global Variables
			if ($Res === null)
				$Res = DynVar_Get($Expr, null);
				
			if ($NotRes)
			{
				if ($Res)
					$Res = 0;
				else 
					$Res = 1;
			}
			
			
			if ($Res)
				return true;
				
		 	return false;
		}
		
		
		function NativeEvaluate($Expr)
		{
			$Result = "";
			eval($Expr);
			return $Result;		
		}
		
		
		
		function SplitParam($Src, $UseDynVar = true)
		{		
			$Res = array();		
			$SrcLen = strlen($Src);
			$BucketLv = 0;
			$StIndex = 0;
			
			$Quote = "";
			for($Index = 0; $Index < $SrcLen; $Index ++)
			{
				$Ch = $Src[$Index];
				if ($Ch == '(')
					$BucketLv++;							
				if ($Ch == ')')
					$BucketLv--;								
				if (($Ch == '"') || ($Ch == "'"))
				{
					if ($Quote == "")
						$Quote = $Ch;
					else 
					{
						if  ($Quote == $Ch)
							$Quote = "";
					}
				}
											
				if (($BucketLv == 0) && ($Ch == '&') && ($Quote == ""))
				{
					$Val = substr($Src, $StIndex, $Index - $StIndex);
					$Vars[] = $Val;
					$Index++;
					$StIndex = $Index;
				}
			}
			$Vars[] = substr($Src, $StIndex );
			
			$Index = 0;
			while($Index < count($Vars))
			{		 
				$Val = $Vars[$Index];
				$VarName = urldecode( trim( GetStrBefore($Val, "=", false) ) );		
				$VarValue = urldecode( trim( GetStrAfter($Val, "=", false) ) );
			
			//echo "*****$Val*******$VarName********$VarValue*************\r\n";
			
				if ($UseDynVar)
					DynVar_SetValue($Res, $VarName, $VarValue);
				else
					$Res[ strtoupper( $VarName ) ] = $VarValue;
			
				$Index++;		
			}
			return $Res;	
		}
		
	}
	
	class DynModule
	{		
		public $DnNamespace = "DN:";
		public $ID;
		public $Version;
		public $Name;
		public $Elements;
		public $FileName, $FileData, $FileSize; 
	
		function GetModuleID($Module)
		{
			return sprintf("%u", crc32($Name));
		}
		
		/*--------------------------------------------------------
		 * DynModules_ParseModule
		 * 
		 * Parse Module to Dynamic Structure Data 
		 */
		function ParseModule()
		{		
			// Get Content To Parse into ModuleElements
			
			DynDebug("ParseModule ");			
			
			$this->Elements = $this->ParseContent($this->FileData);
			
			if(isset($this->Elements) )
			{				
				// Find module name before processing Control Tags
				foreach ($this->Elements as &$Element)
				{
					$ElementName = $Element['_NAME'];
					
									
					//	var_dump($Element);
					// DN:MODULE
					if ($ElementName == "MODULE") 
					{
						$this->Name = strtoupper( trim( $Element['NAME'] ) );
						$this->Version = strtoupper( trim( $Element['VERSION'] ) );				
						$this->ID = $this->GetModuleID($Module);
					}
					// DN:REQUIRE 
					else if ($ElementName == "REQUIRE") 
					{
						$RequireType = strtoupper($Element['TYPE']);
						if ($RequireType == "CODE")
						{
							$RequireFName = $Element['SRC'];
							$FuncName = $Element['INIT'];
							
								//echo "..$RequireFName..\r\n";								
							if (IsStrFileName( $RequireFName ))
							{
								$RequireFName = dirname($this->FileName) . "/" . $RequireFName;
								
								//echo "$RequireFName ...\r\n";
							}
							
							if ($RequireFName)
							{
								require_once($RequireFName);
								
								if (function_exists($FuncName))
								{
									call_user_func($FuncName);
								}
								else
								{
									echo "--Module $RequireFName Init Function not found--";	
								}
							} 
						}
					}
					// DN:MAIN
					else if ($ElementName == "MAIN")
					{
						$Element['_NAME'] = "BLOCK";
						$Element['NAME'] = "MAIN";
					}
				}		
				//$Module['NAME'] = $ModuleName;
				
				//DynDebug("Module-Name", $this->Name);

				// Process Control Tags
				$this->ProcessModuleTags();
				
				//var_dump($this);
				
				return 1;
			}
			return 0;
	
		}
		
		function ProcessModuleTags()
		{
			if (! isset($this->Elements))
				return "";
				
			DynDebug("ProcessModuleTags");
		
			
			foreach ($this->Elements as &$Element)
			{
				$ElementName = $Element['_NAME'];
				
				// DN:SCRIPT 
				if ($ElementName == "SCRIPT") 
				{
					$ScriptType = strtoupper( trim( $Element['TYPE'] ) );
					$ScriptSrc = trim( $Element['SRC'] );
					
					if ($ScriptType == "SERVER")
					{
						DynModules_NativeEvaluate($Element['_TEXT']);					
					}
					
					else if ($ScriptType == "CSS")
					{
						if ($ScriptSrc == "")					
							DynVar_GlobalContent_Add("CLIENT_CSS", $Element['_TEXT']);
						else
							DynVar_GlobalContent_AddLine("CLIENT_CSS_LINK", $ScriptSrc);
					}			
					else if (($ScriptType == "CLIENT") || ($ScriptType == "JS") || ($ScriptType == "JAVASCRIPT"))
					{
						if ($ScriptSrc == "")
							DynVar_GlobalContent_Add("CLIENT_SCRIPT", $Element['_TEXT']);
						else
							DynVar_GlobalContent_AddLine("CLIENT_SCRIPT_LINK", $ScriptSrc);
					} 
				}				
				// DN:VAR
				else if ($ElementName == "VAR")
				{
					$VarName = trim( $Element['NAME'] );
					
					
					if ($Element['MAP'] != "")
					{
						$VarValue = $this->Evaluate($Element['MAP']);
						echo "Var Eval: $VarValue \r\n";
					}	
					else
					{
						$VarValue = trim( $Element['_TEXT'] );
					}
					
					DynVar_Set($VarName, $VarValue);
									
				}
			}	
			return $this; 
		}
		
		
	
		/*--------------------------------------------------------
		 * DynModules_ParseContent
		 * 
		 * Parse Contents to Dynamic Structure Data 
		 */
		function ParseContent($Content)
		{	
			DynDebug("ParseContent");
			
			preg_match_all( "/<DN:(.*?)[\\/]?[^>]*>/ims", $Content, $TagHeads, PREG_OFFSET_CAPTURE);
					
			$TagStorage = null;
			
			if (sizeof($TagHeads[0]) > 0)
			{
				$TagHeads[] = null;
				
				foreach ($TagHeads[0] as $Value)
				{
					$TagPosSt = $Value[1];					
					$Res = $this->ParseTagHead( $Content, $Value[0], $TagPosSt);
				
					if (sizeof($TagHeads[0]) > 0)
					{
						$TagStorage[] = $Res;
					}
				}
			}
			return $TagStorage;
		}
		
		
		
		function ParseTagHead($Content, $TagHead, $TagPosSt)
		{				
			$TagHeadLen = strlen($TagHead);
			
			$TagComplete = false;
			if (strpos($TagHead, "/>") !== false)
				$TagComplete = true;
							
			// get tag name between <DN:???? />
			$this->Name = GetStrBetween($TagHead, $this->DnNamespace, " ", false, true);
					
			// remove < >
			$this->Name = strtoupper(trim($this->Name, "<>"));
			
			$Res['_TAGHEAD'] = $TagHead;
			$Res['_NAMESPACE'] = $this->DnNamespace;
			$Res['_NAME'] = $this->Name ;
			$Res['_COMPLETE'] = $TagComplete;
			
			// extract attributes & value
			//preg_match_all('/\w+\s*=\s*"[^"]*"/ims', $TagHead, $Attributes);
			preg_match_all('/([^\s=]+)\s*=\s*(\'[^<\']*\'|"[^<"]*"|[^ ^>]+)/ims', $TagHead, $Attributes);
			
			
			foreach ($Attributes[0] as $Key => $Value)
			{
				//echo $Key . $Value;
				list($AttrName, $AttrValue) = split("=", $Value);
				
				if (strlen($AttrName) > 0)
				{
					$Res[strtoupper($AttrName)] = trim($AttrValue, " '\"");
				}
			}
			
			
			// find closing tag
			if (!$TagComplete)
			{
				$TagContSt = $TagPosSt + $TagHeadLen;
				$TagFoot = "</" . $this->DnNamespace . $this->Name;
				$TagFootSt = stripos( $Content, $TagFoot , $TagContSt);
			
				$Res['_FOOT'] = $TagFoot;
				$Res['_TEXT'] = substr($Content, $TagContSt, $TagFootSt - $TagContSt);
	
				/*
				$Subs = $this->ParseContent($Res['_TEXT']);
				if ($Subs !== null)
				$Res['_ELEMENTS'] = $Subs; 		 
				*/
			
			}
					
			return $Res;		
		}
		
	
		
		// DynModules_BlockByName - Find Block by Name
		
		function BlockByName($BlockName)
		{
			if (isset($this->Elements) && ($BlockName != ""))
			{
				$BlockName = strtoupper($BlockName);
				
				foreach($this->Elements as $Element)
				{
					if (($Element['_NAME'] == "BLOCK") && (strtoupper( $Element['NAME'] ) == $BlockName) )
						return $Element;
				}
			}
			return null;
		}
		
		
				
		
		
		
		
		
		
		
		
	}
	
	
	
?>