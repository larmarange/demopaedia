<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_maj_demopaedia_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	$edition = $arg;
	
	include_spip('inc/demopaedia');
	$ok = demopaedia_maj_edition($edition);
	
	// Redirection
	include_spip('inc_headers');
	if ($ok)
		redirige_par_entete("./?exec=demopaedia&message_maj=$edition");
	else
		redirige_par_entete("./?exec=demopaedia&message_maj_pb=$edition");
}
?>