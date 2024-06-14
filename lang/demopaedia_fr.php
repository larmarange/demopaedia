<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(
	// Interface du site publique
	'oui' => 'Oui',
	'non' => 'Non',
	'rechercher' => 'Rechercher',
	'consulter' => 'Consulter',
	'generer' => 'Générer',
	'label_terme_recherche' => 'Texte à rechercher',
	'label_terme_recherche_details' => 'La recherche est insensible à la casse et aux accents. Ainsi, <i>FECONDITE</i> retournera bien <i>fécondité</i>. Vous pouvez également saisir uniquement une portion d\'un mot : <i>fecon</i> renverra ainsi <i>fécondité</i>.<br />Si vous souhaitez afficher toutes les entrées d\'index, cocher la case ci-dessous.',
	'label_entrees_secondaires' => 'Inclure les entrées d\'index secondaires dans la recherche',
	'label_edition' => 'Édition du dictionnaire',
	'label_format' => 'Format',
	'label_voir_tous' => 'Afficher tous les termes',
	'label_limiter_recherche_edition' => 'Limiter la recherche à une édition',
	'label_limiter_recherche_chapitre' => 'Limiter la recherche à un chapitre',
	'label_corr' => 'Termes correspondants des éditions (optionnel)',
	'liste_couvertures' => 'Couvertures',
	'texte_recherche' => 'Texte recherché :',
	'recherche_aucun_resultat' => 'Votre recherche n\'a produit aucun résultat.',
	'sources' => 'Sources :',
	'terme' => 'Terme',
	'termes' => 'Termes',
	'termes_notes' => 'Termes des notes',
	'section' => 'Section :',
	'numero_reference' => 'Numéro de référence :',
	'terme_dans_contexte' => 'Terme dans son contexte',
	'entrees_principales' => 'Entrée(s) principale(s)',
	'entrees_secondaires' => 'Entrée(s) secondaire(s)',
	'termes_correspondants_autres_editions' => 'Termes correspondants dans les autres éditions',
	'lire_sur_demopaedia' => 'Lire sur demopædia',
	'langue_interface' => 'Langue de l\'interface',
	'toutes_les_editions' => 'Toutes les éditions',
	'tous_les_chapitres' => 'Tous les chapitres',
	'chapitre' => 'Chapitre',
	'sommaire' => 'Sommaire',
	'changer_edition' => 'Changer d\'édition',
	
	// Commun aux deux
	
	'fr' => 'Français',
	'es' => 'Espagnol',
	'ar' => 'Arabe',
	'ru' => 'Russe',
	'pl' => 'Polonais',
	'cs' => 'Tchèque',
	'pt' => 'Portugais',
	'it' => 'Italien',
	'sv' => 'Suédois',
	'et' => 'Estonien',
	'zh' => 'Chinois',
	'ja' => 'Japonais',
	'de' => 'Allemand',
	'en' => 'Anglais',
	'id' => 'Indonésien',
	'ko' => 'Coréen',
	'ms' => 'Malais',
	'th' => 'Thaï',
	'ne' => 'Népalais',
	'vi' => 'Vietnamien',
	'sh' => 'Serbo-Croate',
	'i' => 'première édition',
	'ii' => 'seconde édition',
	
	// Interface de configuration (espace privé)
	'demopaedia' => 'Demopædia',
	
	'cfg_general' => 'Paramètres généraux',
	'configurer_une_edition' => 'Configurer une édition',
	'demopaedia_edition' => 'Paramètres de chaque édition',
	'demopaedia_termes' => 'Termes de chaque édition',
	'cfg_selection_edition' => 'Choisir une édition',
	'cfg_afficher' => 'Afficher',
	'cfg_configurer' => 'Configurer :',
	'cfg_termes' => 'Termes du dictionnaire :',
	'cfg_termes_details' => 'Rentrer dans la langue du dictionnaire les termes employés et les titres de chapitre.',
	'configurer_demopaedia' => 'Configurer Demopædia',
	
	'label_avertissement' => 'Avertissement / Notice',
	'label_domaine' => 'Nom de domaine',
	'label_domaine_details' => 'Sans http:// ni www, par exemple : <i>demopaedia.org</i>.',
	'label_editions' => 'Éditions du dictionnaire',
	'label_editions_details' => 'Liste des codes des éditons disponibles en ligne, séparés par une virgule, exemple : <i>fr-i,fr-ii,en-i,en-ii</i>.',
	'label_pages_i' => 'Sections de la première édition',
	'label_pages_ii' => 'Sections de la seconde édition',
	'label_pages_details' => 'Liste des sections numériques (pages wiki) du dictionnaire démographique, séparés par une virgule, exemple : <i>10,11,12,20,21</i>.',
	'label_choix_edition' => 'Choisir une édition',
	'label_page_avertissement' => 'Page contenant l\'avertissement',
	'label_page_avertissement_details' => 'Indiquer le nom de la page (partie située après <i>/wiki/</i> dans l\'URL de la page. Laisser vide si cette page n\'existe pas ou ne doit pas être prise en compte.',
	'label_page_introduction' => 'Page contenant l\'introduction',
	'label_page_introduction_details' => 'Indiquer le nom de la page (partie située après <i>/wiki/</i> dans l\'URL de la page. Par exemple, <i>Introduction</i> dans la 2e édition française. Laisser vide si cette page n\'existe pas ou si elle ne doit pas être prise en compte.',
	'label_page_preface' => 'Page contenant la préface',
	'label_page_preface_details' => 'Indiquer le nom de la page (partie située après <i>/wiki/</i> dans l\'URL de la page. Par exemple, <i>Préface</i> dans la 2e édition française. Laisser vide si cette page n\'existe pas.',
	'label_page_copyright' => 'Page contenant le copyright',
	'label_page_copyright_details' => 'Indiquer le nom de la page (partie située après <i>/wiki/</i> dans l\'URL de la page. Laisser vide si cette page n\'existe pas.',
	'label_interclassement' => 'Interclassement',
	'label_interclassement_details' => 'Spécifie le mode de tri alphabétique (<i>utf8mb4_unicode_ci</i> par défaut).',
	
	'label_titre_dico' => 'Titre du dictionnaire',
	'label_titre_dico_details' => 'Exemple : <i>Dictionnaire démographique multilingue</i>.',
	'label_annee' => 'Année de l\'édition',
	'label_details_edition' => 'Détails de l\'édition',
	'label_details_edition_details' => 'Par exemple : <i>seconde édition française de 1981</i>.',
	'label_titre_index' => 'Titre de l\'index',
	'label_titre_index_details' => 'Exemple : <i>Index français du dictionnaire démographique multilingue</i>.',
	'label_titre_index_court' => 'Titre court de l\'index',
	'label_titre_index_court_details' => 'Exemple : <i>Index français</i> ou <i>English index</i>.',
	'label_introduction' => 'Introduction',
	'label_preface' => 'Préface',
	'label_chapitre' => 'Chapitre',
	'label_chapitre1' => 'Titre du chap. 1',
	'label_chapitre2' => 'Titre du chap. 2',
	'label_chapitre3' => 'Titre du chap. 3',
	'label_chapitre4' => 'Titre du chap. 4',
	'label_chapitre5' => 'Titre du chap. 5',
	'label_chapitre6' => 'Titre du chap. 6',
	'label_chapitre7' => 'Titre du chap. 7',
	'label_chapitre8' => 'Titre du chap. 8',
	'label_chapitre9' => 'Titre du chap. 9',
	'label_index' => 'Index',
	'label_sommaire' => 'Sommaire',
	'label_termes_correspondants' => 'Termes correspondants',
	'label_termes_correspondants_details' => 'Exemple : <i>avec les termes correspondants des éditions suivantes :</i>',
	'label_droit_auteur' => 'Droits d\'auteur (copyright)',
	'label_licence' => 'Licence',
	'label_details_licence' => 'Détails de la licence',
	'label_details_licence_details' => 'Exemple : <i>Contenu disponible sous Attribution-Share Alike 3.0 Unported (http://creativecommons.org/licenses/by-sa/3.0/).</i>',
	'label_date_document' => 'Date du document',
	'label_date_document_details' => 'Exemple : <i>Ce document a été généré le @date@.</i> Le code @date@ doit impérativement être présent.',
	
	'maj_base' => 'Mise à jour de la base de donnée',
	'maj' => 'Mettre à jour',
	'effacer' => 'Effacer',
	'derniere_maj' => 'MAJ :',
	'jamais_maj' => 'JAMAIS TÉLÉCHARGÉE',
	'generation_documents' => 'Génération des documents',
	'generer_pdf' => 'Générer le PDF',
	'generer_html' => 'Générer le HTML',
	
	'message_pdf' => 'Le PDF de l\'édition <strong>@edition@</strong> a été généré.',
	'message_pdf_pb' => 'Problème lors de la génération du PDF de l\'édition <strong>@edition@</strong>.',
	'message_html' => 'La version HTML de l\'édition <strong>@edition@</strong> a été générée.',
	'message_html_pb' => 'Problème lors de la génération de la version HTML de l\'édition <strong>@edition@</strong>.',
	'message_maj' => 'L\'édition <strong>@edition@</strong> a été mise à jour.',
	'message_maj_pb' => 'Problème lors de la mise à jour de l\'édition <strong>@edition@</strong>.',
	'message_supp' => 'L\'édition <strong>@edition@</strong> a été effacée de la base de données.',
	
	'nom_menu_articlessecteurlangue' => 'Articles d\'un secteur de langue',
	'description_menu_articlessecteurlangue' => 'Cette entrée est spécifique aux sites utilisant un secteur par langue. Elle affiche automatiquement un menu listant les articles du secteur correspondant à la langue de la page. Par défaut, affiche toutes les articles depuis la racine, triés par titre (numériquement puis alphabétiquement).',
	
	'recherche_index' => 'Recherche dans l\'index',
	'recherche_index_description' => 'Choisir cette composition pour les articles qui doivent intégrer le formulaire de recherche dans les index.',
	'generer_dictionnaire' => 'Générateur de Dictionnaire',
	'generer_dictionnaire_description' => 'Choisir cette composition pour les articles qui doivent intégrer le formulaire pour générer un dictionnaire.',
	'generer_index' => 'Générateur d\'Index',
	'generer_index_description' => 'Choisir cette composition pour les articles qui doivent intégrer le formulaire pour générer un index.',
	'consulter_dictionnaire' => 'Consulter les dictionnaires',
	'consulter_dictionnaire_description' => 'Choisir cette composition pour les articles proposant la consultation des dictionnaires.',

);

?>
