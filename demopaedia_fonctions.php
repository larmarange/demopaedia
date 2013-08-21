<?php

// Filtre permettant d'insérer un document 'in line'
// Voir http://en.wikipedia.org/wiki/Data:_URI_scheme
// Exemple d'utilisation : [<img src="(#LOGO_SITE_SPIP||extraire_attribut{src}|insere_data_URI)" />]
function filtre_insere_data_URI ($src) {
	$extension = substr(strrchr($src,'.'),1);
	$mime_type = sql_getfetsel('mime_type','spip_types_documents','extension='.sql_quote($extension));
	$base64 = base64_encode(file_get_contents($src));
	return ('data:'.$mime_type.';base64,'.$base64);
}

// Remplace la chaine @date@ par la date courante
function filtre_ajoute_date ($texte, $lang='') {
	if ($lang AND in_array($lang,array('th','ko')))
		return str_replace('@date@',date('d/m/Y'),$texte);
	else
		return str_replace('@date@',affdate(date('Y-m-d')),$texte);
}

// Ajoute des liens sur les textterms et les codes de section
function filtre_ajoute_liens_termes ($texte,$edition,$section) {
	$texte = preg_replace('/([0-9]{3})([^-])/','<a href="spip.php?page=consulter&edition='.$edition.'&section=$1">$1</a>$2',$texte);
	$texte = preg_replace('/\<b\>(.*)\<\/b\>\<sup\>([0-9]*)\<\/sup\>/U','<b><a href="spip.php?page=terme&edition='.$edition.'&section='.$section.'&numterme=$2&terme=$1">$1</a></b><sup>$2</sup>',$texte);
	$texte = preg_replace('/([0-9]{3})-([0-9]{1,2})/','<a href="spip.php?page=terme&edition='.$edition.'&section=$1&numterme=$2">$1-$2</a>',$texte);
	return $texte;
}


