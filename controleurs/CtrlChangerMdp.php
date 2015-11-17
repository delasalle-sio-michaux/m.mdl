<?php
// inclusion de la classe Outils
	include_once ('modele/Outils.class.php');
	// inclusion des paramètres de l'application
	include_once ('modele/parametres.localhost.php');
	include_once ('modele/DAO.class.php');
	$dao = new DAO();



if (!isset($_POST['saisieMdp1']) || !isset($_POST['saisieMdp2']))
	{
		$msgFooter = "Changer mon mot de passe";
		$themeFooter = $themeNormal;
		include_once('vues/VueChangementMdp.php');
	}
else
	{
		if (empty($_POST['saisieMdp1']) || empty($_POST['saisieMdp2']))
			{
				$msgFooter = "Données incomplètes ou incorrectes";
				$themeFooter = $themeProbleme;
				include_once('vues/VueChangementMdp.php');
			}
			else 
			{
				if ($_POST['saisieMdp1'] != $_POST['saisieMdp2']) 
				{
					$msgFooter = "Le nouveau mot de passe et <br>sa confirmation sont différents !";
					$themeFooter = $themeProbleme;
					include_once('vues/VueChangementMdp.php');
				}
				else 
				{
					$dao->modifierMdpUser($nom, $_POST['saisieMdp1']);
				
					$email = $dao->getUtilisateur($nom)->getEmail();
				
					// envoie un mail de confirmation de l'enregistrement
					$sujet = "Changement de mot de passe M2L";
					$message = "Vous venez de changer votre mot de passe \n\n";
					$message .= "Votre nouveau mot de passe est : " . $_POST['saisieMdp1'] . "\n";
				
					$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
					if ( $ok )
					{
						$msgFooter = "Enregistrement effectué ; <br> Vous allez recevoir un mail de confirmation.";
						$themeFooter = $themeNormal;
						include_once('vues/VueChangementMdp.php');
					}
					else
					{
						$msgFooter = "Enregistrement effectué ; <br> l'envoi du mail à l'utilisateur a rencontré un problème.";
						$themeFooter = $themeNormal;
						include_once('vues/VueChangementMdp.php');
					
					return;
					}
				}
			}
	}


/*
// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


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

function TraitementNormal($nouveauMdp)
{
	// redéclaration des données globales utilisées dans la fonction
	global $doc;
	global $dao;
	global $nom, $numRes, $mdp, $email ;
	global $ADR_MAIL_EMETTEUR;

	$email = $dao->getUtilisateur($nom)->getEmail();

	// envoie un mail de confirmation de l'enregistrement
	$sujet = "Changement de mot de passe M2L";
	$message = "Vous venez de changer votre mot de passe \n\n";
	$message .= "Votre nouveau mot de passe est : " . $nouveauMdp . "\n";

	$ok = Outils::envoyerMail ($email, $sujet, $message, $ADR_MAIL_EMETTEUR);
	if ( $ok )
		$msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation.";
	else
		$msg = "Enregistrement non effectué ; l'envoi du mail à l'utilisateur a rencontré un problème.";

	// crée l'élément 'data' à la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}*/

?>