<?

// l() allows easy translation.
function l($input,$lang=false){
	if (!is_array($input)) return l(array_combine(array_slice(array_keys(languages()),0,func_num_args()), func_get_args()));
	if (!$lang) $lang = language();
	return isset($input[$lang])?$input[$lang]:current($input);
}

// e() allows easy escaping of characters that might mess up input fields
function e($input){
	return htmlentities($input,ENT_COMPAT,'UTF-8',false);
}

// htmlentities_all() is htmlentities for arrays
function htmlentities_all($data,$keephtml=false){
	if (is_array($data)) {
		foreach ($data as $key => $value) {
			$data[$key] = htmlentities_all($value,$keephtml);
		}
	}
	if (is_string($data)) {
		$data = htmlentities($data,ENT_QUOTES,'UTF-8',false);
		if ($keephtml) $data = htmlspecialchars_decode($data);
	}

	return $data;
}

// linkify() converts URL's in text to clickable links
function linkify($string) {
	if (preg_match_all('/((http|https):\/\/)?([a-z0-9-]+\.)?[a-z0-9-]+(\.[a-z]{2,6}){1,3}(\/[a-z0-9.,_\/~#&=;%+?-]*)?/is',$string,$matches,PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$fullurl = $url = $match[0];
			if (!strstr($fullurl,'://')) $fullurl = 'http://'.$fullurl;
			$parsedurl = parse_url($fullurl);
			$label = str_replace('www.','',$parsedurl['host']);
			if (isset($parsedurl['path'])) $label .= $parsedurl['path'];
			$string = str_replace($url,'<a href="'.$fullurl.'" rel="nofollow">'.$label.'</a>',$string);
		}
	}
	return $string;
}

