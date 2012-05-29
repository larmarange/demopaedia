<?php

// Affiche le code langue à partir d'une édition
function lg_code ($edition) {
	return substr($edition,0,2);
}

// Renvoie la langue de l'édition sous forme textuelle, dans la langue courante
function lg_txt ($edition) {
	$code = substr($edition,0,2);
	return _T("demopaedia:$code");
}

// Affiche le code du numéro d'édition (i ou ii)
function ed_code ($edition) {
	return substr($edition,3,3);
}

// Renvoie le numéro de l'édition sous forme textuelle, dans la langue courante
function ed_txt ($edition) {
	$code = substr($edition,3,3);
	return _T("demopaedia:$code");
}

// Renvoie la langue et le numéro de l'édition sous forme textuelle, dans la langue courante
function lg_ed_txt ($edition) {
	if ($edition!='')
		return lg_txt($edition).' '.ed_txt($edition);
	else
		return '';
}

?>