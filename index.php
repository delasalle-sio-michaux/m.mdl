<?php
// Projet Réservations M2L - version web mobile
// Ecrit le 12/10/2015 par Jim

// Fonction de la page principale index.php : analyser toutes les demandes et activer le contrôleur chargé de traiter l'action demandée

// Ce fichier est appelé par tous les liens internes, et par la validation de tous les formulaires
// il est appelé avec un paramètre action qui peut prendre les valeurs suivantes :

//    index.php?action=Connecter              : pour afficher la page de connexion
//    index.php?action=Menu                   : pour afficher le menu
//    index.php?action=DemanderMdp            : pour afficher la page "mot de passe oublié"
//    index.php?action=ConsulterReservations  : pour afficher la liste des réservations de l'utilisateur
//    index.php?action=ConfirmerReservation   : pour afficher la page de confirmation d'une réservation
//    index.php?action=AnnulerReservation     : pour afficher la page de suppression d'une réservation
//    index.php?action=ChangerDeMdp           : pour afficher la page de changement de mot de passe
//    index.php?action=CreerUtilisateur       : pour afficher la page de création d'un utilisateur (il faut être administrateur pour cela)
//    index.php?action=SupprimerUtilisateur   : pour afficher la page de suppression d'un utilisateur (il faut être administrateur pour cela)

session_start();		// permet d'utiliser des variables de session

// si $debug est égal à true, certaines variables sont affichées (pour la mise au point)
$debug = false;

// choix des styles graphiques
$version = "1.4.5";			// choix de la version de JQuery Mobile (voir fichier head.php) : 1.2.0,  1.2.1,  1.3.2,  1.4.5
$themeNormal = "a";			// thème de base
$themeProbleme = "b";		// thème utilisé pour afficher un message en cas de problème

// on vérifie le paramètre action de l'URL
if ( ! isset ($_GET['action']) == true)  $action = '';  else   $action = $_GET['action'];

// lors d'une première connexion, ou après une déconnexion, on initialise à vide les variables de session
if ($action == '' || $action == 'Deconnecter')
{	$_SESSION['nom'] = '';
	$_SESSION['mdp'] = '';
	$_SESSION['niveauUtilisateur'] = '';
}

// tests des variables de session
if ( ! isset ($_SESSION['nom']) == true)  $nom = '';  else  $nom = $_SESSION['nom'];
if ( ! isset ($_SESSION['mdp']) == true)  $mdp = '';  else  $mdp = $_SESSION['mdp'];
if ( ! isset ($_SESSION['niveauUtilisateur']) == true)  $niveauUtilisateur = '';  else  $niveauUtilisateur = $_SESSION['niveauUtilisateur'];

// si l'utilisateur n'est pas encore identifié, il sera automatiquement redirigé vers le contrôleur d'authentification
// (sauf s'il ne peut pas se connecter et demande de se faire envoyer son mot de passe qu'il a oublié)
if ($nom == '' && $action != 'DemanderMdp') $action = 'Connecter';

switch($action){
	case 'Connecter': {
		include_once ('controleurs/CtrlConnecter.php'); break;
	}
	case 'Menu': {
		include_once ('controleurs/CtrlMenu.php'); break;
	}
	case 'DemanderMdp': {
		include_once ('controleurs/CtrlDemanderMdp.php'); break;
	}
	case 'ConsulterReservations': {
		//include_once ('controleurs/CtrlConsulterReservations.php'); break;
		include_once ('controleurs/CtrlConsulterReservations.php'); break;	// ligne provisoire
	}
	case 'ConfirmerReservation': {
		//include_once ('controleurs/CtrlConfirmerReservation.php'); break;
		include_once ('controleurs/CtrlConfirmerReservation.php'); break;	// ligne provisoire
	}
	case 'AnnulerReservation': {
		//include_once ('controleurs/CtrlAnnulerReservation.php'); break;
		include_once ('controleurs/CtrlAnnulerReservation.php'); break;	// ligne provisoire
	}
	case 'ChangerDeMdp': {
		//include_once ('controleurs/CtrlChangerDeMdp.php'); break;
		include_once ('controleurs/CtrlChangerMdp.php'); break;	// ligne provisoire
	}
	case 'CreerUtilisateur': {
		//include_once ('controleurs/CtrlCreerUtilisateur.php'); break;
		include_once ('controleurs/CtrlCreerUtilisateur.php'); break;	// ligne provisoire
	}
	case 'SupprimerUtilisateur': {
		//include_once ('controleurs/CtrlSupprimerUtilisateur.php'); break;
		include_once ('controleurs/CtrlSupprimerUtilisateur.php'); break;	// ligne provisoire
	}
	default : {
		// toute autre tentative est automatiquement redirigée vers le contrôleur d'authentification
		include_once ('controleurs/CtrlConnecter.php'); break;
	}
}