function email_encode($email_address) {
	return mb_encode_numericentity($email_address, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
}

function capitalize($string, $all_words = true) {
	if (strtolower($string) == $string || strtoupper($string) == $string) return $all_words ? ucwords(strtolower($string)) : ucfirst(strtolower($string));
	else return $string;
}

function phone_format($string) {

	$zonesnl = array(
		'10','111','113','114','115','117','118','13','14','15','161','162','164','165','166','167','168','172','174','180','181',
		'182','183','184','186','187','20','222','223','224','226','227','228','229','23','24','251','252','255','26','294','297',
		'299','30','313','314','315','316','317','318','320','321','33','341','342','343','344','345','346','347','348','35','36',
		'38','40','411','412','413','416','418','43','45','46','475','478','481','485','486','487','488','492','493','495','497',
		'499','50','511','512','513','514','515','516','517','518','519','521','522','523','524','525','527','528','529','53','541',
		'543','544','545','546','547','548','55','561','562','566','570','571','572','573','575','577','578','58','591','592','593',
		'594','595','596','597','598','599','70','71','72','73','74','75','76','77','78','79','66','67','800','82','84','85','87','88',
		'900','906','909','970','91','14',
		'61','62','63','64','65','68','69'
	);
	$zonesbe = array(
		'10','11','12','13','14','15','16','19','2','3','4','50','51','52','53','54','55','56','57','58','59','60','61','63','64','65',
		'67','68','69','71','80','81','82','83','84','85','86','87','89','9','70','77','78','800','900','902','903','905','909',
		'468','470','471','472','473','474','475','476','477','478','479','483','484','485','486','487','488','489',
		'490','491','492','493','494','495','496','497','498','499'
	);
	
	// Remove unneeded zero's and spaces
	$number = preg_replace('/^00[0]+/s','00',
		str_replace('(0)','',
			preg_replace('/\s/s','',$string)
		)
	);
	
	// Format country code and remove non-numeric characters
	$number = preg_replace('/^3([1-2])/s','003$1',
		preg_replace('/[^0-9]/s','',
			preg_replace('/^\+/s','00',$number)
		)
	);
	
	// Add a leading zero if needed
	if (substr($number,0,1) != '0') $number = '0'.$number;
	
	// Too short to format
	if (strlen($number) < 8) return $string;
	
	// Determine country
	$country = false;
	if (substr($number,0,4) == '0032' && strlen($number) == 13 && in_array(substr($number,4,3),$zonesbe)) $country = 'be';
	else if (substr($number,0,4) == '0031' && strlen($number) == 13 && in_array(substr($number,4,2),$zonesnl)) $country = 'nl';
	else if (substr($number,0,4) == '0032' && strlen($number) == 12 && in_array(substr($number,4,3),$zonesbe)) $country = 'be';
	else if (substr($number,0,4) == '0032' && strlen($number) == 12 && in_array(substr($number,4,2),$zonesbe)) $country = 'be';
	else if (substr($number,0,4) == '0032' && strlen($number) == 12 && in_array(substr($number,4,1),$zonesbe)) $country = 'be';
	else if (substr($number,0,4) == '0031' && strlen($number) == 13 && in_array(substr($number,4,3),$zonesnl)) $country = 'nl';

	// Temporarily remove country code and leading zero's	
	if (preg_match('/^003[1-2]/is',$number,$country_code)) $number = substr($number,4);
	if ($country_code) $country_code = $country_code[0];
	$number = preg_replace('/^[0]+/s','',$number);
	
	// Format number
	if ($country == 'be' && in_array(substr($number,0,3),$zonesbe) && strlen($number) == 8) $number = substr($number,0,3).' '.substr($number,3,2).' '.substr($number,5);
	else if ($country == 'be' && in_array(substr($number,0,3),$zonesbe)) $number = substr($number,0,3).' '.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
	else if ($country == 'be' && in_array(substr($number,0,2),$zonesbe)) $number = substr($number,0,2).' '.substr($number,2,2).' '.substr($number,4,2).' '.substr($number,6);
	else if ($country == 'be' && in_array(substr($number,0,1),$zonesbe)) $number = substr($number,0,1).' '.substr($number,1,3).' '.substr($number,4,2).' '.substr($number,6);
	else if ($country == 'nl' && in_array(substr($number,0,3),$zonesnl) && strlen($number) > 9) $number = substr($number,0,3).'-'.substr($number,3,3).' '.substr($number,6,2).' '.substr($number,8);
	else if ($country == 'nl' && in_array(substr($number,0,3),$zonesnl)) $number = substr($number,0,3).'-'.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
	else if ($country == 'nl' && in_array(substr($number,0,2),$zonesnl) && substr($number,0,1) == '6') $number = substr($number,0,1).'-'.substr($number,1,2).' '.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
	else if ($country == 'nl' && in_array(substr($number,0,2),$zonesnl)) $number = substr($number,0,2).'-'.substr($number,2,3).' '.substr($number,5,2).' '.substr($number,7);
	else if (in_array(substr($number,0,3),$zonesbe) && strlen($number) == 8) $number = substr($number,0,3).' '.substr($number,3,2).' '.substr($number,5);
	else if (in_array(substr($number,0,3),$zonesbe)) $number = substr($number,0,3).' '.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
	else if (in_array(substr($number,0,2),$zonesbe)) $number = substr($number,0,2).' '.substr($number,2,2).' '.substr($number,4,2).' '.substr($number,6);
	else if (in_array(substr($number,0,1),$zonesbe)) $number = substr($number,0,1).' '.substr($number,1,3).' '.substr($number,4,2).' '.substr($number,6);
	else if (in_array(substr($number,0,3),$zonesnl) && strlen($number) > 9) {
		$number = substr($number,0,3).'-'.substr($number,3,3).' '.substr($number,6,2).' '.substr($number,8);
		$country_code = '0031';
	} else if (in_array(substr($number,0,3),$zonesnl)) {
		$number = substr($number,0,3).'-'.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
		$country_code = '0031';
	} else if (in_array(substr($number,0,2),$zonesnl) && substr($number,0,1) == '6') {
		$number = substr($number,0,1).'-'.substr($number,1,2).' '.substr($number,3,2).' '.substr($number,5,2).' '.substr($number,7);
		$country_code = '0031';
	} else if (in_array(substr($number,0,2),$zonesnl)) {
		$number = substr($number,0,2).'-'.substr($number,2,3).' '.substr($number,5,2).' '.substr($number,7);
		$country_code = '0031';
	}
	
	// Add country code back
	if ($country_code && $country_code != '0032') $number = $country_code.' (0)'.$number;
	else $number = '0'.$number;
	$number = preg_replace('/^00/s','+',$number);
	
	return $number;
}

function vat_format($string) {
	$result = preg_replace('/^[a-z]{2}([a-z]{2})/is','$1', strtoupper(preg_replace('/\W/is','',$string)) );
	
	if (is_numeric(substr($result,0,2))) {
		if (preg_match('/B\d\d$/is',$result)) $result = 'NL'.$result;
		else $result = 'BE'.$result;
	}
	
	if (substr($result,0,2) == 'NL') {
		if (strlen($result) < 14) return strtoupper($string);
		return 'NL '.substr($result,-12,4).'.'.substr($result,-8,2).'.'.substr($result,-6,3).'.'.substr($result,-3,3);
	} else if (substr($result,0,2) == 'BE') {
		if (strlen($result) < 12) return strtoupper($string);
		return 'BE '.substr($result,-10,4).'.'.substr($result,-6,3).'.'.substr($result,-3,3);
	} else if (substr($result,0,2) == 'DE') {
		if (strlen($result) < 11) return strtoupper($string);
		return 'DE '.substr($result,-9,3).'.'.substr($result,-6,3).'.'.substr($result,-3,3);
	} else if (substr($result,0,2) == 'IT') {
		if (strlen($result) < 13) return strtoupper($string);
		return 'IT '.substr($result,-11);
	}
	
	return $string;
}

function url_format($address) {
    if (!empty($address) && $address{0} != '#' && strpos(strtolower($address), 'mailto:') === FALSE && strpos(strtolower($address), 'javascript:') === FALSE) {
        $address = explode('/', strtolower($address));
        $keys = array_keys($address, '..');

        foreach($keys AS $keypos => $key) array_splice($address, $key - ($keypos * 2 + 1), 2);

        $address = implode('/', $address);
        $address = str_replace('./', '', $address);
        
        $scheme = parse_url($address);
        
        if (empty($scheme['scheme'])) $address = 'http://' . $address;

        $parts = parse_url($address);
        $address = strtolower($parts['scheme']) . '://';

        if (!empty($parts['user'])) {
            $address .= $parts['user'];

            if (!empty($parts['pass'])) $address .= ':' . $parts['pass'];

            $address .= '@';
        }

        if (!empty($parts['host'])) {
            $host = str_replace(',', '.', strtolower($parts['host']));

            if (strpos(ltrim($host, 'www.'), '.') === FALSE) $host .= '.com';

            $address .= $host;
        }

        if (!empty($parts['port'])) $address .= ':' . $parts['port'];

        $address .= '/';

        if (!empty($parts['path'])) {
            $path = trim($parts['path'], ' /\\');

            if (!empty($path) AND strpos($path, '.') === FALSE) $path .= '/';
                
            $address .= $path;
        }

        if (!empty($parts['query'])) $address .= '?' . $parts['query'];

        return $address;
        
    } else return $address;
}

function pretty_url($url) {
	$url = parse_url($url,PHP_URL_HOST);
	if (substr_count($url,'.') < 2) $url = 'www.'.$url;
	return $url;
}

function valid_url($address) {
    if (!empty($address) && $address{0} != '#' && strpos(strtolower($address), 'mailto:') === FALSE && strpos(strtolower($address), 'javascript:') === FALSE) {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');

        foreach($keys AS $keypos => $key) array_splice($address, $key - ($keypos * 2 + 1), 2);

        $address = implode('/', $address);
        $address = str_replace('./', '', $address);
        
        $scheme = parse_url($address);
        
        if (empty($scheme['scheme'])) $address = 'http://' . $address;

        $parts = parse_url($address);
        $address = strtolower($parts['scheme']) . '://';

        if (!empty($parts['user'])) {
            $address .= $parts['user'];

            if (!empty($parts['pass'])) $address .= ':' . $parts['pass'];

            $address .= '@';
        }

        if (!empty($parts['host'])) {
            $host = str_replace(',', '.', strtolower($parts['host']));

            if (strpos(ltrim($host, 'www.'), '.') === FALSE) $host .= '.com';

            $address .= $host;
        }

        if (!empty($parts['port'])) $address .= ':' . $parts['port'];

        $address .= '/';

        if (!empty($parts['path'])) {
            $path = trim($parts['path'], ' /\\');

            if (!empty($path) AND strpos($path, '.') === FALSE) $path .= '/';
                
            $address .= $path;
        }

        if (!empty($parts['query'])) $address .= '?' . $parts['query'];

        return $address;
        
    } else if (strlen($address) > 0) return false;
    else return '';
}

function current_url() {
	$url = 'http';
	if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) $url .= "s";
	$url .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	return $url;
}

