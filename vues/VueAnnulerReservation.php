<?php
	// Projet Réservations M2L - version web mobile
	// Fonction de la vue VueAnnulerReservation.php : permet d'entrer un numéro de réservation à annuler
	// Ecrit le 17/11/2015 par Alban
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
				<a href="index.php?action=Deconnecter">Retour Menu</a>
			</div>
			
			<div data-role="content">
				<h4 style="text-align: center; margin-top: 10px; margin-bottom: 10px;">Annuler une réservation</h4>
				<form name="form1" id="form1" action="index.php?action=AnnulerReservation" method="POST">
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="saisieRes" align='center'><b>Annuler une reservation<b></b></label>
						<input type='text' name="saisieRes" id="saisieRes" placeholder='Entrer le numéro de reservation'>
					</div>
					<div data-role="fieldcontain" data-type="horizontal" data-mini="true" class="ui-hide-label">
						<input type="submit" name="confirmer" id="confirmer" value="Annuler la reservation">
					</div>
				</form>
			</div>
			
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4><?php echo $msgFooter; ?></h4>
			</div>
		</div>
	</body>
</html>