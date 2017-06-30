<?php

require_once "LibClass.php";
class User {
    public $apiUrl;
    // TODO private
    public $lib;
    public function __construct(){
        $this->apiUrl = "https://test.ogust.com/api/v2/apiogust.php?method=";
        $this->lib = new Lib($this->apiUrl);
    }


    public function availabilities(){

        $date = $this->lib->FormatDate();
        if($date=="incorrect" || $date=="past" || $date=="impossible"){
            if($date=="incorrect") $this->lib->AddButton("Format de la date incorrect :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
            else if($date=="past") $this->lib->AddButton("Tu vies dans le passée ! :p",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
            else if($date=="impossible") $this->lib->AddButton("Ce jour n'existe pas, tu m'auras pas comme ça ;)",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
            return 0;
        }

        $result = DataSearching($this->apiUrl,$this->lib->token,"search","service",['start_date' => "@between|" . $date . "0000|" . $date . "2359"]);
        if(count($result["array_service"]["result"])=="0") $this->lib->AddButton("Tu n'as pas d'intervention prévu ce jour !",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
        else{
            $messages=[];
            foreach ($result["array_service"]["result"] as $service) {
                $Info_client = Customer_Adress($this->apiUrl, $this->lib->token, $service["id_customer"]);
                array_push($messages,"Intervention le ".DateAffichage($service["start_date"],$service["end_date"])." chez ".ucfirst(strtolower($Info_client["nom"]))." ".ucfirst(strtolower($Info_client["prenom"]))." habitant au ".strtolower($Info_client["line"])." ".$Info_client["zip"]." ".ucfirst(strtolower($Info_client["city"]))." ".strtolower($Info_client["country"]));
            }
            $this->lib->AddButton($messages,[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
        }

//
//        /*Gestion de la date*/
//        $Date = DayFormatting();
//
//        if($Date=="Incorrect" || $Date=="Passee" || $Date=="Impossible"){
//            if($Date=="Incorrect") Ajout_Bouton("Format de la date incorrect :/",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
//            else if($Date=="Passee") Ajout_Bouton("Tu vies dans le passée ! :p",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
//            else if($Date=="Impossible") Ajout_Bouton("Ce jour n'existe pas, tu m'auras pas comme ça ;)",[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
//            return 0;
//        }
//
//        $result=DataSearching($this->apiUrl,$this->lib->token,"search","service",['start_date' => "@between|".$Date["annee"].$Date["mois"].$Date["jour"]."0000|".$Date["annee"].$Date["mois"].$Date["jour"]."2359"]);
//        if(count($result["array_service"]["result"])=="0") Affichage(["Tu n'as pas d'intervention prévu ce jour !"]);
//        else{
//            $messages=[];
//            foreach ($result["array_service"]["result"] as $service) {
//                $Info_client = Customer_Adress($this->apiUrl, $this->lib->token, $service["id_customer"]);
//                array_push($messages,"Intervention le ".DateAffichage($service["start_date"],$service["end_date"])." chez ".ucfirst(strtolower($Info_client["nom"]))." ".ucfirst(strtolower($Info_client["prenom"]))." habitant au ".strtolower($Info_client["line"])." ".$Info_client["zip"]." ".ucfirst(strtolower($Info_client["city"]))." ".strtolower($Info_client["country"]));
//            }
//            Ajout_Bouton($messages,[["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
//        }
    }
}