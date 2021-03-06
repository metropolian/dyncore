<?php

	function base_convert_ex($value,$quellformat,$zielformat)
	{
	  $vorrat = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	  if(max($quellformat,$zielformat) > strlen($vorrat))
		  trigger_error('Bad Format max: '.strlen($vorrat),E_USER_ERROR);
	  if(min($quellformat,$zielformat) < 2) 
		  trigger_error('Bad Format min: 2',E_USER_ERROR);
	  $dezi   = '0';
	  $level  = 0;
	  $result = '';
	  $value  = trim((string)$value,"\r\n\t +");
	  $vorzeichen = '-' === $value{0}?'-':'';
	  $value  = ltrim($value,"-0");
	  $len    = strlen($value);
	  for($i=0;$i<$len;$i++)
	  {
		$wert = strpos($vorrat,$value{$len-1-$i});
		if(FALSE === $wert) trigger_error('Bad Char in input 1',E_USER_ERROR);
		if($wert >= $quellformat) trigger_error('Bad Char in input 2',E_USER_ERROR);
		$dezi = bcadd($dezi,bcmul(bcpow($quellformat,$i),$wert));
	  }
	  if(10 == $zielformat) return $vorzeichen.$dezi; // abk?rzung
	  while(1 !== bccomp(bcpow($zielformat,$level++),$dezi));
	  for($i=$level-2;$i>=0;$i--)
	  {
		$factor  = bcpow($zielformat,$i);
		$zahl    = bcdiv($dezi,$factor,0);
		$dezi    = bcmod($dezi,$factor);
		$result .= $vorrat{$zahl};
	  }
	  $result = empty($result)?'0':$result;
	  return $vorzeichen.$result ;
	}
?>