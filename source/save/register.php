<?php

require('authenticate.php');

//pre-define validation parameters

$usernamenotempty=TRUE;
$usernamenotduplicate=TRUE;
$passwordnotempty=TRUE;
$passwordmatch=TRUE;
$prenomnotempty=TRUE;
$nomnotempty=TRUE;
$emailnotempty=TRUE;
$emailvalidate=TRUE;
$emailmatch=TRUE;

//Check if user submitted the desired password and username
if ( (isset($_POST["desired_password"])) && (isset($_POST["desired_username"])) && (isset($_POST["desired_password1"])) && (isset($_POST["desired_prenom"])) && (isset($_POST["desired_nom"])) && (isset($_POST["desired_email"])) && (isset($_POST["desired_email1"])))  {
	
//Username and Password has been submitted by the user
//Receive and validate the submitted information

//sanitize user inputs

function sanitize($data){
$data=trim($data);
$data=htmlspecialchars($data);
$data=mysql_real_escape_string($data);
return $data;
}

$desired_username=sanitize($_POST["desired_username"]);
$desired_password=sanitize($_POST["desired_password"]);
$desired_password1=sanitize($_POST["desired_password1"]);
$desired_prenom=sanitize($_POST["desired_prenom"]);
$desired_nom=sanitize($_POST["desired_nom"]);
$desired_email=sanitize($_POST["desired_email"]);
$desired_email1=sanitize($_POST["desired_email1"]);
$desired_agence=$_POST["desired_agence"];
$desired_type=$_POST["desired_type"];


//validate username

if (empty($desired_username)) {
$usernamenotempty=FALSE;
} else {
$usernamenotempty=TRUE;
}

if (!($fetch = mysql_fetch_array( mysql_query("SELECT `username` FROM `see_authentification` WHERE `username`='$desired_username'")))) {
//no records for this user in the MySQL database
$usernamenotduplicate=TRUE;
}
else {
$usernamenotduplicate=FALSE;
}

//validate prenom

if (empty($desired_prenom)) {
$prenomnotempty=FALSE;
} else {
$prenomnotempty=TRUE;
}

//validate prenom

if (empty($desired_nom)) {
$nomnotempty=FALSE;
} else {
$nomnotempty=TRUE;
}

//validate password

if (empty($desired_password)) {
$passwordnotempty=FALSE;
} else {
$passwordnotempty=TRUE;
}

if ($desired_password==$desired_password1) {
$passwordmatch=TRUE;
} else {
$passwordmatch=FALSE;
}

//Validate email

if(empty($desired_email)) {
$emailnotempty=FALSE;
} else {
$emailnotempty=TRUE;
}

$emailatom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // caractères autorisés avant l'arobase
$emaildomain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // caractères autorisés après l'arobase (nom de domaine)
$emailregex = '/^' . $emailatom . '+' .   // Une ou plusieurs fois les caractères autorisés avant l'arobase
'(\.' . $emailatom . '+)*' .         // Suivis par zéro point ou plus
											 // séparés par des caractères autorisés avant l'arobase
'@' .                          			 // Suivis d'un arobase
'(' . $emaildomain . '{1,63}\.)+' .  // Suivis par 1 à 63 caractères autorisés pour le nom de domaine
                                // séparés par des points
$emaildomain . '{2,63}$/i';          // Suivi de 2 à 63 caractères autorisés pour le nom de domaine

if(preg_match($emailregex, $desired_email)) {
	$emailvalidate=TRUE;
} else {
	$emailvalidate=FALSE;
}


if ($desired_email==$desired_email1) {
$emailmatch=TRUE;
} else {
$emailmatch=FALSE;
}


if (($usernamenotempty==TRUE)
&& ($prenomnotempty==TRUE)
&& ($nomnotempty==TRUE)
&& ($usernamenotduplicate==TRUE)
&& ($passwordnotempty==TRUE)
&& ($passwordmatch==TRUE)
&& ($emailnotempty==TRUE)
&& ($emailmatch==TRUE)
&& ($emailvalidate==TRUE)) {
//The username, password and recaptcha validation succeeds.

//Hash the password
//This is very important for security reasons because once the password has been compromised,
//The attacker cannot still get the plain text password equivalent without brute force.

function HashPassword($input)
{
//Credits: http://crackstation.net/hashing-security.html
//This is secure hashing the consist of strong hash algorithm sha 256 and using highly random salt
$salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); 
$hash = hash("sha256", $salt . $input); 
$final = $salt . $hash; 
return $final;
}

$hashedpassword= HashPassword($desired_password);

//Insert username and the hashed password to MySQL database

mysql_query("INSERT INTO `see_authentification` (`username`, `password`, `prenom`, `nom`, `agence`, `email`, `type`) VALUES ('$desired_username', '$hashedpassword', '$desired_prenom', '$desired_nom', '$desired_agence', '$desired_email', '$desired_type')") or die(mysql_error());
//Send notification to webmaster

$subject = "Suivi des Etudes d'Eclairage";
$headers = "From: webmaster@indal-france.com\r\n";
$headers .= "Mime-Version: 1.0\r\n";
$headers .= "Reply-To: webmaster@indal-france.com\r\n";
$headers .= "Return-path: webmaster@indal-france.com\r\n";
$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
$headers .= "\r\n";

$message = "<html><head><title>INDAL - Suivi des Etudes d'Eclairage</title><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /></head>
		<body style='font: 12px Verdana, Arial, Helvetica, sans-serif; color: #a5a5a5; background: #fff; width: 590px;'>
			<table width='600' border='0' cellpadding='0' cellspacing='0'>
				<tbody>
					<tr><td><div style='padding-left: 30px;'><img src='http://www.indal-france.com/see/images/header-mailing.png' alt='Indal - Suivi des Etudes d'Eclairage' /></div></td></tr>
					<tr>
						<td style='width: 590px; padding: 50px 30px; font: 12px Verdana, Arial, Helvetica, sans-serif; color: #a5a5a5;'>
							<h1 style=' font-size: 14px; font-weight: bold;' >Bienvenue au programme de Suivi des Etudes d'Eclairage Indal,</h1>
							<p>Voici vos identifiants :</p><br />
							<div style='padding: 100px; background: #eee; width: 300px;' align='center'>
								<form action='http://www.indal-france.com/indal/see/' method='get'>
									<div><br /><b>Identifiant : </b><input type='text' name='identifiant' value='".$desired_username."' readonly='readonly' size='15'></div>
									<div><b>Mot de passe : </b><input type='text' name='mot de passe' value='".$desired_password."' readonly='readonly' size='15'></div><br />
									<div style='padding: 0 50px; background-color: #FF820A; color: #FFF; font-weight: bold; border: 0; font-size:15px; width: 200px;'>
										<a href='http://www.indal-france.com/see/index.php?login=".$desired_username."&mdp=".$desired_password."' style='color: #FFF; text-decoration: none;'> Accéder au programme >></a><br />
									</div>
								</form>
							</div><br />
							<p>Cordialement,<br />
							<span style='padding-left: 150px; font-style: italic; padding-bottom: 100px;' >L'équipe des Etudes d'Eclairage Indal</span></p><br />
							<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
						</td>
					</tr>
				</tbody>
			</table>
		</body>	
";


date_default_timezone_set("Europe/Paris");
mail($desired_email, $subject, $message, $headers);
//mail($desired_email, $subject, $message, $from, "-f $email");
//redirect to login page
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
			$('form').jqTransform({imgPath:'images/jqtransform/'});
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
					<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
					<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
						<div class="forminput"><label>Identifiant : </label><input type="text" class="<?php if (($usernamenotempty==FALSE) || ($usernamevalidate==FALSE) || ($usernamenotduplicate==FALSE))  echo "erreur"; ?>" id="desired_username" name="desired_username" /></div>
						<div class="clear"></div>
						<div class="forminput"><label>Nom : </label><input type="text" id="desired_nom" name="desired_nom" /></div>
						<div class="forminput"><label>Prénom : </label><input type="text" id="desired_prenom" name="desired_prenom" /></div>
						<div class="clear"></div>
						<div class="forminput"><label>Email : </label><input type="text" id="desired_email" name="desired_email" /></div>
						<div class="forminput"><label>Retaper l'email : </label><input type="text" id="desired_email1" name="desired_email1" /></div>
						<div class="clear"></div>
						<div class="forminput"><label>Mot de passe : </label><input name="desired_password" type="password" class="<?php if (($passwordnotempty==FALSE) || ($passwordmatch==FALSE) || ($passwordvalidate==FALSE)) echo "erreur"; ?>" id="desired_password" /></div>
						<div class="forminput"><label>Retaper le mot de passe : </label><input name="desired_password1" type="password" class="<?php if (($passwordnotempty==FALSE) || ($passwordmatch==FALSE) || ($passwordvalidate==FALSE)) echo "erreur"; ?>" id="desired_password1" /></div>
						<div class="clear"></div>
						<div class="forminput"><label>Agence : </label><select name="desired_agence" >
							<option value="Indal">Indal</option>
							<option value="Actilum">Actilum</option>
							<option value="AGP">AGP</option>
							<option value="LMR">LMR</option>
							<option value="ATCD">ATCD</option>
							<option value="MBAssociés">MBAssociés</option>
							<option value="CITEquipe">CITEquipe</option>
							<option value="Vision">Vision</option>
							<option value="FIMEC">FIMEC</option>
							<option value="LumièreAlsace">LumièreAlsace</option>
							<option value="Luxyol">Luxyol</option>
							<option value="Lumi-Ouest">Lumi-Ouest</option>
							<option value="Lumi-Loire">Lumi-Loire</option>
							<option value="Prysma">Prysma</option>
							<option value="CandLum">CandLum</option>
							<option value="Export">Export</option>
						</select></div>
						<div class="clear"></div>
						<div class="forminput"><label>Type : </label><select name="desired_type" ><option value="admin">Admin</option><option value="seed">Seed</option><option value="agent">Agent</option></select></div>
						<div class="clear"></div>
						<br /><br />
						<div class="forminput"><input type="submit" value="Valider"/></div>
						<br /><br />
						
						<!-- Display validation errors -->
						<?php if ($usernamenotempty==FALSE) echo '<font color="red">Le champ d\'identifiant est vide.</font>'; ?><br />
						<?php if ($nomnotempty==FALSE) echo '<font color="red">Le champ de nom est vide.</font>'; ?><br />
						<?php if ($prenomnotempty==FALSE) echo '<font color="red">Le champ de prénom est vide.</font>'; ?><br />
						<?php if ($usernamenotduplicate==FALSE) echo '<font color="red">Veuillez choisir un autre identifiant.</font>'; ?><br />
						<?php if ($passwordnotempty==FALSE) echo '<font color="red">Le champ mot de passe est vide.</font>'; ?><br />
						<?php if ($passwordmatch==FALSE) echo '<font color="red">Vos mots de passe ne correspondent pas.</font>'; ?><br />
						<?php if ($emailnotempty==FALSE) echo '<font color="red">Le champ email est vide.</font>'; ?><br />
						<?php if ($emailvalidate==FALSE) echo '<font color="red">Vos email n\'est pas valide.</font>'; ?><br />
						<?php if ($emailmatch==FALSE) echo '<font color="red">Vos emails ne correspondent pas.</font>'; ?><br />
						</form>
				<?php } ?>
			</div>

		</div>
		<div id="footer">
			<p class="vide">Vide</p>
			<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
		</div>
	</div>
</body>
</html>