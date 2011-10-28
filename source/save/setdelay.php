<?php
require('authenticate.php');

if(isset($_POST["delay"])) {
	$retard = $_POST["delay"];
	$query = "UPDATE `see_manage` SET `retard`= '$retard' WHERE `id`=1 LIMIT 1";
	mysql_query("SET NAMES UTF8");
	$result = mysql_query($query) or die(mysql_error());
	header(sprintf("Location: %s", $domain."see/index.php"));	
	exit;
} else {
	$queryfile = "SELECT * FROM `see_manage`";
	mysql_query("SET NAMES UTF8");
	$resultfile = mysql_query($queryfile) or die(mysql_error());
	$manage = mysql_fetch_object($resultfile);
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
	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.jqtransform.js"></script>
	<script type="text/javascript" src="js/jquery.tipTip.minified.js"></script>
	<script language="javascript">
		$(function(){
			$("form").jqTransform({imgPath:'images/jqtransform/'});

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
							<a href="archive.php" class="btn" ><span><span class="archives">Accéder archives</span></span></a>
						<?php } ?>
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="tableau">
				<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
					<div class="forminput"><label>Nb de jours de retard : </label><input type="text" id="delay" name="delay" size="3" value="<?php echo $manage->retard; ?>" /></div>
					<div class="forminput"><input type="submit" value="Valider"/></div>
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