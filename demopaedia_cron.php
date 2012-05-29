<?php

function demopaedia_taches_generales_cron($taches){
	$taches['majdemopaedia'] = 600; // toutes les 5 minutes
	return $taches;
}
?>