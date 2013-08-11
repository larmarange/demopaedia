<?php

// Inspire de cvtconf_formulaire_charger dans inc/cvt_configurer.php
// Juste besoin d'injecter edition dans l'environnement

function formulaires_configurer_demopaedia_edition_charger($edition){
	$meta_casier = "demopaedia-$edition";
	$contexte = demopaedia_formulaires_configurer_edition_recense("configurer_demopaedia_edition",$meta_casier);
	$contexte['editable'] = true;
	$contexte['edition'] = $edition;
	$contexte['_meta_casier'] = $meta_casier;
	if (_request('var_mode')=='configurer' AND autoriser('webmestre')){
		if (!_AJAX) var_dump($contexte);
		// reinjecter pour la trace au traitement
		$contexte['_hidden'] = "<input type='hidden' name='var_mode' value='configurer' />";
	}
	return $contexte;
}

// Modifiee pour spécifier le bon _meta_casier

function demopaedia_formulaires_configurer_edition_recense($form,$meta_casier){
	$valeurs = array('editable'=>' ');

	// sinon cas analyse du squelette
	if ($f = find_in_path($form.'.' . _EXTENSION_SQUELETTES, 'formulaires/')
		AND lire_fichier($f, $contenu)) {

		for ($i=0;$i<2;$i++) {
			// a la seconde iteration, evaluer le fond avec les valeurs deja trouvees
			// permet de trouver aussi les name="#GET{truc}"
			if ($i==1) $contenu = recuperer_fond("formulaires/$form",$valeurs);

			$balises = array_merge(extraire_balises($contenu,'input'),
				extraire_balises($contenu,'textarea'),
				extraire_balises($contenu,'select'));

			foreach($balises as $b) {
				if ($n = extraire_attribut($b, 'name')
					AND preg_match(",^([\w\-]+)(\[\w*\])?$,",$n,$r)
					AND !in_array($n,array('formulaire_action','formulaire_action_args'))
					AND extraire_attribut($b,'type')!=='submit') {
						$valeurs[$r[1]] = '';
						// recuperer les valeurs _meta_xx qui peuvent etre fournies
						// en input hidden dans le squelette
						if (strncmp($r[1],'_meta_',6)==0)
							$valeurs[$r[1]] = extraire_attribut($b,'value');
					}
			}
		}
	}

	$valeurs['_meta_casier'] = $meta_casier;
	cvtconf_configurer_lire_meta($form,$valeurs);
	return $valeurs;
}


