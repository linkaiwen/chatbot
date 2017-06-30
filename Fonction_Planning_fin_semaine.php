<?php

/*Fonction permettant de connaitre les services jusqu'à la fin de la semaine*/
function Planning_fin_semaine($apiUrl,$token){
	$objet='information';
	/*On cherche l'ID de l'employé, puis on cherche la liste des services qui lui sont rattachés, puis on cherche les données de ces services (nom client, adresse, date)*/
	$Donnee_service=Planning_service($apiUrl,$token,Service_Searching_fin_semaine($apiUrl,$token,ID_searching($apiUrl,$token),$objet));
	
	/*Création du message en format Json*/
	$cpt_global=0; //Sert à compter le nombre de message Json envoyé car 10 max
	$cpt_message_temp=0; //Sert à compté le nombre de message dans un message Json envoyé, max 5
	$cpt_nb_message=0; //Sert à compter le nombre de message dans les message Json envoyé
	
	$message=""; //Message dans un message
	$messages=[]; //Tableau des messages à envoyer
	if($Donnee_service==NULL){
		if($_POST["Semaine"]=="actuelle") Affichage(["Tu n'as plus rien de prévu cette semaine !"]);
		else if($_POST["Semaine"]=="prochaine") Affichage(["text" => "Tu n'as rien de prévu la semaine prochaine !"]);
	}
	else{
		foreach ($Donnee_service as $service) {

			$Info_client=Customer_Adress($apiUrl,$token,$service["ID_client"]);	

			if($Info_client["nom"]!="" && $Info_client["line"]!="" && $cpt_global<10){
				$message=$message."Le ".DateAffichage($service["Horaire_debut"],$service["Horaire_fin"])." chez ".ucfirst(strtolower($Info_client["nom"]))." ".ucfirst(strtolower($Info_client["prenom"]))." habitant au ".strtolower($Info_client["line"])." ".$Info_client["zip"]." ".ucfirst(strtolower($Info_client["city"]))." ".strtolower($Info_client["country"]."\n\n");
				$cpt_message_temp++;

				if($cpt_message_temp==5){					
					array_push($messages,$message);
					$cpt_message_temp=0;
					$message="";
					$cpt_global ++;
				}	
				$cpt_nb_message++;	
				
			}
		}
	}

	if($message!="") array_push($messages,$message);
	Affichage($messages);
}

/*Fonction permettant de lister les services liés à une personne*/
function Service_Searching_fin_semaine($apiUrl,$token,$ID){
	if($_POST["Semaine"]=="actuelle"){
		$DateHeureActuelle=DateHeure("actuelle");		
		$DateHeureFinSemaine=DateHeure("actuellefin");
	}

	else if($_POST["Semaine"]=="prochaine"){
		$DateHeureActuelle=DateHeure("prochaine");		
		$DateHeureFinSemaine=DateHeure("prochainefin");
	}

	$result=DataSearching($apiUrl,$token,"search","service",["id_employee"=>$ID,'start_date' => "@between|".$DateHeureActuelle."|".$DateHeureFinSemaine]);
	$liste_ID_service=[];
	foreach ($result["array_service"]["result"] as $val) {
		array_push($liste_ID_service,$val["id_service"]);		
	}

	return $liste_ID_service;
}

/*Fonction permettant de calculer en format correct la date et l'heure d'un debut ou fin de semaine*/
function DateHeure($semaine){
	if($semaine=="actuelle") return date("Y").date("m").date("d").date("H").date("i");
	else if($semaine=="actuellefin"){
		//Calcule du nombre de jour jusqu'au prochain dimanche: date du jour - numero du jour dans la semaine + 7
		$JourFinSemaine=date("j")-date("w")+7;
		//Calcule du jour du nouveau mois : jour % nb de jour dans le mois
		$Newjour=$JourFinSemaine%date("t");
		//Calcule du nouveau mois : mois actuel + division entière du jour ou l'on est divisé par le nombre de jour dans le mois
		$Newmois=date("n")+(int)($JourFinSemaine/date("t"));
		//Calcule de la nouvelle année : année acutelle + nouveau mois divisé par 12
		$Newannee=date("Y")+(int)($Newmois/12);
		if($Newmois>12) $Newmois=$Newmois%12;

		if(strlen($Newjour)==1) $Newjour="0".$Newjour;
		if(strlen($Newmois)==1) $Newmois="0".$Newmois;
		return $Newannee.$Newmois.$Newjour."2359";
	}
	else if($semaine=="prochaine"){
		//Calcule du nombre de jour jusqu'au prochain dimanche: date du jour - numero du jour dans la semaine + 7
		$JourFinSemaine=date("j")-date("w")+8;
		//Calcule du jour du nouveau mois : jour % nb de jour dans le mois
		$Newjour=$JourFinSemaine%date("t");
		//Calcule du nouveau mois : mois actuel + division entière du jour ou l'on est divisé par le nombre de jour dans le mois
		$Newmois=date("n")+(int)($JourFinSemaine/date("t"));
		//Calcule de la nouvelle année : année acutelle + nouveau mois divisé par 12
		$Newannee=date("Y")+(int)($Newmois/12);
		if($Newmois>12) $Newmois=$Newmois%12;

		if(strlen($Newjour)==1) $Newjour="0".$Newjour;
		if(strlen($Newmois)==1) $Newmois="0".$Newmois;
		return $Newannee.$Newmois.$Newjour."0000";
	}
	else if($semaine=="prochainefin") {
		//Calcule du nombre de jour jusqu'au prochain dimanche: date du jour - numero du jour dans la semaine + 7
		$JourFinSemaine=date("j")-date("w")+14;
		//Calcule du jour du nouveau mois : jour % nb de jour dans le mois
		$Newjour=$JourFinSemaine%date("t");
		//Calcule du nouveau mois : mois actuel + division entière du jour ou l'on est divisé par le nombre de jour dans le mois
		$Newmois=date("n")+(int)($JourFinSemaine/date("t"));
		//Calcule de la nouvelle année : année acutelle + nouveau mois divisé par 12
		$Newannee=date("Y")+(int)($Newmois/12);
		if($Newmois>12) $Newmois=$Newmois%12;

		if(strlen($Newjour)==1) $Newjour="0".$Newjour;
		if(strlen($Newmois)==1) $Newmois="0".$Newmois;
		return $Newannee.$Newmois.$Newjour."2359";
	}
}

?>