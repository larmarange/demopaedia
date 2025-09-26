<?php
//echo "hi not";exit;
define('_DIR_DEMOPAEDIA', _DIR_IMG.'demopaedia/');
define('_DIR_DEMOPAEDIA_DICTIONARY', _DIR_DEMOPAEDIA.'dictionary/');

include_spip('inc/config');
//spip_log("Checkpoint A: edition = $edition", "demopaedia_debug");
spip_log("\nStarting demopaedia update", "demopaedia"); //should be in tmp/log/demopaedia.log
//spip_log("Variable X = " . var_export($x, true), "demopaedia");
//spip_log("Reached cleanup exact replacements", "demopaedia");

function demopaedia_effacer_edition($edition){
	include_spip('base/abstract_sql');
	sql_delete('spip_demodef','edition = '.sql_quote($edition));
	if (lg_code($edition) == 'zh'){
//	  if( $variant == 'zh_hant'){
	    spip_log("Demopaedia : la table spip_demoindexzh_trad va être supprimée ". $variant, "demopaedia");
	    sql_delete('spip_demoindexzh_trad','edition = '.sql_quote($edition));
//	  }else{ // on ne doit pas supprimer spip_demoindexzh qui a été juste créée dans demopaedia_maj_edition via generate_demoindex_from_python
	    spip_log("Demopaedia : la table spip_demoindexzh va être supprimée ". $variant, "demopaedia");
	    sql_delete('spip_demoindexzh','edition = '.sql_quote($edition));
//	  }
	  spip_log("Demopaedia : la table spip_demoindex de ". $edition ." va être supprimée mais pas la mémoire demoindex ". $variant, "demopaedia");
	}
	sql_delete('spip_demoindex','edition = '.sql_quote($edition));

	// Check if the table exists in the current connection
	if (sql_showtable('spip_demoindexth', true)) {
	  sql_delete('spip_demoindexth', 'edition = ' . sql_quote($edition));
	}
	/* sql_delete('spip_demoindexth','edition = '.sql_quote($edition)); */
	sql_delete('spip_demonotes','edition = '.sql_quote($edition));
	sql_delete('spip_demoinfo','edition = '.sql_quote($edition));
	if (lg_code($edition) == 'zh'){
	  spip_log("Demopaedia : la table spip_demoinfo a été supprimée ". $variant, "demopaedia");
	}
	spip_log("Demopaedia : l'édition $edition a été supprimée de la base de donnée.");
	if (lg_code($edition) == 'zh'){
	  spip_log("Demopaedia : l'édition $edition a été supprimée de la base de donnée.","demopaedia");
	}
        // On invalide le cache
	include_spip('inc/invalideur');
	suivre_invalideur($edition);
}

function convert_simplified_to_traditional($text) {
    $tmpfile = tempnam(sys_get_temp_dir(), 'simp');
    file_put_contents($tmpfile, $text);

    // Escape for shell
    $escaped = escapeshellarg($tmpfile);

    // Call Python OpenCC
    $cmd = "PYTHONIOENCODING=utf-8 python3 -c \"from opencc import OpenCC; "
         . "print(OpenCC('s2t').convert(open($escaped, encoding='utf-8').read()))\"";
    //  cmd= PYTHONIOENCODING=utf-8 python3 -c "from opencc import OpenCC; print(OpenCC('s2t').convert(open('/tmp/simpWUz5hC', encoding='utf-8').read()))"
    //die("convert_simplified_to_traditional  $cmd");
    // log is on ./tmp/log/generate_index.log
    $converted = shell_exec($cmd . " 2>&1");
    // converted is a text stream
    
    // Split lines for individual processing
    //$lines = preg_split('/\R/u', $converted);

    // Prepare log file
    //$log_file =  _DIR_TMP . "log/generate_index2.log";

    // Known exact replacements in the text, not in the index.
    $override_traditional = [
        "姓名錶" => "姓名表",
	//"聯閤家庭" => "聯合家庭", // Shouldn't be 聯閤家庭 which is too old
	//"复合家庭" => "聯合家庭", // Shouldn't be 聯閤家庭 which is too old
	"聯合家庭" => "聯合家庭", // Shouldn't be 聯閤家庭 which is too old
	"聯機" => "網際網路", // (on line) (228-2) we tend to translate as網際網路, but it is up to you.
	"記錄" => "紀錄", // # 15.  P. 118, 記錄(record)（211-3）, should be 紀錄 。Yes but in Taiwan and Hong Kong only
	"勞動力參加比" => "勞動力參與比", //labor force participation ratio）(350-6), we tend to translate as 勞動力參與比。Here, 與should be pronounced as ㄩˋ（4th sound）.
	//"人口重新分佈" => "人口重新分布",
	"分佈" => "分布", // In addition, distribution is translated as 「分布」not 「分佈」in nowadays
	"家庭計劃" => "家庭計畫", //家庭計畫 (Jiātíng Jìhuà): The standard term used in Taiwan. 家庭計劃 (Jiātíng Jìhuà): The standard term used in Mainland China.
	"家庭生育計劃" => "家庭生育計畫", // 624-4
	"計劃生育者" => "計畫生育者",  // 624-2 and 3
	"計劃生育者" => "計畫生育者",
	"計劃生育" => "計畫生育", //625-1
	"幹擾" => "干擾", //819-7 back to simplified in fact!
	"计划的生育" => "計畫的生育", // No reference to one-child policy, more neutral
	"抽樣計劃" => "抽樣計畫", // Taiwan, sampling plan but there are others 160-5
	"最初年齡分佈" => "最初年齡分布" // Taiwan 703-4 initial age distribution  
    ];
    foreach ($override_traditional as $bad => $good) {
        $hits = substr_count($converted, $bad);
        if ($hits) {
	  spip_log("override_traditional: '$bad' → '$good' ×$hits", "demopaedia");
	  $converted = strtr($converted, [$bad => $good]);
        }
    }
    // 2) robust regex cleanups (UTF-8 aware)
    //    - drop leading “予” immediately before 免稅/免税 (with optional spaces)
    //    - strip trailing article codes like （931-4） or (931-4)
    //    - trim and normalize spaces around CJK punctuation
    $patterns = [
        // Remove 予 before 免稅/免税 at word/segment boundaries
        ['rx' => '/(^|[\s>（(])予(?=\s*免[稅税])/u', 'rep' => '$1', 'desc' => "drop '予' before 免稅/免税"],
        // Remove trailing code in fullwidth or ASCII parens
        ['rx' => '/\s*[（(]\d{3,4}-\d+[）)]\s*/u', 'rep' => '', 'desc' => 'drop trailing code (（1234-5）)'],
        // Collapse excessive horizontal spaces
        ['rx' => '/\h+/u', 'rep' => ' ', 'desc' => 'collapse spaces'],
        // Remove spaces next to CJK punctuation
        ['rx' => '/\s+(?=[，。；：？！、）)》」』])/u', 'rep' => '', 'desc' => 'trim space before right punc'],
        ['rx' => '/(?<=[（(《「『])\s+/u',          'rep' => '', 'desc' => 'trim space after left punc'],
    ];
    foreach ($patterns as $p) {
        $text2 = preg_replace($p['rx'], $p['rep'], $converted, -1, $count);
        if ($count) {
	  spip_log("cleanup regex: {$p['desc']} — {$count} hit(s)", "demopaedia");
            $converted = $text2;
        }
    }
    // Remove possible trailing newline or carriage return
    $converted = rtrim($converted, "\r\n");

    return $converted;
}
function chiffre_chinois($n) {
    $map = [
        0=>'零',1=>'一',2=>'二',3=>'三',4=>'四',
        5=>'五',6=>'六',7=>'七',8=>'八',9=>'九',10=>'十',
        11=>'十一',12=>'十二',13=>'十三',14=>'十四',15=>'十五',16=>'十六'
    ];
    return isset($map[$n]) ? $map[$n] : $n;
}
			
