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
				<form name="form1" id="form1" action="index.php?action=CreerUtilisateur" method="POST">
				
					<div data-role="fieldcontain" class="ui-hide-label">
						<label for="saisieUser" align='center'><b>Créer un compte utilisateur</b></label>
						<input type='text' name="saisieUser" id="saisieUser" placeholder="Entrer le nom de l'utilisateur">
					</div>
					
					<div data-role="fieldcontain" class="ui-hide-label">
						<input type='text' name="saisieUserMail" id="saisieUserMail" placeholder="Entrez l'adresse mail">
					</div>
					
					<div data-role="fieldcontain">
						<fieldset data-role="controlgroup" data-mini="true">
				        <legend>Niveau :</legend>
				        	
					        <input type="radio" name="radioNiveau" id="radio-choice-v-2a" value="0" checked="checked">
					        <label for="radio-choice-v-2a">Invité</label>
					        <input type="radio" name="radioNiveau" id="radio-choice-v-2b" value="1">
					        <label for="radio-choice-v-2b">Utilisateurs</label>
					        <input type="radio" name="radioNiveau" id="radio-choice-v-2c" value="2">
					        <label for="radio-choice-v-2c">Administrateur</label>
				    	</fieldset>
			    	</div>
			    	
			    	<div data-role="fieldcontain">
						<input type="submit" name="creer" id="creer" value="Créer l'utilisateur">
			    	</div>
				</form>
			</div>
			
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4><?php echo $msgFooter ?></h4>
			</div>
			
		</div>
	</body>
</html>