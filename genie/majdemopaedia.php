<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('base/abstract_sql');

// http://doc.spip.org/@genie_optimiser_dist
function genie_majdemopaedia_dist($t) {
	// On prends une édition au hasard
	$cfg_demopaedia = unserialize($GLOBALS['meta']['demopaedia']);
	$editions = explode(',',$cfg_demopaedia['editions']);
	shuffle($editions);
	$hasard = $editions[0];
	include_spip('inc/demopaedia');
	demopaedia_maj_edition($hasard);
	
	// On mets aussi à jour les deux éditions avec les entrées les plus anciennes
	$old = sql_getfetsel('edition', 'spip_demodef', '', '', 'maj', '0,1');
	demopaedia_maj_edition($old);
	$old = sql_getfetsel('edition', 'spip_demodef', '', '', 'maj', '0,1');
	demopaedia_maj_edition($old);
	
	return 0;
}

?>
