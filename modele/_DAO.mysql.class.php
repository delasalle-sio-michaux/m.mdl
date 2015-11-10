<?php
// -------------------------------------------------------------------------------------------------------------------------
//                                                 DAO : Data Access Object
//                   Cette classe fournit des méthodes d'accès à la bdd mrbs (projet Réservations M2L)
//                                             Elle utilise les fonctions mysql
//                       Auteur : JM Cartron                       Dernière modification : 12/10/2015
// -------------------------------------------------------------------------------------------------------------------------

// liste des méthodes de cette classe (dans l'ordre d'apparition dans la classe) :

// __construct                   : le constructeur crée la connexion $cnx à la base de données
// __destruct                    : le destructeur ferme la connexion $cnx à la base de données
// getNiveauUtilisateur          : fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
// genererUnDigicode             : génération aléatoire d'un digicode de 6 caractères hexadécimaux
// creerLesDigicodesManquants    : mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
// listeReservations             : fournit la liste des réservations à venir d'un utilisateur ($nomUser)
// existeReservation             : fournit true si la réservation ($idReservation) existe, false sinon
// estLeCreateur                 : teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
// getReservation                : fournit un objet Reservation à partir de son identifiant $idReservation
// getUtilisateur                : fournit un objet Utilisateur à partir de son nom $nomUser
// confirmerReservation          : enregistre la confirmation de réservation dans la bdd
// annulerReservation            : enregistre l'annulation de réservation dans la bdd
// existeUtilisateur             : fournit true si l'utilisateur ($nomUser) existe, false sinon
// modifierMdpUser               : enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir hashé en MD5
// envoyerMdp                    : envoie un mail à l'utilisateur avec son nouveau mot de passe
// testerDigicodeSalle           : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation
// testerDigicodeBatiment        : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
// enregistrerUtilisateur        : enregistre l'utilisateur dans la bdd
// aPasseDesReservations         : recherche si l'utilisateur ($name) a passé des réservations à venir
// supprimerUtilisateur          : supprime l'utilisateur dans la bdd

// listeSalles                   : fournit la liste des salles disponibles à la réservation

// certaines méthodes nécessitent les fichiers Reservation.class.php, Utilisateur.class.php et Outils.class.php
include_once ('Utilisateur.class.php');
include_once ('Reservation.class.php');
include_once ('Salle.class.php');
include_once ('Outils.class.php');

// inclusion des paramètres de l'application
include_once ('parametres.free.php');
//include_once ('parametres.localhost.php');

// début de la classe DAO (Data Access Object)
class DAO
{
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Membres privés de la classe ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
		
	private $cnx;				// la connexion à la base de données
	
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Constructeur et destructeur ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
	public function __construct() {
		global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
		try
		{	// Phase 1 : connexion de l'application cliente au SGBD MySQL
			$this->cnx = mysql_connect ($PARAM_HOTE , $PARAM_USER , $PARAM_PWD);
			// Phase 2 : sélection de la base de données mrbs :
			mysql_select_db ($PARAM_BDD, $this->cnx);
			return true;
		}
		catch (Exception $ex)
		{	echo ("Echec de la connexion a la base de donnees <br>");
			echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
			echo ("PARAM_HOTE = " . $PARAM_HOTE);
			return false;
		}
	}
	
	public function __destruct() {
		// ferme la connexion à MySQL :
		mysql_close ($this->cnx);
	}

	// ------------------------------------------------------------------------------------------------------
	// -------------------------------------- Méthodes d'instances ------------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	// fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
	// renvoie "utilisateur" ou "administrateur" si authentification correcte, "inconnu" sinon
	// modifié par Jim le 24/9/2015
	public function  getNiveauUtilisateur($nomUser, $mdpUser)
	{	// préparation de la requete de recherche
		$req = "Select level from mrbs_users where name = '" . $nomUser . "' and password = '" . md5($mdpUser) . "' and level > 0";
		//echo  $req;		// on peut afficher le texte de la requete en mise au point, et le masquer ensuite !
		
		// extraction des données et comptage des réponses (0 ou une seule ligne de réponse)
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
	
		$reponse = "inconnu";
		if ($nbReponses > 0)
		{	$ligne = mysql_fetch_array ($jdd);
			$level = $ligne['level'];
			if ($level == "1") $reponse = "utilisateur";
			if ($level == "2") $reponse = "administrateur";	
		}
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);	
		
