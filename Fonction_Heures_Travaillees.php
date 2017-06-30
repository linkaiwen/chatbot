<?php
/*Fonction permettant de connaitre le nombre d'heure travaillé depuis un jour donné jusqu'au jour actuel*/
// function Heures_Trvaillees($apiUrl,$token){

// }

/*Fonction permettant de calculer le nombre d'heures travaillées depuis une date donnée*/
function Recherche_Nb_Heures($apiUrl,$token){
	$ID=ID_searching($apiUrl,$token);
	if($_POST["Debut"]=="semaine") $DateAnterieure=date("Ymd", strtotime("-".(date("w")-1)." days"))."0000";
	else if($_POST["Debut"]=="mois") $DateAnterieure=date("Y").date("m")."010000";
	else{
		$DateAnterieure=DayFormatting();
		if($DateAnterieure=="Incorrect" || $DateAnterieure=="Futur" || $DateAnterieure=="Impossible"){
			if($DateAnterieure=="Incorrect") Ajout_Bouton("Format de la date incorrect :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
			else if($DateAnterieure=="Futur") Ajout_Bouton("T'es dans le futur ;)",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
			else if($DateAnterieure=="Impossible") Ajout_Bouton("Ce jour n'existe pas, tu m'auras pas comme ça ;)",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
			return 0;
		}
		$DateAnterieure=$DateAnterieure["annee"].$DateAnterieure["mois"].$DateAnterieure["jour"]."0000";
	}
	$liste_ID_service=[];
	for ($i=1; $i<=50 ; $i++) { 	
		$result=DataSearching($apiUrl,$token,"search","service",["id_employee"=>$ID,'start_date' => "@between|".$DateAnterieure."|".date("Y").date("m").date("d").date("H").date("i"),'nbperpage' => 50,'pagenum' => $i]);
		foreach ($result["array_service"]["result"] as $val) {
			array_push($liste_ID_service,$val["id_service"]);		
		}		
	}
	$nbHeure=nb_heure($apiUrl,$token,$liste_ID_service,$ID);	
	if($_POST["Debut"]=="semaine") Ajout_Bouton("Tu as travaillé ".$nbHeure." depuis le début de la semaine ! :)",[["type" => "show_block","block_name" => "ListePointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
	else if($_POST["Debut"]=="mois") Ajout_Bouton("Tu as travaillé ".$nbHeure." depuis le début du mois ! :)",[["type" => "show_block","block_name" => "ListePointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
	else Ajout_Bouton("Tu as travaillé ".$nbHeure." depuis le ".$_POST["Date"]." ! :)",[["type" => "show_block","block_name" => "ListePointage","title" => "Pointer intervention"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
}

/*Fonction retournant le nombre d'heure travaillée prenant en argument une liste de service*/
function nb_heure($apiUrl,$token,$liste_ID_service,$ID_employee){

	$nbHeureRealisee=0;
	$nbHeurePasrealisee=0;
	foreach ($liste_ID_service as $ID) {		
		$result=DataSearching($apiUrl,$token,"get","service",["id_service"=>$ID]);
		/*Si le service a été réalisé on le compte comme heure dû*/
		if($result["service"]["status"]=="R"){
			$debut=str_split($result["service"]["start_date"]);
			$fin=str_split($result["service"]["end_date"]);
			$nbHeureRealisee=$nbHeureRealisee+60-intval($debut[10].$debut[11])+60*(intval($fin[8].$fin[9])-1-intval($debut[8].$debut[9]))+intval($fin[10].$fin[11]);
		}
		/*Sinon on le compte comme heure non pointé*/
		else if($result["service"]["status"]=="A"){
			$debut=str_split($result["service"]["start_date"]);
			$fin=str_split($result["service"]["end_date"]);
			$nbHeurePasRealisee=$nbHeurePasRealisee+60-intval($debut[10].$debut[11])+60*(intval($fin[8].$fin[9])-1-intval($debut[8].$debut[9]))+intval($fin[10].$fin[11]);
		}

	}
	$mess="";
	if($nbHeureRealisee%60<10) $mess=$mess.((int)($nbHeureRealisee/60)."h0".($nbHeureRealisee%60));
	else $mess=$mess.((int)($nbHeureRealisee/60)."h".($nbHeureRealisee%60));
	if($nbHeurePasRealisee!=0){
		if($nbHeurePasRealisee%60<10) $mess=$mess." et il y a eu ".((int)($nbHeurePasRealisee/60)."h0".($nbHeurePasRealisee%60));
		else $mess=$mess." et il y a eu ".((int)($nbHeurePasRealisee/60)."h".($nbHeurePasRealisee%60));
		return $mess." non pointées (ou non réalisées)";
	}
	else return $mess;
	
}


?>