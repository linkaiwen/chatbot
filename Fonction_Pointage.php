<?php

/*Fonction permettant de liste les intervention de la journée pouvant être pointée*/
function ListePointage($apiUrl,$token){
	$result=DataSearching($apiUrl,$token,"search","service",["status" => "A",'start_date' => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d").date("H").date("i")]);
	if(count($result["array_service"]["result"])==0){
		$result=DataSearching($apiUrl,$token,"search","service",['start_date' => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d")."2359"]);
		if(count($result["array_service"]["result"])>0) $texte="Tu as pointé toutes tes interventions pour aujourd'hui :)";
		else $texte="Tu n'as rien à pointer car tu n'as pas d'interventions aujourd'hui :)";
		$boutons=[["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
		Ajout_Bouton($texte,$boutons);
		return 0;
	}
	$message="";
	/*On classe les services par ordre chronologique*/	
	$liste_ID_service=[];
	$cpt=0;
	foreach ($result["array_service"]["result"] as $val) {
		array_push($liste_ID_service,$val["id_service"]);	
		$cpt++;	
	}	
	$liste_donnée_service=[];
	foreach ($liste_ID_service as $ID) {		
		$result=DataSearching($apiUrl,$token,"get","service",["id_service"=>$ID]);
		array_push($liste_donnée_service,["ID_service" => $ID,"ID_client" => $result["service"]["id_customer"],"Horaire_debut" => $result["service"]["start_date"],"Horaire_fin" => $result["service"]["end_date"]]);
	
	}	
	$liste_donnée_service=Chronologique($liste_donnée_service);
	
	/**************************************/
	foreach ($liste_donnée_service as $key => $service){
		$DonneesService=DataSearching($apiUrl,$token,"get","service",["id_service" => $service["ID_service"]]);
		if($DonneesService["service"]["status"]=="A"){
			$DonneesClient=DataSearching($apiUrl,$token,"get","customer",["id_customer" => $DonneesService["service"]["id_customer"]]);
			$message=$message."Service n°: ".($key+1)." chez ".$DonneesClient["customer"][0]["first_name"]." ".$DonneesClient["customer"][0]["last_name"]." de ".$DonneesService["service"]["start_date"][8].$DonneesService["service"]["start_date"][9]."h".$DonneesService["service"]["start_date"][10].$DonneesService["service"]["start_date"][11]." à ".$DonneesService["service"]["end_date"][8].$DonneesService["service"]["end_date"][9]."h".$DonneesService["service"]["end_date"][10].$DonneesService["service"]["end_date"][11]."\n";
		}
	}
	$texte="Voilà la liste des services de la journée non pointés :)\n".$message."\nTu peux pointer une seule intervention et y laisser un commentaire, ou alors, pointer toutes les interventions sans y laisser de commentaires :)";
	$boutons=[["type" => "show_block","block_name" => "VerifPointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Tout pointer Verif","title" => "Tout pointer"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
	Ajout_Bouton($texte,$boutons);

}

/*Fonction permettant de pointer une intervention*/
function Pointage($apiUrl,$token){
	//Recherche de l'intervention correspondant au numero entré par l'utilisateur
	$result=DataSearching($apiUrl,$token,"search","service",["status" => "A",'start_date' => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d").date("H").date("i")]);
	
	/*On classe les services par ordre chronologique*/	
	$liste_ID_service=[];
	$cpt=0;
	foreach ($result["array_service"]["result"] as $val) {
		array_push($liste_ID_service,$val["id_service"]);	
		$cpt++;	
	}	
	$liste_donnée_service=[];
	foreach ($liste_ID_service as $ID) {		
		$result=DataSearching($apiUrl,$token,"get","service",["id_service"=>$ID]);
		array_push($liste_donnée_service,["ID_service" => $ID,"ID_client" => $result["service"]["id_customer"],"Horaire_debut" => $result["service"]["start_date"],"Horaire_fin" => $result["service"]["end_date"]]);
	
	}	
	$liste_donnée_service=Chronologique($liste_donnée_service);
	$_POST["ID_service"]=$liste_donnée_service[$_POST["ID_service"]-1]["ID_service"];
	$result=DataSearching($apiUrl,$token,"get","information");
	$_POST["Commentaire"]="Intervention pointée par ".$result["information"][0]["first_name"]." ".$result["information"][0]["last_name"]." le ".date("d")."/".date("m")."/".date("Y")." à ".date("H")."h".date("i");
	Commentaire_intervention($apiUrl,$token,$_POST["ID_service"]);
	DataSearching($apiUrl,$token,"set","service",["id_service" => $_POST["ID_service"],"status" => "R"]);
	Ajout_Bouton("C'est pointé ! ;)\n\nTu peux si tu veux laisser un commentaire sur cette intervention, ou alors, en pointer une autre :)",[["set_attributes" => ["ID_service" => $_POST["ID_service"]],"type" => "show_block","block_name" => "Commentaire pointage","title" => "Laisser Commentaire"],["type" => "show_block","block_name" => "ListePointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
}

function VerifPointage($apiUrl,$token){
	//Recherche de l'intervention correspondant au numero entré par l'utilisateur
	$result=DataSearching($apiUrl,$token,"search","service",["status" => "A",'start_date' => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d").date("H").date("i")]);
	
	/*On classe les services par ordre chronologique*/	
	$liste_ID_service=[];
	$cpt=0;
	foreach ($result["array_service"]["result"] as $val) {
		array_push($liste_ID_service,$val["id_service"]);	
		$cpt++;	
	}	
	$liste_donnée_service=[];
	foreach ($liste_ID_service as $ID) {		
		$result=DataSearching($apiUrl,$token,"get","service",["id_service"=>$ID]);
		array_push($liste_donnée_service,["ID_service" => $ID,"ID_client" => $result["service"]["id_customer"],"Horaire_debut" => $result["service"]["start_date"],"Horaire_fin" => $result["service"]["end_date"]]);
	
	}	
	$liste_donnée_service=Chronologique($liste_donnée_service);
	$_POST["ID_service"]=$liste_donnée_service[$_POST["ID_service"]-1]["ID_service"];

	$result=DataSearching($apiUrl,$token,"search","service",["id_service" => $_POST["ID_service"],"id_employee" => $_POST["ID_employee"],"start_date" => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d")."2359"]);
	if(count($result["array_service"]["result"])==0){
		Ajout_Bouton("Je ne trouve pas le service que tu souhaites pointer :/",[["type" => "show_block","block_name" => "VerifPointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
		return 0;
	}
	$result=DataSearching($apiUrl,$token,"get","service",["id_service" => $_POST["ID_service"]]);
	if(count($result["service"])==0) Ajout_Bouton("Je ne trouve pas le service que tu souhaites pointer :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
	else{
		$client=DataSearching($apiUrl,$token,"get","customer",["id_customer" => $result["service"]["id_customer"]]);
		$texte="Tu souhaites bien pointer l'intervention de ".$result["service"]["start_date"][8].$result["service"]["start_date"][9]."h".$result["service"]["start_date"][10].$result["service"]["start_date"][11]." à ".$result["service"]["end_date"][8].$result["service"]["end_date"][9]."h".$result["service"]["end_date"][10].$result["service"]["end_date"][11]." chez ".$client["customer"][0]["first_name"]." ".$client["customer"][0]["last_name"]." ? :)";
		$boutons=[["type" => "show_block","block_name" => "PointageService","title" => "Oui"],["type" => "show_block","block_name" => "Menu","title" => "Non / Menu"]];
		Ajout_Bouton($texte,$boutons);
	}
}

/*Fonction permettant de laisser un sur l'intervention en entrée*/
function Commentaire_intervention($apiUrl,$token,$ID_service){

	if($_POST["Recherche"]=="LaisserCommentaire"){	
		$result=DataSearching($apiUrl,$token,"get","service",["id_service" => $ID_service]);
		$texte_initial=$result["service"]["comment"];		
		$result=DataSearching($apiUrl,$token,"get","information");
		$texte_initial=$texte_initial."\nCommentaire de ".$result["information"][0]["first_name"]." ".$result["information"][0]["last_name"].", le ".date("d")."/".date("m")."/".date("Y")." à ".date("H")."h".date("i")." :\n".$_POST["Commentaire"];
		Ajout_Bouton("Commentaire ajouté ! :)\nVeux-tu laisser un autre commentaire ?",[["type" => "show_block","block_name" => "Commentaire pointage","title" => "Oui"],["type" => "show_block","block_name" => "Menu","title" => "Non / Menu"],["type" => "show_block","block_name" => "ListePointage","title" => "Pointer intervention"]]);
		DataSearching($apiUrl,$token,"set","service",["id_service" => $ID_service,"comment" => $texte_initial]);
	}
	else{
		$result=DataSearching($apiUrl,$token,"get","service",["id_service" => $ID_service]);
		$texte_initial=$result["service"]["comment"];
		$texte_initial=$texte_initial.$_POST["Commentaire"];
		DataSearching($apiUrl,$token,"set","service",["id_service" => $ID_service,"comment" => $texte_initial]);
	}	
}

/*Fonction permettant de pointer toute les interventions*/
function Tout_Pointer($apiUrl,$token){
	$result=DataSearching($apiUrl,$token,"search","service",["status" => "A",'start_date' => "@between|".date("Y").date("m").date("d")."0000|".date("Y").date("m").date("d").date("H").date("i")]);		
	foreach ($result["array_service"]["result"] as $service){
		if($service["status"]=="A"){
			$Info=DataSearching($apiUrl,$token,"get","information");
			$_POST["Commentaire"]="Intervention pointée par ".$Info["information"][0]["first_name"]." ".$Info["information"][0]["last_name"]." le ".date("d")."/".date("m")."/".date("Y")." à ".date("H")."h".date("i");
			Commentaire_intervention($apiUrl,$token,$service["id_service"]);
			DataSearching($apiUrl,$token,"set","service",["id_service" => $service["id_service"],"status" => "R"]);
		}
	}

	Ajout_Bouton("Tout à bien été pointé ! :)",[["type" => "show_block","block_name" => "Heures travaillées","title" => "Heures Travaillées"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
}

?>