function generate_demoindex_from_python($edition, $variant) {
    //die("generate_demo_index $edition $variant");

    // Path to your shell script
    $shell_script = '/var/www/html/demopaediahead/demopaedia-mw28/html/tools/plugins/auto/demopaedia/inc/generate_demoindex_from_shell.sh';
  
    // Build args
      
    $args = ["--edition=$edition"];
    if ($variant === 'zh_hant') {
        $args[] = "--traditional";
    }

    // Build the full command with escaped arguments
    $cmd = escapeshellcmd($shell_script);
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }
   // Add environment print for debug
    /* $cmd = "whoami; id; $cmd 2>&1"; */

    /* $output = shell_exec($cmd); */
    /* die("CMD: $cmd\nOUTPUT:\n" . $output); */

    // Run and capture output (including stderr)
    $output = shell_exec("$cmd 2>&1");
    
    //die("TO generate_demo_index e=$edition v=$variant c=$cmd o=$output");

    // Check output
    if (strpos($output, 'Exception') !== false || strpos($output, 'Traceback') !== false) { 
         die("generate_demoindex_from_python failed:\nCommand: $cmd\nOutput:\n$output"); 
    } 

    return true;
}


function demopaedia_maj_edition($edition){
	include_spip('base/abstract_sql');
	include_spip('inc/flock');
	// Added for variants of and edition. For example in Chinese /wiki/10&variant=zh_hant will display in traditional Chinese
	// but the xml is still in simplified Chinese by default.
	$variant = _request('variant'); // returns 'zh_hant' if present

	// $variant = ''; // default is empty
	// Construction de l'URL
	$url = 'http://'.$edition.'.';
	$url .= lire_config('demopaedia/domaine');
	$url .= '/w/index.php?title=Special:Export&pages=';
	$url .= str_replace(',','%0A',lire_config('demopaedia/pages_'.substr($edition,3,3)));
	if (lire_config("demopaedia-$edition/page_introduction")!='')
	  $url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_introduction"));
	if (lire_config("demopaedia-$edition/page_preface")!='')
	  $url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_preface"));
	if (lire_config("demopaedia-$edition/page_avertissement")!=''){
	  $url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_avertissement"));
	}
	// Récupération du flux
	$xml = spip_file_get_contents($url);
		// Added for traditional Chinese if their is a variant
	//Make sure OpenCC is installed (e.g., sudo yum install opencc or apt install opencc).
	// Convert to Traditional if needed
	if (lg_code($edition) == 'zh'){
	  if ( $variant == 'zh_hant') {  //
	    spip_log("Demopaedia : applies simplified_to_traditional to the wiki (xml) ". $variant, "demopaedia");
	    $xml = convert_simplified_to_traditional($xml);
	  }else{
	    spip_log("Demopaedia : no conversion of the wiki (xml) to traditional ". $variant, "demopaedia");
	  }
	  // Sauvegarde du flux xml pour debug
	  spip_log("Demopaedia : save the xml on file ". $variant, "demopaedia");
	  $logfile = _DIR_TMP . "xml_raw_" . $edition . $variant . ".xml";
	  if (!ecrire_fichier($logfile, $xml)) return false;
	}
	
	// Call Python indexer with proper variant. Works only on sql column spip_demoindex term for indexing in various transcriptions, not on text.
	// It means that in case of a variant and if there are exceptions to be treated, they have to be treated also
	// in generate_demoindex_from python!
	/* if (lg_code($edition) == 'zh' ){ */
	/*   // Feeds the table spip_demoindexzh or demoindexzh_trad from spip_demoindex for pinyin, bopop, stroke etc. */
	/*   // but doesn't change the main text. */
	/*   if( $variant == 'zh_hant'){ */
	/*     spip_log("Demopaedia : generate_demoindex_from_python (deletes and creates table spip_demoindexzh_trad from table spip_demoindex ". $variant, "demopaedia"); */
	/*   }else{ */
	/*     spip_log("Demopaedia : generate_demoindex_from_python (deletes and creates table spip_demoindexzh from table spip_demoindex ". $variant, "demopaedia"); */
	/*   } */
	/*   if (!generate_demoindex_from_python($edition, $variant)) { */
	/*     return false; // Fail early if Python call didn't work */
	/*   } */
	/* } */
	

	// On créé les tableaux qui vont récupérer les données traitées
	$demodef = array();
	$demonotes = array();
	$demoindex = array();
	$demoinfo = array();
	
	// On créé un tableau pour chaque page depuis le fichier $xml qui a peut-être été transcrit dans une autre variante.
	preg_match_all('/\<page\>(.*?)\<\/page\>/s',$xml,$pages);
	
	if (lg_code($edition) == 'zh' ){
	  spip_log("Demopaedia : on traite les pages ". $variant, "demopaedia");
	}
	// On traite chaque page séparément
	foreach($pages[1] as $page) {
		// On récupère le texte et le titre de la page
		preg_match_all('/\<title\>(.*?)\<\/title\>/',$page,$title);
		preg_match_all('/\<text(.*?)\>(.*?)\<\/text\>/s',$page,$text);
		$texte = html_entity_decode($text[2][0],ENT_NOQUOTES,'UTF-8');
		
		// Dans le cas où il s'agit d'une section du dictionnaire
		if (is_numeric($title[1][0])) {
			// Echappement des TextTerm
			$texte = preg_replace('/\{\{NewTextTerm\|([^}|]*)\|([0-9]+)\|([^}]*)\}\}/U','{TextTerm|$1|$2|nouveau=oui|$3}',$texte); // Les NewTextTerm sont traités comme des TextTerm
			$texte = preg_replace('/\{\{NewTextTerm\|([^}|]*)\|([0-9]+)\}\}/U','{TextTerm|$1|$2|nouveau=oui}',$texte); // Cas court
			$texte = preg_replace('/\{\{TextTerm\|(.*)\}\}/U','{TextTerm|$1}',$texte);
			$texte = preg_replace('/IndexEntry=/U','IndexEntry:',$texte);
			$texte = preg_replace('/OtherIndexEntry=/U','OtherIndexEntry:',$texte);
			$texte = preg_replace('/OtherIndexEntry2=/U','OtherIndexEntry2:',$texte);
			$texte = preg_replace('/OtherIndexEntry3=/U','OtherIndexEntry3:',$texte);
			$texte = preg_replace('/OtherIndexEntry4=/U','OtherIndexEntry4:',$texte);
			$texte = preg_replace('/OtherIndexEntry5=/U','OtherIndexEntry5:',$texte);
			$texte = preg_replace('/OtherIndexEntry6=/U','OtherIndexEntry6:',$texte);
			$texte = preg_replace('/OtherIndexEntry7=/U','OtherIndexEntry7:',$texte);
			$texte = preg_replace('/OtherIndexEntry8=/U','OtherIndexEntry8:',$texte);
			$texte = preg_replace('/OtherIndexEntry9=/U','OtherIndexEntry9:',$texte);
			$texte = preg_replace('/OtherIndexEntryTwo=/U','OtherIndexEntryTwo:',$texte);
			$texte = preg_replace('/OtherIndexEntryThree=/U','OtherIndexEntryThree:',$texte);
			$texte = preg_replace('/OtherIndexEntryFour=/U','OtherIndexEntryFour:',$texte);
			$texte = preg_replace('/OtherIndexEntryFive=/U','OtherIndexEntryFive:',$texte);
			$texte = preg_replace('/EnglishEntry=/U','EnglishEntry:',$texte);
			
			// On traite les NonRefTerm
			$texte = preg_replace('/\{\{NonRefTerm\|(.+)\}\}/U','<em>$1</em>',$texte);
			
			// On traite les RefNumber
			$texte = preg_replace('/\{\{RefNumber\|([0-9]+)\|([0-9]+)\|([0-9]+)\}\}/U','$1$2-$3',$texte);
			$texte = preg_replace('/\{\{RefNumber\|([0-9]+)\|([0-9]+)\|\}\}/U','$1$2',$texte); // Syntaxe incorrecte mais malheureusement utilisée
			
			// Echappement des NoteTerm avec des crochets et suppression du pipe pour éviter interférences avec le traitement des Notes
			// Les pipes liés aux IndexEntry et OtherIndexEntry sont transformés en double point d'exclamation
			$texte = preg_replace('/\{\{NoteTerm\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\}\}/U','[*NoteTerm!!$1!!$2!!$3!!$4!!$5*]',$texte);
			$texte = preg_replace('/\{\{NoteTerm\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\}\}/U','[*NoteTerm!!$1!!$2!!$3!!$4*]',$texte);
			$texte = preg_replace('/\{\{NoteTerm\|([^|{}]+)\|([^|{}]+)\|([^|{}]+)\}\}/U','[*NoteTerm!!$1!!$2!!$3*]',$texte);
			$texte = preg_replace('/\{\{NoteTerm\|([^|{}]+)\|([^|{}]+)\}\}/U','[*NoteTerm!!$1!!$2*]',$texte);
			$texte = preg_replace('/\{\{NoteTerm\|([^|{}]+)\}\}/U','[*NoteTerm!!$1*]',$texte);
			
			// On supprime commentaires, sommaire, (après avoir protégé les Notes et les titres de section) etc.
			// Note : ajout de *** pour repérer les fins de section
			$texte = preg_replace('/\{\{Note\|(.+)\}\}/U','{Note|$1}',$texte);
			$texte = preg_replace('/=== ([0-9]{3}) ===/U','***{section$1}',$texte);
			
			$texte = preg_replace('/\<\!--(.*)--\>/U','',$texte);
			$texte = preg_replace('/\{\{(.+)\}\}/U','',$texte);
			$texte = preg_replace('/__NOTOC__/U','',$texte);
			$texte = preg_replace('/\n==(.+)==/U','',$texte);
			$texte = preg_replace('/\n=(.+)=/U','',$texte);
			$texte .= '***';
			
			// On traite maintenant les définitions et les notes
			preg_match_all('/\{section([0-9]*)\}(.+)\*\*\*/Us',$texte,$sections);
			$i=0;
			foreach($sections[2] as $section) {
				$num_section = $sections[1][$i];
				$doublons = array(); // tableau qui servira à stocker les termes pour essayer de filtrer les doublons dans les NoteTerm
				
				// Traitement des TextTerm
				preg_match_all('/\{TextTerm\|(.*)\}/U',$section,$textterms);
				foreach($textterms[1] as $textterm) {
					$textterm = explode ('|',$textterm);
					$entree_principale = '';
					$entree_anglaise = '';
					$intexte = trim($textterm[0]);
					$numterme = trim($textterm[1]);
					$nouveau = 'non';
					$doublons[] = mb_strtolower($intexte);
					
					// On traite chacun des éléments (car l'ordre des IndexEntry ou des OtherIndexEntry n'est pas uniforme)
					foreach ($textterm as $entree) {
						// Est-ce une entrée principale ?
						if(substr($entree,0,10)=='IndexEntry') {
							$entree_principale = trim(substr($entree,11));
							$doublons[] = mb_strtolower($entree_principale);
						}
						
						// Est-ce un NewTextTerm ?
						if($entree=='nouveau=oui')
							$nouveau = 'oui';
						
						// Est-ce une entrée secondaire d'index ?
						$entree_secondaire = '';
						if(substr($entree,0,16)=='OtherIndexEntry2' OR substr($entree,0,16)=='OtherIndexEntry3' 
						  OR substr($entree,0,16)=='OtherIndexEntry4' OR substr($entree,0,16)=='OtherIndexEntry5'
						  OR substr($entree,0,16)=='OtherIndexEntry8' OR substr($entree,0,16)=='OtherIndexEntry7'
						  OR substr($entree,0,16)=='OtherIndexEntry9')
							$entree_secondaire = substr($entree,17);
						elseif(substr($entree,0,18)=='OtherIndexEntryTwo')
							$entree_secondaire = substr($entree,19);
						elseif(substr($entree,0,20)=='OtherIndexEntryThree')
							$entree_secondaire = substr($entree,21);
						elseif(substr($entree,0,19)=='OtherIndexEntryFour')
							$entree_secondaire = substr($entree,20);
						elseif(substr($entree,0,19)=='OtherIndexEntryFive')
							$entree_secondaire = substr($entree,20);
						elseif(substr($entree,0,15)=='OtherIndexEntry')
							$entree_secondaire = substr($entree,16);
						
						// Nettoyage des entrées secondaires
						// On essaye d'harmoniser la présentation 
						$entree_secondaire = trim($entree_secondaire);
						$entree_secondaire = str_replace('...','—',$entree_secondaire);
						$entree_secondaire = str_replace(' . ',' / ',$entree_secondaire);
						$entree_secondaire = str_replace(' , ',' / ',$entree_secondaire);
						$entree_secondaire = str_replace('. ',' / ',$entree_secondaire);
						$entree_secondaire = str_replace(', ',' / ',$entree_secondaire);
						$note_secondaire = str_replace('-—','- —',$note_secondaire);
						
						if ($entree_secondaire != '') {
							$demoindex[] = array(
								'edition' => $edition,
								'section' => $num_section,
								'numterme' => $numterme,
								'terme' => $entree_secondaire,
								'termeen' => '',
								'entree' => 'secondaire',
								'intexte' => $intexte,
								'nouveau' => $nouveau
							);
						}
						
						// Y a-t-il un EnglishEntry ?
						if(substr($entree,0,12)=='EnglishEntry')
							$entree_anglaise = substr($entree,13);
					}

					// On sauvegarde l'entrée principale
					if ($entree_principale == '')
						$entree_principale = $intexte;
					$demoindex[] = array(
						'edition' => $edition,
						'section' => $num_section,
						'numterme' => $textterm[1],
						'terme' => $entree_principale,
						'termeen' => $entree_anglaise,
						'entree' => 'principale',
						'intexte' => $intexte,
						'nouveau' => $nouveau
					);
				}
				// On remplace les TextTerm par leur version en HTML simplifié
				// Avec EnglishEntry
				//section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ($4) ',$section);
				//section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\|([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ($4) ',$section);
				// In Chinese we decided to not publish the English translation.
				// English translation will be found only in the indexes
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ',$section);
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\|([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ',$section);
				// Sans EnglishEntry
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|nouveau=oui([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2★</sup>',$section);
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup>',$section);
$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup>',$section); // Cas où on a une syntaxe courte avec juste le num du terme
				
				// Traitement des notes
                                //spip_log("Demopaedia2 : on traite les notes.","demopaedia");
				preg_match_all('/\{Note\|(.+)\}/U',$section,$notes);
				foreach($notes[1] as $note) {
					$note = explode ('|',$note);
					$entree_anglaise = ''; // Ajout des termes anglais dans une note du chinois mais il peut y avoir plusieurs Note tandis qu'il n'y a qu'un TextTerm par section
					$texte_note = $note[1];
					// Traitement des NoteTerm
                    // echo "hi NoteTerm";exit;
					preg_match_all('/\[\*NoteTerm\!\!(.*)\*\]/U',$texte_note,$noteterms);
                    //echo "hi English";exit;
					foreach ($noteterms[1] as $noteterm) {
						if (!strpos($noteterm,'!')) {
							$note_terme = trim($noteterm);
							$note_intexte = trim($noteterm);
							$noteterm = array($noteterm);
						} else {
							$noteterm = explode('!!',$noteterm);
							$note_terme = trim($noteterm[0]);
							$note_intexte = trim($noteterm[0]);
						}
						
						// On traite chacun des éléments (car l'ordre des IndexEntry ou des OtherIndexEntry n'est pas uniforme)
						foreach ($noteterm as $entree) {
							// Est-ce une entrée principale ?
							if(substr($entree,0,10)=='IndexEntry')
								$note_terme = trim(substr($entree,11));
							
							// Est-ce une entrée secondaire d'index ?
							$note_secondaire = '';
							if(substr($entree,0,16)=='OtherIndexEntry2' OR substr($entree,0,16)=='OtherIndexEntry3' 
							  OR substr($entree,0,16)=='OtherIndexEntry4' OR substr($entree,0,16)=='OtherIndexEntry5'
							  OR substr($entree,0,16)=='OtherIndexEntry8' OR substr($entree,0,16)=='OtherIndexEntry7'
							  OR substr($entree,0,16)=='OtherIndexEntry9')
								$note_secondaire = substr($entree,17);
							elseif(substr($entree,0,18)=='OtherIndexEntryTwo')
								$note_secondaire = substr($entree,19);
							elseif(substr($entree,0,20)=='OtherIndexEntryThree')
								$note_secondaire = substr($entree,21);
							elseif(substr($entree,0,19)=='OtherIndexEntryFour')
								$note_secondaire = substr($entree,20);
							elseif(substr($entree,0,19)=='OtherIndexEntryFive')
								$note_secondaire = substr($entree,20);
							elseif(substr($entree,0,15)=='OtherIndexEntry')
								$note_secondaire = substr($entree,16);
                            // Y a-t-il un EnglishEntry ?
                            if(substr($entree,0,12)=='EnglishEntry')  //Ajout pour le chinois
                                $entree_anglaise = substr($entree,13);
							
							// Nettoyage des entrées secondaires
							// On essaye d'harmoniser la présentation 
							$note_secondaire = trim($note_secondaire);
							$note_secondaire = str_replace('...','—',$note_secondaire);
							$note_secondaire = str_replace(' . ',' / ',$note_secondaire);
							$note_secondaire = str_replace(' , ',' / ',$note_secondaire);
							$note_secondaire = str_replace('. ',' / ',$note_secondaire);
							$note_secondaire = str_replace(', ',' / ',$note_secondaire);
							$note_secondaire = str_replace('-—','- —',$note_secondaire);
							//  On ajoute entree_anglaise si chinois
							if ($note_secondaire != '' && !in_array(mb_strtolower($note_secondaire),$doublons)) {
								$demoindex[] = array(
									'edition' => $edition,
									'section' => $num_section,
									'numterme' => $note[0],
									'terme' => $note_secondaire,
									'termeen' => $entree_anglaise,
									'entree' => 'note',
									'intexte' => $note_intexte,
									'nouveau' => 'non'
								);
							}
						}
						
						// On sauvegarde l'entrée principale de la note, on ajoute entree_anglaise si chinois
						if (!in_array(mb_strtolower($note_terme),$doublons)) // Seulement si le terme n'est pas deja renseigne en entree principale
							$demoindex[] = array(
								'edition' => $edition,
								'section' => $num_section,
								'numterme' => $note[0],
								'terme' => $note_terme,
								'termeen' => $entree_anglaise,
								'entree' => 'note',
								'intexte' => $note_intexte,
								'nouveau' => 'non'
							);
					}
					$texte_note = preg_replace('/\[\*NoteTerm\!\!([^![\]]+)\!\!(.+)\*\]/U','<strong>$1</strong>',$texte_note); // mise en forme avant enregistrement de la note
					$texte_note = preg_replace('/\[\*NoteTerm\!\!([^![\]]+)\*\]/U','<strong>$1</strong>',$texte_note);
					$demonotes[] = array(
						'edition' => $edition,
						'section' => $num_section,
						'note' => $note[0],
						'texte' => $texte_note
					);
				}
				// Nettoyage du texte de la section
				$section = preg_replace('/\{Note\|(.+)\}/U','',$section);
				$section = preg_replace('/\n/','',$section);
				$demodef[] = array(
					'edition' => $edition,
					'section' => $num_section,
					'texte' => $section
				);
				
				$i++;
			}
		// Fin traitement section numérique
		}
		// Traitement des autres pages
		else {
			$type = '';
			if ($title[1][0]==str_replace('_',' ',lire_config("demopaedia-$edition/page_introduction")))
			  $type = 'introduction';
			if ($title[1][0]==str_replace('_',' ',lire_config("demopaedia-$edition/page_preface")))
			  $type = 'preface';
			// In Chinese the avertissement is not identical in simplified (default) and in traditional
			// therefore we need to convert first.
			if (lg_code($edition)=='zh') {
			  spip_log("Demopaedia : debug demoinfo zh  ". $variant, "demopaedia");
			  if( $variant == 'zh_hant'){
			    $config_avertissement = convert_simplified_to_traditional(
			      str_replace('_',' ',lire_config("demopaedia-$edition/page_avertissement")));
			    spip_log("Demopaedia : debug demoinfo variant $config_avertissement .". $variant, "demopaedia");
			    spip_log("Demopaedia : debug demoinfo variant title= $title  .". $variant, "demopaedia");
			    spip_log("Demopaedia : title raw=".json_encode($title[1][0]), "demopaedia");
			    spip_log("Demopaedia : config raw=".json_encode($config_avertissement), "demopaedia");
			    if ($title[1][0] == $config_avertissement) {
			      $type = 'avertissement';
			      spip_log("Demopaedia : debug demoinfo Variant $type .". $variant, "demopaedia");
			    }
			  }else{
			    if ($title[1][0]==str_replace('_',' ',lire_config("demopaedia-$edition/page_avertissement")))
			      $type = 'avertissement';
			    spip_log("Demopaedia : debug demoinfo no variant $type .". $variant, "demopaedia");
			  }
			} else{     
			  if ($title[1][0]==str_replace('_',' ',lire_config("demopaedia-$edition/page_avertissement")))
			    $type = 'avertissement';
			}
			// Si on récupère la page
			if ($type !='') {
				// On redéfinit l'édition en cours à chaque page à cause des redirections possibles
				$edition_en_cours = $edition;
				// On vérifie qu'on n'a pas à faire à une redirection sur le About de en-ii
				if (preg_match('@\[\[en-ii(.*)About\]\]@',$texte)) {
					$url_about = 'http://en-ii.';
					$url_about .= lire_config('demopaedia/domaine');
					$url_about .= '/wiki/Special:Export/Demop%C3%A6dia:About';
					$xml_about = spip_file_get_contents($url_about);
					preg_match('/\<text.*\>(.*?)\<\/text\>/s',$xml_about,$texte_about);
					$texte = html_entity_decode($texte_about[1],ENT_NOQUOTES,'UTF-8');
					$edition_en_cours = 'en-ii';
				}
				// On supprime les commentaires et les sommaires
				$texte = preg_replace('/\<\!--(.*)--\>/U','',$texte);
				$texte = preg_replace('/\{\{(.+)\}\}/U','',$texte);
				$texte = preg_replace('/__NOTOC__/U','',$texte);
				$texte = preg_replace('/__TOC__/U','',$texte);
				$texte = preg_replace('/__FORCETOC__/U','',$texte);
				$texte = preg_replace('/__NOEDITSECTION__/U','',$texte);
				$texte = preg_replace('/__HIDDENCAT__/U','',$texte);
				$texte = preg_replace('/__NEWSECTIONLINK__/U','',$texte);
				$texte = preg_replace('/__NOGALLERY__/U','',$texte);
				// Suppression de <noinclure> et de <noinclude>
				$texte = preg_replace('/\<noinclure\>(.*)<\/noinclure\>/isU','',$texte);
				$texte = preg_replace('/\<noinclude\>(.*)<\/noinclude\>/isU','',$texte);
				// On gère les titres
				$texte = preg_replace('/=====(.+)=====/U','<h>$1</h7>',$texte);
				$texte = preg_replace('/====(.+)====/U','<h6>$1</h6>',$texte);
				$texte = preg_replace('/===(.+)===/U','<h5>$1</h5>',$texte);
				$texte = preg_replace('/==(.+)==/U','<h4>$1</h4>',$texte);
				$texte = preg_replace('/\n=(.+)=/U',"\n<h3>$1</h3>",$texte); // Le \n est nécessaire à cause des = qui peuvent trainer dans une URL
				$texte = preg_replace('/^=(.+)=/U',"\n<h3>$1</h3>",$texte); // Cas où on est au tout début du texte
				// Gras et italique
				$texte = preg_replace("/'''''(.+)'''''/U",'<strong><em>$1</em></strong>',$texte);
				$texte = preg_replace("/'''(.+)'''/U",'<strong>$1</strong>',$texte);
				$texte = preg_replace("/''(.+)''/U",'<em>$1</em>',$texte);
				// Gestion des liens
				$domaine = lire_config('demopaedia/domaine');
				$texte = preg_replace('#\[(http://[^ ]*) (.*)\]#iU', '<a href="$1">$2</a>', $texte);
				$texte = preg_replace('@\[\[(.*):(.*)\]\]@iU', '<a href="http://$1.'.$domaine.'/wiki/$2">$2</a>', $texte);
				$texte = preg_replace('@\[\[(.*)\|(.*)\]\]@iU', '<a href="http://'.$edition_en_cours.'.'.$domaine.'/wiki/$1">$2</a>', $texte);
				$texte = preg_replace('@\[\[(.*)\]\]@iU', '<a href="http://'.$edition_en_cours.'.'.$domaine.'/wiki/$1">$1</a>', $texte);
				$texte = preg_replace('@ http://([^ <]*)@i', ' <a href="http://$1">http://$1</a>', $texte);
				// Gestion des listes (deux niveaux pour le moment)
				$texte = preg_replace('/\n\*\*(.*)/',"\n<ul><ul><li>$1</li></ul></ul>",$texte);
				$texte = preg_replace('/\n\*(.*)/',"\n<ul><li>$1</li></ul>",$texte);
				$texte = preg_replace('/\<\/ul\>\<\/ul\>\n\<ul\>\<ul\>/',"\n",$texte);
				$texte = preg_replace('/\<\/li\>\<\/ul\>\n\<ul\>\<ul\>/',"\n<ul>",$texte);
				$texte = preg_replace('/\<\/ul\>\<\/ul\>\n/',"</ul></li></ul>\n",$texte);
				$texte = preg_replace('/\<\/ul\>\n\<ul\>/',"\n",$texte);
				// Gestion des listes numérotées (deux niveaux pour le moment)
				$texte = preg_replace('/\n\#\#(.*)/',"\n<ol><ol><li>$1</li></ol></ol>",$texte);
				$texte = preg_replace('/\n\#(.*)/',"\n<ol><li>$1</li></ol>",$texte);
				$texte = preg_replace('/\<\/ol\>\<\/ol\>\n\<ol\>\<ol\>/',"\n",$texte);
				$texte = preg_replace('/\<\/li\>\<\/ol\>\n\<ol\>\<ol\>/',"\n<ol>",$texte);
				$texte = preg_replace('/\<\/ol\>\<\/ol\>\n/',"</ol></li></ol>\n",$texte);
				$texte = preg_replace('/\<\/ol\>\n\<ol\>/',"\n",$texte);
				// Suppression de <nowiki> (doit se trouver après la gestion des listes)
				$texte = preg_replace('/\<nowiki\>/','',$texte);
				$texte = preg_replace('/\<\/nowiki\>/','',$texte);
				// Traitement des Tableaux
				preg_match_all('/\{\|(.*?)\|\}/Us',$texte,$tableaux);
				foreach($tableaux[1] as $tableau) {
					$tableau_traite = '<table>';
					$lignes = explode('|-',$tableau);
					foreach ($lignes as $ligne) {
						$tableau_traite .= '<tr>';
						$ligne = substr($ligne,1);
						$ligne = preg_replace('/\|\|/','|',$ligne);
						$colonnes = explode('|',$ligne);
						foreach ($colonnes as $colonne)
							$tableau_traite .= "<td>$colonne</td>";
						
						$tableau_traite .= '</tr>';
					}
					$tableau_traite .= '</table>';
					$tableau_traite = preg_replace('/\n/','',$tableau_traite);
					$texte = preg_replace('/\{\|(.*?)\|\}/Us',$tableau_traite,$texte,1);
				}
				
				// Sauts de paragraphe
				$texte = preg_replace('/\n+/',"\n",$texte);
				$texte = preg_replace('/^\n/','',$texte);
				$texte = preg_replace('/\n$/','',$texte);
				$paragraphes = explode ("\n",trim($texte));
				$texte = '';
				foreach($paragraphes as $paragraphe)
					if (substr($paragraphe,0,3)=='<p>' OR substr($paragraphe,0,7)=='<table>' OR substr($paragraphe,0,2)=='<h' OR substr($paragraphe,0,3)=='<ul' OR substr($paragraphe,0,3)=='<ol' OR substr($paragraphe,0,3)=='<li')
						$texte .= $paragraphe."\n";
					else
						$texte .= "<p>".$paragraphe."</p>\n";
				
				// On prépare les données à insérer
				$demoinfo[] = array(
					'edition' => $edition,
					'type' => $type,
					'texte' => trim($texte)
				);
				if (lg_code($edition)=='zh' and $type == 'avertissement') {
				  spip_log("Demopaedia : debug demoinfo TEXTE $texte AND  ". $variant, "demopaedia");
				}      
			}
		// Fin traitement des autres pages
		}
	// Fin traitement page
	}
	
			//if (count($demodef)>0 and count($demoindex)>0) {
			//if (lg_code($edition !='zh' and count($demodef)>0 and count($demoindex)>0) {
	/* if (count($demodef)>0 and count($demoindex)>0) { */
	if (count($demodef)>0 and count($demoindex)>0 and count($demoinfo)>0) {
		// On supprime les anciennes entrées
	        demopaedia_effacer_edition($edition);
		// Gestion du cas particulier du Thai
		if (lg_code($edition)=='th') {
			foreach ($demoindex as $c => $v)
				$demoindex[$c]['termeth'] = $demoindex[$c]['terme'];
		}
		// Gestion du cas particulier du Chinois en effet si la base est utf8mb4 l'ordre de tri par exemple en pinyin doit être
		// dans une variable ayant ce collate. Be careful that 'terme' could be already in traditional
		if (lg_code($edition)=='zh') {
		  spip_log("Demopaedia : remplacement en mémoire de demoindex terme par termezh  ". $variant, "demopaedia");
			foreach ($demoindex as $c => $v)
				$demoindex[$c]['termezh'] = $demoindex[$c]['terme'];
		}
		// On met à jour la base de données
		sql_insertq_multi('spip_demodef',$demodef);
		sql_insertq_multi('spip_demonotes',$demonotes);
		if (lg_code($edition)=='zh') {
		  spip_log("Demopaedia : copie de la memoire demoindex, eventuellement transformé, dans la table spip_demoindex  ". $variant, "demopaedia");
		}

		sql_insertq_multi('spip_demoindex',$demoindex);
		// once the table is created we create spip_demoindexzh or spip_demoindexzh_trad
		if (lg_code($edition) == 'zh' ){
		  // Feeds the table spip_demoindexzh or demoindexzh_trad from spip_demoindex for pinyin, bopop, stroke etc.
		  // but doesn't change the main text.
		  if( $variant == 'zh_hant'){
		    spip_log("Demopaedia : generate_demoindex_from_python (deletes and creates table spip_demoindexzh_trad from table spip_demoindex ". $variant, "demopaedia");
		  }else{
		    spip_log("Demopaedia : generate_demoindex_from_python (deletes and creates table spip_demoindexzh from table spip_demoindex ". $variant, "demopaedia");
		  }
		  if (!generate_demoindex_from_python($edition, $variant)) {
		    return false; // Fail early if Python call didn't work
		  }
		}

		if (count($demoinfo)>0){
		  sql_insertq_multi('spip_demoinfo',$demoinfo);
		  if (lg_code($edition) == 'zh' ){
		    spip_log("Demopaedia :  table spip_demoinfo created ". $variant, "demopaedia");
		  }
		}
		if (lg_code($edition) == 'zh' ){
		  spip_log("Demopaedia : l'édition $edition a été mise à jour.", "demopaedia");
		}
		// On invalide le cache
		include_spip('inc/invalideur');
		suivre_invalideur($edition);
		return true;
	}
	else {
		spip_log("Demopaedia : PROBLEME lors de la mise à jour de l'édition $edition.");
		return false;
	}
}

function demopaedia_verifier_export(){
	if(!is_dir(_DIR_DEMOPAEDIA))
		mkdir(_DIR_DEMOPAEDIA, 0775, true);
	if(!is_dir(_DIR_DEMOPAEDIA_DICTIONARY))
		mkdir(_DIR_DEMOPAEDIA_DICTIONARY, 0775, true);
}

function demopaedia_generer_pdf($edition){
	demopaedia_verifier_export();
	include_spip('inc/flock');
	include_spip('inc/utils');
	//die("demopaedia_generer_pdf called");
	// NEW: detect variant
	$variant = _request('variant'); // 'zh_hant' or null
	
        // If there's a variant (e.g., 'zh_hant'), append it to the filenames
	$suffix = $variant ? '-'.$variant : '';

	$file_pdf_text = _DIR_DEMOPAEDIA_DICTIONARY . $edition . $suffix . '-text.pdf';
	$file_html_prince = _DIR_DEMOPAEDIA_DICTIONARY . $edition . $suffix . '-prince.html';

	$file_html_swath = _DIR_DEMOPAEDIA_DICTIONARY.$edition.'-prince-swath.html';
	$file_pdf      = _DIR_DEMOPAEDIA_DICTIONARY . $edition . $suffix . '.pdf';

	$cover_back = find_in_path('covers/back.pdf');
	$cover_front = find_in_path('covers/' . $edition . $suffix . '.pdf');

	if (!$cover_front) $cover_front = find_in_path("covers/default-".ed_code($edition).".pdf");
	// copy($file_html_prince, _DIR_TMP . 'debug-prince.html'); 
	touch(_DIR_DEMOPAEDIA_DICTIONARY . 'test.txt');
	file_put_contents(_DIR_TMP . 'demopaedia_debug.txt', var_export($file_html_prince, true));

	if (lg_code($edition) == 'zh' ){
	  if( $variant == 'zh_hant'){
	    spip_log("Demopaedia : demopaedia_generer_pdf traditional using recuperer_fond in html and creating $file_html_prince ". $variant, "demopaedia");
	  }else{
	    spip_log("Demopaedia : demopaedia_generer_pdf simplified using recuperer_fond in html and creating $file_html_prince ". $variant, "demopaedia");
	  }
	}
	//if (!ecrire_fichier($file_html_prince,recuperer_fond('generate_dictionary', array('format' => 'prince', 'edition' => $edition)))) return false;
	// Generate the HTML
	$html = recuperer_fond('generate_dictionary', array(
				 'format' => 'prince',
				 'edition' => $edition,
				 'variant' => $variant
				 ));

	if (lg_code($edition) == 'zh' and $variant == 'zh_hant') {
	  /*   $html = convert_simplified_to_traditional($html); */
	  spip_log("Demopaedia : demopaedia_generer_pdf zh_hant not transforming again but writing $file_html_prince ". $variant, "demopaedia");
	}

	if (!ecrire_fichier($file_html_prince, $html)) return false;

	// swath requis pour le Thai
	if ($edition=='th-ii') {
		exec("cat $file_html_prince | swath -u u,u -f html | tee $file_html_swath");
		$file_html_prince = $file_html_swath;
	}
	exec('prince '.$file_html_prince.' -o '.$file_pdf_text, $out, $ret);
	$nb_pages = getPDFPages($file_pdf_text);
	if ($nb_pages % 2 == 0)   // Si nombre de pages paire
		exec("pdfjam $cover_front '1,{}' $file_pdf_text '-' $cover_back '{},1' --papersize '{152.4mm,228.6mm}' --twoside -o $file_pdf");
	else
		exec("pdfjam $cover_front '1,{}' $file_pdf_text '-' $cover_back '1' --papersize '{152.4mm,228.6mm}' --twoside -o $file_pdf");
	return file_exists($file_pdf);
}

// Source http://stackoverflow.com/questions/14644353/get-the-number-of-pages-in-a-pdf-document

function getPDFPages($document){
	// Parse entire output
	exec("pdfinfo $document", $output);
	// Iterate through lines
	$pagecount = 0;
	foreach($output as $op) {
		// Extract the number
		if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
			$pagecount = intval($matches[1]);
			break;
		}
	}
	return $pagecount;
}

function demopaedia_generer_html($edition){
    spip_log("Demopaedia : ok");
	demopaedia_verifier_export();
	include_spip('inc/flock');
	include_spip('inc/utils');
	// NEW: detect variant
	$variant = _request('variant'); // 'zh_hant' or null

	$file_html = _DIR_DEMOPAEDIA_DICTIONARY.$edition.'.html';
	return ecrire_fichier($file_html,recuperer_fond('generate_dictionary', array('format' => 'html', 'edition' => $edition, 'variant' => $variant)));
}


?>