function str_to_date($string) {
	list($day,$month,$year) = explode('/',$string);
	return mktime(0,0,0,$month,$day,$year);
}

function slug_format($string) {
	return strtolower(preg_replace('/[\s\-]+/is', '-', trim(preg_replace('/[^a-z0-9\s\-]/is', '', remove_accents($string)) )));
}

function relative_to_absolute($html) {
	return preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|\+)[^"]*"))#','$1="'.substr(url('',false),0,-1).'$2$3',$html);
}

function decode($string) {
	
	if (is_array($string)) {
		foreach ($string as &$item) $item = decode($item);
		return $string;
	}
	
	$badwordchars=array(
        "\xe2\x80\x98", // left single quote
        "\xe2\x80\x99", // right single quote
        "\xe2\x80\x9c", // left double quote
        "\xe2\x80\x9d", // right double quote
        "\xe2\x80\x93", // en dash
        "\xe2\x80\x94", // em dash
        "\xe2\x80\xa6", // elipses
        "\xe2\x82\xac" // euro sign
    );
    $fixedwordchars=array(
        "&lsquo;",
        "&rsquo;",
        '&ldquo;',
        '&rdquo;',
        '&ndash;',
        '&mdash;',
        '&hellip;',
        '&euro;'
    );
    
    $new_string = iconv("UTF-8", "CP1252", str_replace($badwordchars,$fixedwordchars,$string));
    
	return $new_string;
}

