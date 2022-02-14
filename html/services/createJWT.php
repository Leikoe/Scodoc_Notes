<?php
/***************************************/
/* Service de création de token JWT   /*
/* https://github.com/firebase/php-jwt */
/***************************************/

	$path = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');

	include_once "$path/includes/default_config.php";
	include_once "$path/includes/user.class.php";
	$user = new User();

	if(
		$user->getSessionName() != 'sebastien.lehmann@uha.fr' &&
		$user->getSessionName() != 'denis.graef@uha.fr'
	){ 
		die("Ce service n'est autorisé que pour Sébastien Lehmann, vous pouvez le contacter.");
	}

	use \Firebase\JWT\JWT;

	include $path . '/lib/JWT/JWT.php';

	$exp = time() + 7 * 3600 * 24 ; // today + 7 days
    $root_url = (isset($_SERVER["https"]) ? "https://" : "http://" ). $_SERVER["HTTP_HOST"];
	$payload = [
		'session' => 'lorenzo.jacq@uha.fr', // identifiant (nip, mail ou autre), de la personne identifié dans le jeton (on peut se faire passer pour n'importe qui)
		'statut' => 'etudiant', // 'etudiant' | 'personnel' | 'administrateur' | 'superadministrateur' | INCONNU
		'exp' => $exp // (optionnel) timestamp d'expiration du token
	];
	echo $root_url."?token=".JWT::encode($payload, $Config->JWT_key);
?>
