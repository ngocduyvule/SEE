<?php
require('authenticate.php');

$querydelay= "SELECT * FROM `see_manage` WHERE `id` = 1 LIMIT 1";
mysql_query("SET NAMES UTF8");
$resultdelay = mysql_query($querydelay) or die(mysql_error());
$manage = mysql_fetch_object($resultdelay);

$queryagence = "SELECT * FROM `see_agence` ORDER BY `id` ASC";
mysql_query("SET NAMES UTF8");
$resultagence = mysql_query($queryagence) or die(mysql_error());

$listagence = "<div class='forminput agence'><label>Agence : </label><select name='fileagence'><option></option>";

while($rowagence = mysql_fetch_array($resultagence, MYSQL_ASSOC)) {
	$listagence .= "<option>".$rowagence["nom"]."</option>";
}
$listagence .= "</select></div>";

if((isset($_POST["fileagence"])) || (isset($_POST["fileday"]))) {
	function sanitize($data){
		$data=trim($data);
		$data=htmlspecialchars($data);
		$data=mysql_real_escape_string($data);
		return $data;
	};
	$fileagence = sanitize($_POST["fileagence"]);
	$fileday = $_POST["fileday"];
	$query = "SELECT * FROM `see_files` WHERE ";
	if($fileagence != "") {
		$query .= "`agence` = '$fileagence'";
		if($fileday != ""){
			$query .= " AND ";
		}
	}
	if($fileday != "") {
		$query .= " `datearrive` >= DATE_SUB(CURDATE(), INTERVAL '$fileday' DAY) ORDER BY `datearrive` DESC";
	} else {
		$query .= " `datearrive` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
	}
} else {
	$query = "SELECT * FROM `see_files` WHERE `datearrive` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
}

mysql_query("SET NAMES UTF8");
$result = mysql_query($query) or die(mysql_error());
$filelist = "";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$filelist .= "<tr>";
	if ($_SESSION['type'] != "agent") {
		$filelist .= "<td><a href='setfile.php?id=".$row['id']."'><img src='images/wrench_12x12.png' /></a></td>";
	}
	$filelist .= "<td>".$row['num']."".$row['indice']."</td>";
	$filelist .= "<td>".$row['agence']."</td>";
	$filelist .= "<td>".$row['agent']."</td>";
	$filelist .= "<td>".$row['affaire'];
	if($row['urgence'] == "1") {
		$filelist .= "<br /><span class='rouge'>Traitement en urgence</span>";
	}
	$filelist .= "</td>";
	$filelist .= "<td>".$row['datearrive']."</td>";
	if($row['statut'] == "En cours") {
		$filelist .= "<td class='statutcours'>".$row['statut']." ";
	} else if($row['statut'] == "En attente"){
		$filelist .= "<td class='statutattente' title='Il manque les éléments ci-dessous à ce dossier. Le traitement en est interrompu.&nbsp;".$row['manque']."'>".$row['statut']." ";
		$filelist .= "<img src='images/info_6x12.png' />";
	} else if($row['statut'] == "Envoyé"){
		$filelist .= "<td class='statutenvoye'>".$row['statut'];
	}
	$filelist .= "</td>";
	if($row['statut'] == "Envoyé") {
		$filelist .= "<td>".$row['dateenvoi']."</td>";
	} else {
		$filelist .= "<td></td>";
	}
	$filelist .="</tr>";
}



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
	<?php if (isset($_SESSION['logged_in'])) { ?>
		<?php if ($_SESSION['type'] != "agent") { ?>
			<meta http-equiv="refresh" content="30" />
		<?php } ?>
	<?php } ?>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" type="text/css" href="jqtransform.css" />
	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.jqtransform.js"></script>
	<script type="text/javascript" src="js/jquery.tipTip.minified.js"></script>
	<script language="javascript">
		$(function(){
			$("form").jqTransform({imgPath:'images/jqtransform/'});
			
			$(".tip").tipTip();
			
			$("tr:odd").css("background-color", "#ddd");
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
							<a href="setdelay.php" class="btn" ><span><span class="setdelay">Modifier retard</span></span></a>
						<?php } ?>
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="info">
				<p>Délai normal de 7 jours</p>
				<p>Retard estimatif de <?php echo $manage->retard; ?> jours</p>
			</div>
			
			<div id="tableau">
				<?php if ($_SESSION['type'] != "agent") { ?>
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
					<?php echo $listagence; ?>
					<div class="forminput"><label>Période : </label><input type="text" id="fileday" name="fileday" size="3"/></div>
					<div class="forminput"><input type="submit" value="Valider"/></div>
				</form>
				<div class="clear"><br /><br /><br /></div>
				<?php } ?>
				<table class="table">
					<tr class="tabletitle">
						<?php if ($_SESSION['type'] != "agent") { ?>
							<td></td>
						<?php } ?>
						<td>N° dossier</td>
						<td>Agence</td>
						<td>Agent</td>
						<td>Nom de l'affaire</td>
						<td>Date arrivée</td>
						<td>Statut</td>
						<td>Date envoi</td>
					</tr>
					<?php echo $filelist; ?>
				</table>
			</div>

		</div>
		<div id="footer">
			<p class="vide">Vide</p>
			<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
		</div>
	</div>
</body>
</html>