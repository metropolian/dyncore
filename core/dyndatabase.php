<?php 

	$DynDatabaseLogIndex = 0;
	$DynDatabaseLog = array();
	
	
	function DynDb_Connect($Configs)
	{
	
		if (!is_array($Configs))
			return false;
		
		
		$Res = mysql_connect($Configs['SERVER'], $Configs['USERNAME'], $Configs['PASSWORD']);
		
		if ($Res)
		{
			mysql_query("SET NAMES UTF8");
			mysql_selectdb($Configs['DATABASE']);
			return $Res;		
		}
		return false;
	}
		

	function DynDb_SqlQuote($Value)
	{
		if (is_null($Value))
			return "NULL";
		if (is_string($Value))
			return "'" . mysql_escape_string($Value) . "'";
		return $Value;
	}
	
	/*  --------------------------------------------------------------------------- 
	 *  GetStrCombineKVParams - Combine Key-Value Pair Parameter
	 * 
	 *  Format Example:      $PrefK $Key $SuffK $Mid $PrefV $Value $SuffV $Spr ...
     * 						[keyaaa]=(valueaaa), [keybbb]=(valuebbb), [keyccc]=(valueccc) 
	 * 	 				
	 */	
	function DynDb_SqlCombineKVParams($Params, $PrefK, $SuffK, $Middle, $PrefV, $SuffV, $Spr, $StIndex = 0, $Max = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = "";
		$Index = $StIndex;
		while($Index + 2 <= $MaxParams)
		{
			$Res .= $PrefK . $Params[$Index] . $SuffK;			
			$Index++;
			$Res .= $Middle . DynDb_SqlQuote($Params[$Index]);			
			$Index++;
			if ($Index + 2 <= $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;
	}
	
	/*  ---------------------------------------------------------------------------
	 * 	GetStrCombineParamsArgs - Combine Parameter by Function Arguments
	 * 
	 *  Format Example:      $PrefK $Key $SuffK $Mid $PrefV $Value $SuffV $Spr ...
     * 						aaaa , bbb , cccc 
	 * 	 				
	 */
	
	function DynDb_SqlCombineParams($Params, $PrefV, $SuffV, $Spr, $StIndex = 0, $Inc = 1, $Max = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = "";
		$Index = $StIndex;
		while($Index < $MaxParams)
		{
			$Res .= $PrefV . $Params[$Index] . $SuffV;
			$Index += $Inc;			
			if ($Index < $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;
	}
	
	function DynDb_SqlCombineQuoteParams($Params, $Spr, $StIndex = 0, $Inc = 1, $Max = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = "";
		$Index = $StIndex;
		while($Index < $MaxParams)
		{
			$Res .= DynDb_SqlQuote($Params[$Index]);
			$Index += $Inc;			
			if ($Index < $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;
	}
	
	
	function DynDb_SqlParameters()
	{
		$Params = func_get_args();
		return DynDb_SqlConvertParameters($Params[0], $Params, 1);		
	}
	
	function DynDb_SqlConvertParameters($Src, $Params, $StIndex = 0)
	{
		if ($Max > 0)
			$MaxParams = $Max;
		else 
			$MaxParams = count($Params);		
		$Res = $Src;
		$Index = $StIndex;
		while($Index + 2 <= $MaxParams)
		{
			$Key = $Params[$Index];		
			$Index++;
			$Value = $Params[$Index];						
			$Res = str_ireplace($Key, DynDb_SqlQuote($Value), $Res);			
			$Index++;
			if ($Index + 2 <= $MaxParams)
				$Res .= $Spr;			
		}
		return $Res;

	}
	
	function DynDb_SqlDate($TimeStamp = null)
	{
		if (is_null($TimeStamp))
			$TimeStamp = time();
		return date("Y-m-d H:i:s", $TimeStamp);
	}
	
	
	
	
	
	
	
	
	
	
	
	/*  ---------------------------------------------------------------------------
	 * 	DynDb_Exec - Run SQL Command
	 * 
	 * 	 				
	 */
	
	function DynDb_ExecParams( )
	{
		$Params = func_get_args();
		return DynDb_Exec( DynDb_SqlConvertParameters($Params[1], $Params, 2) );		
		
	}
	
	function DynDb_Exec( $Command )
	{		
		{
			// fixed for [ ] bucket (for mysql
			//$Command = str_replace( "[", "`", str_replace("]", "`", $Command  ) );
			// fixed for GetDate() to CURRENT_TIMESTAMP
			$Command = str_replace( "'GetDate()'", "CURRENT_TIMESTAMP", $Command );
			
		

			DynDb_LogCommand($Command);						
			$DBRes = mysql_query( $Command);			
			if ($DBRes)
			{			
				DynDb_Log($Command, "AFFECT", mysql_affected_rows());
			}				
			else
			{			
				DynDb_Log($Command, "ERRORNO", mysql_errno());
				DynDb_Log($Command, "ERRORDESC", mysql_error());
			}
			DynDb_Log($Command, "TIME", 0);
			
			return $DBRes;
		}
	}
	
	
	function DynDb_Delete($TableName, $Condition)
	{
		$Query = "DELETE FROM `$TableName` ";
		if ($Cond != "")
		{
			if (stripos($Condition, "WHERE") === false)
				$Query .= "WHERE ";
			$Query .= $Condition;
			return DynDb_Exec($Condition);
		}	
		return 0;	 		
	}
	
	
	
	function DynDb_InsertExec($TableName, $Args, $StIndex)
	{
		$Res = 0;
		$ArgsCount = count($Args);
		if ($Args >= 2)
		{		
			if (($ArgsCount - $StIndex) % 2 > 0)
				$ArgsCount -= 1;
				
			$Columns = "(" . DynDb_SqlCombineParams($Args, "`", "`", ",", $StIndex, 2, $ArgsCount) . ")";
			$Values = "(" . DynDb_SqlCombineQuoteParams($Args, ",", $StIndex + 1, 2, $ArgsCount) . ")";			
			$Sql = "INSERT INTO `$TableName` " . $Columns . " VALUES " . $Values;

			if (DynDb_Exec($Sql))
			{		
				$Res = mysql_insert_id();
				DynDb_Log($Command, "INSERTID", $Res);
			}
		}
		return $Res;
	}
		
	
	function DynDb_Insert($TableName)
	{
		$Args = func_get_args();
		return DynDb_InsertExec($TableName, $Args, 1);				
	}
	
		

	
	function DynDb_Update($TableName)
	{
		$Args = func_get_args();
		$ArgsCount = func_num_args();
		return DynDb_UpdateExec($TableName, $Args, 1);
	}
	
	function DynDb_UpdateExec($TableName, $Args, $StIndex)
	{
		$ArgsCount = count($Args);
		if ($Args >= 2)
		{
			$Values = DynDb_SqlCombineKVParams($Args, "`", "`", "=", "'", "'", ",",  $StIndex);
			$Sql = "UPDATE `$TableName` SET " . $Values  ;
			if (($ArgsCount - $StIndex) % 2 > 0)
			{
				$Condition = $Args[$ArgsCount - 1];
				if (stripos($Condition, "WHERE") === false)
					$Sql .= " WHERE ";
				
				$Sql .= $Condition;
			}
								
			if (DynDb_Exec($Sql))
				return mysql_affected_rows();			
		}
		return 0;
	}
	
	
	
	
	function DynDb_Merge($TableName)
	{
		$Args = func_get_args();
		$ArgsCount = func_num_args();
		return DynDb_MergeExec($TableName, $Args, 1);
	}
	
	
	function DynDb_MergeExec($TableName, $Args, $StIndex)
	{
		$ArgsCount = count($Args);
		$Res = DynDb_UpdateExec($TableName, $Args, $StIndex);
		if ($Res <= 0)
		{
			$Res = DynDb_InsertExec($TableName, $Args, $StIndex); 
		}
		return $Res;
	}
	
	
	
	
	
	
	
	function DynDb_TableRowsCount($HTable)
	{
		if ($HTable)		
			return mysql_num_rows($HTable);
		return 0; 
	}
	
	function DynDb_TableReadRow($HTable, $ResType = 0)
	{
		$AssocType = MYSQL_NUM;
		switch ($ResType)
		{
			case 2: 
				$AssocType = MYSQL_ASSOC;
				break;
			case 3:
				$AssocType = MYSQL_BOTH;
				break;
							
		}
		if ($HTable)
		{
			if ($ResType > 0)		
				return mysql_fetch_array($HTable, $AssocType);
			else
				return mysql_fetch_row($HTable);
		}
		return null; 
	}
	
	
	function DynDb_SelectTable( $Query, $IsSingleRow = false )
	{
		$Res = array();
		$HTable = DynDb_Exec($Query);
		if ($HTable)
		{
			$RowCount = DynDb_TableRowsCount($HTable);
			if ($RowCount > 0 )
			{
				if ($IsSingleRow)
					return DynDb_TableReadRow($HTable, 2);
				for ($Index = 0; $Index < $RowCount; $Index++)
				{
					$Res[] = DynDb_TableReadRow($HTable, 2);
				}
			}
		}
		return  $Res;
	}
	
	function DynDb_SelectValue( $Query, $Def )
	{
		$Res = $Def;
		$HTable = DynDb_Exec($Query);
		if ($HTable)
		{
			$RowCount = DynDb_TableRowsCount($HTable);
			if ($RowCount > 0 )
			{
				$ResRow = DynDb_TableReadRow($HTable, 0);
				return $ResRow[0];
			}
		}
		return  $Res;
	}
	
	function DynDb_LogCommand($Sql)
	{	global $DynDatabaseLog, $DynDatabaseLogIndex;
			
		$DynDatabaseLogIndex++;
		$DynDatabaseLog[$DynDatabaseLogIndex] =	
			array('COMMAND' => $Sql,
				  'TIME' => Microtime_Begin() );
	}
	
	function DynDb_Log($Sql, $LogName, $LogValue)
	{	global $DynDatabaseLog, $DynDatabaseLogIndex;

		if ($LogName == "TIME")
		{
			$Time = Microtime_CatchResult($DynDatabaseLog[$DynDatabaseLogIndex][$LogName]);
		
			$DynDatabaseLog[$DynDatabaseLogIndex][$LogName] = sprintf("%.4f", $Time);
		}
		else
			$DynDatabaseLog[$DynDatabaseLogIndex][$LogName] = $LogValue;
	}
	
	
	
	
?>