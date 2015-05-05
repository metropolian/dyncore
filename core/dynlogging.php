<?php

    class DynLog
    {        
        public $log_name = '';
        public $start_time = 0;
        public $last_time = 0;
        public $logs = array();
        
        function __construct($Name = '')
        {
            $this->log_name = $Name;
            $this->last_time = $this->start_time = $this->CurrentTime();
            
        }
        
        function CurrentTime()
        {
            return ((float)microtime(true));            
        }
        
        function Write($type, $src)
        {
            $cur_time = $this->CurrentTime();
            $dif_time = $cur_time - $this->last_time;
            $this->last_time = $cur_time;
			
			$callers = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            if (count($callers) >= 2)
			$fname = basename( $callers[2]['file'] ) . ':' . $callers[2]['line'];
			
			if ($type == 'error')
				$msg = '->' . $callers[1]['function'] . ' ' . $src;
			else
				$msg = $src;
			
			$this->logs[] = array( 
                't' => time(),
                'tcur' => $cur_time - $this->start_time,
                'tdif' => $dif_time,
                'type' => $type,
                'fname' => $fname,
                'msg' => $msg
            );
        }
        
        function ShowLogs($Type = '')
        {
			$res = "";
			if ( is_array( $this->logs ) )
			foreach($this->logs as $d)
            {
                if ($type != '')
                    if ($type != $d['type'])
                        continue;
                $cur_time = number_format( $d['tcur'], 3);
                $dif_time = number_format( $d['tdif'], 3);
			    $date = date('Y-m-d H:i:s', $d['t']);
                $msg = ( is_array( $d['msg']) ) ? print_r($d['msg'], true) : $d['msg'];
                $res .= "{$date} @{$cur_time} ~{$dif_time} {$d['type']} ({$d['fname']}) {$msg} \r\n";
            }
			echo $res;             
        }
    }


    $DynLogs = array();

    function GetLog($Name = 'main')
    {   global $DynLogs;
     
        if (isset($DynLogs[$Name]))
            return $DynLogs[$Name];     
        return $DynLogs[$Name] = new DynLog($Name);
    }

    function LogWrite($Type, $V)
    {   
        $Log = GetLog();
        $Log->Write($Type, $V);
    }

   
?>
