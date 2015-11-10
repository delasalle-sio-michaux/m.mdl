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
				<form name="form1" id="form1" action="index.php?action=ConfirmerReservation" method="POST">
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="saisieRes" align='center'><b>Confirmer une reservation<b></b></label>
						<input type='text' name="saisieRes" id="saisieRes" placeholder='Entrer le numéro de reservation'>
					</div>
					<div data-role="fieldcontain" data-type="horizontal" data-mini="true" class="ui-hide-label">
						<input type="submit" name="confirmer" id="confirmer" value="Confirmer la reservation">
					</div>
				</form>
			</div>
			
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4>Confirmer une réservation</h4>
			</div>
		</div>
	</body>
</html>