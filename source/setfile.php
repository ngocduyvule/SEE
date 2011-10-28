<?php
require('authenticate.php');

$idfile = $_GET["id"];
$queryfile = "SELECT * FROM `see_files` WHERE `id` = '$idfile'";
mysql_query("SET NAMES UTF8");
$resultfile = mysql_query($queryfile) or die(mysql_error());
$file = mysql_fetch_object($resultfile);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>INDAL - Suivi des Etudes d'Eclairage</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta name="description" content="Indal - Suivi des Etudes d'Eclairage" />
	<meta name="keywords" content="" />
	<meta name="robots" content="index,follow" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" type="text/css" href="jqtransform.css" />
	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.jqtransform.js"></script>
	<script type="text/javascript" src="js/indal.js"></script>
	<script language="javascript">
		$(function(){
			$("form").jqTransform({imgPath:'images/jqtransform/'});
			
			$("#dossier div.statut ul li a").click(function() {
				var statut = $("#dossier div.statut div.jqTransformSelectWrapper span").text();
				selectStatut(statut);
				return false;
			});
			
			$("#dossier div.agence ul li a").click(function() {
				$.post("getagents.php", { agence: $(this).text() },
				function success(data) {
					readAgents(data);
				});
				return false;
			});
			
			function goto_confirm(url) {
				if(confirm("Etes-vous sûr de vouloir supprimer ce dossier ?")) {
					document.location.href = url;
				} else {
					return false;
				}
			}
			
			$("button[name=delete]").click(function() {
				if(confirm("Etes-vous sûr de vouloir supprimer ce dossier ?")) {
					document.location.href = "setfilego.php";
				} else {
					return false;
				}
			});
		});
	</script>
</head>
<body >
	<div id="wrap">
		<div id="header">
			<h1>Indal Logo</h1>
			<h2>Suivi des Etudes d'Eclairage</h2>
		</div>
		<div id="main">
			<div id="menu">
				<?php if (isset($_SESSION['logged_in'])) { ?>
					<p class="username">
						<?php echo $_SESSION['prenom'];
						echo " ";
						echo $_SESSION['nom']; ?>
						, <a href="logout.php?signature=<?php echo $_SESSION['signature']; ?>" class="btn" ><span><span class="logout">Déconnexion</span></span></a>
					</p><br />
					<p>
						<?php if ($_SESSION['type'] == "admin") { ?>
							<a href="register.php" class="btn" ><span><span class="adduser">Ajouter utilisateur</span></span></a>
						<?php } ?>
						<?php if ($_SESSION['type'] != "agent") { ?>
							<a href="addfile.php" class="btn" ><span><span class="addfile">Ajouter dossier</span></span></a>
							<a href="archives.php" class="btn" ><span><span class="archives">Accéder archives</span></span></a>
						<?php } ?>
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="dossier">
				<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
				<form action="setfilego.php" method="POST">
					<div class="forminput"><input type="hidden" id="fileid" name="fileid" value="<?php echo $idfile; ?>" /></div>
					<div class="forminput"><label>N° de dossier : </label><input type="text" id="filenum" name="filenum" value="<?php echo $file->num; ?>" /></div>
					<div class="forminput"><label>Indice : </label><input type="text" id="fileind" name="fileind" size="3" value="<?php echo $file->indice; ?>"/></div>
					<div class="forminput"><label>Nom de l'affaire : </label><input type="text" id="filename" name="filename" value="<?php echo $file->affaire; ?>" /></div>
					<div class="clear"><br /></div>
					<div class="forminput statut">
						<label>Statut : </label>
						<div class="left"><select name="filestatut">
							<option <?php if($file->statut == "En cours") echo "selected" ?>>En cours</option>
							<option <?php if($file->statut == "En attente") echo "selected" ?>>En attente</option>
							<option <?php if($file->statut == "Envoyé") echo "selected" ?>>Envoyé</option>
						</select></div>
						<div class="paddingleft50 left"><label class="rouge" >Traitement en urgence : </label><input type="checkbox" name="fileurgence[]" value="1" <?php if($file->urgence == "1") echo "checked=\"checked\"" ?>/></div>
					</div>
					<div class="forminput <?php if($file->statut == "En attente") { echo "listvis"; } else { echo "listhide"; } ?>" id="manque">
						<label>Eléments manquants : </label>
						<textarea id="filemanque" name="filemanque" cols=80 rows=6 ><?php echo $file->manque; ?></textarea>
					</div>
					<div class="clear"><br /></div>
					<div class='forminput agence'>
						<label>Agence : </label>
						<select id="Agc" name='fileagence'>
							<option ></option>
							<?php
							$queryagence = "SELECT * FROM `see_agence` ORDER BY `id` ASC";
							mysql_query("SET NAMES UTF8");
							$resultagence = mysql_query($queryagence) or die(mysql_error());
							while($rowagence = mysql_fetch_array($resultagence, MYSQL_ASSOC)) {
								echo "<option value='".$rowagence["nom"]."'";
								if ($file->agence == $rowagence["nom"]) {
									echo " selected='selected' ";
								}
								echo ">".$rowagence["nom"]."</option>";
							}
							?>
						</select>
					</div>
					<div class="forminput agents" id="Ags">
						<label>Agent : </label>
						<select name="fileagent" >
							<?php
								$agencenom = $file->agence;
								$queryagent = "SELECT * FROM `see_agent` WHERE `agence` = '$agencenom' ORDER BY `nom`";
								mysql_query("SET NAMES UTF8");
								$resultagent = mysql_query($queryagent) or die(mysql_error());
								while($rowagent = mysql_fetch_array($resultagent, MYSQL_ASSOC)) {
									$agentnom = $rowagent["prenom"] . " " . $rowagent["nom"];
									echo "<option value='".$agentnom."'";
									if ($file->agent == $agentnom) {
										echo " selected='selected' ";
									}
									echo ">".$agentnom."</option>";
								}
							?>
						</select>
					</div>
					<div class="clear"><br /><br /><br /></div>
					<div class="forminput"><input type="submit" name="submit" value="Modifier" class="submit"/><input type="submit" name="delete" value="Supprimer" onClick="goto_confirm('setfilego.php')" class="submit"/><input type="submit" name="reload" value="Relancer"  class="submit"/></div>
					<div class="clear"></div>
				</form>
			</div>

		</div>
		<div id="footer">
			<p class="vide">Vide</p>
			<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
		</div>
	</div>
</body>
</html>