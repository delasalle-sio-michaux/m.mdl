<?php
include_once('modele/DAO.class.php');
$dao = new DAO();


// Contrôle de la présence des paramètres
if ( !isset($_POST["saisieRes"]))
{	$msgFooter = "Confirmer une réservation";
	$themeFooter = $themeNormal;
	include_once('vues/VueConfirmer.php');
}
else
{
	if (empty($_POST["saisieRes"]))
	{
		$msgFooter = "Données incomplètes ou incorrectes";
		$themeFooter = $themeProbleme;
		include_once('vues/VueConfirmer.php');
	}
	else {
		if ($dao->existeReservation($res) == false)
		{
			$msgFooter = "Numéro de réservation inexistant !";
			$themeFooter = $themeProbleme;
			include_once('vues/VueConfirmer.php');
		}
		else
		{
			if ($dao->estLeCreateur($nom, $_POST["saisieRes"]) == false)
			{
				$msgFooter = "Vous n'êtes pas l'auteur de cette réservation !";
				$themeFooter = $themeProbleme;
				include_once('vues/VueConfirmer.php');
			}
			else
			{
				$laReservation = $dao->getReservation($_POST["saisieRes"]);
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
						$msgFooter = "Cette réservation est déjà passée !";
						$themeFooter = $themeProbleme;
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
	}
	// ferme la connexion à MySQL
	unset($dao);
}
?>
