<?php

/*Fonction renvoyant un bouton avec le lien pour se rendre chez le prochain client*/
function Itineraire_prochain_client($apiUrl,$token){
	$objet='information';
	/*On cherche l'ID de l'employé, puis on cherche la liste des services qui lui sont rattachés, puis on cherche les données de ces services (nom client, adresse, date)*/
	//On cherche les 10 prochains services car parfois le premier service n'est pas forcement le suivant
	$_POST["Nb_service"]=10;
	$Donnee_service=Chronologique(Planning_service($apiUrl,$token,Service_Searching($apiUrl,$token,ID_searching($apiUrl,$token))));
		
	if($Donnee_service==NULL){
		Affichage(["Pas de client trouvé."]);
		return 0;
	}
	else{	
		$Info_client=Customer_Adress($apiUrl,$token,$Donnee_service[0]["ID_client"]);
		$Destination=strtolower(str_replace(' ','+',$Info_client["line"]))."+".$Info_client["zip"]."+".ucfirst(strtolower($Info_client["city"]));
	}

	if(count($_POST["latitude"])!=0) $Origine=$_POST["latitude"]."+".$_POST["longitude"];
	else if(count($_POST["AdresseDepart"])!=0) $Origine=str_replace(' ','+',$_POST["AdresseDepart"]);
	
	//driving, walking, bicycling, transit
	if(strstr(strtolower($_POST["ModeTransport"]),"pied")=="pied") $ModeTransport="walking";
	else if(strstr(strtolower($_POST["ModeTransport"]),"vélo")=="vélo" || strstr(strtolower($_POST["ModeTransport"]),"velo")=="velo") $ModeTransport="bicycling";
	else if(strstr(strtolower($_POST["ModeTransport"]),"voiture")=="voiture") $ModeTransport="driving";
	else if(strstr(strtolower($_POST["ModeTransport"]),"transport")=="transport" || strstr(strtolower($_POST["ModeTransport"]),"transport")=="transports") $ModeTransport="transit";
		
	$URL="https://www.google.com/maps/dir/?api=1&origin=".$Origine."&destination=".$Destination."&travelmode=".$ModeTransport;
	$boutons=[["type" => "web_url","url" => $URL,"title" =>"Voir"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
	switch ($ModeTransport) {
		case "walking":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." à pied :)";
			break;		
		case "driving":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en voiture :)";
			break;
		case "bicycling":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en vélo :)";
			break;
		case "transit":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en transports :)";
			break;
		default:
			$texte="Je n'ai pas compris ton mode de transport, mais voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." :)";
			break;
	}		
	Ajout_Bouton($texte,$boutons);
}