// Ce filtre permet de générer les éléments du sommaire de niveau inférieur
// Si name='non' on ajoute pas de liens
function filtre_sommaire_sous_elements($texte,$name='',$niveau='h3',$niveau_max=''){
	// Ajout d'une balise <fin> pour repérer les fin de blocs
	$texte = preg_replace('/\<'.$niveau.'/','<fin><'.$niveau,$texte);
	$texte .= '<fin>';
	if (preg_match_all("/\<".$niveau."\>(.*?)\<\/".$niveau."\>([\s\S]*)\<fin\>/U",$texte,$soustitres)) {
		$retour = "\n<ul>";
		foreach ($soustitres[1] as $cle => $soustitre) {
			$i = $cle+1;
			if ($name!='non')
				$retour .= "\n<li><a href='#$name$i'>$soustitre</a>";
			else
				$retour .= "\n<li>$soustitre";
			if ($niveau=='h3' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h4',$niveau_max);
			if ($niveau=='h4' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h5',$niveau_max);
			if ($niveau=='h5' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h6',$niveau_max);
			if ($niveau=='h6' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h7',$niveau_max);
			if ($niveau=='h7' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h8',$niveau_max);
			if ($niveau=='h8' and $niveau!=$niveau_max)
				$retour .= filtre_sommaire_sous_elements($soustitres[2][$cle],$name.$i,'h9',$niveau_max);
			$retour .= "</li>";
		}
		$retour .= "\n</ul>";
		return $retour;
	}
	else
		return '';
}

function filtre_ajoute_name($texte,$name='',$niveau='h3'){
// Ajout d'une balise <fin> pour repérer les fin de blocs
	$texte2 = preg_replace('/\<'.$niveau.'/','<fin><'.$niveau,$texte);
	$texte2 .= '<fin>';
	if (preg_match_all("/\<".$niveau."\>(.*?)\<\/".$niveau."\>([\s\S]*)\<fin\>/U",$texte2,$soustitres)) {
		$retour = "";
		// On récupère le début du texte
		if (preg_match("/^([\s\S]*)\<".$niveau."\>/U",$texte,$debut))
			$retour .= $debut[1];
		foreach ($soustitres[1] as $cle => $soustitre) {
			$i = $cle+1;
			$retour .= "\n".'<'.$niveau.'><a name="'.$name.$i.'"></a>'.$soustitre.'</'.$niveau.'>';
			if ($niveau=='h3')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h4');
			if ($niveau=='h4')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h5');
			if ($niveau=='h5')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h6');
			if ($niveau=='h6')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h7');
			if ($niveau=='h7')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h8');
			if ($niveau=='h8')
				$retour .= filtre_ajoute_name($soustitres[2][$cle],$name.$i,'h9');
		}
		return $retour;
	}
	else
		return $texte;
}

// http://stackoverflow.com/questions/10472294/how-to-convert-to-korean-initials
// Modifier pour ne pas prendre en compte les voyelles doubles
function ko_initial($letter) {
	$value = uord($letter);
	if ($value >=44032 AND £VALUE <+ 55230) {
		$initials = "ㄱㄱㄴㄷㄷㄹㅁㅂㅂㅅㅅㅇㅈㅈㅊㅋㅌㅍㅎ"; // "ㄱㄲㄴㄷㄸㄹㅁㅂㅃㅅㅆㅇㅈㅉㅊㅋㅌㅍㅎ"
		$value = abs(($value - 44032) / 588);
		return(mb_substr($initials, $value, 1));
	} else
		return $letter;
}

//extrait la première lettre, supprime les accents et la passe en majuscules
function filtre_initiale($texte, $edition='', $start=0, $lower=false) {
	$premiere_lettre = mb_substr($texte,$start,1,"UTF-8"); // première lettre
	$langue = lg_code($edition);
	$langues_traitees = array();
	$langues_traitees[] = 'fr';
	$langues_traitees[] = 'es';
	$langues_traitees[] = 'pl';
	$langues_traitees[] = 'cs';
	$langues_traitees[] = 'pt';
	$langues_traitees[] = 'it';
	$langues_traitees[] = 'sv';
	$langues_traitees[] = 'et';
	$langues_traitees[] = 'de';
	$langues_traitees[] = 'en';
	$langues_traitees[] = 'ru';
	$langues_traitees[] = 'ar';
	$langues_traitees[] = 'th';
	$langues_traitees[] = 'ko';
	if (in_array($langue,$langues_traitees)) {
		$initiales = array();
		$initiales["A"] = 'A'; // A Capital A
		$initiales["a"] = 'A'; // a Lowercase A
		$initiales["À"] = 'A'; // À Capital A-grave
		$initiales["à"] = 'A'; // à Lowercase A-grave
		$initiales["Á"] = 'A'; // Á Capital A-acute
		$initiales["á"] = 'A'; // á Lowercase A-acute
		$initiales["Â"] = 'A'; // Â Capital A-circumflex
		$initiales["â"] = 'A'; // â Lowercase A-circumflex
		$initiales["Ã"] = 'A'; // Ã Capital A-tilde
		$initiales["ã"] = 'A'; // ã Lowercase A-tilde
		$initiales["Ä"] = 'A'; // Ä Capital A-umlaut
		$initiales["ä"] = 'A'; // ä Lowercase A-umlaut
		$initiales["Å"] = 'A'; // Å Capital A-ring
		$initiales["å"] = 'A'; // å Lowercase A-umlaut
		$initiales["Ā"] = 'A'; // Ā Capital A-macron
		$initiales["ā"] = 'A'; // ā Lowercase A-macron
		$initiales["Ă"] = 'A'; // Ă Capital A-breve
		$initiales["ă"] = 'A'; // ă Lowercase A-breve
		$initiales["Ą"] = 'A'; // Ą Capital A-ogonek
		$initiales["ą"] = 'A'; // ą Lowercase A-ogonek
		$initiales["Ǟ"] = 'A'; // Ǟ Capital A-diaeresis and macron
		$initiales["ǟ"] = 'A'; // ǟ Lowercase A-diaeresis and macron
		$initiales["Ǻ"] = 'A'; // Ǻ Capital A-acute ring
		$initiales["ǻ"] = 'A'; // ǻ Lowercase A-acute ring
		$initiales["Æ"] = 'A'; // Æ Capital AE Ligature
		$initiales["æ"] = 'A'; // æ Lowercase AE Ligature
		$initiales["Ǽ"] = 'A'; // Ǽ Capital AE Ligature-acute
		$initiales["ǽ"] = 'A'; // ǽ Lowercase AE Ligature-acute
		$initiales["B"] = 'B'; // B Capital B
		$initiales["b"] = 'B'; // b Lowercase B
		$initiales["Ḃ"] = 'B'; // Ḃ Capital B-dot
		$initiales["ḃ"] = 'B'; // ḃ Lowercase B-dot
		$initiales["C"] = 'C'; // C Capital C
		$initiales["c"] = 'C'; // c Lowercase C
		$initiales["Ć"] = 'C'; // Ć Capital C-acute
		$initiales["ć"] = 'C'; // ć Lowercase C-acute
		$initiales["Ç"] = 'C'; // Ç Capital C-cedilla
		$initiales["ç"] = 'C'; // ç Lowercase C-cedilla
		$initiales["Č"] = 'C'; // Č Capital C-hachek
		$initiales["č"] = 'C'; // č Lowercase C-hachek
		$initiales["Ĉ"] = 'C'; // Ĉ Capital C-circumflex
		$initiales["ĉ"] = 'C'; // ĉ Lowercase C-circumflex
		$initiales["Ċ"] = 'C'; // Ċ Capital C-dot
		$initiales["ċ"] = 'C'; // ċ Lowercase C-dot
		$initiales["D"] = 'D'; // D Capital D
		$initiales["d"] = 'D'; // d Lowercase D
		$initiales["Ḑ"] = 'D'; // Ḑ Capital D-cedilla
		$initiales["ḑ"] = 'D'; // ḑ Lowercase D-cedilla
		$initiales["Ď"] = 'D'; // Ď Capital D-hachek
		$initiales["ď"] = 'D'; // ď Lowercase D-hachek
		$initiales["Ḋ"] = 'D'; // Ḋ Capital D-dot
		$initiales["ḋ"] = 'D'; // ḋ Lowercase D-dot
		$initiales["Đ"] = 'D'; // Đ Capital D-stroke
		$initiales["đ"] = 'D'; // đ Lowercase D-stroke
		$initiales["Ð"] = 'D'; // Ð Capital Eth (Icelandic)
		$initiales["ð"] = 'D'; // ð Lowercase Eth (Icelandic)
		$initiales["DZ"] = 'D'; // DZ Capital DZ Ligature
		$initiales["ǲ"] = 'D'; // ǲ Capital DZ Ligature
		$initiales["ǳ"] = 'D'; // ǳ Lowercase DZ Ligature
		$initiales["Ǆ"] = 'D'; // Ǆ Capital DZ-hachek
		$initiales["ǅ"] = 'D'; // ǅ Capital DZ-hachek
		$initiales["ǆ"] = 'D'; // ǆ Lowercase DZ-hachek
		$initiales["E"] = 'E'; // E Capital E
		$initiales["e"] = 'E'; // e Lowercase E
		$initiales["È"] = 'E'; // È Capital E-grave
		$initiales["è"] = 'E'; // è Lowercase E-grave
		$initiales["É"] = 'E'; // É Capital E-acute
		$initiales["é"] = 'E'; // é Lowercase E-acute
		$initiales["Ě"] = 'E'; // Ě Capital E-hachek
		$initiales["ě"] = 'E'; // ě Lowercase E-hachek
		$initiales["Ê"] = 'E'; // Ê Capital E-circumflex
		$initiales["ê"] = 'E'; // ê Lowercase E-circumflex
		$initiales["Ë"] = 'E'; // Ë Capital E-umlaut
		$initiales["ë"] = 'E'; // ë Lowercase E-umlaut
		$initiales["Ē"] = 'E'; // Ē Capital E-macron
		$initiales["ē"] = 'E'; // ē Lowercase E-macron
		$initiales["Ĕ"] = 'E'; // Ĕ Capital E-breve
		$initiales["ĕ"] = 'E'; // ĕ Lowercase E-breve
		$initiales["Ę"] = 'E'; // Ę Capital E-ogonek
		$initiales["ę"] = 'E'; // ę Lowercase E-ogonek
		$initiales["Ė"] = 'E'; // Ė Capital E-dot
		$initiales["ė"] = 'E'; // ė Lowercase E-dot
		$initiales["Ʒ"] = 'E'; // Ʒ Capital Ezh
		$initiales["ʒ"] = 'E'; // ʒ Lowercase Ezh
		$initiales["Ǯ"] = 'E'; // Ǯ Capital Ezh-hachek
		$initiales["ǯ"] = 'E'; // ǯ Lowercase Ezh-hachek
		$initiales["F"] = 'F'; // F Capital F
		$initiales["f"] = 'F'; // f Lowercase F
		$initiales["Ḟ"] = 'F'; // Ḟ Capital F-dot
		$initiales["ḟ"] = 'F'; // ḟ Lowercase F-dot
		$initiales["ƒ"] = 'F'; // ƒ Lowercase F-hook
		$initiales["ﬀ"] = 'F'; // ﬀ Lowercase FF Ligature
		$initiales["ﬁ"] = 'F'; // ﬁ Lowercase FI Ligature
		$initiales["ﬂ"] = 'F'; // ﬂ Lowercase FL Ligature
		$initiales["ﬃ"] = 'F'; // ﬃ Lowercase FFI Ligature
		$initiales["ﬄ"] = 'F'; // ﬄ Lowercase FFL Ligature
		$initiales["ﬅ"] = 'F'; // ﬅ Lowercase FT Ligature
		$initiales["G"] = 'G'; // G Capital G
		$initiales["g"] = 'G'; // g Lowercase G
		$initiales["Ǵ"] = 'G'; // Ǵ Capital G-acute
		$initiales["ǵ"] = 'G'; // ǵ Lowercase G-acute
		$initiales["Ģ"] = 'G'; // Ģ Capital G-cedilla
		$initiales["ģ"] = 'G'; // ģ Lowercase G-cedilla
		$initiales["Ǧ"] = 'G'; // Ǧ Capital G-hachek
		$initiales["ǧ"] = 'G'; // ǧ Lowercase G-hachek
		$initiales["Ĝ"] = 'G'; // Ĝ Capital G-circumflex
		$initiales["ĝ"] = 'G'; // ĝ Lowercase G-circumflex
		$initiales["Ğ"] = 'G'; // Ğ Capital G-breve
		$initiales["ğ"] = 'G'; // ğ Lowercase G-breve
		$initiales["Ġ"] = 'G'; // Ġ Capital G-dot
		$initiales["ġ"] = 'G'; // ġ Lowercase G-dot
		$initiales["Ǥ"] = 'G'; // Ǥ Capital G-stroke
		$initiales["ǥ"] = 'G'; // ǥ Lowercase G-stroke
		$initiales["H"] = 'H'; // H Capital H
		$initiales["h"] = 'H'; // h Lowercase H
		$initiales["Ĥ"] = 'H'; // Ĥ Capital H-circumflex
		$initiales["ĥ"] = 'H'; // ĥ Lowercase H-circumflex
		$initiales["Ħ"] = 'H'; // Ħ Capital H-stroke
		$initiales["ħ"] = 'H'; // ħ Lowercase H-stroke
		$initiales["I"] = 'I'; // I Capital I
		$initiales["i"] = 'I'; // i Lowercase I
		$initiales["Ì"] = 'I'; // Ì Capital I-grave
		$initiales["ì"] = 'I'; // ì Lowercase I-grave
		$initiales["Í"] = 'I'; // Í Capital I-acute
		$initiales["í"] = 'I'; // í Lowercase I-acute
		$initiales["Î"] = 'I'; // Î Capital I-circumflex
		$initiales["î"] = 'I'; // î Lowercase I-circumflex
		$initiales["Ĩ"] = 'I'; // Ĩ Capital I-tilde
		$initiales["ĩ"] = 'I'; // ĩ Lowercase I-tilde
		$initiales["Ï"] = 'I'; // Ï Capital I-umlaut
		$initiales["ï"] = 'I'; // ï Lowercase I-umlaut
		$initiales["Ī"] = 'I'; // Ī Capital I-macron
		$initiales["ī"] = 'I'; // ī Lowercase I-macron
		$initiales["Ĭ"] = 'I'; // Ĭ Capital I-breve
		$initiales["ĭ"] = 'I'; // ĭ Lowercase I-breve
		$initiales["Į"] = 'I'; // Į Capital I-ogonek
		$initiales["į"] = 'I'; // į Lowercase I-ogonek
		$initiales["İ"] = 'I'; // İ Capital I-dot
		$initiales["ı"] = 'I'; // ı Lowercase I-dotless
		$initiales["Ĳ"] = 'I'; // Ĳ Capital IJ Ligature
		$initiales["ĳ"] = 'I'; // ĳ Lowercase IJ Ligature
		$initiales["J"] = 'J'; // J Capital J
		$initiales["j"] = 'J'; // j Lowercase J
		$initiales["Ĵ"] = 'J'; // Ĵ Capital J-circumflex
		$initiales["ĵ"] = 'J'; // ĵ Lowercase J-circumflex
		$initiales["K"] = 'K'; // K Capital K
		$initiales["k"] = 'K'; // k Lowercase K
		$initiales["Ḱ"] = 'K'; // Ḱ Capital K-acute
		$initiales["ḱ"] = 'K'; // ḱ Lowercase K-acute
		$initiales["Ķ"] = 'K'; // Ķ Capital K-cedilla
		$initiales["ķ"] = 'K'; // ķ Lowercase K-cedilla
		$initiales["Ǩ"] = 'K'; // Ǩ Capital K-hachek
		$initiales["ǩ"] = 'K'; // ǩ Lowercase K-hachek
		$initiales["ĸ"] = 'K'; // ĸ Small Capital K
		$initiales["L"] = 'L'; // L Capital L
		$initiales["l"] = 'L'; // l Lowercase L
		$initiales["Ĺ"] = 'L'; // Ĺ Capital L-acute
		$initiales["ĺ"] = 'L'; // ĺ Lowercase L-acute
		$initiales["Ļ"] = 'L'; // Ļ Capital L-cedilla
		$initiales["ļ"] = 'L'; // ļ Lowercase L-cedilla
		$initiales["Ľ"] = 'L'; // Ľ Capital L-hachek
		$initiales["ľ"] = 'L'; // ľ Lowercase L-hachek
		$initiales["Ŀ"] = 'L'; // Ŀ Capital L-middle dot
		$initiales["ŀ"] = 'L'; // ŀ Lowercase L-middle dot
		$initiales["Ł"] = 'L'; // Ł Capital L-stroke
		$initiales["ł"] = 'L'; // ł Lowercase L-stroke
		$initiales["LJ"] = 'L'; // LJ Capital LJ Ligature
		$initiales["Ǉ"] = 'L'; // Ǉ Capital LJ Ligature
		$initiales["ǈ"] = 'L'; // ǈ Capital LJ Ligature
		$initiales["ǉ"] = 'L'; // ǉ Lowercase LJ Ligature
		$initiales["M"] = 'M'; // M Capital M
		$initiales["m"] = 'M'; // m Lowercase M
		$initiales["Ṁ"] = 'M'; // Ṁ Capital M-dot
		$initiales["ṁ"] = 'M'; // ṁ Lowercase M-dot
		$initiales["N"] = 'N'; // N Capital N
		$initiales["n"] = 'N'; // n Lowercase N
		$initiales["Ń"] = 'N'; // Ń Capital N-acute
		$initiales["ń"] = 'N'; // ń Lowercase N-acute
		$initiales["Ņ"] = 'N'; // Ņ Capital N-cedilla
		$initiales["ņ"] = 'N'; // ņ Lowercase N-cedilla
		$initiales["Ň"] = 'N'; // Ň Capital N-hachek
		$initiales["ň"] = 'N'; // ň Lowercase N-hachek
		$initiales["Ñ"] = 'N'; // Ñ Capital N-tilde
		$initiales["ñ"] = 'N'; // ñ Lowercase N-tilde
		$initiales["ŉ"] = 'N'; // ŉ Lowercase N-apostrophe (before)
		$initiales["Ŋ"] = 'N'; // Ŋ Capital Eng
		$initiales["ŋ"] = 'N'; // ŋ Lowercase Eng
		$initiales["NJ"] = 'N'; // NJ Capital NJ Ligature
		$initiales["ǋ"] = 'N'; // ǋ Capital NJ Ligature
		$initiales["ǌ"] = 'N'; // ǌ Lowercase NJ Ligature
		$initiales["O"] = 'O'; // O Capital O
		$initiales["o"] = 'O'; // o Lowercase O
		$initiales["Ò"] = 'O'; // Ò Capital O-grave
		$initiales["ò"] = 'O'; // ò Lowercase O-grave
		$initiales["Ó"] = 'O'; // Ó Capital O-acute
		$initiales["ó"] = 'O'; // ó Lowercase O-acute
		$initiales["Ô"] = 'O'; // Ô Capital O-circumflex
		$initiales["ô"] = 'O'; // ô Lowercase O-circumflex
		$initiales["Õ"] = 'O'; // Õ Capital O-tilde
		$initiales["õ"] = 'O'; // õ Lowercase O-tilde
		$initiales["Ö"] = 'O'; // Ö Capital O-umlaut
		$initiales["ö"] = 'O'; // ö Lowercase O-umlaut
		$initiales["Ō"] = 'O'; // Ō Capital O-macron
		$initiales["ō"] = 'O'; // ō Lowercase O-macron
		$initiales["Ŏ"] = 'O'; // Ŏ Capital O-breve
		$initiales["ŏ"] = 'O'; // ŏ Lowercase O-breve
		$initiales["Ø"] = 'O'; // Ø Capital O-slash
		$initiales["ø"] = 'O'; // ø Lowercase O-slash
		$initiales["Ő"] = 'O'; // Ő Capital O-double acute
		$initiales["ő"] = 'O'; // ő Lowercase O-double acute
		$initiales["Ǿ"] = 'O'; // Ǿ Capital O-acute slash
		$initiales["ǿ"] = 'O'; // ǿ Lowercase O-acute slash
		$initiales["Œ"] = 'O'; // Œ Capital OE Ligature
		$initiales["œ"] = 'O'; // œ Lowercase OE Ligature
		$initiales["P"] = 'P'; // P Capital P
		$initiales["p"] = 'P'; // p Lowercase P
		$initiales["Ṗ"] = 'P'; // Ṗ Capital P-dot
		$initiales["ṗ"] = 'P'; // ṗ Lowercase P-dot
		$initiales["Q"] = 'Q'; // Q Capital Q
		$initiales["q"] = 'Q'; // q Lowercase Q
		$initiales["R"] = 'R'; // R Capital R
		$initiales["r"] = 'R'; // r Lowercase R
		$initiales["Ŕ"] = 'R'; // Ŕ Capital R-acute
		$initiales["ŕ"] = 'R'; // ŕ Lowercase R-acute
		$initiales["Ŗ"] = 'R'; // Ŗ Capital R-cedilla
		$initiales["ŗ"] = 'R'; // ŗ Lowercase R-cedilla
		$initiales["Ř"] = 'R'; // Ř Capital R-hachek
		$initiales["ř"] = 'R'; // ř Lowercase R-hachek
		$initiales["ɼ"] = 'R'; // ɼ Lowercase R-Long leg
		$initiales["S"] = 'S'; // S Capital S
		$initiales["s"] = 'S'; // s Lowercase S
		$initiales["Ś"] = 'S'; // Ś Capital S-acute
		$initiales["ś"] = 'S'; // ś Lowercase S-acute
		$initiales["Ş"] = 'S'; // Ş Capital S-cedilla
		$initiales["ş"] = 'S'; // ş Lowercase S-cedilla
		$initiales["Š"] = 'S'; // Š Capital S-hachek
		$initiales["š"] = 'S'; // š Lowercase S-hachek
		$initiales["Ŝ"] = 'S'; // Ŝ Capital S-circumflex
		$initiales["ŝ"] = 'S'; // ŝ Lowercase S-circumflex
		$initiales["Ṡ"] = 'S'; // Ṡ Capital S-dot
		$initiales["ṡ"] = 'S'; // ṡ Lowercase S-dot
		$initiales["ſ"] = 'S'; // ſ Lowercase S-long
		$initiales["ß"] = 'S'; // ß Lowercase SZ Ligature
		$initiales["T"] = 'T'; // T Capital T
		$initiales["t"] = 'T'; // t Lowercase T
		$initiales["Ţ"] = 'T'; // Ţ Capital T-cedilla
		$initiales["ţ"] = 'T'; // ţ Lowercase T-cedilla
		$initiales["Ť"] = 'T'; // Ť Capital T-hachek
		$initiales["ť"] = 'T'; // ť Lowercase T-hachek
		$initiales["Ṫ"] = 'T'; // Ṫ Capital T-dot
		$initiales["ṫ"] = 'T'; // ṫ Lowercase T-dot
		$initiales["Ŧ"] = 'T'; // Ŧ Capital T-stroke
		$initiales["ŧ"] = 'T'; // ŧ Lowercase T-stroke
		$initiales["Þ"] = 'T'; // Þ Capital Thorn
		$initiales["þ"] = 'T'; // þ Lowercase Thorn
		$initiales["U"] = 'U'; // U Capital U
		$initiales["u"] = 'U'; // u Lowercase U
		$initiales["Ù"] = 'U'; // Ù Capital U-grave
		$initiales["ù"] = 'U'; // ù Lowercase U-grave
		$initiales["Ú"] = 'U'; // Ú Capital U-acute
		$initiales["ú"] = 'U'; // ú Lowercase U-acute
		$initiales["Û"] = 'U'; // Û Capital U-circumflex
		$initiales["û"] = 'U'; // û Lowercase U-circumflex
		$initiales["Ũ"] = 'U'; // Ũ Capital U-tilde
		$initiales["ũ"] = 'U'; // ũ Lowercase U-tilde
		$initiales["Ü"] = 'U'; // Ü Capital U-umlaut
		$initiales["ü"] = 'U'; // ü Lowercase U-umlaut
		$initiales["Ů"] = 'U'; // Ů Capital U-ring
		$initiales["ů"] = 'U'; // ů Lowercase U-ring
		$initiales["Ū"] = 'U'; // Ū Capital U-macron
		$initiales["ū"] = 'U'; // ū Lowercase U-macron
		$initiales["Ŭ"] = 'U'; // Ŭ Capital U-breve
		$initiales["ŭ"] = 'U'; // ŭ Lowercase U-breve
		$initiales["Ų"] = 'U'; // Ų Capital U-ogonek
		$initiales["ų"] = 'U'; // ų Lowercase U-ogonek
		$initiales["Ű"] = 'U'; // Ű Capital U-double acute
		$initiales["ű"] = 'U'; // ű Lowercase U-double acute
		$initiales["V"] = 'V'; // V Capital V
		$initiales["v"] = 'V'; // v Lowercase V
		$initiales["W"] = 'W'; // W Capital W
		$initiales["w"] = 'W'; // w Lowercase W
		$initiales["Ẁ"] = 'W'; // Ẁ Capital W-grave
		$initiales["ẁ"] = 'W'; // ẁ Lowercase W-grave
		$initiales["Ẃ"] = 'W'; // Ẃ Capital W-acute
		$initiales["ẃ"] = 'W'; // ẃ Lowercase W-acute
		$initiales["Ŵ"] = 'W'; // Ŵ Capital W-circumflex
		$initiales["ŵ"] = 'W'; // ŵ Lowercase W-circumflex
		$initiales["Ẅ"] = 'W'; // Ẅ Capital W-umlaut
		$initiales["ẅ"] = 'W'; // ẅ Lowercase W-umlaut
		$initiales["X"] = 'X'; // X Capital X
		$initiales["x"] = 'X'; // x Lowercase X
		$initiales["Y"] = 'Y'; // Y Capital Y
		$initiales["y"] = 'Y'; // y Lowercase Y
		$initiales["Ỳ"] = 'Y'; // Ỳ Capital Y-grave
		$initiales["ỳ"] = 'Y'; // ỳ Lowercase Y-grave
		$initiales["Ý"] = 'Y'; // Ý Capital Y-acute
		$initiales["ý"] = 'Y'; // ý Lowercase Y-acute
		$initiales["Ŷ"] = 'Y'; // Ŷ Capital Y-circumflex
		$initiales["ŷ"] = 'Y'; // ŷ Lowercase Y-circumflex
		$initiales["Ÿ"] = 'Y'; // Ÿ Capital Y-umlaut
		$initiales["ÿ"] = 'Y'; // ÿ Lowercase Y-umlaut
		$initiales["Z"] = 'Z'; // Z Capital Z
		$initiales["z"] = 'Z'; // z Lowercase Z
		$initiales["Ź"] = 'Z'; // Ź Capital Z-acute
		$initiales["ź"] = 'Z'; // ź Lowercase Z-acute
		$initiales["Ž"] = 'Z'; // Ž Capital Z-hachek
		$initiales["ž"] = 'Z'; // ž Lowercase Z-hachek
		$initiales["Ż"] = 'Z'; // Ż Capital Z-dot
		$initiales["ż"] = 'Z'; // ż Lowercase Z-dot
		
		// Alphabet cyrillique pour le russe
		$initiales["А"] = 'А'; // А CYRILLIC CAPITAL LETTER A
		$initiales["Б"] = 'Б'; // Б CYRILLIC CAPITAL LETTER BE
		$initiales["В"] = 'В'; // В CYRILLIC CAPITAL LETTER VE
		$initiales["Г"] = 'Г'; // Г CYRILLIC CAPITAL LETTER GHE
		$initiales["Д"] = 'Д'; // Д CYRILLIC CAPITAL LETTER DE
		$initiales["Е"] = 'Е'; // Е CYRILLIC CAPITAL LETTER IE
		$initiales["Ж"] = 'Ж'; // Ж CYRILLIC CAPITAL LETTER ZHE
		$initiales["З"] = 'З'; // З CYRILLIC CAPITAL LETTER ZE
		$initiales["И"] = 'И'; // И CYRILLIC CAPITAL LETTER I
		$initiales["Й"] = 'Й'; // Й CYRILLIC CAPITAL LETTER SHORT I
		$initiales["К"] = 'К'; // К CYRILLIC CAPITAL LETTER KA
		$initiales["Л"] = 'Л'; // Л CYRILLIC CAPITAL LETTER EL
		$initiales["М"] = 'М'; // М CYRILLIC CAPITAL LETTER EM
		$initiales["Н"] = 'Н'; // Н CYRILLIC CAPITAL LETTER EN
		$initiales["О"] = 'О'; // О CYRILLIC CAPITAL LETTER O
		$initiales["П"] = 'П'; // П CYRILLIC CAPITAL LETTER PE
		$initiales["Р"] = 'Р'; // Р CYRILLIC CAPITAL LETTER ER
		$initiales["С"] = 'С'; // С CYRILLIC CAPITAL LETTER ES
		$initiales["Т"] = 'Т'; // Т CYRILLIC CAPITAL LETTER TE
		$initiales["У"] = 'У'; // У CYRILLIC CAPITAL LETTER U
		$initiales["Ф"] = 'Ф'; // Ф CYRILLIC CAPITAL LETTER EF
		$initiales["Х"] = 'Х'; // Х CYRILLIC CAPITAL LETTER HA
		$initiales["Ц"] = 'Ц'; // Ц CYRILLIC CAPITAL LETTER TSE
		$initiales["Ч"] = 'Ч'; // Ч CYRILLIC CAPITAL LETTER CHE
		$initiales["Ш"] = 'Ш'; // Ш CYRILLIC CAPITAL LETTER SHA
		$initiales["Щ"] = 'Щ'; // Щ CYRILLIC CAPITAL LETTER SHCHA
		$initiales["Ъ"] = 'Ъ'; // Ъ CYRILLIC CAPITAL LETTER HARD SIGN
		$initiales["Ы"] = 'Ы'; // Ы CYRILLIC CAPITAL LETTER YERU
		$initiales["Ь"] = 'Ь'; // Ь CYRILLIC CAPITAL LETTER SOFT SIGN
		$initiales["Э"] = 'Э'; // Э CYRILLIC CAPITAL LETTER E
		$initiales["Ю"] = 'Ю'; // Ю CYRILLIC CAPITAL LETTER YU
		$initiales["Я"] = 'Я'; // Я CYRILLIC CAPITAL LETTER YA
		$initiales["а"] = 'А'; // а CYRILLIC SMALL LETTER A
		$initiales["б"] = 'Б'; // б CYRILLIC SMALL LETTER BE
		$initiales["в"] = 'В'; // в CYRILLIC SMALL LETTER VE
		$initiales["г"] = 'Г'; // г CYRILLIC SMALL LETTER GHE
		$initiales["д"] = 'Д'; // д CYRILLIC SMALL LETTER DE
		$initiales["е"] = 'Е'; // е CYRILLIC SMALL LETTER IE
		$initiales["ж"] = 'Ж'; // ж CYRILLIC SMALL LETTER ZHE
		$initiales["з"] = 'З'; // з CYRILLIC SMALL LETTER ZE
		$initiales["и"] = 'И'; // и CYRILLIC SMALL LETTER I
		$initiales["й"] = 'Й'; // й CYRILLIC SMALL LETTER SHORT I
		$initiales["к"] = 'К'; // к CYRILLIC SMALL LETTER KA
		$initiales["л"] = 'Л'; // л CYRILLIC SMALL LETTER EL
		$initiales["м"] = 'М'; // м CYRILLIC SMALL LETTER EM
		$initiales["н"] = 'Н'; // н CYRILLIC SMALL LETTER EN
		$initiales["о"] = 'О'; // о CYRILLIC SMALL LETTER O
		$initiales["п"] = 'П'; // п CYRILLIC SMALL LETTER PE
		$initiales["р"] = 'Р'; // р CYRILLIC SMALL LETTER ER
		$initiales["с"] = 'С'; // с CYRILLIC SMALL LETTER ES
		$initiales["т"] = 'Т'; // т CYRILLIC SMALL LETTER TE
		$initiales["у"] = 'У'; // у CYRILLIC SMALL LETTER U
		$initiales["ф"] = 'Ф'; // ф CYRILLIC SMALL LETTER EF
		$initiales["х"] = 'Х'; // х CYRILLIC SMALL LETTER HA
		$initiales["ц"] = 'Ц'; // ц CYRILLIC SMALL LETTER TSE
		$initiales["ч"] = 'Ч'; // ч CYRILLIC SMALL LETTER CHE
		$initiales["ш"] = 'Ш'; // ш CYRILLIC SMALL LETTER SHA
		$initiales["щ"] = 'Щ'; // щ CYRILLIC SMALL LETTER SHCHA
		$initiales["ъ"] = 'Ъ'; // ъ CYRILLIC SMALL LETTER HARD SIGN
		$initiales["ы"] = 'Ы'; // ы CYRILLIC SMALL LETTER YERU
		$initiales["ь"] = 'Ь'; // ь CYRILLIC SMALL LETTER SOFT SIGN
		$initiales["э"] = 'Э'; // э CYRILLIC SMALL LETTER E
		$initiales["ю"] = 'Ю'; // ю CYRILLIC SMALL LETTER YU
		$initiales["я"] = 'Я'; // я CYRILLIC SMALL LETTER YA
		
		// Arabe
		$initiales["آ"] = 'ا';
		$initiales["أ"] = 'ا';
		$initiales["إ"] = 'ا';
		
		
		// Application de la règle post 1994 pour l'espagnol (ch et ll ne sont plus des lettres autonomes
		if ($langue=='es') {
			$initiales["Ñ"] = 'Ñ'; // Ñ Capital N-tilde
			$initiales["ñ"] = 'Ñ'; // ñ Lowercase N-tilde
		}
		
		// En tchèque, Č, Ř, Š, Ž et CH sont considérées comme des lettres distinctes
		if ($langue=='cs') {
			$initiales["Č"] = 'Č'; // Č Capital C-hachek
			$initiales["č"] = 'Č'; // č Lowercase C-hachek
			$initiales["Ř"] = 'Ř'; // Ř Capital R-hachek
			$initiales["ř"] = 'Ř'; // ř Lowercase R-hachek
			$initiales["Š"] = 'Š'; // Š Capital S-hachek
			$initiales["š"] = 'Š'; // š Lowercase S-hachek
			$initiales["Ž"] = 'Ž'; // Ž Capital Z-hachek
			$initiales["ž"] = 'Ž'; // ž Lowercase Z-hachek
			// Cas de CH
			if (mb_substr($texte,0,2)=='ch'|'Ch'|'CH')
				$premiere_lettre = 'CH';
		}
		
		// En polonais, Ą Ć Ę Ł Ń Ó Ś Ż et Ź sont considérées comme des lettres distinctes
		if ($langue=='pl') {
			$initiales["Ą"] = 'Ą'; // Ą Capital A-ogonek
			$initiales["ą"] = 'Ą'; // ą Lowercase A-ogonek
			$initiales["Ć"] = 'Ć'; // Ć Capital C-acute
			$initiales["ć"] = 'Ć'; // ć Lowercase C-acute
			$initiales["Ę"] = 'Ę'; // Ę Capital E-ogonek
			$initiales["ę"] = 'Ę'; // ę Lowercase E-ogonek
			$initiales["Ł"] = 'Ł'; // Ł Capital L-stroke
			$initiales["ł"] = 'Ł'; // ł Lowercase L-stroke
			$initiales["Ń"] = 'Ń'; // Ń Capital N-acute
			$initiales["ń"] = 'Ń'; // ń Lowercase N-acute
			$initiales["Ó"] = 'Ó'; // Ó Capital O-acute
			$initiales["ó"] = 'Ó'; // ó Lowercase O-acute
			$initiales["Ś"] = 'Ś'; // Ś Capital S-acute
			$initiales["ś"] = 'Ś'; // ś Lowercase S-acute
			$initiales["Ż"] = 'Ż'; // Ż Capital Z-dot
			$initiales["ż"] = 'Ż'; // ż Lowercase Z-dot
			$initiales["Ź"] = 'Ź'; // Ź Capital Z-acute
			$initiales["ź"] = 'Ź'; // ź Lowercase Z-acute

		}
		
		// Cas du Coréen "ㄱㄲㄴㄷㄸㄹㅁㅂㅃㅅㅆㅇㅈㅉㅊㅋㅌㅍㅎ"
		if ($langue=='ko') {
			$premiere_lettre = ko_initial($premiere_lettre);
		}

		// Cas particulier des voyelles situées en début de mot en Thai
		if (in_array($premiere_lettre,array('เ', 'แ', 'โ', 'ใ', 'ไ'))) $premiere_lettre = mb_substr($texte,$start+1,1,"UTF-8"); // seconde lettre
		
		foreach ($initiales as $cle => $valeur) {
			$premiere_lettre = mb_ereg_replace($cle,$valeur,$premiere_lettre);
		}

		
		if ($lower) return mb_strtolower($premiere_lettre,"UTF-8");
		else return $premiere_lettre;
	}
	else
		return '';
}

// Ce filtre est utilisé pour les termes correspondants dans un index
// Il permet de ne faire qu'une seule requête MySQL par edition afin de gagner du temps

function filtre_termes_correspondants($edition,$section,$numterme,$separateur) {
	static $index = null;

	if (is_null($index[$edition][$separateur])) {
		$interclassement = lire_config('demopaedia-'.$edition.'/interclassement');
		if ($interclassement == '')
			$interclassement = 'utf8_unicode_ci';
		$resultats = sql_allfetsel (
			array("CONCAT( section, '-', numterme )",'GROUP_CONCAT( terme ORDER BY terme COLLATE utf8_unicode_ci ASC SEPARATOR '.sql_quote($separateur).' )'),
			'spip_demoindex',
			'entree = "principale" AND edition = '.sql_quote($edition),
			'section, numterme',
			'section, numterme'
		);
		foreach($resultats as $res) {
			$res = array_values($res);
			$index[$edition][$separateur][$res[0]] = $res[1];
		}
	}
	return $index[$edition][$separateur][$section.'-'.$numterme];
}

// Identifier les termes manquants

function filtre_termes_manquants($edition) {
	$all = sql_allfetsel (
			"CONCAT( section, '-', numterme )",
			'spip_demoindex',
			"SUBSTRING(edition FROM 4) = ".sql_quote(ed_code($edition))." AND entree='principale'", #'edition = '.sql_quote($edition),
			'section, numterme',
			'section, numterme'
		);
	foreach ($all as $cle => $value)
		$all[$cle] = $value["CONCAT( section, '-', numterme )"];
	
	$this_ed = sql_allfetsel (
			array("CONCAT( section, '-', numterme )"),
			'spip_demoindex',
			'edition = '.sql_quote($edition)." AND entree='principale'",
			'section, numterme',
			'section, numterme'
		);
	foreach ($this_ed as $cle => $value)
		$this_ed[$cle] = $value["CONCAT( section, '-', numterme )"];
	
	return array_diff($all,$this_ed);
}


/* Filtre NORM_LIENS v2.0 - 29 juillet 2003 - Par Led
   Source : http://www.spip-contrib.net/Transformer-les-liens-texte-en
   
   Permet de normaliser les liens lorsque ceux-ci sont orphelins (sans balise
   HREF). Par exemple:
   "http://www.url.com" deviendra "<a href="http://www.url.com">http://www.url.com</a>"

   Le filtre s'utilise avec les balises #CHAPO, #TEXTE, #PS, #NOTES,
   #INTRODUCTION, #DESCRIPTIF et #BIO.

   SYNTAXE DANS LES SQUELETTES:
   [(#TEXTE|norm_liens)]
   [(#TEXTE|norm_liens{tag}]
   Où tag doit avoir comme valeur blank, self, parent ou top.
   Si aucun tag n'est spécifié la balise HREF n'aura pas de target.

   ATTENTION: Si vous désirez utiliser ce filtre avec le filtre CIBLES_LIENS (du
              21 juillet 2003 et écrit par moi-même) sur une même balise SPIP il
              faut obligatoirement placer le filtre NORM_LIENS en premier.
              Exemples: [(#TEXTE|norm_liens|cibles_liens)]
                        [(#TEXTE|norm_liens{tag}|cibles_liens)]
*/

function norm_liens($texte, $target='') {

    $target = '_'.$target;
    if ( $target != "_" ) {
        $texte = preg_replace('@ http://([^ <]*)@i', ' <a href="http://$1" target="'.$target.'">http://$1</a>', $texte);
        $texte = preg_replace('@ ftp://([^ <]*)@i', ' <a href="ftp://$1" target="'.$target.'">ftp://$1</a>', $texte);
        $texte = preg_replace('@ www.([^ <]*)@i', ' <a href="http://www.$1" target="'.$target.'">www.$1</a>', $texte);
        $texte = preg_replace('@ ftp.([^ <]*)@i', ' <a href="ftp://ftp.$1" target="'.$target.'">ftp.$1</a>', $texte);
        $texte = preg_replace('@\(http://([^ <]*)\)@i', ' (<a href="http://$1" target="'.$target.'">http://$1</a>)', $texte);
        $texte = preg_replace('@\(ftp://([^ <]*)\)@i', ' (<a href="ftp://$1" target="'.$target.'">ftp://$1</a>)', $texte);
        $texte = preg_replace('@\(www.([^ <]*)\)@i', ' (<a href="http://www.$1" target="'.$target.'">www.$1</a>)', $texte);
        $texte = preg_replace('@\(ftp.([^ <]*)\)@i', ' (<a href="ftp://ftp.$1" target="'.$target.'">ftp.$1</a>)', $texte);
        $texte = preg_replace('@^http://([^ <]*)@i', '<a href="http://$1" target="'.$target.'">http://$1</a>', $texte);
        $texte = preg_replace('@^ftp://([^ <]*)@i', '<a href="ftp://$1" target="'.$target.'">ftp://$1</a>', $texte);
        $texte = preg_replace('@^www.([^ <]*)@i', '<a href="http://www.$1" target="'.$target.'">www.$1</a>', $texte);
        $texte = preg_replace('@^ftp.([^ <]*)@i', '<a href="ftp://ftp.$1" target="'.$target.'">ftp.$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">http://([^ <]*)@i', '<p class="spip"><a href="http://$1" target="'.$target.'">http://$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">ftp://([^ <]*)@i', '<p class="spip"><a href="ftp://$1" target="'.$target.'">ftp://$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">www.([^ <]*)@i', '<p class="spip"><a href="http://www.$1" target="'.$target.'">www.$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">ftp.([^ <]*)@i', '<p class="spip"><a href="ftp://ftp.$1" target="'.$target.'">ftp.$1</a>', $texte);
        }
    else {
        $texte = preg_replace('@ http://([^ <]*)@i', ' <a href="http://$1">http://$1</a>', $texte);
        $texte = preg_replace('@ ftp://([^ <]*)@i', ' <a href="ftp://$1">ftp://$1</a>', $texte);
        $texte = preg_replace('@ www.([^ <]*)@i', ' <a href="http://www.$1">www.$1</a>', $texte);
        $texte = preg_replace('@ ftp.([^ <]*)@i', ' <a href="ftp://ftp.$1">ftp.$1</a>', $texte);
        $texte = preg_replace('@\(http://([^ <]*)\)@i', ' (<a href="http://$1">http://$1</a>)', $texte);
        $texte = preg_replace('@\(ftp://([^ <]*)\)@i', ' (<a href="ftp://$1">ftp://$1</a>)', $texte);
        $texte = preg_replace('@\(www.([^ <]*)\)@i', ' (<a href="http://www.$1">www.$1</a>)', $texte);
        $texte = preg_replace('@\(ftp.([^ <]*)\)@i', ' (<a href="ftp://ftp.$1">ftp.$1</a>)', $texte);
        $texte = preg_replace('@^http://([^ <]*)@i', '<a href="http://$1">http://$1</a>', $texte);
        $texte = preg_replace('@^ftp://([^ <]*)@i', '<a href="ftp://$1">ftp://$1</a>', $texte);
        $texte = preg_replace('@^www.([^ <]*)@i', '<a href="http://www.$1">www.$1</a>', $texte);
        $texte = preg_replace('@^ftp.([^ <]*)@i', '<a href="ftp://ftp.$1">ftp.$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">http://([^ <]*)@i', '<p class="spip"><a href="http://$1">http://$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">ftp://([^ <]*)@i', '<p class="spip"><a href="ftp://$1">ftp://$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">www.([^ <]*)@i', '<p class="spip"><a href="http://www.$1">www.$1</a>', $texte);
        $texte = preg_replace('@<p class="spip">ftp.([^ <]*)@i', '<a href="ftp://ftp.$1">ftp.$1</a>', $texte);
        }
    $texte = preg_replace('#([^ >]*)@([^ ,:!?&<]*)#i', ' <a href="mailto:$1@$2">$1@$2</a>', $texte);

    return $texte;
}

function asterisme ($section,$edition) {
	$retour = ' ';
	// Pas d'asterisme pour les sections non multiples de 10
	if ((intval($section)%10)>0)
		$retour = '';
	
	// Exceptions de la seconde édition
	if (ed_code($edition)=='ii' AND in_array(intval($section),array(360,640,810))) # Avant 630 c'est discutable. Ca dépend des versions papiers. Mais 2 pages Wiki
		$retour = '';
	
	// Attention la page 81 commence à 812, 810 et 811 étant sur la 80
	if (intval($section)==812)
		$retour = ' ';
	
	return $retour;
}

// Filtre pour déterminer la bonne page d'une section (en raison des irrégularités de la seconde édition)
function filtre_page_section ($section) {
	$section = intval($section);
	if ($section>=360 AND $section<365)
		return 35;
	elseif ($section==640)
		return 63;
	elseif ($section==810 OR $section==811)
		return 80;
	else
		return substr($section,0,2);
}

// Ajoute des liens sur les textterms et les codes de section
function filtre_ajoute_liens ($texte) {
	$texte = preg_replace('/([0-9]{3})([^-])/','<a href="#$1">$1</a>$2',$texte);
	$texte = preg_replace('/([0-9]{3})-([0-9]{1,2})/','<a href="#$1">$1</a>-$2',$texte);
	return $texte;
}

// Cette fonction vise à améliorer la présentation de l'index
// Si deux entrées commencent par le même mot
function ameliorer_index($texte) {
	static $prec='';
	$texte = str_replace(' —','—',$texte);
	$texte = str_replace('—','&nbsp;—',$texte);
	
	$pos = mb_strpos($texte,'/');
	if ($pos === FALSE) {
		$cur = trim($texte);
	} else {
		$cur = trim(mb_substr($texte,0,$pos));
	}

	if ($prec =='')
		$idem = FALSE;
	// elseif ($pos === FALSE AND mb_strpos($cur,$prec.' ')===0) Si on souhaite aussi regrouper les entrées principales commençant par la même chose.
	//	$idem = TRUE;
	elseif ($cur==$prec)
		$idem = TRUE;
	else
		$idem = FALSE;
	if ($idem) {
		if ($pos===FALSE) {
			return '<span class="indent">&nbsp;</span>— '.trim(mb_substr($texte,mb_strlen($prec)));
		} else {
			return '<span class="indent">&nbsp;</span>'.trim(mb_substr($texte,$pos+1));
		}
	} else {
		if ($pos===FALSE) {
			$prec = $cur;
			return $texte;
		} else {
			$prec = $cur;
			return str_replace(' / ','<br />',$texte);
		}
	}
}

?>