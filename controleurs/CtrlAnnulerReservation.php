<?php

	// inclusion de la classe Outils
	include_once ('modele/Outils.class.php');
	// inclusion des paramètres de l'application
	include_once ('modele/parametres.localhost.php');
	include_once ('modele/DAO.class.php');
	
	
	if(!isset($_POST["saisieRes"])){
		$msgFooter = "Annuler une réservation";
		$themeFooter = $themeNormal;
		include_once('vues/VueAnnulerReservation.php');
	}
	else{
		if(empty($_POST["saisieRes"])){
			$msgFooter = "Données incomplètes ou incorrectes !";
			$themeFooter = $themeProbleme;
			include_once('vues/VueAnnulerReservation.php');
		}
		else{
			$dao = new DAO();
			if($dao->existeReservation($_POST["saisieRes"]) == false){
				$msgFooter = "Numéro de réservation inexistant !";
				$themeFooter = $themeProbleme;
				include_once('vues/VueAnnulerReservation.php');
			}
			else{
				$laReservation = $dao->getReservation($_POST["saisieRes"]);
				if ($dao->estLeCreateur($nom, $_POST["saisieRes"]) == false){
					$msgFooter = "Vous n'êtes pas l'auteur de cette réservation !";
					$themeFooter = $themeProbleme;
					include_once('vues/VueAnnulerReservation.php');
				}
				else{
					if(time() > $laReservation->getEnd_time()){
						$msgFooter = "Cette réservation est déjà passée !";
						$themeFooter = $themeProbleme;
						include_once('vues/VueAnnulerReservation.php');
					}
					else{
						$themeFooter = $themeNormal;
						$email = $dao->getUtilisateur($nom)->getEmail();
						// envoie un mail de confirmation de l'enregistrement
						$sujet = "Annulation d'une réservation";
						$message = "Votre réservation ".$POST_["saisieRes"]." a bien été annulée";
						
						$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
						if ( $ok )
							$msgFooter = "Annulation effectué.<br>Vous allez recevoir un mail de confirmation.";
						else
							$msgFooter = "Annulation effectué.<br>L'envoi du mail de confirmation a rencontré un problème. ";
							
						$dao->annulerReservation($POST_["saisieRes"]);
						include_once('vues/VueAnnulerReservation.php');
					}
				}
			}
		}	
	}
	
	
	// ferme la connexion à MySQL
	unset($dao);

?>