/*Fonction permettant de renvoyer l'itinéraire de la journée*/
function Itineraire_Journee($apiUrl,$token){
	/*On cherche tous les services de la journée*/
	$result=DataSearching($apiUrl,$token,"search","service",["id_employee"=>ID_searching($apiUrl,$token),'start_date' => "@between|".date("Y").date("m").date("d").date("H").date("i")."|".date("Y").date("m").date("d")."2359"]);
	$liste_ID_service=[];
	foreach ($result["array_service"]["result"] as $val) array_push($liste_ID_service,$val["id_service"]);	
	$Donnee_service=Chronologique(Planning_service($apiUrl,$token,$liste_ID_service));

	if(count($_POST["latitude"])!=0){
		$Origine=$_POST["latitude"]."+".$_POST["longitude"];
		$Destination=$Origine;
	}
	else if(count($_POST["AdresseDepart"])!=0){
		$Origine=str_replace(' ','+',$_POST["AdresseDepart"]);
		$Destination=str_replace(' ','+',$_POST["AdresseFin"]);
	}

	//driving, walking, bicycling, transit
	if(strstr(strtolower($_POST["ModeTransport"]),"pied")=="pied") $ModeTransport="walking";
	else if(strstr(strtolower($_POST["ModeTransport"]),"vélo")=="vélo" || strstr(strtolower($_POST["ModeTransport"]),"velo")=="velo") $ModeTransport="bicycling";
	else if(strstr(strtolower($_POST["ModeTransport"]),"voiture")=="voiture") $ModeTransport="driving";
	else if(strstr(strtolower($_POST["ModeTransport"]),"transport")=="transport" || strstr(strtolower($_POST["ModeTransport"]),"transport")=="transports"){
		Affichage(["Le mode 'transport' n'est pas disponible pour le trajet de la journée, désolé :/"]);
		return 0;
	}

	
	$URL="https://www.google.com/maps/dir/?api=1&origin=".$Origine."&destination=".$Destination."&travelmode=".$ModeTransport."&waypoints=";

	if($Donnee_service==NULL){
		Affichage(["Tu n'as plus de client pour aujourd'hui ! :)"]);
		return 0;
	}
	else{
		foreach ($Donnee_service as $service) {
			$Info_client=Customer_Adress($apiUrl,$token,$service["ID_client"]);
			$Destination=strtolower(str_replace(' ','+',$Info_client["line"]))."+".$Info_client["zip"]."+".ucfirst(strtolower($Info_client["city"]));
			$URL=$URL.$Destination."%7C";
		}	
	}
	//On retire le dernier %7C
	$URL=substr(substr(substr($URL,0,-1),0,-1),0,-1);		

	$boutons=[["type" => "web_url","url" => $URL,"title" =>"Voir"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
	switch ($ModeTransport) {
		case "walking":
			$texte="Voilà ton itinéraire à pied pour la fin de la journée :)";
			break;		
		case "driving":
			$texte="Voilà ton itinéraire en voiture pour la fin de la journée :)";
			break;
		case "bicycling":
			$texte="Voilà ton itinéraire en vélo pour la fin de la journée :)";
			break;		
		default:
			$texte="Je n'ai pas compris ton mode de transport, mais voilà ton itinéraire pour la fin de la journée :)";
			break;
	}		
	Ajout_Bouton($texte,$boutons);
}

/*Fonction permettant de connaitre le trajet jusqu'à chez un client en particulier*/
function Itineraire_client_particulier($apiUrl,$token){
	$Client=Trouver_client($apiUrl,$token);
	$Client=explode(" ",$Client);
	$_POST["Nom_client"]=$Client[0];
	$_POST["Prenom_client"]=$Client[1];
	$result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"],"first_name" => $_POST["Prenom_client"]]);
	if(count($result["array_customer"]["result"][0]["id_customer"])==0) $result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"]." ".$_POST["Prenom_client"]]);
	$ID_client=$result["array_customer"]["result"][0]["id_customer"];

	$Info_client=Customer_Adress($apiUrl,$token,$ID_client);
	$Destination=strtolower(str_replace(' ','+',$Info_client["line"]))."+".$Info_client["zip"]."+".ucfirst(strtolower($Info_client["city"]));
	
	if(count($_POST["latitude"])!=0) $Origine=$_POST["latitude"]."+".$_POST["longitude"];
	else if(count($_POST["AdresseDepart"])!=0) $Origine=str_replace(' ','+',$_POST["AdresseDepart"]);
	
	//driving, walking, bicycling, transit
	if(strstr(strtolower($_POST["ModeTransport"]),"pied")=="pied") $ModeTransport="walking";
	else if(strstr(strtolower($_POST["ModeTransport"]),"vélo")=="vélo" || strstr(strtolower($_POST["ModeTransport"]),"velo")=="velo") $ModeTransport="bicycling";
	else if(strstr(strtolower($_POST["ModeTransport"]),"voiture")=="voiture") $ModeTransport="driving";
	else if(strstr(strtolower($_POST["ModeTransport"]),"transport")=="transport" || strstr(strtolower($_POST["ModeTransport"]),"transport")=="transports") $ModeTransport="transit";
		
	$URL="https://www.google.com/maps/dir/?api=1&origin=".$Origine."&destination=".$Destination."&travelmode=".$ModeTransport;
	$boutons=[["type" => "web_url","url" => $URL,"title" =>"Voir"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
	switch ($ModeTransport) {
		case "walking":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." à pied :)";
			break;		
		case "driving":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en voiture :)";
			break;
		case "bicycling":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en vélo :)";
			break;
		case "transit":
			$texte="Voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." en transports :)";
			break;
		default:
			$texte="Je n'ai pas compris ton mode de transport, mais voilà le chemin pour aller chez ".$Info_client["prenom"]." ".$Info_client["nom"]." :)";
			break;
	}		
	Ajout_Bouton($texte,$boutons);
}

?>