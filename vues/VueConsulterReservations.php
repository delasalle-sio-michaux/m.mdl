<?php
	// Projet Réservations M2L - version web mobile
	// Fonction de la vue VueConsulterReservation.php : visualise les réservations passées
	// Ecrit le 10/11/2015 par Alban
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
				<a href="index.php?action=Menu">Retour menu</a>
			</div>
			<div data-role="content">
				<h4 style="text-align: center; margin-top: 10px; margin-bottom: 10px;">Consulter mes réservations</h4>
				<ul data-role="listview" style="margin-top: 5px;">
				<?php 
					foreach($lesReservations as $uneReservation){ ?>
						<li><a href="#">
							<h5>Réserv. n°<?php echo $uneReservation->getId() ?></h5>
							<p>Passée le <?php echo Outils::convertirEnDateFR(substr($uneReservation->getTimestamp(), 0, 10)) ?></p>
							<p>Début : <?php echo $uneReservation->getStart_time() ?></p>
							<p>Fin : <?php echo $uneReservation->getEnd_time() ?></p>
							<p>Salle : <?php echo $uneReservation->getRoom_name() ?></p>
							<p>Etat : <?php $etat = $uneReservation->getStatus();
								if($etat == 0)
									 echo "Confirmée";
								else
									echo "Provisoire";
								?></p>
							<h5 class="ui-li-aside">Digicode <?php echo $uneReservation->getDigicode() ?></h5>					
						</a></li>
					<?php	
					}	
				?>
				</ul>
			</div>
			<div data-role="footer" data-position="fixed" data-theme="<?php echo $themeFooter; ?>">
				<h4><?php echo $msgFooter; ?></h4>
			</div>
		</div>
	</body>
</html>