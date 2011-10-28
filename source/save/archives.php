<?php
require('authenticate.php');

$queryagence = "SELECT * FROM `see_agence` ORDER BY `id` ASC";
mysql_query("SET NAMES UTF8");
$resultagence = mysql_query($queryagence) or die(mysql_error());

$listagence = "<div class='forminput agence'><label>Agence : </label><select name='fileagence'><option></option>";

while($rowagence = mysql_fetch_array($resultagence, MYSQL_ASSOC)) {
	$listagence .= "<option>".$rowagence["nom"]."</option>";
}
$listagence .= "</select></div>";


$queryagent = "SELECT * FROM `see_agent` ORDER BY `nom`ASC";
mysql_query("SET NAMES UTF8");
$resultagent = mysql_query($queryagent) or die(mysql_error());
$listagent = "<div class='forminput agents'><label>Agent : </label><select name='fileagent' ><option ></option>";
while($rowagent = mysql_fetch_array($resultagent, MYSQL_ASSOC)) {
	$agentnom = $rowagent["prenom"] . " " . $rowagent["nom"];
	$listagent .= "<option>".$agentnom."</option>";
}
$listagent .= "</select></div>";

$query = "";
if((isset($_POST["filenum"])) || (isset($_POST["filename"])) || (isset($_POST["fileagence"])) || (isset($_POST["fileagent"]))){
	function sanitize($data){
		$data=trim($data);
		$data=htmlspecialchars($data);
		$data=mysql_real_escape_string($data);
		return $data;
	};

	$filenum = sanitize($_POST["filenum"]);
	$filename = sanitize($_POST["filename"]);
	$fileagence = sanitize($_POST["fileagence"]);
	$fileagent = sanitize($_POST["fileagent"]);
	$filedate = $_POST["filedate"];
	
	$query = "SELECT * FROM `see_files` WHERE ";
	if($filenum != "") {
		$query .= "`num` = '$filenum'";
		if($filename != "" || $fileagence != "" || $fileagent != ""){
			$query .= " AND ";
		}
	}
	if($filename != "") {
		$query .= "`affaire` = '$filename'";
		if($fileagence != "" || $fileagent != ""){
			$query .= " AND ";
		}
	}
	if($fileagence != "") {
		$query .= "`agence` = '$fileagence'";
		if($fileagent != ""){
			$query .= " AND ";
		}
	}
	if($fileagent != "") {
		$query .= "`agent` = '$fileagent'";
		if($filedate != ""){
			$query .= " AND ";
		}
	}
	if($filedate != "") {
		date_default_timezone_set("Europe/Paris");
		$insertdate = date("Y-m-d", strtotime($filedate));
		$query .= " `datearrive` = '$insertdate' ORDER BY `datearrive` DESC";
	} else {
		$query .= " `datearrive` <= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
	}
} else {
	$query = "SELECT * FROM `see_files` WHERE `datearrive` <= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
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
		$filelist .= "<td class='statutattente' title='Il manque les éléments ci-dessous à ce dossier. Le délai de 7 jours ne pourra pas être respecté.&nbsp;".$row['manque']."'>".$row['statut']." ";
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
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" type="text/css" href="jqtransform.css" />
	<link rel="stylesheet" type="text/css" href="jquery-ui-smoothness.css" />
	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.jqtransform.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
	<script language="javascript">
		$(function(){
			$("form").jqTransform({imgPath:'images/jqtransform/'});

			$("#filedate").datepicker({ dateFormat: 'dd-mm-yy' });
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
						<?php } ?>
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="tableau">
				<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
					<div class="forminput"><label>N° de dossier sans indice : </label><input type="text" id="filenum" name="filenum"/></div>
					<div class="forminput"><label>Nom de l'affaire : </label><input type="text" id="filename" name="filename"/></div>
					<div class="forminput"><label>Date : </label><input type="text" id="filedate" name="filedate"/></div>
					<div class="clear"><br /></div>
					<?php echo $listagence; ?>
					<?php echo $listagent; ?>
					<div class="forminput"><input type="submit" value="Valider"/></div>
				</form>
				<div class="clear"><br /><br /><br /></div>
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