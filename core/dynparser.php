<?php 
/*-------------------------------------------------------------------------------------------------------------------------
	Dynamic - Core - Parsers
	DML Corp. 2009
---------------------------------------------------------------------------------------------------------------------------*/

	function DynXml_To_Array($xml) {
        $xmlary = array();
               
        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

        preg_match_all($reels, $xml, $elements);
        
        foreach ($elements[1] as $ie => $xx) {
                $xmlary[$ie]["name"] = $elements[1][$ie];
               
                if ($attributes = trim($elements[2][$ie])) {
                        preg_match_all($reattrs, $attributes, $att);
                        foreach ($att[1] as $ia => $xx)
                                $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                }

                $cdend = strpos($elements[3][$ie], "<");
                if ($cdend > 0) {
                        $xmlary[$ie]["text"] = substr($elements[3][$ie], 0, $cdend - 1);
                }

                if (preg_match($reels, $elements[3][$ie]))
                        $xmlary[$ie]["elements"] = DynXml_To_Array($elements[3][$ie]);
                else if ($elements[3][$ie]) {
                        $xmlary[$ie]["text"] = $elements[3][$ie];
                }
        }

        return $xmlary;
	}

	function DynXml_GetChildren($Root, $TagName = null, $Index = -1)
	{	
		if (!isset($Root))
			return null;
			
		if ($TagName === null)
			return $Root['elements'];
			
		$Res = null;
		$Cnt = 0;
		foreach($Root['elements'] as $Element)
		{
			//var_dump($Element['name']);
			if ($Element['name'] == $TagName)
			{
				if ($Index < 0)
					$Res[] = $Element;					
				else
					if ($Cnt == $Index)
						return $Element;
					

				$Cnt++;
			}
		}
		return $Res;		
	}
	
	function DynXml_GetElement($Root, $TagName, $Index)
	{
		return DynXml_GetElementByTagName($Root, $TagName, $Index);
	}
	
	function DynXml_GetElementByTagName($Root, $TagName, $Index)
	{		
		if (!isset($Root))
			return null;
			
		//print_r($Root);
			
		$Cnt = 0;
		foreach($Root as $Element)
		{
			if ($Element['name'] == $TagName)
			{
				if ($Cnt == $Index)
					return $Element;
				$Cnt++;
			}
		}
		return null;
	}
	
	function DynXml_GetChildCount($Root, $TagName = null)
	{
		if ($TagName === null)
			return count($Root);
			
		$Cnt = 0;
		foreach($Root as $Element)
		{
			if ($Element['name'] == $TagName)
				$Cnt++;
		}
		return $Cnt;		
	}

/*	header("content-type: text/plain");
	
	
	
	$XmlRoot = DynXml_To_Array( file_get_contents("default.xml") );
	
	$Root = DynXml_GetElementByTagName($XmlRoot, "arangsig", 0);
			
	$Layouts = DynXml_GetElementByTagName($Root['elements'], "layout", 0);
	
	$Res = $Layouts;
	print_r($Res);
	
	*/


/*
    Working with XML. Usage: 
    $xml=xml2ary(file_get_contents('1.xml'));
    $link=&$xml['ddd']['_c'];
    $link['twomore']=$link['onemore'];
    // ins2ary(); // dot not insert a link, and arrays with links inside!
    echo ary2xml($xml);
*/

// XML to Array
function Dyn_XmlArrayStruct(&$string) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $string, $vals, $index);
    xml_parser_free($parser);

    $mnary=array();
    $ary=&$mnary;
    
    //var_dump($vals);
    foreach ($vals as $r) {
        $t=$r['tag'];
        if ($r['type']=='open') {
            if (isset($ary[$t])) {
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            $cv=array();
            $cv['_p']=&$ary;
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_attrib'][$k]=$v;}
            $ary=&$cv;

        } elseif ($r['type']=='complete') {
            if (isset($ary[$t])) { // same as open
                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
                $cv=&$ary[$t][count($ary[$t])-1];
            } else $cv=&$ary[$t];
            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_attrib'][$k]=$v;}
            $cv['_text']=(isset($r['value']) ? $r['value'] : '');

        } elseif ($r['type']=='close') {
            $ary=&$ary['_p'];
        }
    }    
    
    _del_p($mnary);
    return $mnary;
}

// _Internal: Remove recursion in result array
function _del_p(&$ary) {
    foreach ($ary as $k=>$v) {
        if ($k==='_p') unset($ary[$k]);
        elseif (is_array($ary[$k])) _del_p($ary[$k]);
    }
}

// Array to XML
function ary2xml($cary, $d=0, $forcetag='') {
    $res=array();
    foreach ($cary as $tag=>$r) {
        if (isset($r[0])) {
            $res[]=ary2xml($r, $d, $tag);
        } else {
            if ($forcetag) $tag=$forcetag;
            $sp=str_repeat("\t", $d);
            $res[]="$sp<$tag";
            if (isset($r['_a'])) {foreach ($r['_a'] as $at=>$av) $res[]=" $at=\"$av\"";}
            $res[]=">".((isset($r['_c'])) ? "\n" : '');
            if (isset($r['_c'])) $res[]=ary2xml($r['_c'], $d+1);
            elseif (isset($r['_v'])) $res[]=$r['_v'];
            $res[]=(isset($r['_c']) ? $sp : '')."</$tag>\n";
        }
        
    }
    return implode('', $res);
}

// Insert element into array
function ins2ary(&$ary, $element, $pos) {
    $ar1=array_slice($ary, 0, $pos); $ar1[]=$element;
    $ary=array_merge($ar1, array_slice($ary, $pos));
}
	?>