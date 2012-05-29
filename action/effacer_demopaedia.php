<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_effacer_demopaedia_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	$edition = $arg;
	
	include_spip('inc/demopaedia');
	demopaedia_effacer_edition($edition);
	
	// Redirection
	include_spip('inc_headers');
	redirige_par_entete("./?exec=demopaedia&message_supp=$edition");
}
?>