function excerpt($string,$maxchars=200,$allow_linebreak=false) {
	if (!$allow_linebreak) {
		$string = preg_replace('/\<br\s*\/?\s*>/ius',' ',$string);
		$string = strip_tags($string);
	} else {
		$string = strip_tags($string,'<br><br/><br /><br / >');
	}
	$string = preg_replace('/[\s\n\r]+/us', ' ', $string);
	if (mb_strlen($string) <= $maxchars) return $string;
	$string = mb_substr($string, 0, $maxchars);
	
	if (preg_match('/^(.*[\.\!\?\;])\s+[0-9A-Z].*?$/us',$string,$match)) {
		$string = $match[1];
	} else {
		$pos = mb_strrpos($string, " ");
		if ($pos>0) {
			$string = mb_substr($string, 0, $pos+1);
		}
	}
	$string = trim($string);
	if (!in_array(mb_substr($string, -1), array('.',',',';','?','!'))) $string .= '&hellip;';
	if (mb_substr($string, -1) == ',') $string = mb_substr($string, 0, -1).'&hellip;';
	if (mb_substr($string, -1) == '.' && mb_substr_count($string, '(') > mb_substr_count($string, ')')) $string = mb_substr($string, 0, -1).'&hellip;';
	return $string;
}

function search_excerpt($string,$search_string,$maxchars=200) {
	$string = preg_replace('/\<br\s*\/?\s*>/is',' ',$string);
	$string = strip_tags($string);
	$string = preg_replace('/[\s\n\r]+/s', ' ', $string);
	
	$terms = explode(' ',$search_string);
	
	if (strlen($string) > $maxchars) {
		
		$pos = false;
		foreach ($terms as $term) {
			$firstpos = stripos($string,$term);
			if ($firstpos !== false && ($pos === false || $firstpos < $pos)) $pos = $firstpos;
		}
		
		$dotpos = strrpos(substr($string, 0, $pos),'.');
		
		if ($dotpos === false) $dotpos = 0;
		else $dotpos += 1;
	
		$string = trim(substr($string, $dotpos, $maxchars));
		$pos = strrpos($string, ".");
		if ($pos>0) {
			$string = substr($string, 0, $pos+1);
		} else {
			$pos = strrpos($string, ",");
			if ($pos>0) {
				$string = substr($string, 0, $pos+1);
			} else {
				$pos = strrpos($string, " ");
				if ($pos>0) {
					$string = substr($string, 0, $pos+1);
				}
			}
		}
		$string = trim($string);
		if (!in_array(substr($string, -1), array('.',',',';','?','!'))) $string .= '&hellip;';
		if (substr($string, -1) == ',') $string = substr($string, 0, -1).'&hellip;';
		if (substr($string, -1) == '.' && substr_count($string, '(') > substr_count($string, ')')) $string = substr($string, 0, -1).'&hellip;';
		
	}
	
	foreach (array_unique($terms) as $term) {
		$string = preg_replace('/'.preg_quote($term).'/is','<strong>$0</strong>', $string);
	}
	
	return $string;
}

function _strlen_sort($a,$b){
    return strlen($b)-strlen($a);
}

