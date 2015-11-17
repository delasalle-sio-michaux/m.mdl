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
				<form name="form1" id="form1" action="index.php?action=ChangerDeMdp" method="POST">
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="saisieRes" align='center'><b>Changer mon mot de passe</b></label>
						<input type='text' name="saisieMdp1" id="saisieMdp1" placeholder='Entrer votre nouveau mot de passe'>
					</div>
					<div data-role="fieldcontain" class="ui-hide-label">
						<input type='text' name="saisieMdp2" id="saisieMdp2" placeholder='Confirmer votre nouveau mot de passe'>
					</div>
					<div data-role="fieldcontain" data-type="horizontal" data-mini="true" class="ui-hide-label">
						<input type="submit" name="changerMdp" id="changerMdp" value="Changer mon mot de passe">
					</div>
				</form>
			</div>
			
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4><?php echo $msgFooter ?></h4>
			</div>
		</div>
	</body>
</html>