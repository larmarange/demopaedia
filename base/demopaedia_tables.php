<?php

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;

function demopaedia_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	$interface['table_des_tables']['demodef']='demodef';
	$interface['table_des_tables']['demonotes']='demonotes';
	$interface['table_des_tables']['demoindex']='demoindex';
	$interface['table_des_tables']['demoinfo']='demoinfo';
	
	return $interface;
}

function demopaedia_declarer_tables_principales($tables_principales){
	//-- Table demodef -----------------------------------------------------------
	$demodef = array(
		"edition" => "varchar(10) not null",
		"section" => "int DEFAULT '0' NOT NULL",
		"texte" => "longtext DEFAULT '' NOT NULL",
		"maj"	=> "TIMESTAMP"
	);
	
	$demodef_cles = array(
		"KEY edition" => "edition",
		"KEY section" => "section"
	);
	
	$tables_principales['spip_demodef'] = array(
		'field' => &$demodef,
		'key' => &$demodef_cles
	);
	

	//-- Table demonotes -----------------------------------------------------------
	$demonotes = array(
		"edition" => "varchar(10) not null",
		"section" => "int DEFAULT '0' NOT NULL",
		"note" => "int DEFAULT '0' NOT NULL",
		"texte" => "longtext DEFAULT '' NOT NULL",
		"maj"	=> "TIMESTAMP"
	);
	
	$demonotes_cles = array(
		"KEY edition" => "edition",
		"KEY section" => "section",
		"KEY note" => "note"
	);
	
	$tables_principales['spip_demonotes'] = array(
		'field' => &$demonotes,
		'key' => &$demonotes_cles
	);
	

	//-- Table demoindex -----------------------------------------------------------
	$demoindex = array(
		"edition" => "varchar(10) not null",
		"section" => "int DEFAULT '0' NOT NULL",
		"numterme" => "int DEFAULT '0' NOT NULL",
		"entree" => "ENUM('principale', 'secondaire', 'note') DEFAULT 'principale' NOT NULL",
		"terme" => "longtext DEFAULT '' NOT NULL",
		"maj"	=> "TIMESTAMP",
		"intexte" => "longtext DEFAULT '' NOT NULL",
		"nouveau" => "ENUM('non', 'oui') DEFAULT 'non'"
	);
	
	$demoindex_cles = array(
		"KEY edition" => "edition",
		"KEY section" => "section",
		"KEY numterme" => "numterme",
		"KEY entree" => "entree"
	);
	
	$tables_principales['spip_demoindex'] = array(
		'field' => &$demoindex,
		'key' => &$demoindex_cles
	);
	

	//-- Table demoinfo -----------------------------------------------------------
	$demoinfo = array(
		"edition" => "varchar(10) not null",
		"type" => "varchar(25) not null",
		"texte" => "longtext DEFAULT '' NOT NULL",
		"maj"	=> "TIMESTAMP"
	);
	
	$demoinfo_cles = array(
		"KEY edition" => "edition",
		"KEY type" => "type"
	);
	
	$tables_principales['spip_demoinfo'] = array(
		'field' => &$demoinfo,
		'key' => &$demoinfo_cles
	);
	

	return $tables_principales;
}

?>
