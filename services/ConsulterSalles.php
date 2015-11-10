<?php
// Service web du projet Réservations M2L
// Ecrit le 21/5/2015 par Jim

// Ce service web permet à un utilisateur de consulter la liste des salles proposées à la réservation
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 2 paramètres : nom, mdp
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConsulterSalles.php?nom=zenelsy&mdp=passe
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterSalles.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer
global $lesSalles, $nbReponses;

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/parametres.localhost.php');

// crée une instance de DOMdocument 
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';
  
// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web ConsulterSalles - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
}
  
// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" )
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
		TraitementAnormal("Erreur : authentification incorrecte.");
	else 
	{	// récupération de la liste des salles
		$lesSalles = $dao->listeSalles($nom);
		$nbReponses = sizeof($lesSalles);
		
		TraitementNormal();
	}
	// ferme la connexion à MySQL
	unset($dao);
}
// Mise en forme finale   
$doc->formatOutput = true;  
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


// fonction de traitement des cas anormaux
function TraitementAnormal($msg)
{	// redéclaration des données globales utilisées dans la fonction
	global $doc;
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
 

// fonction de traitement des cas normaux
function TraitementNormal()
{	// redéclaration des données globales utilisées dans la fonction
	global $doc;
	global $lesSalles, $nbReponses;
		
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $nbReponses . " salles disponibles en réservation.");
	$elt_data->appendChild($elt_reponse);
	
	$elt_donnees = $doc->createElement('donnees');
	$elt_data->appendChild($elt_donnees);
	
	// traitement des salles
	foreach ($lesSalles as $uneSalle)
	{
		// crée un élément vide 'salle'
		$elt_salle = $doc->createElement('salle');
		// place l'élément 'salle' dans l'élément 'donnees'
		$elt_donnees->appendChild($elt_salle);

		// crée les éléments enfants de l'élément 'reservation'
		$elt_id         = $doc->createElement('id', utf8_encode($uneSalle->getId()));
		$elt_salle->appendChild($elt_id);
		$elt_room_name  = $doc->createElement('room_name', utf8_encode($uneSalle->getRoom_name()));
		$elt_salle->appendChild($elt_room_name);
		$elt_capacity  = $doc->createElement('capacity', utf8_encode($uneSalle->getCapacity()));
		$elt_salle->appendChild($elt_capacity);
		$elt_area_name = $doc->createElement('area_name', utf8_encode($uneSalle->getAeraName()));
		$elt_salle->appendChild($elt_area_name);
		$elt_area_admin_email   = $doc->createElement('area_admin_email', utf8_encode($uneSalle->getAeraAdminEmail()));
		$elt_salle->appendChild($elt_area_admin_email);
	}
	return;
}
?>
