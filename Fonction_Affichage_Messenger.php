<?php

/*Fonction permettant d'afficher au format Json le texte en entrée*/

/* pas plus de 5*/
/******** $messages est un tableau !!!********/
function Affichage($messages){	

	$cpt = 0;
	$arrMess = ["messages" => []];
	foreach ($messages as $mess) {
		if($cpt < 10) $cpt++;
		else break;
		$element_ajoute = ["text" => $mess];
		array_push($arrMess["messages"],$element_ajoute);
	}

	$mess = json_encode($arrMess);
	print($mess);	

}

/*Fonction permettant de créer un bouton dans messenger*/
function Ajout_Bouton($texte,$boutons){
	/*Structure bouton (3 boutons max):
	Pour URL : ["type" => "web_url","url" => $URL,"title" =>"Voir"]
	Pour block :["type" => "show_block","block_name" => "Menu","title" => "Menu"];
	Pour Appel :["type" => "phone_number","title" => "Call Representative","phone_number" => "+33612345678"]
	Ex: $boutons=[["type" => "web_url","url" => $URL,"title" =>"Voir"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
*/	
	$payload = ["template_type" => "button","text" => $texte,"buttons" => $boutons];
	$arrMess = ["messages" => []];
	$attachment=["type" => "template","payload" => $payload];
	array_push($arrMess["messages"],["attachment" => $attachment]);
	$mess = json_encode($arrMess);
	print($mess);
}









?>