<?php
function autoriser_demopaedia_bouton_dist($faire, $type, $id, $qui, $opt) {
	return autoriser('configurer', $type, $id, $qui, $opt);
}

/**
 * Fonction vide pour le pipeline homonyme
 */
function demopaedia_autoriser(){}

?>