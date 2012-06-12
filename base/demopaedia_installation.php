<?php

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');

// Installation et mise à jour
function demopaedia_upgrade($nom_meta_version_base, $version_cible){

	$version_actuelle = '0.0';
	if (
		(!isset($GLOBALS['meta'][$nom_meta_version_base]))
		|| (($version_actuelle = $GLOBALS['meta'][$nom_meta_version_base]) != $version_cible)
	){
		include_spip('base/create');
		include_spip('base/abstract_sql');
		
		if (version_compare($version_actuelle,'0.0','=')){
			// Création des tables
			creer_base();
			
			echo "Installation des tables du plugin Demopaedia<br/>";
			ecrire_meta($nom_meta_version_base, $version_actuelle=$version_cible, 'non');
		}
		
		if (version_compare($version_actuelle,'0.4','<')){
			sql_alter("TABLE spip_demoindex CHANGE entree entree ENUM('principale', 'secondaire', 'note') DEFAULT 'principale' NOT NULL");
		}
		
		if (version_compare($version_actuelle,'0.7','<')){
			sql_alter("TABLE spip_demoinfo CHANGE type type varchar(25) not null");
		}
		
		if (version_compare($version_actuelle,'0.7','<')){
			maj_tables('spip_demoindex');
			echo "Mise à jour des tables du plugin Demopaedia<br/>";
			ecrire_meta($nom_meta_version_base, $version_actuelle=$version_cible, 'non');
		}
	
	}

}

// Désinstallation
function demopaedia_vider_tables($nom_meta_version_base){

	include_spip('base/abstract_sql');
	
	// On efface les tables du plugin
	sql_drop_table('spip_demodef');
	sql_drop_table('spip_demonotes');
	sql_drop_table('spip_demoindex');
	sql_drop_table('spip_demoinfo');
		
	// On efface la version enregistrée
	effacer_meta($nom_meta_version_base);

}

?>
