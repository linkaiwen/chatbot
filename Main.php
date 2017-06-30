<?php
/*Fichiers nécessaires*/
require "Fonction_Planning.php";
require "Creation_Token.php";
require "Fonction_Dispo.php";
require "Fonction_DataSearching.php";
require "Validite_nom_prenom.php";
require "Fonction_Planning_fin_semaine.php";
require "Fonction_Heures_Travaillees.php";
require "Itineraire.php";
require "Fonction_Affichage_Messenger.php";
require "Fonction_Contact_Client.php";
require "Fonction_Pointage.php";
require_once "incs/lib.crypt.php";

// $_POST["Nb_service"]=12;
// // $_POST["Date"]="14/05/2017";
//$_POST["Recherche"]="LaisserCommentaire";
// // $_POST["Semaine"]="actuelle";
// $_POST["latitude"]="48.864666";
// $_POST["longitude"]="2.423941";
// // $_POST["PublicKey"]="=>37821821860976421314"; 
// // $_POST["PrivateKey"]="=>767E3B9E6A8ED052";
// // $_POST["messenger_user_id"]="1318197634933404";
//$_POST["Nom_client"]="0client";
//$_POST["Prenom_client"]="didier";
// $_POST["ModeTransport"]="en voiture";
//$_POST["ref"]="37821821860976421314:767E3B9E6A8ED052:11299";
//$_POST["ID_service"]="2";
//Jamesbond
//$_POST["ref"]="OJNQotN7OJMKsI58Im8YotAhIvuFMty1PP5KpjIdIFZOC1pmJ2yHYjqzN2VOLtO0OTtQYSN7I2cKBSS1OG0ToNIzNm0NMtI+Im1rsSp/P2xQoyLbHzRWottmImHSp1IeNKMKBygaIQpUqNZvNGVNWDDtN31DBypfImuELDIeOvRSZDAxNQtSoSp2Kw9KWjgzNlx";
//$_POST["Commentaire"]="J'ai bien fait à manger, c'était grave bon !";
//$_POST["Client"]="sapin michel";

function main(){
	$apiUrl="https://test.ogust.com/api/v2/apiogust.php?method=";
	$token=Create_Token($apiUrl);
	if(empty($token)) return 0;

	// $result=DataSearching($apiUrl,$token,"get","productlevel",["id_productlevel" => "56648123"]);
	// print_r($result);
	if(!empty($_POST["Recherche"])){
		switch ($_POST["Recherche"]) {

			case 'Aide':
				/*Permet de gérer la rubrique d'aide affiche un bouton en cas de besoin*/
				Ajout_Bouton("Pas de panique je vais t'aider ! ;)\n\nPour m'utiliser, tu peux simplement me dire ce dont tu as besoin en l'écrivant. Tu peux sinon suivre les différents onglets qui te mèneront certainement à ta requête :)\n\nIl peut arriver que je ne comprenne pas ce que tu recherches, que je ne réponde pas correctement, ou encore, que tu ne trouves pas ce que tu souhaites.\n\nDans ces cas, je t'invite soit à consulter le fichier contenant en détail toutes mes compétences, soit à regarder le schéma qui décrit comment arriver à chacun de mes onglets ! :p\n\nTu as également un menu déroulant en bas à gauche de la fenêtre de chat que tu peux consulter ! ;)",
				[["type" => "web_url","url" => "https://test.ogust.com/lancelot/Aides.pdf","title" =>"Fichier d'aide"],
				["type" => "web_url","url" => "https://test.ogust.com/lancelot/Arbre_Accessions.pdf","title" =>"Schéma onglets"],
				["type" => "show_block","block_name" => "Menu","title" => "Menu"]]);
				break;

			case 'Planning':
				//Nécessite $_POST["ref"],$_POST["Nb_service"]		
				Planning($apiUrl,$token);
				break;

			case 'Dispo':
				//Nécessite $_POST["ref"],$_POST["Date"]		
				disponiblite($apiUrl,$token);
				break;

			case 'Semaine':
				//Nécessite $_POST["ref"],$_POST["Semaine"]=(prochaine ou actuelle)	
				Planning_fin_semaine($apiUrl,$token);
				break;

			case 'HeuresTravaillees':
				//Nécessite $_POST["ref"],$_POST["Date"]		
				Recherche_Nb_Heures($apiUrl,$token);
				break;

			case 'Itineraire_prochain_client':
				//Nécessite $_POST["ref"],($_POST["latitude"],$_POST["longitude"] ou $_POST["AdressDepart"]),$_POST["ModeTransport"] 		
				Itineraire_prochain_client($apiUrl,$token);
				break;

			case 'Itineraire_client_particulier':
				//Nécessite $_POST["ref"],($_POST["latitude"],$_POST["longitude"] ou $_POST["AdressDepart"]),$_POST["ModeTransport"] 		
				Itineraire_client_particulier($apiUrl,$token);
				break;

			case 'Itineraire_journee':
				//Nécessite $_POST["ref"],($_POST["latitude"],$_POST["longitude"] ou $_POST["AdressDepart"]),$_POST["ModeTransport"] 		
				Itineraire_Journee($apiUrl,$token);
				break;

			case 'Contacte_Prochain_Client':
				//Nécessite $_POST["ref"]
				Contact_Prochain_Client($apiUrl,$token);
				break; 		

			case 'Contacte_Client_Particulier':
				//Nécessite $_POST["ref"],$_POST["Nom_client"],$_POST["Prenom_client"]
				Contact_Client_Particulier($apiUrl,$token);
				break;

			case 'ListePointage':
				//Nécessite $_POST["ref"]
				ListePointage($apiUrl,$token);
				break;

			case 'Pointage':
				//Nécessite $_POST["ref"],$_POST["ID_service"];
				Pointage($apiUrl,$token);
				break;

			case 'VerifPointage':
				//Nécessite $_POST["ref"],$_POST["ID_service"];
				VerifPointage($apiUrl,$token);
				break;

			case 'CommentaireProchaineInter':
				//Nécessite $_POST["ref"]
				Commentaire_prochaine_inter($apiUrl,$token);
				break;

			case 'client_particulier':
				//Nécessite $_POST["ref"]
				Verification_Client($apiUrl,$token);
				break;

			case 'LaisserCommentaire':
				//Nécessite $_POST["ref"],$_POST["ID_service"],$_POST["Commentaire"]
				Commentaire_intervention($apiUrl,$token,$_POST["ID_service"]);
				break;

			case 'ToutPointer':
				//Nécessite $_POST["ref"]
				Tout_Pointer($apiUrl,$token);
				break;

			case 'ListeClient':
				//Nécessite $_POST["ref"]
				Liste_client($apiUrl,$token);
				break;

			default:
				Affichage(["Recherche inconnue :/"]);
				break;
		}
	}
		
	else{
		$result = DataSearching($apiUrl,$token,"get","information");
		$texte="Bonjour ".$result["information"][0]["first_name"]." ,\n\nJe suis Ogust, le robot qui est là pour toi ! Tu peux me demander ce que tu veux j'essayerai d'y répondre !\n\nComment puis-je t'être utile  ? ;)\n\nN'hésites pas à me dire si tu as besoin d'aide en écrivant simplement \"aide\" ou \"help\" ;)";
		$boutons=[["type" => "show_block","block_name" => "Bouton planning","title" => "Voir Planning"],["type" => "show_block","block_name" => "Bouton client","title" => "Informations Client"],["type" => "show_block","block_name" => "bouton autres","title" => "Autres"]];
		Ajout_Bouton($texte,$boutons);
		return 0;
	}
	
}

main();

