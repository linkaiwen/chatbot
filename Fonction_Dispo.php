<?php

function disponiblite($apiUrl,$token){
	$ID = ID_searching($apiUrl,$token);

	/*Gestion de la date*/
	$Date = DayFormatting();

	if($Date=="Incorrect" || $Date=="Passee" || $Date=="Impossible"){		
		if($Date=="Incorrect") Ajout_Bouton("Format de la date incorrect :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
		else if($Date=="Passee") Ajout_Bouton("Tu vies dans le passée ! :p",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
		else if($Date=="Impossible") Ajout_Bouton("Ce jour n'existe pas, tu m'auras pas comme ça ;)",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
		return 0;
	}
			
	$result=DataSearching($apiUrl,$token,"search","service",["id_employee"=>$ID,'start_date' => "@between|".$Date["annee"].$Date["mois"].$Date["jour"]."0000|".$Date["annee"].$Date["mois"].$Date["jour"]."2359"]);
	if(count($result["array_service"]["result"])=="0") Affichage(["Tu n'as pas d'intervention prévu ce jour !"]);
	else{
		$messages=[];
		foreach ($result["array_service"]["result"] as $service) {
			$Info_client=Customer_Adress($apiUrl,$token,$service["id_customer"]);			
			array_push($messages,"Intervention le ".DateAffichage($service["start_date"],$service["end_date"])." chez ".ucfirst(strtolower($Info_client["nom"]))." ".ucfirst(strtolower($Info_client["prenom"]))." habitant au ".strtolower($Info_client["line"])." ".$Info_client["zip"]." ".ucfirst(strtolower($Info_client["city"]))." ".strtolower($Info_client["country"]));
		}
		Ajout_Bouton($messages,[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
	}
}

//fonction permettant de mettre la date entrée par l'utilisateur au format adéquat


function DayFormatting(){
	$date=str_split($_POST["Date"]);

	/*Vérification du bon format JJ/MM/ANNE*/
	if(count($date)==10){
		$jour=$date[0].$date[1];
		$mois=$date[3].$date[4];
		$annee=$date[6].$date[7].$date[8].$date[9];		
		$jourmois=cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
		$Dateformate=["annee" => $annee, "mois" => $mois, "jour" => $jour];
		/*Vérification que la date n'est pas antérieur au jour J*/	
		if($jour > $jourmois || $jour<=0 || $mois>12 || $mois<0) return "Impossible";
		if($_POST["Recherche"]=="Dispo"){
			if($annee < date("Y")) return "Passee";	
			else if($annee == date("Y") && $mois < date("n")) return "Passee";			
			else if($annee == date("Y") && $mois == date("n") && $jour < date("j")) return "Passee";
			
		}
		else if($_POST["Recherche"]=="HeuresTravaillees"){
			if($annee > date("Y")) return "Futur";
			else if($annee == date("Y") && $mois > date("n")) return "Futur";
			else if($annee == date("Y") && $mois == date("n") && $jour > date("j")) return "Futur";			
		}
			
		

		return $Dateformate;
	
	}
	else if(count($date)!=10) return "Incorrect";
}




?>