<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		if (anim_perso($mysqli, $id)) {
			
			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
				$b_camp = 'b';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
				$b_camp = 'r';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
				$b_camp = 'g';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			if (isset($_POST['nom_mission']) && trim($_POST['nom_mission']) != "" && isset($_POST['desc_mission']) && trim($_POST['desc_mission']) != "") {
				
				$nom_mission 	= addslashes($_POST['nom_mission']);
				$texte_mission	= addslashes($_POST['desc_mission']);
				
				$rec_thune		= 0;
				$rec_xp			= 0;
				$rec_pc			= 0;
				$nombre_part	= null;
				
				$verif_thune	= true;
				$verif_xp		= true;
				$verif_pc		= true;
				$verif_part		= true;
				
				if (isset($_POST['rec_thune'])) {
					$rec_thune = $_POST['rec_thune'];
					
					$verif_thune = preg_match("#^[0-9]*[0-9]$#i","$rec_thune");
				}
				
				if (isset($_POST['rec_xp'])) {
					$rec_xp = $_POST['rec_xp'];
					
					$verif_xp = preg_match("#^[0-9]*[0-9]$#i","$rec_xp");
				}
				
				if (isset($_POST['rec_pc'])) {
					$rec_pc = $_POST['rec_pc'];
					
					$verif_pc = preg_match("#^[0-9]*[0-9]$#i","$rec_pc");
				}
				
				if (isset($_POST['nombre_part'])) {
					$nombre_part = $_POST['nombre_part'];
					
					$verif_part = preg_match("#^[0-9]*[0-9]$#i","$nombre_part");
				}
				
				if($verif_thune && $verif_xp && $verif_pc && $verif_part) {
					
					// On vérifie si la mission existe déjà
					$sql = "SELECT id_mission FROM missions WHERE nom_mission='$nom_mission'";
					$res = $mysqli->query($sql);
					$nb = $res->num_rows;
					
					if ($nb == 0) {
					
						$sql = "INSERT INTO missions (nom_mission, texte_mission, nombre_participant, recompense_thune, recompense_xp, recompense_pc, camp_mission)
								VALUES ('".$nom_mission."', '".$texte_mission."', '".$rec_thune."', '".$rec_xp."', '".$rec_pc."', '".$nombre_part."', '".$camp."')";
						$mysqli->query($sql);
						
						$mess = "Mission ".$nom_mission." créée avec succès !";
					}
					else {
						$mess_erreur = "Une mission du même nom existe déjà";
					}
				}
				else {
					$mess_erreur = "Merci d'éviter de mettre n'importe quoi dans les champs du formulaire...";
				}
			}
			
			if (isset($_GET['id_mission']) && $_GET['id_mission'] != "") {
				
				$id_mission = $_GET['id_mission'];
				
				$verif_id_mission = preg_match("#^[0-9]*[0-9]$#i","$id_mission");
				
				if ($verif_id_mission ) {
					
					if (isset($_GET['activer']) && $_GET['activer'] == 'ok') {
					
						$sql = "UPDATE missions SET date_debut_mission=NOW() WHERE id_mission='$id_mission' AND camp_mission='$camp'";
						$mysqli->query($sql);
						
					}
					
					if (isset($_GET['valider']) && $_GET['valider'] == 'ok') {
					
						$sql = "UPDATE missions SET date_fin_mission=NOW(), objectif_atteint='1' WHERE id_mission='$id_mission' AND camp_mission='$camp'";
						$mysqli->query($sql);
						
					}
					
					if (isset($_GET['echec']) && $_GET['echec'] == 'ok') {
					
						$sql = "UPDATE missions SET date_fin_mission=NOW(), objectif_atteint='0' WHERE id_mission='$id_mission' AND camp_mission='$camp'";
						$mysqli->query($sql);
						
					}
				}
				else {
					$mess_erreur = "Merci d'éviter de ne pas jouer avec les paramètres de l'URL...";
				}
			}
			?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Animation</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Animation - Gestion des missions</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<?php
					if (isset($_GET['creer']) && $_GET['creer'] == 'ok') {
					?>
					<form method='POST' action='anim_missions.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="nom_mission"><b>Nom de la mission <font color='red'>*</font></b></label>
								<input type="text" class="form-control" id="nom_mission" name="nom_mission">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-3">
								<label for="nombre_part"><b>Nombre de participant maximum</b></label>
								<input type="text" class="form-control" id="nombre_part" name="nombre_part">
							</div>
							<div class="form-group col-md-3">
								<label for="rec_thune"><b>Récompense thunes</b></label>
								<input type="text" class="form-control" id="rec_thune" name="rec_thune">
							</div>
							<div class="form-group col-md-3">
								<label for="rec_xp"><b>Récompense XP / XPI</b></label>
								<input type="text" class="form-control" id="rec_xp" name="rec_xp">
							</div>
							<div class="form-group col-md-3">
								<label for="rec_pc"><b>Récompense PC</b></label>
								<input type="text" class="form-control" id="rec_pc" name="rec_pc">
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="desc_mission"><b>Description de la mission <font color='red'>*</font></b></label>
								<textarea class="form-control" id="desc_mission" name="desc_mission" rows="10"></textarea>
							</div>
						</div>
						<button type="submit" class="btn btn-primary">Créer la mission</button>
					</form>
					
					<?php						
					}
					else {
					?>
					<div align="center">
						<a href='anim_missions.php?creer=ok' class='btn btn-warning'>Créer une nouvelle mission</a>
					</div>
					<?php
					}
					?>
				</div>
			</div>
			
			<br />
			<?php
			if (!isset($_GET['creer'])) {
			?>
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liste des missions actives</h2>
						<?php
						// Récupération de la liste des missions actives
						$sql = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant, date_debut_mission, date_fin_mission 
								FROM missions WHERE date_debut_mission IS NOT NULL AND (date_fin_mission IS NULL OR date_fin_mission <= CURDATE())";
						$res = $mysqli->query($sql);
						$nb_missions_actives = $res->num_rows;
						
						if ($nb_missions_actives > 0) {

							echo "<div id='table_mission' class='table-responsive'>";						
							echo "	<table class='table'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th style='text-align:center'>Nom mission</th>";
							echo "				<th style='text-align:center'>Date d'activation de la mission</th>";
							echo "				<th style='text-align:center'>Date d'expiration de la mission</th>";
							echo "				<th style='text-align:center'>Récompense Thune</th>";
							echo "				<th style='text-align:center'>Récompense XP/XPI</th>";
							echo "				<th style='text-align:center'>Récompense PC</th>";
							echo "				<th style='text-align:center'>Nombre participant Max</th>";
							echo "				<th style='text-align:center'>Liste des participants à la mission</th>";
							echo "				<th style='text-align:center'>Actions</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
						
							while ($t = $res->fetch_assoc()) {
								
								$id_mission		= $t['id_mission'];
								$nom_mission 	= stripslashes($t['nom_mission']);
								$desc_mission 	= stripslashes($t['texte_mission']);
								$rec_thune		= $t['recompense_thune'];
								$rec_xp			= $t['recompense_xp'];
								$rec_pc			= $t['recompense_pc'];
								$nb_participant	= $t['nombre_participant'];
								$date_debut		= $t['date_debut_mission'];
								$date_fin		= $t['date_fin_mission'];
								
								$sql_p = "SELECT perso.id_perso, perso.nom_perso FROM perso, perso_in_mission
										WHERE perso.id_perso = perso_in_mission.id_perso
										AND id_mission='$id_mission'";
								$res_p = $mysqli->query($sql_p);
								
								echo "				<tr>";
								echo "					<td align='center'>".$nom_mission."</td>";
								echo "					<td align='center'>".$date_debut."</td>";
								echo "					<td align='center'>".$date_fin."</td>";
								echo "					<td align='center'>".$rec_thune."</td>";
								echo "					<td align='center'>".$rec_xp."</td>";
								echo "					<td align='center'>".$rec_pc."</td>";
								echo "					<td align='center'>".$nb_participant."</td>";
								echo "					<td align='center'>";
								while ($t_p = $res_p->fetch_assoc()) {
									
									$id_perso_mission 	= $t_p['id_perso'];
									$nom_perso_mission	= $t_p['nom_perso'];
									
									echo $nom_perso_mission." [<a href='evenement.php?infoid=".$id_perso_mission."'>".$id_perso_mission."</a>] <br />";
									
								}
								echo "					</td>";
								echo "					<td align='center'>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&affecter_perso=ok' class='btn btn-info'>Ajouter des participants</a>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&valider=ok' class='btn btn-success'>Valider la mission</a>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&echec=ok' class='btn btn-danger'>Fin de la mission (echec)</a>";
								echo "					</td>";
								echo "				</tr>";
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
						}
						else {
							echo "<i>Aucune mission n'est actuellement active</i>";
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liste des missions non actives</h2>
						<?php
						// Récupération de la liste des missions non actives
						$sql = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant 
								FROM missions WHERE date_debut_mission IS NULL";
						$res = $mysqli->query($sql);
						$nb_missions_non_actives = $res->num_rows;
						
						if ($nb_missions_non_actives > 0) {
							
							echo "<div id='table_mission' class='table-responsive'>";						
							echo "	<table class='table'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th style='text-align:center'>Nom mission</th>";
							echo "				<th style='text-align:center'>Récompense Thune</th>";
							echo "				<th style='text-align:center'>Récompense XP/XPI</th>";
							echo "				<th style='text-align:center'>Récompense PC</th>";
							echo "				<th style='text-align:center'>Nombre participant Max</th>";
							echo "				<th style='text-align:center'>Liste des participants à la mission</th>";
							echo "				<th style='text-align:center'>Actions</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
						
							while ($t = $res->fetch_assoc()) {
								
								$id_mission		= $t['id_mission'];
								$nom_mission 	= stripslashes($t['nom_mission']);
								$desc_mission 	= stripslashes($t['texte_mission']);
								$rec_thune		= $t['recompense_thune'];
								$rec_xp			= $t['recompense_xp'];
								$rec_pc			= $t['recompense_pc'];
								$nb_participant	= $t['nombre_participant'];
								
								$sql_p = "SELECT perso.id_perso, perso.nom_perso FROM perso, perso_in_mission
										WHERE perso.id_perso = perso_in_mission.id_perso
										AND id_mission='$id_mission'";
								$res_p = $mysqli->query($sql_p);
								
								echo "				<tr>";
								echo "					<td align='center'>".$nom_mission."</td>";
								echo "					<td align='center'>".$rec_thune."</td>";
								echo "					<td align='center'>".$rec_xp."</td>";
								echo "					<td align='center'>".$rec_pc."</td>";
								echo "					<td align='center'>".$nb_participant."</td>";
								echo "					<td align='center'>";
								while ($t_p = $res_p->fetch_assoc()) {
									
									$id_perso_mission 	= $t_p['id_perso'];
									$nom_perso_mission	= $t_p['nom_perso'];
									
									echo $nom_perso_mission." [<a href='evenement.php?infoid=".$id_perso_mission."'>".$id_perso_mission."</a>] <br />";
									
								}
								echo "					</td>";
								echo "					<td align='center'>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&affecter_perso=ok' class='btn btn-info'>Ajouter des participants</a>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&modifier=ok' class='btn btn-info'>Modifier la mission</a>";
								echo "						<a href='anim_missions.php?id_mission=".$id_mission."&activer=ok' class='btn btn-warning'>Activer la mission</a>";
								echo "					</td>";
								echo "				</tr>";
								
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
						}
						else {
							echo "<i>Aucune mission non active</i>";
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liste des missions terminées</h2>
						<?php
						// Récupération de la liste des missions terminées
						$sql = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, date_debut_mission, date_fin_mission, objectif_atteint 
								FROM missions WHERE date_fin_mission IS NOT NULL AND date_fin_mission > CURDATE()";
						$res = $mysqli->query($sql);
						$nb_missions_terminees = $res->num_rows;
						
						if ($nb_missions_terminees > 0) {

							echo "<div id='table_mission' class='table-responsive'>";						
							echo "	<table class='table'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th style='text-align:center'>Nom mission</th>";
							echo "				<th style='text-align:center'>Date d'activation de la mission</th>";
							echo "				<th style='text-align:center'>Date de fin de la mission</th>";
							echo "				<th style='text-align:center'>Récompense Thune</th>";
							echo "				<th style='text-align:center'>Récompense XP/XPI</th>";
							echo "				<th style='text-align:center'>Récompense PC</th>";
							echo "				<th style='text-align:center'>Liste des participants à la mission</th>";
							echo "				<th style='text-align:center'>Statut de la mission</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
						
							while ($t = $res->fetch_assoc()) {
								
								$id_mission		= $t['id_mission'];
								$nom_mission 	= stripslashes($t['nom_mission']);
								$desc_mission 	= stripslashes($t['texte_mission']);
								$rec_thune		= $t['recompense_thune'];
								$rec_xp			= $t['recompense_xp'];
								$rec_pc			= $t['recompense_pc'];
								$date_debut		= $t['date_debut_mission'];
								$date_fin		= $t['date_fin_mission'];
								$objectif		= $t['objectif_atteint'];
								
								$sql_p = "SELECT perso.id_perso, perso.nom_perso FROM perso, perso_in_mission
										WHERE perso.id_perso = perso_in_mission.id_perso
										AND id_mission='$id_mission'";
								$res_p = $mysqli->query($sql_p);
								
								echo "				<tr>";
								echo "					<td align='center'>".$nom_mission."</td>";
								echo "					<td align='center'>".$date_debut."</td>";
								echo "					<td align='center'>".$date_fin."</td>";
								echo "					<td align='center'>".$rec_thune."</td>";
								echo "					<td align='center'>".$rec_xp."</td>";
								echo "					<td align='center'>".$rec_pc."</td>";
								echo "					<td align='center'>";
								while ($t_p = $res_p->fetch_assoc()) {
									
									$id_perso_mission 	= $t_p['id_perso'];
									$nom_perso_mission	= $t_p['nom_perso'];
									
									echo $nom_perso_mission." [<a href='evenement.php?infoid=".$id_perso_mission."'>".$id_perso_mission."</a>] <br />";
									
								}
								echo "					</td>";
								echo "					<td align='center'>";
								if ($objectif) {
									echo "<img src='../images/success3.png' width='50' height='50'>";
								}
								else {
									echo "<img src='../images/failed.png' width='80' height='50'>";
								}
								echo "					</td>";
								echo "				</tr>";
								
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
						}
						else {
							echo "<i>Aucune mission n'est pour l'instant terminée</i>";
						}
						?>
					</div>
				</div>
			</div>
			<?php
			}
			?>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
			
			<?php
		}
		else {
			// Un joueur essaye d'acceder à la page sans être animateur
			$text_triche = "Tentative accés page animation sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
			
			header("Location:jouer.php");
		}
	}
	else{
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}