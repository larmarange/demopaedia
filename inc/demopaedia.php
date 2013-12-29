<?php
define('_DIR_DEMOPAEDIA', _DIR_IMG.'demopaedia/');
define('_DIR_DEMOPAEDIA_DICTIONARY', _DIR_DEMOPAEDIA.'dictionary/');

include_spip('inc/config');

function demopaedia_effacer_edition($edition){
	include_spip('base/abstract_sql');
	sql_delete('spip_demodef','edition = '.sql_quote($edition));
	sql_delete('spip_demoindex','edition = '.sql_quote($edition));
	sql_delete('spip_demoindexth','edition = '.sql_quote($edition));
	sql_delete('spip_demonotes','edition = '.sql_quote($edition));
	sql_delete('spip_demoinfo','edition = '.sql_quote($edition));
	spip_log("Demopaedia : l'édition $edition a été supprimée de la base de donnée.");
	// On invalide le cache
	include_spip('inc/invalideur');
	suivre_invalideur($edition);
}

function demopaedia_maj_edition($edition){
	include_spip('base/abstract_sql');
	include_spip('inc/flock');
	// Construction de l'URL
	$url = 'http://'.$edition.'.';
	$url .= lire_config('demopaedia/domaine');
	$url .= '/w/index.php?title=Special:Export&pages=';
	$url .= str_replace(',','%0A',lire_config('demopaedia/pages_'.substr($edition,3,3)));
	if (lire_config("demopaedia-$edition/page_introduction")!='')
		$url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_introduction"));
	if (lire_config("demopaedia-$edition/page_preface")!='')
		$url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_preface"));
	if (lire_config("demopaedia-$edition/page_avertissement")!='')
		$url .= '%0A'.urlencode(lire_config("demopaedia-$edition/page_avertissement"));
	
	// Récupération du flux
	$xml = spip_file_get_contents($url);
	
	// On créé les tableaux qui vont récupérer les données traitées
	$demodef = array();
	$demonotes = array();
	$demoindex = array();
	$demoinfo = array();
	
	// On créé un tableau pour chaque page
	preg_match_all('/\<page\>(.*?)\<\/page\>/s',$xml,$pages);
	
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
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ($4) ',$section);
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\|EnglishEntry:([^}]*)\|([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup> ($4) ',$section);
				// Sans EnglishEntry
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|nouveau=oui([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2★</sup>',$section);
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\|([^}]*)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup>',$section);
				$section = preg_replace('/\{TextTerm\|([^}]+)\|([0-9]+)\}/U','<strong class="textterm">$1</strong><sup class="textterm">$2</sup>',$section); // Cas où on a une syntaxe courte avec juste le num du terme
				
				// Traitement des notes
				preg_match_all('/\{Note\|(.+)\}/U',$section,$notes);
				foreach($notes[1] as $note) {
					$note = explode ('|',$note);
					$texte_note = $note[1];
					// Traitement des NoteTerm
					preg_match_all('/\[\*NoteTerm\!\!(.*)\*\]/U',$texte_note,$noteterms);
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
							
							// Nettoyage des entrées secondaires
							// On essaye d'harmoniser la présentation 
							$note_secondaire = trim($note_secondaire);
							$note_secondaire = str_replace('...','—',$note_secondaire);
							$note_secondaire = str_replace(' . ',' / ',$note_secondaire);
							$note_secondaire = str_replace(' , ',' / ',$note_secondaire);
							$note_secondaire = str_replace('. ',' / ',$note_secondaire);
							$note_secondaire = str_replace(', ',' / ',$note_secondaire);
							$note_secondaire = str_replace('-—','- —',$note_secondaire);
							
							if ($note_secondaire != '' && !in_array(mb_strtolower($note_secondaire),$doublons)) {
								$demoindex[] = array(
									'edition' => $edition,
									'section' => $num_section,
									'numterme' => $note[0],
									'terme' => $note_secondaire,
									'entree' => 'note',
									'intexte' => $note_intexte,
									'nouveau' => 'non'
								);
							}
						}
						
						// On sauvegarde l'entrée principale de la note
						if (!in_array(mb_strtolower($note_terme),$doublons)) // Seulement si le terme n'est pas deja renseigne en entree principale
							$demoindex[] = array(
								'edition' => $edition,
								'section' => $num_section,
								'numterme' => $note[0],
								'terme' => $note_terme,
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
			if ($title[1][0]==str_replace('_',' ',lire_config("demopaedia-$edition/page_avertissement")))
				$type = 'avertissement';
			// Si on récupère la page
			if ($type !='') {
				// On redéfini l'édition en cours à chaque page à cause des redirections possibles
				$edition_en_cours = $edition;
				// On vérifie qu'on a pas à faire à une redirection sur le About de en-ii
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
				
				// On préparer les données à insérer
				$demoinfo[] = array(
					'edition' => $edition,
					'type' => $type,
					'texte' => trim($texte)
				);
			}
		// Fin traitement des autres pages
		}
	// Fin traitement page
	}
	
	if (count($demodef)>0 and count($demoindex)>0) {
		// On supprime les anciennes entrées
		demopaedia_effacer_edition($edition);
		// Gestion du cas particulier du Thai
		if (lg_code($edition)=='th') {
			foreach ($demoindex as $c => $v)
				$demoindex[$c]['termeth'] = $demoindex[$c]['terme'];
		}
		// On met à jour la base de données
		sql_insertq_multi('spip_demodef',$demodef);
		sql_insertq_multi('spip_demonotes',$demonotes);
		sql_insertq_multi('spip_demoindex',$demoindex);
		if (count($demoinfo)>0)
			sql_insertq_multi('spip_demoinfo',$demoinfo);
		spip_log("Demopaedia : l'édition $edition a été mise à jour.");
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
	$file_html_prince = _DIR_DEMOPAEDIA_DICTIONARY.$edition.'-prince.html';
	$file_pdf_text = _DIR_DEMOPAEDIA_DICTIONARY.$edition.'-text.pdf';
	$file_pdf = _DIR_DEMOPAEDIA_DICTIONARY.$edition.'.pdf';
	$cover_back = find_in_path('covers/back.pdf');
	$cover_front = find_in_path("covers/$edition.pdf");
	if (!$cover_front) $cover_front = find_in_path("covers/default-".ed_code($edition).".pdf");
	if (!ecrire_fichier($file_html_prince,recuperer_fond('generate_dictionary', array('format' => 'prince', 'edition' => $edition)))) return false;
	// swath requis pour le Thai
	if ($edition=='th-ii')
		exec("swath $file_html_prince -f html -u u,u >$file_html_prince");
	exec('prince '.$file_html_prince.' -o '.$file_pdf_text, $out, $ret);
	$nb_pages = getPDFPages($file_pdf_text);
	if ($nb_pages % 2 == 0)   // Si nombre de pages paire
		exec("pdfjam $cover_front '1,{}' $file_pdf_text '-' $cover_back '{},1' --papersize '{152.4mm,228.6mm}' -o $file_pdf");
	else
		exec("pdfjam $cover_front '1,{}' $file_pdf_text '-' $cover_back '1' --papersize '{152.4mm,228.6mm}' -o $file_pdf");
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

?>