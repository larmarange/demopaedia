<?php

// Affiche le code langue à partir d'une édition
function lg_code ($edition) {
	return substr($edition,0,2);
}

// Renvoie la langue de l'édition sous forme textuelle, dans la langue courante
function lg_txt ($edition) {
	$code = substr($edition,0,2);
	return _T("demopaedia:$code");
}

// Affiche le code du numéro d'édition (i ou ii)
function ed_code ($edition) {
	return substr($edition,3,3);
}

// Renvoie le numéro de l'édition sous forme textuelle, dans la langue courante
function ed_txt ($edition) {
	$code = substr($edition,3,3);
	return _T("demopaedia:$code");
}

// Renvoie la langue et le numéro de l'édition sous forme textuelle, dans la langue courante
function lg_ed_txt ($edition) {
	if ($edition!='')
		return lg_txt($edition).' '.ed_txt($edition);
	else
		return '';
}

// Renvoie la date d'un fichier
function date_fichier ($fichier) {
	return date('Y-m-j G:i', filemtime($fichier));
}

function mb_str_replace($needle, $replacement, $haystack)
{
    $needle_len = mb_strlen($needle);
    $replacement_len = mb_strlen($replacement);
    $pos = mb_strpos($haystack, $needle);
    while ($pos !== false)
    {
        $haystack = mb_substr($haystack, 0, $pos) . $replacement
                . mb_substr($haystack, $pos + $needle_len);
        $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
    }
    return $haystack;
}

if (!function_exists('mb_ucfirst')) {
	function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
		$first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
		$str_end = "";
		if ($lower_str_end) {
			$str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
		}
		else {
			$str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
		}
		$str = $first_letter . $str_end;
		return $str;
	}
}

if (!function_exists('mb_lcfirst')) {
	function mb_lcfirst($str, $encoding = "UTF-8") {
		$first_letter = mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding);
		$str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
		$str = $first_letter . $str_end;
		return $str;
	}
}

// chr() with unicode support
// http://php.net/manual/fr/function.chr.php
function uchr ($codes) {
    if (is_scalar($codes)) $codes= func_get_args();
    $str= '';
    foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
    return $str;
}

// ord() with unicode support
// http://stackoverflow.com/questions/9361303/can-i-get-the-unicode-value-of-a-character-or-vise-versa-with-php
function uord($c) {
    if (ord($c{0}) >=0 && ord($c{0}) <= 127)
        return ord($c{0});
    if (ord($c{0}) >= 192 && ord($c{0}) <= 223)
        return (ord($c{0})-192)*64 + (ord($c{1})-128);
    if (ord($c{0}) >= 224 && ord($c{0}) <= 239)
        return (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
    if (ord($c{0}) >= 240 && ord($c{0}) <= 247)
        return (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
    if (ord($c{0}) >= 248 && ord($c{0}) <= 251)
        return (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
    if (ord($c{0}) >= 252 && ord($c{0}) <= 253)
        return (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
    if (ord($c{0}) >= 254 && ord($c{0}) <= 255)    //  error
        return FALSE;
    return 0;
} 


?>