<?php
// Service web du projet Réservations M2L
// Ecrit le 21/5/2015 par Jim

// ce service web permet à un utilisateur de confirmer une réservation provisoire
// et fournit un compte-rendu d'exécution

// Le service web doit être appelé avec 3 paramètres obligatoires : nom, mdp, numreservation
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConfirmerReservation.php?nom=zenelsy&mdp=passe&numreservation=5
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConfirmerReservation.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer
global $dao, $nom, $mdp, $numReservation;
global $ADR_MAIL_EMETTEUR;

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
$elt_commentaire = $doc->createComment('Service web ConfirmerReservation - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);
	
// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["numreservation"]) == true)  $numReservation = "";  else   $numReservation = $_GET ["numreservation"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" && $numReservation == "" )
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["numreservation"]) == true)  $numReservation = "";  else   $numReservation = $_POST ["numreservation"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" || $numReservation == "" )
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('../modele/DAO.class.php');
	$dao = new DAO();
	
	if ( $dao->getNiveauUtilisateur($nom, $mdp) == "inconnu" )
	{	TraitementAnormal("Erreur : authentification incorrecte.");
	}
	else
	{	if ( ! $dao->existeReservation($numReservation) )
		{	TraitementAnormal("Erreur : numéro de réservation inexistant.");
		}
		else
		{	if ( ! $dao->estLeCreateur($nom, $numReservation) )
			{	TraitementAnormal("Erreur : vous n'êtes pas l'auteur de cette réservation.");
			}
			else
			{	if ( $dao->getReservation($numReservation)->getStatus() == 0 )
				{	TraitementAnormal("Erreur : cette réservation est déjà confirmée.");
				}
				else
				{	if ( $dao->getReservation($numReservation)->getStart_time() < time() )
					{	TraitementAnormal("Erreur : cette réservation est déjà passée.");
					}
					else 
					{	TraitementNormal();
					}
				}
			}
		}
	}
	// ferme la connexion à MySQL :
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
	global $dao, $nom, $mdp, $numReservation;
	global $ADR_MAIL_EMETTEUR;
	
	// enregistre la réservation dans l'état confirmé
	$dao->confirmerReservation($numReservation);

	// recherche de l'adresse mail
	$adrMail = $dao->getUtilisateur($nom)->getEmail();
	// recherche du digicode
	$digicode = $dao->getReservation($numReservation)->getDigicode();
	
	// envoie un mail de confirmation de l'enregistrement
	$sujet = "Confirmation de réservation";
	$message = "Nous avons bien enregistré la confirmation de la réservation N° " . $numReservation . "\n\n";
	$message .= "Le digicode d'accès à la salle est : " . $digicode . "\n";
	$message .= "Il est valable 1 heure avant la réservation, et pendant 1 heure après la réservation.";
	$ok = Outils::envoyerMail($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);

	if ( $ok )
		$msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation.";
	else
		$msg = "Enregistrement effectué ; l'envoi du mail de confirmation a rencontré un problème.";
		
	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse); 
	return;
}
?>
