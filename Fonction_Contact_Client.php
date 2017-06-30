<?php

/*Fonction permettant de contacter un client grâce à son ID*/
function Contact_client($apiUrl,$token,$ID_client){
	$result=DataSearching($apiUrl,$token,"get","customer",["id_customer" => $ID_client]);
	$Tel_fixe="+33".substr($result["customer"][0]["landline"],1);
	$Tel_portable="+33".substr($result["customer"][0]["mobile_phone"],1);
	$Nom=$result["customer"][0]["last_name"];
	$Prenom=$result["customer"][0]["first_name"];
	$Email=$result["customer"][0]["email"];
	
	if($Tel_fixe!="+33" && $Tel_portable!="+33") $texte="Je te propose de contacter ".$Prenom." ".$Nom." sur son téléphone fixe: ".$Tel_fixe."\nOu portable: ".$Tel_portable."\n";
	else if($Tel_fixe!="+33" && $Tel_portable=="+33") $texte="Je te propose de contacter ".$Prenom." ".$Nom." sur son téléphone fixe: ".$Tel_fixe."\n";
	else if($Tel_fixe=="+33" && $Tel_portable!="+33") $texte="Je te propose de contacter ".$Prenom." ".$Nom." sur son téléphone portable ".$Tel_portable."\n";
	if(count($Email)!=0) $texte=$texte."Voici son adresse email: ".$Email;
	if($Tel_fixe!="+33" && $Tel_portable!="+33") $boutons=[["type" => "phone_number","title" => "Appel Fixe","phone_number" => $Tel_fixe],["type" => "phone_number","title" => "Appel Portable","phone_number" => $Tel_portable]];
	else if($Tel_fixe!="+33" && $Tel_portable=="+33") $boutons=[["type" => "phone_number","title" => "Appel Fixe","phone_number" => $Tel_fixe]];
	else if($Tel_fixe=="+33" && $Tel_portable!="+33") $boutons=[["type" => "phone_number","title" => "Appel Portable","phone_number" => $Tel_portable]];
	if($Tel_fixe=="+33" && $Tel_portable=="+33" && count($Email)==0){
		$texte="Il n'y a aucunes informations permettant de contacter ".$Prenom." ".$Nom." :/";
		$boutons=[];
	}
	else if($Tel_fixe=="+33" && $Tel_portable=="+33" && count($Email)!=0){
		$texte="Je n'ai trouvé que l'email de ".$Prenom." ".$Nom." : ".$Email." :)";
		$boutons=[];
	}
	array_push($boutons,["type" => "show_block","block_name" => "Menu","title" => "Menu"]);
	Ajout_Bouton($texte,$boutons);
}

/*Fonction permmettant de contacter le prochain client*/
function Contact_Prochain_Client($apiUrl,$token){
	$objet='information';
	/*On cherche l'ID de l'employé, puis on cherche la liste des services qui lui sont rattachés, puis on cherche les données de ces services (nom client, adresse, date)*/
	//On cherche les 10 prochains services car parfois le premier service n'est pas forcement le suivant
	$_POST["Nb_service"]=10;
	$Donnee_service=Chronologique(Planning_service($apiUrl,$token,Service_Searching($apiUrl,$token,ID_searching($apiUrl,$token))));
		
	if($Donnee_service==NULL){
		Affichage(["Pas de client trouvé."]);
		return 0;
	}

	Contact_client($apiUrl,$token,$Donnee_service[0]["ID_client"]);
}

/*Fonction permmettant de contacter un client en particulier*/
function Contact_Client_Particulier($apiUrl,$token){
	$Client=Trouver_client($apiUrl,$token);
	$Client=explode(" ",$Client);
	$_POST["Nom_client"]=$Client[0];
	$_POST["Prenom_client"]=$Client[1];	
	$result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"],"first_name" => $_POST["Prenom_client"]]);
	if(count($result["array_customer"]["result"][0]["id_customer"])==0) $result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"]." ".$_POST["Prenom_client"]]);
	Contact_client($apiUrl,$token,$result["array_customer"]["result"][0]["id_customer"]);
}

/*Fonction permettant d'afficher la liste des clients de l'employée à plus ou moins 15 jours */
function Liste_client($apiUrl,$token){
	$date="@between|".date("Ymd", strtotime("-15 days"))."0000|".date("Ymd", strtotime("+15 days"))."2359";	
	$result=DataSearching($apiUrl,$token,"search","service",["start_date" => $date]);

	$nb_page=(int)($result["array_service"]["pagination"]["count"]/20)+1;
	$Liste_client=[];
	for ($i=1; $i<=$nb_page; $i++){ 	
		$service=DataSearching($apiUrl,$token,"search","service",['start_date' => $date,'pagenum' => $i]);
		foreach ($service["array_service"]["result"] as $service) {
			$client=DataSearching($apiUrl,$token,"get","customer",['id_customer' => $service["id_customer"]]);
			if($Liste_client[$client["customer"][0]["title"].$client["customer"][0]["last_name"]." ".$client["customer"][0]["first_name"]]!=1)
				$Liste_client[$client["customer"][0]["title"].$client["customer"][0]["last_name"]." ".$client["customer"][0]["first_name"]]=1;				
		}		
	}
	$message="Voici la liste de tes clients :)";
	$i=0;
	foreach ($Liste_client as $key => $value) {
		$i++;
		$message=$message."\nn°: ".$i." ".$key;
	}
	if(!empty($Liste_client)) Affichage([$message]);
	else Affichage(["Je ne t'ai pas trouvé de client :/"]);

}

//Fonction permettant de trouver un client en fonction du numéro envoyé par l'utilisateur
function Trouver_client($apiUrl,$token){
	$date="@between|".date("Ymd", strtotime("-15 days"))."0000|".date("Ymd", strtotime("+15 days"))."2359";	
	$result=DataSearching($apiUrl,$token,"search","service",["start_date" => $date]);

	$nb_page=(int)($result["array_service"]["pagination"]["count"]/20)+1;
	$Liste_client=[];
	for ($i=1; $i<=$nb_page; $i++){ 	
		$service=DataSearching($apiUrl,$token,"search","service",['start_date' => $date,'pagenum' => $i]);
		foreach ($service["array_service"]["result"] as $service) {
			$client=DataSearching($apiUrl,$token,"get","customer",['id_customer' => $service["id_customer"]]);
			if($Liste_client[$client["customer"][0]["last_name"]." ".$client["customer"][0]["first_name"]]!=1)
				$Liste_client[$client["customer"][0]["last_name"]." ".$client["customer"][0]["first_name"]]=1;				
		}		
	}
	$i=0;
	foreach ($Liste_client as $key => $value) {
		$i++;
		if($i==$_POST["Client"]){
			return $key;
		}
	}
}

?>