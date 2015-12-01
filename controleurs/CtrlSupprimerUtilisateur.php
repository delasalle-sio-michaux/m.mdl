<?php

	// inclusion de la classe Outils
	include_once ('modele/Outils.class.php');
	// inclusion des paramètres de l'application
	include_once ('modele/parametres.localhost.php');
	include_once ('modele/DAO.class.php');
	
	
	if(!isset($_POST["saisieRes"])){
		$msgFooter = "Supprimer un utilisateur";
		$themeFooter = $themeNormal;
		include_once('vues/VueSupprimerUtilisateur.php');
	}
	else{
		if(empty($_POST["saisieRes"])){
			$msgFooter = "Données incomplètes ou incorrectes !";
			$themeFooter = $themeProbleme;
			include_once('vues/VueSupprimerUtilisateur.php');
		}
		else{
			$dao = new DAO();
			if($dao->existeUtilisateur($_POST["saisieRes"]) == false){
				$msgFooter = "Nom d'utilisateur inexistant";
				$themeFooter = $themeProbleme;
				include_once('vues/VueSupprimerUtilisateur.php');
			}
			else{
				$unUser = $dao->getUtilisateur($_POST["saisieRes"]);
				if ($dao->aPasseDesReservations($_POST["saisieRes"]) == true){
					$msgFooter = "Cet utilisateur a des réservations à venir !";
					$themeFooter = $themeProbleme;
					include_once('vues/VueSupprimerUtilisateur.php');
				}
				else{
					$themeFooter = $themeNormal;
					$email = $dao->getUtilisateur($_POST["saisieRes"])->getEmail();
					// envoie un mail de confirmation de la suppression
					$sujet = "Suppression de votre compte";
					$message = "Votre compte ".$POST_["saisieRes"]." de M2L a bien été supprimé.";
						
					$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
					if ( $ok )
						$msgFooter = "Suppression effectuée.<br>Vous allez recevoir un mail de confirmation.";
					else
						$msgFooter = "Suppression effectuée.<br>L'envoi du mail de confirmation a rencontré un problème. ";
					$dao->supprimerUtilisateur($_POST["saisieRes"]);
					include_once('vues/VueSupprimerUtilisateur.php');
				}
			}
		}	
	}
	
	
	// ferme la connexion à MySQL
	unset($dao);

?>