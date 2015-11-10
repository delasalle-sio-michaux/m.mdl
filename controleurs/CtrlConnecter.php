<?php
// Projet Réservations M2L - version web mobile
// Fonction du contrôleur CtrlConnecter.php : traiter la demande de connexion d'un utilisateur
// Ecrit le 12/10/2015 par Jim

// Ce contrôleur vérifie l'authentification de l'utilisateur
// si l'authentification est bonne, il affiche le menu utilisateur ou administrateur (vue VueMenu.php)
// si l'authentification est incorrecte, il réaffiche la page de connexion (vue VueConnecter.php)

// on teste si le terminal client est sous Android, pour lui proposer de télécharger l'application Android
require_once 'Mobile_Detect.php';
$detect = new Mobile_Detect;

if ( $detect->isAndroidOS() ) $OS = "Android"; else $OS = "autre";

if ( ! isset ($_POST ["nom"]) == true) {
	// si les données n'ont pas été postées, c'est le premier appel du formulaire : affichage de la vue sans message d'erreur
	$nom = '';
	$mdp = '';
	$afficherMdp = 'off';
	$niveauUtilisateur = '';
	$msgFooter = 'Connexion';
	$themeFooter = $themeNormal;
	include_once ('vues/VueConnecter.php');
}
else {
	// récupération des données postées
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["afficherMdp"]) == true)  $afficherMdp = "off";  else   $afficherMdp = $_POST ["afficherMdp"];
			
	if ($nom == '' || $mdp == '') {
		// si les données sont incomplètes, réaffichage de la vue avec un message explicatif
		$msgFooter = 'Données incomplètes ou incorrectes !';
		$themeFooter = $themeProbleme;
		$niveauUtilisateur = '';
		include_once ('vues/VueConnecter.php');
	}
	else {
		// connexion du serveur web à la base MySQL
		include_once ('modele/DAO.class.php');
		$dao = new DAO();
		
		// test de l'authentification de l'utilisateur
		// la méthode getNiveauUtilisateur de la classe DAO retourne les valeurs 'inconnu' ou 'utilisateur' ou 'administrateur'
		$niveauUtilisateur = $dao->getNiveauUtilisateur($nom, $mdp);
		
		if ( $niveauUtilisateur == "inconnu" ) {
			// si l'authentification est incorrecte, retour à la vue de connexion
			$msgFooter = 'Authentification incorrecte !';
			$themeFooter = $themeProbleme;
			include_once ('vues/VueConnecter.php');
		}
		else {
			// si l'authentification est correcte, mémorisation des données de connexion dans des variables de session
			$_SESSION['nom'] = $nom;
			$_SESSION['mdp'] = $mdp;
			$_SESSION['niveauUtilisateur'] = $niveauUtilisateur;
			// affichage du menu
			include_once ('controleurs/CtrlMenu.php');
		}
		
		unset($dao);		// fermeture de la connexion à MySQL
	}
}