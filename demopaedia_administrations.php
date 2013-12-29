<?php

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/meta');

// Installation et mise à jour
function demopaedia_upgrade($nom_meta_base_version, $version_cible){
  $maj = array();
  
  $maj['create'] = array(
    array('maj_tables',array('spip_demodef','spip_demonotes','spip_demoindex','spip_demoinfo'))
  );
  
  $maj['0.4'] = array(
    array('sql_alter',"TABLE spip_demoindex CHANGE entree entree ENUM('principale', 'secondaire', 'note') DEFAULT 'principale' NOT NULL")
  );

  $maj['0.7'] = array(
    array('sql_alter',"TABLE spip_demoinfo CHANGE type type varchar(25) not null")
  );
  
  $maj['0.9'] = array(
    array('maj_tables',array('spip_demoindex'))
  );
  
  include_spip('base/upgrade');
  maj_plugin($nom_meta_base_version, $version_cible, $maj);
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
