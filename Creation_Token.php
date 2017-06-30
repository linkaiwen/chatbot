<?php

/*Fonction permettant de générer un token*/
function Create_Token($apiUrl){
	$Donnees = arrayDecrypte($_POST["ref"]);
	$payload = array(		
		'login' => $Donnees["login"],		
		'password' => $Donnees["pass"],		
		'request' => 'GET_TOKEN',		
		'time' => gmdate('YmdHis').'.'.mt_rand(100000, 999999)	
		);	
	ksort( $payload );		
	$curl = curl_init($apiUrl."getTokenEmployee");	
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
		Ajout_Bouton("Tu peux envoyer des messages à cette page. Cependant, si tu souhaites te connecter en tant qu'utilisateur, je t'invite à te connecter sur ton compte Ogust :)",[["type" => "web_url","url" => "https://test.ogust.com/lancelot/login.php","title" =>"Connexion"]]);
		return 0;		
		//die('Requete pour récupérer numéro de token échouée : '.$result['message']);	
	}	

	$token = $result['token'];
	
	return $token;
}

?>