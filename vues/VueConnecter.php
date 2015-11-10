<?php
	// Projet Réservations M2L - version web mobile
	// Fonction de la vue VueConnecter.php : visualiser la vue de connexion
	// Ecrit le 12/10/2015 par Jim
?>
<!doctype html>
<html>
	<head>	
		<?php include_once ('vues/head.php'); ?>
	</head> 
	<body>
		<div data-role="page">
			<div data-role="header" data-theme="<?php echo $themeNormal; ?>">
				<h4>M2L-GRR</h4>
			</div>
			<div data-role="content">
				<h4 style="text-align: center; margin-top: 0px; margin-bottom: 0px;">M'identifier</h4>
				<form name="form1" id="form1" action="index.php?action=Connecter" method="post">
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="nom">Utilisateur :</label>
						<input type="text" name="nom" id="nom" data-mini="true" placeholder="Entrez votre nom" value="<?php echo $nom; ?>" >

						<label for="mdp">Mot de passe :</label>
						<input type="<?php if ($afficherMdp == 'off') echo 'password'; else echo 'text'; ?>" name="mdp" id="mdp" data-mini="true" placeholder="Entrez votre mot de passe" value="<?php echo $mdp; ?>" >
					</div>														
					<div data-role="fieldcontain" data-type="horizontal" data-mini="true" class="ui-hide-label">
						<label for="afficherMdp">Afficher le mot de passe</label>
						<input type="checkbox" name="afficherMdp" id="afficherMdp" data-mini="true" <?php if ($afficherMdp == 'on') echo 'checked'; ?>>
					</div>
					<div data-role="fieldcontain" data-mini="true" style="margin-top: 0px; margin-bottom: 0px;">
						<p style="margin-top: 0px; margin-bottom: 0px;">
							<input type="submit" name="connecter" id="connecter" data-mini="true" value="Me connecter">
						</p>
					</div>
				</form>
				
				<p style="margin-top: 0px; margin-bottom: 20px;">
					<a href="index.php?action=DemanderMdp" data-mini="true" data-role="button">J'ai oublié mon mot de passe</a>
				</p>
				
				<?php if ($OS == "Android") { ?>
				<p style="margin-top: 0px; margin-bottom: 0px;">
					<a href="./controleurs/CtrlTelechargerApk.php" data-mini="true" data-role="button" data-ajax="false">Télécharger l'application Android</a>
				</p>
				<?php };?>
				
				<?php if($debug == true) {
					// en mise au point, on peut afficher certaines variables dans la page
					echo "<p>SESSION['nom'] = " . $_SESSION['nom'] . "</p>";
					echo "<p>SESSION['mdp'] = " . $_SESSION['mdp'] . "</p>";
					echo "<p>SESSION['niveauUtilisateur'] = " . $_SESSION['niveauUtilisateur'] . "</p>";
					echo "<p>mode = " . $afficherMdp . "</p>";
				} ?>
			</div>
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4><?php echo $msgFooter; ?></h4>
			</div>
		</div>
	</body>
</html>