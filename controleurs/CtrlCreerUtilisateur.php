<?php
// connexion du serveur web à la base MySQL ("include_once" peut être remplacé par "require_once")
	include_once ('modele/Outils.class.php');
	// inclusion des paramètres de l'application
	include_once ('modele/parametres.localhost.php');
	include_once ('modele/DAO.class.php');
	$dao = new DAO();
// Contrôle de la présence des paramètres
if ( !isset($_POST['saisieUser']) || !isset($_POST['saisieUserMail'])|| Outils::estUneAdrMailValide ($_POST['saisieUserMail']) == false )
{	$msgFooter = "Créer un utilisateur";
	$themeFooter = $themeNormal;
	include_once ('vues/VueCreerUtilisateur.php');
}
else
{
	if(empty($_POST["saisieUser"]) || empty($_POST['saisieUserMail']))
	{
		$msgFooter = "Données incomplètes ou incorrectes";
		$themeFooter = $themeProbleme;
		include_once ('vues/VueCreerUtilisateur.php');
	}
	else
	{
			if ( $dao->existeUtilisateur($_POST['saisieUser']) )
			{	$msgFooter = "Nom d'utilisateur déjà existant";
				$themeFooter = $themeProbleme;
				include_once ('vues/VueCreerUtilisateur.php');
			}
			else
			{	// création d'un mot de passe aléatoire de 8 caractères
				$password = Outils::creerMdp();
				// enregistre l'utilisateur dans la bdd
				$ok = $dao->enregistrerUtilisateur($_POST['saisieUser'], $_POST['radioNiveau'], $mdp, $email);
				if ( ! $ok )
				{
					$msgFooter = "Problème lors de l'enregistrement";
					$themeFooter = $themeProbleme;
					include_once ('vues/VueCreerUtilisateur.php');
				}
				else
				{
					global $ADR_MAIL_EMETTEUR;
					// envoie un mail de confirmation de l'enregistrement
					$sujet = "Création de votre compte dans le système de réservation de M2L";
					$message = "L'administrateur du système de réservations de la M2L vient de vous créer un compte utilisateur.\n\n";
					$message .= "Les données enregistrées sont :\n\n";
					$message .= "Votre nom : " . $nom . "\n";
					$message .= "Votre mot de passe : " . $mdp . " (nous vous conseillons de le changer lors de la première connexion)\n";
					$message .= "Votre niveau d'accès (0 : invité    1 : utilisateur    2 : administrateur) : " . $_POST['radioNiveau'] . "\n";
					
					$ok = Outils::envoyerMail ($_POST['saisieUserMail'], $sujet, $message, $ADR_MAIL_EMETTEUR);
					if ( $ok ){
						$msgFooter = " Enregistrement effectué.<br>Un mail va être envoyé à l'utilisateur !";
						$themeFooter = $themeNormal;
						include_once ('vues/VueCreerUtilisateur.php');
					}
					else
					{
						$msgFooter = "Enregistrement effectué.<br> L'envoi du mail à l'utilisateur a rencontré un problème ! ";
						$themeFooter = $themeNormal;
						include_once ('vues/VueCreerUtilisateur.php');
					return;
					}
				}
			}
		}
		// ferme la connexion à MySQL :
		unset($dao);
	}
?>