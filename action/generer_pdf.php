<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

function action_generer_pdf_dist(){
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	$edition = $arg;
	
	include_spip('inc/demopaedia');
	$ok = demopaedia_generer_pdf($edition);
	
	// Redirection
	include_spip('inc_headers');
	if ($ok)
		redirige_par_entete("./?exec=demopaedia&message_pdf=$edition#generer");
	else
		redirige_par_entete("./?exec=demopaedia&message_pdf_pb=$edition#generer");
}
?>