function remove_accents($string) {
	if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

	if (seems_utf8($string)) {
		$chars = array(
		// Decompositions for Latin-1 Supplement
		chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
		chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
		chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
		chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
		chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
		chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
		chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
		chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
		chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
		chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
		chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
		chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
		chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
		chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
		chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
		chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
		chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
		chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
		chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
		chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
		chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
		chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
		chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
		chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
		chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
		chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
		chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
		chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
		chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
		chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
		chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
		chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
		// Decompositions for Latin Extended-A
		chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
		chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
		chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
		chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
		chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
		chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
		chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
		chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
		chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
		chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
		chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
		chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
		chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
		chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
		chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
		chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
		chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
		chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
		chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
		chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
		chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
		chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
		chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
		chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
		chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
		chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
		chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
		chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
		chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
		chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
		chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
		chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
		chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
		chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
		chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
		chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
		chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
		chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
		chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
		chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
		chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
		chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
		chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
		chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
		chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
		chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
		chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
		chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
		chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
		chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
		chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
		chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
		chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
		chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
		chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
		chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
		chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
		chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
		chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
		chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
		chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
		chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
		chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
		chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
		// Decompositions for Latin Extended-B
		chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
		chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
		// Euro Sign
		chr(226).chr(130).chr(172) => 'E',
		// GBP (Pound) Sign
		chr(194).chr(163) => '',
		// Vowels with diacritic (Vietnamese)
		// unmarked
		chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
		chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
		// grave accent
		chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
		chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
		chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
		chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
		chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
		chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
		chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
		// hook
		chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
		chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
		chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
		chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
		chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
		chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
		chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
		chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
		chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
		chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
		chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
		chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
		// tilde
		chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
		chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
		chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
		chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
		chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
		chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
		chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
		chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
		// acute accent
		chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
		chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
		chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
		chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
		chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
		chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
		// dot below
		chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
		chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
		chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
		chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
		chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
		chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
		chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
		chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
		chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
		chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
		chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
		chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
		);

		$string = strtr($string, $chars);
	} else {
		$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

		$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

		$string = strtr($string, $chars['in'], $chars['out']);
		$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
		$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
		$string = str_replace($double_chars['in'], $double_chars['out'], $string);
	}

	return $string;
}

function seems_utf8($str) {
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}

function number_format_trim($number,$decimals=2,$decimal_separator=',',$thousand_separator='.') {
	$number = number_format($number,$decimals,$decimal_separator,$thousand_separator);
	if (strstr($number,',')) $number = rtrim(rtrim($number,'0'),',');
	return $number;
}

function random_string($length,$case_sensitive=false) {
	$key = '';
    if ($case_sensitive) $keys = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    else $keys = array_merge(range(0, 9), range('a', 'z'));

	$alphabetic_count = 0;
    for ($i = 0; $i < $length; $i++) {
	    $rand = array_rand($keys);
	    if ($alphabetic_count > 2) $rand = rand(0,9);
	    if ($rand > 9) $alphabetic_count++;
	    else $alphabetic_count = 0;
        $key .= $keys[$rand];
    }

    return strval($key);
}

function random($array) {
	$index = array_rand($array);
	return $array[$index];
}
function br2nl($string){
    return preg_replace('/\\s*<br(\s*)?\/?\>\s*/i', PHP_EOL, $string);
}

/**
 * UTF-8 aware replacement for ltrim().
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see http://www.php.net/ltrim
 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 * @param string $str
 * @param string $charlist
 * @return string
 */
function utf8_ltrim($str, $charlist = '')
{
	if(empty($charlist))
		return ltrim($str);
	// Quote charlist for use in a characterclass
	$charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);
	return preg_replace('/^['.$charlist.']+/u', '', $str);
}
/**
 * UTF-8 aware replacement for rtrim().
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see http://www.php.net/rtrim
 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 * @param string $str
 * @param string $charlist
 * @return string
 */
function utf8_rtrim($str, $charlist= '')
{
	if(empty($charlist))
		return rtrim($str);
	// Quote charlist for use in a characterclass
	$charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);
	return preg_replace('/['.$charlist.']+$/u', '', $str);
}
/**
 * UTF-8 aware replacement for trim().
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @see http://www.php.net/trim
 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
 * @param string $str
 * @param boolean $charlist
 * @return string
 */
function utf8_trim($str, $charlist= '')
{
	if(empty($charlist))
		return trim($str);
	return utf8_ltrim(utf8_rtrim($str, $charlist), $charlist);
}