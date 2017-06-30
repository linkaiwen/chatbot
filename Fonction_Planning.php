<?php

//Verif
/*Fonction permettant de récupérer l'heure, le lieu et le nom du client d'un service*/
function Planning($apiUrl,$token){
	/*On cherche l'ID de l'employé, puis on cherche la liste des services qui lui sont rattachés, puis on cherche les données de ces services (nom client, adresse, date)*/
	$Donnee_service=Chronologique(Planning_service($apiUrl,$token,Service_Searching($apiUrl,$token,ID_searching($apiUrl,$token,"information"))));
	/*Création du message en format Json*/
	$cpt_global=0; //Sert à compter le nombre de message Json envoyé car 10 max
	$cpt_message_temp=0; //Sert à compté le nombre de message dans un message Json envoyé, max 5
	$cpt_nb_message=0; //Sert à compter le nombre de message dans les message Json envoyé

	$arrMess = ["messages" => []];
	$message=""; //message dans un message
	$messages=[]; //Tableau des messages pour l'affichage
	if($Donnee_service==NULL && $_POST["Nb_service"]>1){
		Affichage(["Aucunes interventions trouvées pour les prochains jours."]);
		return 0;
	}
	else if($Donnee_service==NULL && $_POST["Nb_service"]==1){
		Ajout_Bouton("Oups, je ne t'ai pas trouvé de prochain client :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
		return 0;
	}
	else{
		foreach ($Donnee_service as $service) {
			$Info_client=Customer_Adress($apiUrl,$token,$service["ID_client"]);	
			if($Info_client["nom"]!="" && $Info_client["line"]!="" && $cpt_global<10 && $cpt_nb_message<$_POST["Nb_service"]){
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
	//Si jamais il y a moins d'intervention que demandé
	if($cpt_nb_message<$_POST["Nb_service"] && $cpt_global<10){
		array_push($messages,"Pas d'autres interventions trouvées.");
		
	}	
	array_push($messages,$message);
	if($_POST["Nb_service"]>1) Affichage($messages);
	else if($_POST["Nb_service"]==1) Ajout_Bouton("Voici ton prochain client ;)\n\n".$messages[0]."Je peux si tu veux te montrer comment le contacter, te rendre chez lui, ou encore, le commentaire lié à cette intervention :)",[["type" => "show_block","block_name" => "Contacter prochain client","title" => "Le contacter"],["type" => "show_block","block_name" => "Trajet prochain client","title" => "Se rendre chez lui"],["type" => "show_block","block_name" => "Commentaire intervention","title" => "Commentaire"]]);

}

//Verif
/*fonction permettant de trouver l'ID d'un employe*/
function ID_searching($apiUrl,$token){
	$result=DataSearching($apiUrl,$token,"getinformation");
	$ID=$result["information"][0]["id_employee"];
	return $ID;
}

//Verif
/*Fonction permettant de lister les services liés à une personne*/
function Service_Searching($apiUrl,$token,$ID){
	$result=DataSearching($apiUrl,$token,"search","service",["id_employee"=>$ID,'start_date' => "@>|".date("Y").date("m").date("d").date("H").date("i")]);
	$liste_ID_service=[];
	$cpt=0;
	foreach ($result["array_service"]["result"] as $val) {
		if($cpt<$_POST["Nb_service"]) array_push($liste_ID_service,$val["id_service"]);	
		$cpt++;	
	}	
	return $liste_ID_service;
}

//Verif
/*Fonction permettant de connaître l'horaire et l'id du client du service en fonction de l'intervenant*/
function Planning_service($apiUrl,$token,$liste_ID_service){

	$liste_donnée_service=[];
	foreach ($liste_ID_service as $ID) {		
		$result=DataSearching($apiUrl,$token,"get","service",["id_service"=>$ID]);
		array_push($liste_donnée_service,["ID_service" => $ID,"ID_client" => $result["service"]["id_customer"],"Horaire_debut" => $result["service"]["start_date"],"Horaire_fin" => $result["service"]["end_date"]]);
	
	}
	return $liste_donnée_service;
}

/*Fonction permettant d'afficher lisiblement une date de planning pour messenger*/
function DateAffichage($Date_Start,$Date_End){
	$tab_start=str_split($Date_Start);
	$tab_end=str_split($Date_End);

	if (($tab_start[0]==$tab_end[0])&&($tab_start[1]==$tab_end[1])&&($tab_start[2]==$tab_end[2])&&($tab_start[3]==$tab_end[3])&&($tab_start[4]==$tab_end[4])&&($tab_start[5]==$tab_end[5])&&($tab_start[6]==$tab_end[6])&&($tab_start[7]==$tab_end[7])) return ($tab_start[6].$tab_start[7]."/".$tab_start[4].$tab_start[5]."/".$tab_start[0].$tab_start[1].$tab_start[2].$tab_start[3]." de ".$tab_start[8].$tab_start[9]."h".$tab_start[10].$tab_start[11]." à ".$tab_end[8].$tab_end[9]."h".$tab_end[10].$tab_end[11]);

	else return ($tab_start[6].$tab_start[7]."/".$tab_start[4].$tab_start[5]."/".$tab_start[0].$tab_start[1].$tab_start[2].$tab_start[3]." de ".$tab_start[8].$tab_start[9]."h".$tab_start[10].$tab_start[11]." jusqu'au ".$tab_end[6].$tab_end[7]."/".$tab_end[4].$tab_end[5]."/".$tab_end[0].$tab_end[1].$tab_end[2].$tab_end[3]." à ".$tab_end[8].$tab_end[9]."h".$tab_end[10].$tab_end[11]);
}

//Verif
/*Fonction permettant de renvoyer l'adresse d'un client*/
function Customer_Adress($apiUrl,$token,$ID){
	$result=DataSearching($apiUrl,$token,"get","customer",["id_customer"=>$ID]);
	$info_client=["nom" => $result["customer"][0]["last_name"],"prenom" => $result["customer"][0]["first_name"],"line" => $result["customer"][0]["main_address"]["line"],"zip" => $result["customer"][0]["main_address"]["zip"],"city" => $result["customer"][0]["main_address"]["city"],"country" => $result["customer"][0]["main_address"]["country"]];
	return $info_client;
}

/*Fonction permettant de trier les services par ordre chronologique*/
function Chronologique($Donnee_service){
	$trie=True;	
	for ($i=0;$i<count($Donnee_service)-1;$i++){ 
		for ($j=$i+1;$j<count($Donnee_service);$j++){
			if(Compare_date($Donnee_service[$i]["Horaire_debut"],$Donnee_service[$j]["Horaire_debut"])==False){
				$temp=$Donnee_service[$i];
				$Donnee_service[$i]=$Donnee_service[$j];
				$Donnee_service[$j]=$temp;
				$trie=False;
			}
		}
		if($trie==True) break;	
	}
	return $Donnee_service;	
}

/*Fonction comparant deux dates et renvoyant True si $date1 < $date2*/
function Compare_date($date1,$date2){
	$date1split=str_split($date1);
	$date2split=str_split($date2);
	/*Date1*///$_POST["Recherche"]="ListePointage";
	$minute1=$date1split[10].$date1split[11];
	$heure1=$date1split[8].$date1split[9];
	$jour1=$date1split[6].$date1split[7];
	$mois1=$date1split[4].$date1split[5];
	$annee1=$date1split[0].$date1split[1].$date1split[2].$date1split[3];
	/*Date2*/
	$minute2=$date2split[10].$date2split[11];
	$heure2=$date2split[8].$date2split[9];
	$jour2=$date2split[6].$date2split[7];
	$mois2=$date2split[4].$date2split[5];
	$annee2=$date2split[0].$date2split[1].$date2split[2].$date2split[3];

	if($annee1!=$annee2){
		if($annee1<$annee2) return True;
		else return False;
	}
	else if($annee1==$annee2 && $mois1!=$mois2){
		if($mois1<$mois2) return True;
		else return False;
	}
	else if($annee1==$annee2 && $mois1==$mois2 && $jour1!=$jour2){
		if($jour1<$jour2) return True;
		else return False;
	}
	else if($annee1==$annee2 && $mois1==$mois2 && $jour1==$jour2 && $heure1!=$heure2){
		if($heure1<$heure2) return True;
		else return False;
	}
	else if($annee1==$annee2 && $mois1==$mois2 && $jour1==$jour2 && $heure1==$heure2 && $minute1!=$minute2){
		if($minute1<$minute2) return True;
		else return False;
	}
	else return True;
}

/*Fonction permettant d'afficher le commentaire de la prochaine intervention*/
function Commentaire_prochaine_inter($apiUrl,$token){
	//On cherche la prochaine intervention
	/*On cherche l'ID de l'employé, puis on cherche la liste des services qui lui sont rattachés, puis on cherche les données de ces services (nom client, adresse, date)*/
	//On cherche les 10 prochains services car parfois le premier service n'est pas forcement le suivant
	$_POST["Nb_service"]=10;
	$Donnee_service=Chronologique(Planning_service($apiUrl,$token,Service_Searching($apiUrl,$token,ID_searching($apiUrl,$token))));
	$Donnee_service=DataSearching($apiUrl,$token,"get","service",["id_service"=>$Donnee_service[0]["ID_service"]]);
	if($Donnee_service["service"]["comment"]!=""){
		$texte="Voilà le commentaire lié à la prochaine intervention :)\n\n\" ".$Donnee_service["service"]["comment"]." \"";
		$boutons=[["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
		Ajout_Bouton($texte,$boutons);
	}
	else{
		$texte="Il n'y a pas de commentaire sur la prochaine intervention :/";
		$boutons=[["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
		Ajout_Bouton($texte,$boutons);
	}
}

?>

