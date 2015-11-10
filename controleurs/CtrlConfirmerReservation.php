<?php
// Service web du projet Réservations M2L
// Ecrit le 22/09/2015 par Roger Alban

// Ce service web permet à un utilisateur autorisé de confirmer une réservation provisoire
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 3 paramètres : nom, mdp, numReservation
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ConsulterReservations.php?nom=zenelsy&mdp=passe
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConsulterReservations.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions

// inclusion de la classe Outils
include_once ('modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('modele/include.parametres.php');


// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( empty ($_POST ["saisieRes"]) == true)  $res = "";  else   $res = $_POST ["saisieRes"];

// Contrôle de la présence des paramètres
if ( $res == '' )
{	$msgFooter = "Données incomplètes ou incorrectes !";
	$themeFooter = $themeProbleme;
	include_once('vues/VueConfirmer.php');
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('modele/DAO.class.php');
	$dao = new DAO();
	// Controle de la présence de l'utilisateur
		if ($dao->existeReservation($res) == false)
		{
			$msgFooter = "Numéro de réservation inexistant !";
			$themeFooter = $themeProbleme;
			include_once('vues/VueConfirmer.php');
		}
		else
		{
			if ($dao->estLeCreateur($nom, $res) == false)
			{
				$msgFooter = "Vous n'êtes pas l'auteur de cette réservation !";
				$themeFooter = $themeProbleme;
				include_once('vues/VueConfirmer.php');
			}
			else
			{
				$laReservation = $dao->getReservation($res);
				$statut = $laReservation->getStatus();
				if ($statut == 0)
				{
					$msgFooter = "Cette réservation est déjà confirmée !";
					$themeFooter = $themeProbleme;
					include_once('vues/VueConfirmer.php');
				}
				else
				{
					if( time() > $laReservation->getEnd_time() )
					{
						$themeFooter = $themeProbleme;
						$msgFooter = "Cette réservation est déjà passée !";
						include_once('vues/VueConfirmer.php');
					}
					else
						$themeFooter = $themeNormal;
						$email = $dao->getUtilisateur($nom)->getEmail();
						// envoie un mail de confirmation de l'enregistrement
						$sujet = "Confirmation d'une réservation";
						$message = "Votre réservation ".$res." a bien été confirmée";
						
						$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
						if ( $ok )
							$msgFooter = "Enregistrement effectué.<br>Vous allez recevoir un mail de confirmation.";
						else
							$msgFooter = "Enregistrement effectué.<br>L'envoi du mail de confirmation a rencontré un problème. ";
						
						$dao->confirmerReservation($reservation);
						include_once('vues/VueConfirmer.php');
				}
			}
		}

	// ferme la connexion à MySQL
	unset($dao);
}
