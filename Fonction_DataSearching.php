<?php

/*methode=(get,set,rem,search)/objet=(customer,employee,service,etc)/param=(id_customer,last_name,etc)/valeur=(num_id,le vrai nom,etc)*/
function DataSearching($apiUrl, $token, $methode, $objet, $paramvaleur = false){
	$payload = ['token' => $token];
	foreach ($paramvaleur as $key => $value) {
		$payload[$key]=$value;
	}
	$curl = curl_init($apiUrl.$methode.$objet);
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

?>