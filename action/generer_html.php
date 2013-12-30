<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_generer_html_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	$edition = $arg;
	
	include_spip('inc/demopaedia');
	$ok = demopaedia_generer_html($edition);
	
	// Redirection
	include_spip('inc_headers');
	if ($ok)
		redirige_par_entete("./?exec=demopaedia&message_html=$edition&var_mode=calcul#generer");
	else
		redirige_par_entete("./?exec=demopaedia&message_html_pb=$edition&var_mode=calcul#generer");
}
?>