<?php
// Projet Réservations M2L - version web mobile
// Fonction du contrôleur CtrlConsulterReservations.php : traiter la demande de connexion d'un utilisateur
// Ecrit le 03/11/2015 par Alban Roger

// Ce contrôleur permet d'afficher les réservations
// si l'utilisateur a passé des réservations, l'application les affiche (vue VueConsulterReservations.php)
// si l'utilisateur n'a pas passé de réservations, l'application affiche un message d'erreur (vue VueConsulterReservations.php)


// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")

include_once ('modele/DAO.class.php');

$dao = new DAO();

// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
$dao->creerLesDigicodesManquants();
	
// récupération des réservations à venir créées par l'utilisateur
$lesReservations = $dao->listeReservations($nom);
$nbReponses = sizeof($lesReservations);
	
if ($nbReponses == 0){
	$msgFooter = "Vous n'avez aucune réservation !";
	$themeFooter = $themeProbleme;
	include_once ('vues/VueConsulterReservations.php');
}
else{
	$msgFooter = "Vous avez ".$nbReponses." réservation(s) !";
	$themeFooter = $themeNormal;
	include_once ('vues/VueConsulterReservations.php');
}
// ferme la connexion à MySQL
unset($dao);


