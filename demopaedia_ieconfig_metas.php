<?php

function demopaedia_ieconfig_metas($table){
	$table['demopaedia']['titre'] = _T('demopaedia:demopaedia');
	$table['demopaedia']['icone'] = 'img/demopaedia-24.png';
	$table['demopaedia']['metas_serialize'] = 'demopaedia';
	return $table;
}

?>