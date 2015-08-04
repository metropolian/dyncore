<?php
/**
* DynDb 
*
* Database Connection Class
*
* @package  DynCore
* @author   Metro Kobchaipuk <metropolian@live.com>
* @version  $Revision: 1.0 $
*/

	class DynDb
	{
		public $server_type = 'mysql';
		private $host = '';
		private $username = '';
		private $password = '';
		public $db_name = '';		
		public $current_db_name = '';
		
		public $log_enabled = true;
        public $logs;
        
		public $client = null;
		public $query;		
		public $result_mode = 1;
		
		public $connected = false;		
		public $connected_time = 0;
		
		public $fetch_mode = PDO::FETCH_ASSOC;
		
		function __construct($server, $host, $name, $pass, $dbname) 
		{   
			$this->server_type = $server;
			$this->host = $host;
			$this->username = $name;
			$this->password = $pass;
			$this->db_name = $dbname;

            $this->logs = GetLog();
        }
		
		function Log($type, $src)
		{
			if ($this->log_enabled == false)
				return;            
            $this->logs->Write($type, $src); 
		}
		
		function Connect()
		{
			if ($this->connected)
				return true;
			
			try
			{
				$con_str = "{$this->server_type}:host={$this->host}";
					
				$this->client = new PDO($con_str, $this->username, $this->password);
				//$this->client->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    			$this->client->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    			$this->client->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

				$this->connected = true;
				$this->connected_time = round(microtime(true) * 1000);
				
				$this->Log('db', 'connected to ' . $con_str);
				
				$this->UseDatabase($this->db_name);
				return true;
			}
			catch(PDOException $ex)
			{
				$this->connected = false;
				$this->client = null;
				$this->Log('error', $ex);
			}
			return false;
		}
		
		function UseDatabase($DbName)
		{
			if ($this->current_db_name == $DbName)
				return;

			if ($this->connected)
			{				
				$this->Execute("USE $DbName");
				$this->current_db_name = $DbName;
				return true;
			}
			else
			{
				$this->db_name = $DbName;
				return true;
			}
			return false;
			
		}
		
		function Select($Sql, $P = null)
		{			
			if ($this->Query($Sql, $P))
				return $this->query->fetchAll();
			return null;
		}
		
		function SelectValue($Sql, $P = null)
		{
			if ($this->Query($Sql, $P))
			{
				return $this->query->fetchColumn(0);
			}
			return null;
		}
		
		function SelectRow($Sql, $P = null)
		{
			if ($this->Query($Sql, $P))
				return $this->query->fetch($this->fetch_mode);
		}
		
		
		function Date($Timestamp = 0)
		{
			if ($Timestamp == 0)
				$Timestamp = time();
			$res = date('Y-m-d H:i:s', $Timestamp);
			return $res;
		}
		
		function SqlString($Src)
		{			
			if ( ! $this->Connect() )
				return;			
			return $this->client->quote($Src);
		}
		
	
		function Query($Sql, $P)
		{
			if ( ! $this->Connect() )
				return;			
			try
			{								
				$this->Log('sql', "query: $Sql");				
				
				if ( is_array( $P ) )
				{
					foreach($P as $K => $V)
					{
						$_K = $K;
						$K = str_replace('@', ':', $K);
						$Sql = str_replace($_K, $K, $Sql);
					
						unset($P[$_K]);
						$P[$K] = $V;
					}
				}
				else
					$P = null;
				
				$this->query = $this->client->prepare($Sql);
				$this->query->execute($P);
				return $this->query;
				
			}
			catch(PDOException $ex)
			{
				$this->Log('error', $ex);
			} 			
			return null;
		}

		function Execute($Sql, $P = null)
		{
			if ( ! $this->Connect() )
				return;
			
			try
			{
				$this->Log('sql', "exec: $Sql");
				
				if ( is_array($P ) )
				{
					foreach($P as $K => $V)
					{
						$_K = $K;
						$K = str_replace('@', ':', $K);
						$Sql = str_replace($_K, $K, $Sql);
						
						unset($P[$_K]);
						$P[$K] = $V;						
					}
					
					$this->query = $this->client->prepare($Sql);
				
					if ($this->query->execute($P))
						return $this->query->rowCount();
					return 0;
				}
				
				return $this->client->exec($Sql);
			}
			catch(PDOException $ex)
			{
				$this->Log('error', $ex);
			} 			
		}
		
		function Insert($TbName, $Data)
		{				
			if (($TbName == null) || ($Data == null))
				return;
			
			if ( ! $this->Connect() )
				return;
			
			foreach($Data as &$V)
			{				
				if (is_string($V))
					$V = $this->client->quote($V);
				else
				if ($V === null)
					$V = 'null';
			}
			$Columns = "(`" . implode("`,`", array_keys( $Data )) . "`)";
			$Values = "(" . implode(",", array_values( $Data )) . ")";
			
			$Sql = "INSERT INTO `$TbName` " . $Columns . " VALUES " . $Values;
			
			if ( $this->Execute($Sql) )
				return $this->client->lastInsertId();			
			return 0;
		}
		
		
		function Update($TbName, $Data, $Cond, $P = null)
		{				
			if (($TbName == null) || ($Data == null))
				return;
			
			if ( ! $this->Connect() )
				return;
			
			$Fields = array();
			foreach($Data as $K => $V)
			{
				if (is_string($V))
					$V = $this->client->quote($V);
				else
				if ($V === null)
					$V = 'null';
				
				$Fields[] = "`$K`=$V";
			}
			$Values = implode(',', $Fields);			
			$Sql = "UPDATE `$TbName` SET $Values WHERE $Cond";
			
			return $this->Execute($Sql, $P);
		}
		
		function Delete($TbName, $Cond, $P = null)
		{
			if (($TbName == null) || ($Cond == null))
				return;
			
			if ( ! $this->Connect() )
				return;
			
			$Sql = "DELETE FROM `$TbName` WHERE $Cond";			
			return $this->Execute($Sql, $P);
		}
		
		function ShowLogs()
		{
            $this->logs->ShowLogs('db');
		}
		
		function ShowErrors()
		{            
			$this->logs->ShowLogs('dberror');
		}
		
	}

?>