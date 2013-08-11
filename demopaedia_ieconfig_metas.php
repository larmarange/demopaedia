<?php

function demopaedia_ieconfig_metas($table){
	$table['demopaedia']['titre'] = _T('demopaedia:demopaedia');
	$table['demopaedia']['icone'] = 'demopaedia-16.png';
	$table['demopaedia']['metas_serialize'] = 'demopaedia,demopaedia-*';
	return $table;
}

?>