<?php
require_once "incs/lib.crypt.php";

class Lib {
    public $token;
    public $apiUrl;

    public function __construct($apiUrl){
        $this->apiUrl = $apiUrl;
        $this->token = $this->create_token();
    }

    public function create_token(){
        $Donnees = arrayDecrypte($_POST["ref"]);
        $payload = array(
            'login' => $Donnees["login"],
            'password' => $Donnees["pass"],
            'request' => 'GET_TOKEN',
            'time' => gmdate('YmdHis').'.'.mt_rand(100000, 999999)
        );
        ksort( $payload );
        $curl = curl_init($this->apiUrl."getTokenEmployee");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        $curl_response = curl_exec($curl);
        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        $result = json_decode($curl_response, true, 512, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT);
        curl_close($curl);
        //$result = json_decode($curl_response);
        if('OK' != $result['status']){
            $this->AddButton("Tu peux envoyer des messages à cette page. Cependant, si tu souhaites te connecter en tant qu'utilisateur, je t'invite à te connecter sur ton compte Ogust :)",[["type" => "web_url","url" => "https://test.ogust.com/lancelot/login.php","title" =>"Connexion"]]);
            return 0;
            //die('Requete pour récupérer numéro de token échouée : '.$result['message']);
        }
        return $this->token = $result['token'];
    }


    public function AddButton($text, $buttons){
        /*Structure bouton (3 boutons max):
        Pour URL : ["type" => "web_url","url" => $URL,"title" =>"Voir"]
        Pour block :["type" => "show_block","block_name" => "Menu","title" => "Menu"];
        Pour Appel :["type" => "phone_number","title" => "Call Representative","phone_number" => "+33612345678"]
        Ex: $boutons=[["type" => "web_url","url" => $URL,"title" =>"Voir"],["type" => "show_block","block_name" => "Menu","title" => "Menu"]];
    */
        $payload = ["template_type" => "button","text" => $text,"buttons" => $buttons];
        $arrMess = ["messages" => []];
        $attachment=["type" => "template","payload" => $payload];
        array_push($arrMess["messages"],["attachment" => $attachment]);
        $mess = json_encode($arrMess);
        print($mess);
    }

    public function SearchData($params, $method, $object){
        $payload = ['token' => $this->token ];
        foreach ($params as $key => $value) {
            $payload[$key]=$value;
        }
        $curl = curl_init($this->apiUrl.$method.$object);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        $curl_response = curl_exec($curl);
        if ($curl_response === false) {
            $info = curl_getinfo($curl);
            curl_close($curl);
            die('error occured during curl exec. Additional info: ' . var_export($info));
        }
        curl_close($curl);
        $result = json_decode($curl_response, true, 512, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT);
        return $result;
    }

    public function FormatDate(){
        /* check date format DD/MM/YYYY */
        if (preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/([0-9]{4})$/', $_POST["Date"], $matches)){
            /* check if date is in gregorian calender */
            $date =  new DateTime($matches[3] . '-' . $matches[2] . '-' . $matches[1]);
            if(checkdate($matches[2], $matches[1], $matches[3])){
                if($_POST["Recherche"] == "availabilities") {
                   return $date > new DateTime("now") ? $matches[3] . $matches[2] . $matches[1] : "past";
                }else if ($_POST['Recherche'] == "worked_hours"){
                   return $date < new DateTime("now") ? $matches[3] . $matches[2] . $matches[1] : "future";
                }
            }else return "impossible";
        }
        return "incorrect";


        /*Vérification du bon format JJ/MM/ANNE*/
//        if(count($date) == 10){
//            $jour = $date[0].$date[1];
//            $mois = $date[3].$date[4];
//            $annee = $date[6].$date[7].$date[8].$date[9];
//            $jourmois = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
//            $Dateformate = ["annee" => $annee, "mois" => $mois, "jour" => $jour];
//            /*Vérification que la date n'est pas antérieur au jour J*/
//            if($jour > $jourmois || $jour<=0 || $mois>12 || $mois<0) return "Impossible";
//            if($_POST["Recherche"]=="Dispo"){
//                if($annee < date("Y")) return "Passee";
//                else if($annee == date("Y") && $mois < date("n")) return "Passee";
//                else if($annee == date("Y") && $mois == date("n") && $jour < date("j")) return "Passee";
//
//            }
//            else if($_POST["Recherche"]=="HeuresTravaillees"){
//                if($annee > date("Y")) return "Futur";
//                else if($annee == date("Y") && $mois > date("n")) return "Futur";
//                else if($annee == date("Y") && $mois == date("n") && $jour > date("j")) return "Futur";
//            }
//
//
//
//            return $Dateformate;
//
//        }
//        else if(count($date)!=10) return "Incorrect";
    }
}