		return $reponse;
	}

	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
	// modifié par Jim le 5/5/2015
	public function genererUnDigicode()
	{   $caracteresUtilisables = "0123456789ABCDEF";
		$digicode = "";
		// on ajoute 6 caractères
		for ($i = 1 ; $i <= 6 ; $i++)
		{   // on tire au hasard un caractère (position aléatoire entre 0 et le nombre de caractères - 1)
			$position = rand (0, strlen($caracteresUtilisables)-1);
			// on récupère le caracère correspondant à la position dans $caracteresUtilisables
			$unCaractere = substr ($caracteresUtilisables, $position, 1);
			// on ajoute ce caractère au digicode
			$digicode = $digicode . $unCaractere;
		}
		// fourniture de la réponse
		return $digicode;
	}	

	// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
	// cette fonction peut dépanner en cas d'absence des triggers chargés de créer les digicodes
	// modifié par Jim le 12/10/2015
	public function creerLesDigicodesManquants()
	{	// préparation de la requete de recherche des réservations sans digicode
		$req1 = "Select id from mrbs_entry where id not in (select id from mrbs_entry_digicode)";

		// extraction des données
		$jdd = mysql_query ($req1, $this->cnx);
		// extrait une ligne du jeu de données :
		$uneLigne = mysql_fetch_array ($jdd);
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
			$digicode = $this->genererUnDigicode();
			// préparation de la requete d'insertion
			$req2 = "insert into mrbs_entry_digicode (id, digicode) values (";
			$req2 = $req2 . $uneLigne["id"] . ", ";
			$req2 = $req2 . "'" . $digicode . "')";
			// echo $req2;
			$ok = mysql_query ($req2, $this->cnx);		
			
			// extrait la ligne suivante
			$uneLigne = mysql_fetch_array ($jdd);
		}
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);	
		return;
	}	
