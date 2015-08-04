<?php 

    class DynTimezoneList
    {
        public $Timezones = array();

        function OffsetFormat($offset) {
                $hours = $offset / 3600;
                $remainder = $offset % 3600;
                $sign = $hours > 0 ? '+' : '-';
                $hour = (int) abs($hours);
                $minutes = (int) abs($remainder / 60);
                if ($hour == 0 AND $minutes == 0) {
                    $sign = ' ';
                }
                return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes,2, '0');

        }

        public function Generate()
        {
            $this->Timezones = array();
            $utc = new DateTimeZone('UTC');
            $dt = new DateTime('now', $utc);
            foreach(DateTimeZone::listIdentifiers() as $tz) {
                $T = array();

                $current_tz = new DateTimeZone($tz);
                $T['offset'] = $current_tz->getOffset($dt);
                $transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
                $T['abbr'] = $transition[0]['abbr'];
                $T['name'] = $tz;
                $T['clock'] = $this->OffsetFormat($T['offset']);
                $this->Timezones[] = $T;
            }
            
        }
        
        public function ToArray()
        {
            $res = array();
            foreach ($this->Timezones as $T) 
            {
                $res[$T['offset']] = "{$T['name']} ({$T['clock']}) {$T['abbr']} ";
            }
            return $res;
        }
        
    }

/*
    
    echo '<pre>';

    $TL = new DynTimezoneList();
    
    $TL->Generate();
        
        var_dump($TL->ToArray());
        
    echo '</pre>'; */
?>