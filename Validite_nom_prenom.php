<?php
/*Fonction permettant de valider ou non le fait qu'un client appartiennet à la liste des clients d'un employé
utile pour contacter et trajet client en particulier pour ne pas afficher un client que l'employee n'a pas à voir*/
//True si c'est un des clients, False sinon
function Verification_Client($apiUrl,$token){
	$Client=Trouver_client($apiUrl,$token);
	$Client=explode(" ",$Client);
	$_POST["Nom_client"]=$Client[0];
	$_POST["Prenom_client"]=$Client[1];
	$result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"],"first_name" => $_POST["Prenom_client"]]);
	if(count($result["array_customer"]["result"][0]["id_customer"])==0) $result=DataSearching($apiUrl,$token,"search","customer",["last_name" => $_POST["Nom_client"]." ".$_POST["Prenom_client"]]);
	$ID_client=$result["array_customer"]["result"][0]["id_customer"];
	
	$date="@between|".date("Ymd", strtotime("-15 days"))."0000|".date("Ymd", strtotime("+15 days"))."2359";		
	$result=DataSearching($apiUrl,$token,"search","service",["start_date" => $date]);
	$nb_page=(int)($result["array_service"]["pagination"]["count"]/20)+1;
	
	for ($i=1; $i<=$nb_page; $i++) { 	
		$service=DataSearching($apiUrl,$token,"search","service",['start_date' => $date,'pagenum' => $i]);
		foreach ($service["array_service"]["result"] as $service) {
			if($service["id_customer"]==$ID_client){
				Ajout_Bouton("Que veux-tu savoir concernant ce client ? :)",[["type" => "show_block","block_name" => "Contacter client particulier","title" => "Le contacter"],["type" => "show_block","block_name" => "Trajet client particulier","title" => "Me rendre chez lui"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
				return 0;
			}
		}		
	}
	
	Ajout_Bouton("Je ne trouve pas le client que tu souhaites :/\nEs-tu sûre d'avoir entré le bon numéro ? :)",[["type" => "show_block","block_name" => "Client particulier","title" => "Client Particulier"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
	return 0;
}


?>