/*
	// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
	// cette fonction peut dépanner en cas d'absence des triggers chargés de créer les digicodes
	// modifié par Jim le 23/9/2015
	public function creerLesDigicodesManquants()
	{	// préparation de la requete de recherche des réservations sans digicode
		$req1 = "Select id from mrbs_entry where id not in (select id from mrbs_entry_digicode)";

		// extraction des données
		$jdd = mysql_query ($req, $this->cnx1);
		// extrait une ligne du jeu de données :
		$uneLigne = mysql_fetch_array ($jdd);
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
			$digicode = $this->genererUnDigicode();
			// préparation de la requete d'insertion
			$req2 = "insert into mrbs_entry_digicode (id, digicode, dateCreation) values (";
			$req2 = $req2 . $uneLigne["id"] . ", ";
			$req2 = $req2 . "'" . $digicode . "', ";
			$req2 = $req2 . "'" . date('Y-m-d H:i:s', time()) . "')";
			// echo $req2;
			$ok = mysql_query ($req, $this->cnx2);
				
			// extrait la ligne suivante
			$uneLigne = mysql_fetch_array ($jdd);
		}
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);
		return;
	}
*/	
	// fournit la liste des réservations à venir d'un utilisateur ($nomUser)
	// le résultat est fourni sous forme d'une collection d'objets Reservation
	// modifié par Jim le 12/10/2015
	public function listeReservations($nomUser)
	{	// préparation de la requete de recherche
		$req = "Select mrbs_entry.id, timestamp, start_time, end_time, room_name, status, digicode";
		$req = $req . " from mrbs_entry, mrbs_room, mrbs_entry_digicode";
		$req = $req . " where mrbs_entry.room_id = mrbs_room.id";
		$req = $req . " and mrbs_entry.id = mrbs_entry_digicode.id";
		$req = $req . " and create_by = '" . $nomUser . "'";
		$req = $req . " and start_time > " . time();
		$req = $req . " order by start_time, room_name";
		// echo $req;
		
		// extraction des données
		$jdd = mysql_query ($req, $this->cnx);
	 	$uneLigne = mysql_fetch_array ($jdd);
		
		// construction d'une collection d'objets Reservation
		$lesReservations = array();
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// création d'un objet Reservation
			$unId = utf8_encode($uneLigne['id']);
			$unTimeStamp = utf8_encode($uneLigne['timestamp']);
			$unStartTime = utf8_encode($uneLigne['start_time']);
			$unEndTime = utf8_encode($uneLigne['end_time']);
			$unRoomName = utf8_encode($uneLigne['room_name']);
			$unStatus = utf8_encode($uneLigne['status']);
			$unDigicode = utf8_encode($uneLigne['digicode']);
			
			$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
			// ajout de la réservation à la collection
			$lesReservations[] = $uneReservation;
			// extrait la ligne suivante
	 		$uneLigne = mysql_fetch_array ($jdd);
		}
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);
		// fourniture de la collection
		return $lesReservations;
	}

	// fournit true si la réservation ($idReservation) existe, false sinon
	// modifié par Jim le 25/9/2015
	public function existeReservation($idReservation)
	{	// préparation de la requete de recherche
		$req = "Select id from mrbs_entry where id = " . $idReservation ;
		//echo  $req;		// on peut afficher le texte de la requete en mise au point, et le masquer ensuite !
		
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}
	
	// teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
	// renvoie true si l'utilisateur est bien le créateur, false sinon
	// modifié par Jim le 25/9/2015
	public function estLeCreateur($nomUser, $idReservation)
	{	// préparation de la requete de recherche
		$req = "Select id from mrbs_entry where create_by = '" . $nomUser . "' and id = " . $idReservation ;
		//echo  $req;		// on peut afficher le texte de la requete en mise au point, et le masquer ensuite !
		
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}
	
	// fournit un objet Reservation à partir de son identifiant
	// fournit la valeur null si l'identifiant n'existe pas
	// modifié par Jim le 25/9/2015
	public function getReservation($idReservation)
	{	// préparation de la requete de recherche
		$req = "Select mrbs_entry.id, timestamp, start_time, end_time, room_name, status, digicode ";
		$req = $req . " from mrbs_entry, mrbs_entry_digicode, mrbs_room ";
		$req = $req . " where mrbs_entry.room_id = mrbs_room.id ";
		$req = $req . " and mrbs_entry.id = mrbs_entry_digicode.id";
		$req = $req . " and mrbs_entry.id = " . $idReservation;
		// extraction des données
		$jdd = mysql_query ($req, $this->cnx);
	 	$uneLigne = mysql_fetch_array ($jdd);
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);
		
		// traitement de la réponse
		if ( ! $uneLigne)
			return null;
		else
		{	// création d'un objet Reservation
			$unId = utf8_encode($uneLigne['id']);
			$unTimeStamp = utf8_encode($uneLigne['timestamp']);
			$unStartTime = utf8_encode($uneLigne['start_time']);
			$unEndTime = utf8_encode($uneLigne['end_time']);
			$unRoomName = utf8_encode($uneLigne['room_name']);
			$unStatus = utf8_encode($uneLigne['status']);
			$unDigicode = utf8_encode($uneLigne['digicode']);
						
			$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
			return $uneReservation;
		}
	}

	// fournit un objet Utilisateur à partir de son nom ($nomUser)
	// fournit la valeur null si le nom n'existe pas
	// modifié par Jim le 28/9/2015
	public function getUtilisateur($nomUser)
	{	// préparation de la requete de recherche
		$req = "Select * from mrbs_users where name = '" . $nomUser . "'";
		// extraction des données
		$jdd = mysql_query ($req, $this->cnx);
	 	$uneLigne = mysql_fetch_array ($jdd);
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);
		
		// traitement de la réponse
		if ( ! $uneLigne)
			return null;
		else
		{	// création d'un objet Utilisateur
			$unId = utf8_encode($uneLigne['id']);
			$unLevel = utf8_encode($uneLigne['level']);
			$unName = utf8_encode($uneLigne['name']);
			$unPassword = utf8_encode($uneLigne['password']);
			$unEmail = utf8_encode($uneLigne['email']);
				
			$unUtilisateur = new Utilisateur($unId, $unLevel, $unName, $unPassword, $unEmail);
			return $unUtilisateur;
		}
	}
	
	// enregistre la confirmation de réservation dans la bdd
	// modifié par Jim le 28/9/2015
	public function confirmerReservation($idReservation)
	{	// préparation de la requete
		$req = "update mrbs_entry set status = 0 where id = " . $idReservation ;
		// exécution de la requete
		$ok = mysql_query ($req, $this->cnx);
		return $ok;
	}
	
	// enregistre l'annulation de réservation dans la bdd
	// modifié par Jim le 28/9/2015
	public function annulerReservation($idReservation)
	{	// préparation de la requete
		$req = "delete from mrbs_entry where id = " . $idReservation ;
		// exécution de la requete
		$ok = mysql_query ($req, $this->cnx);
		return $ok;
	}
	
	// fournit true si l'utilisateur ($nomUser) existe, false sinon
	// modifié par Jim le 28/9/2015
	public function existeUtilisateur($nomUser)
	{	// préparation de la requete de recherche
		$req = "Select name from mrbs_users where name = '" . $nomUser . "'";
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}

	// enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir hashé en MD5
	// modifié par Jim le 28/9/2015
	public function modifierMdpUser($nomUser, $nouveauMdp)
	{	// préparation de la requete
		$req = "update mrbs_users set password = '" . md5($nouveauMdp) . "' where name = '" . $nomUser . "'";
		// exécution de la requete
		$ok = mysql_query ($req, $this->cnx);
		return $ok;
	}	

	// envoie un mail à l'utilisateur avec son nouveau mot de passe
	// retourne true si envoi correct, false en cas de problème d'envoi
	// modifié par Jim le 28/9/2015
	public function envoyerMdp($nomUser, $nouveauMdp)
	{	global $ADR_MAIL_EMETTEUR;
		// si l'adresse n'est pas dans la table mrbs_users :
		if ( ! $this->existeUtilisateur($nomUser) ) return false;

		// recherche de l'adresse mail
		$adrMail = $this->getUtilisateur($nomUser)->getEmail();
		
		// envoie un mail à l'utilisateur avec son nouveau mot de passe 
		$sujet = "Modification de votre mot de passe d'accès au service Réservations M2L";
		$message = "Votre mot de passe d'accès au service Réservations M2L a été modifié.\n\n";
		$message .= "Votre nouveau mot de passe est : " . $nouveauMdp;
		$ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
		return $ok;
	}

	// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation
	// de la salle indiquée ($idSalle) pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par Jim le 28/9/2015
	public function testerDigicodeSalle($idSalle, $digicodeSaisi)
	{	global $DELAI_DIGICODE;
		// préparation de la requete de recherche
		$req = "Select room_id, digicode";
		$req = $req . " from mrbs_entry, mrbs_entry_digicode";
		$req = $req . " where mrbs_entry.id = mrbs_entry_digicode.id";
		$req = $req . " and room_id = " . $idSalle;
		$req = $req . " and digicode = '" . $digicodeSaisi . "'";
		$req = $req . " and start_time - " . $DELAI_DIGICODE . " < " . time();
		$req = $req . " and end_time + " . $DELAI_DIGICODE . " > " . time();
		// echo $req;
		
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return "0";
		else
			return "1";
	}
	
	// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
	// pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par Jim le 28/9/2015
	public function testerDigicodeBatiment($digicodeSaisi)
	{	global $DELAI_DIGICODE;
		// préparation de la requete de recherche
		$req = "Select room_id, digicode";
		$req = $req . " from mrbs_entry, mrbs_entry_digicode";
		$req = $req . " where mrbs_entry.id = mrbs_entry_digicode.id";
		$req = $req . " and digicode = '" . $digicodeSaisi . "'";
		$req = $req . " and start_time - " . $DELAI_DIGICODE . " < " . time();
		$req = $req . " and end_time + " . $DELAI_DIGICODE . " > " . time();
		// echo $req;
		
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return "0";
		else
			return "1";
	}

	// enregistre l'utilisateur dans la bdd
	// modifié par Jim le 28/9/2015
	public function enregistrerUtilisateur($name, $level, $password, $email)
	{	// préparation de la requete
		$req = "insert into mrbs_users (level, name, password, email) values (";
		$req = $req . utf8_decode($level) . ", ";
		$req = $req . "'" . utf8_decode($name) . "', ";
		$req = $req . "'" . utf8_decode(md5($password)) . "', ";
		$req = $req . "'" . utf8_decode($email) . "')";
		// echo ($req);
		
		// exécution de la requete
		$ok = mysql_query ($req, $this->cnx);
		return $ok;
	}

	// recherche si un utilisateur a passé des réservations à venir
	// modifié par Jim le 28/9/2015
	public function aPasseDesReservations($name)
	{	// préparation de la requete de recherche
		$req = "Select id";
		$req = $req . " from mrbs_entry";
		$req = $req . " where create_by = '" . $name . "'";
		$req = $req . " and start_time > " . time();
		// echo $req;
		
		// extraction des données et comptage des réponses
		$jdd = mysql_query ($req, $this->cnx);
		$nbReponses = mysql_num_rows ($jdd);
		// libère les ressources du jeu de données :
		mysql_free_result ($jdd);
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}
	
	// supprime l'utilisateur dans la bdd
	// modifié par Jim le 28/9/2015
	public function supprimerUtilisateur($name)
	{	// préparation de la requete
		$req = "delete from mrbs_users where name = '" . $name . "'" ;
		// exécution de la requete
		$ok = mysql_query ($req, $this->cnx);
		return $ok;
	}	
	
	// fournit la liste des salles disponibles à la réservation
	// le résultat est fourni sous forme d'une collection d'objets Salle
	// modifié par Jim le 30/9/2015
	function listeSalles()
	{	// préparation de la requete de recherche
		$req = "Select mrbs_room.id, mrbs_room.room_name, mrbs_room.capacity, mrbs_area.area_name, mrbs_area.area_admin_email";
		$req = $req . " from mrbs_room, mrbs_area";
		$req = $req . " where mrbs_room.area_id = mrbs_area.id";
		$req = $req . " order by mrbs_area.area_name, mrbs_room.room_name";
		// extraction des données
		$jdd = mysql_query ($req, $this->cnx);
	 	$uneLigne = mysql_fetch_array ($jdd);
		
		// construction d'une collection d'objets Salle
		$lesSalles = array();
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// création d'un objet Salle
			$unId = utf8_encode($uneLigne['id']);
			$unRoomName = utf8_encode($uneLigne['room_name']);
			$unCapacity = utf8_encode($uneLigne['capacity']);
			$unAreaName = utf8_encode($uneLigne['area_name']);
			$unAeraAdminEmail = utf8_encode($uneLigne['area_admin_email']);
				
			$uneSalle = new Salle($unId, $unRoomName, $unCapacity, $unAreaName, $unAeraAdminEmail);
			// ajout de la réservation à la collection
			$lesSalles[] = $uneSalle;
			// extrait la ligne suivante
	 	$uneLigne = mysql_fetch_array ($jdd);
		}
		// libère les ressources du jeu de données
		mysql_free_result ($jdd);
		// fourniture de la collection
		return $lesSalles;
	}
	
} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!