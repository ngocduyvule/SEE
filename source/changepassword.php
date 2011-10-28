<?php
require('authenticate.php');

//pre-define validation parameters

$passwordnotempty=TRUE;
$passwordmatch=TRUE;

//Check if user submitted the desired password and username
if ( (isset($_POST["passnew"])) && (isset($_POST["passnew2"])) )  {

function sanitize($data){
	$data=trim($data);
	$data=htmlspecialchars($data);
	$data=mysql_real_escape_string($data);
	return $data;
}

$passnew = sanitize($_POST["passnew"]);
$passnew2 = sanitize($_POST["passnew2"]);


if ($passnew==$passnew2) {
	$passwordmatch=TRUE;
} else {
	$passwordmatch=FALSE;
}

if (($passwordnotempty==TRUE)&& ($passwordmatch==TRUE)) {
	function HashPassword($input)
	{
	//Credits: http://crackstation.net/hashing-security.html
	//This is secure hashing the consist of strong hash algorithm sha 256 and using highly random salt
		$salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); 
		$hash = hash("sha256", $salt . $input); 
		$final = $salt . $hash; 
		return $final;
	}

	$hashedpassword= HashPassword($passnew);
	
	$prenom = $_SESSION['prenom'];
	$nom = $_SESSION['nom'];

	$query = "UPDATE `see_authentification` SET `password`= '$hashedpassword' WHERE `prenom` = '$prenom' AND `nom` = '$nom' ";
	mysql_query("SET NAMES UTF8");
	$result = mysql_query($query) or die(mysql_error());
	
	header(sprintf("Location: %s", $loginpage_url));	
	exit;
}


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
							<a href="archives.php" class="btn" ><span><span class="archives">Accéder archives</span></span></a>
						<?php } ?>
					</p>
				<?php } ?>
			</div>
			
			<div id="dossier">
				<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
					<div class="forminput"><label>Nouveau mot de passe : </label><input type="text" id="passnew" name="passnew" /></div>
					<div class="forminput"><label>Retaper mot de passe : </label><input type="text" id="passnew2" name="passnew2" /></div>
					<div class="clear"></div>
					<br /><br />
					<div class="forminput"><input type="submit" value="Valider"/></div>
					<br /><br />
					
					<!-- Display validation errors -->
						<?php if ($passwordnotempty==FALSE) echo '<font color="red">Le champ mot de passe est vide.</font>'; ?><br />
						<?php if ($passwordmatch==FALSE) echo '<font color="red">Vos mots de passe ne correspondent pas.</font>'; ?><br />
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