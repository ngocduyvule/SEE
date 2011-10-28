<?php
require('authenticate.php');


$queryagence = "SELECT * FROM `see_agence` ORDER BY `id` ASC";
mysql_query("SET NAMES UTF8");
$resultagence = mysql_query($queryagence) or die(mysql_error());

$listagence = "<div class='forminput agence'><label>Agence : </label><div class='left'><select name='fileagence'><option></option>";
$listagent = "<div class='forminput agents'><label>Agent : </label><div id='ag' class='listag listvis'><select name='fileagent' ><option ></option></select></div>";
while($rowagence = mysql_fetch_array($resultagence, MYSQL_ASSOC)) {
	$listagence .= "<option>".$rowagence["nom"]."</option>";
	$agencenom = $rowagence["nom"];
	
	$listagent .= "<div id='ag".$agencenom."' class='listag listhid";
	$listagent .= "'><select name='fileagent' >";
	$queryagent = "SELECT * FROM `see_agent` WHERE `agence` = '$agencenom'";
	mysql_query("SET NAMES UTF8");
	$resultagent = mysql_query($queryagent) or die(mysql_error());
	while($rowagent = mysql_fetch_array($resultagent, MYSQL_ASSOC)) {
		$agentnom = $rowagent["prenom"] . " " . $rowagent["nom"];
		$listagent .= "<option>".$agentnom."</option>";
	}
	$listagent .= "</select></div>";
}
$listagence .= "</select></div></div>";
$listagent .= "</div>";



$filenumnotduplicate = TRUE;

if((isset($_POST["filenum"])) && (isset($_POST["filename"])) && (isset($_POST["fileagence"])) && (isset($_POST["fileag"]))) {

	function sanitize($data){
		$data=trim($data);
		$data=htmlspecialchars($data);
		$data=mysql_real_escape_string($data);
		return $data;
	};

	$filenum = sanitize($_POST["filenum"]);
	$fileind = sanitize($_POST["fileind"]);
	$filename = sanitize($_POST["filename"]);
	$fileagence = sanitize($_POST["fileagence"]);
	$fileag = sanitize($_POST["fileag"]);
	$filestatut = $_POST["filestatut"];
	$fileurgence = $_POST["fileurgence"];
	$filemanque = sanitize($_POST["filemanque"]);

	if(!($fetch = mysql_fetch_array(mysql_query("SELECT `num` FROM `see_files` WHERE `num` = '$filenum'")))) {
		$filenumnotduplicate = TRUE; 
	} else {
		$filenumnotduplicate = FALSE;
	}

	if($filenumnotduplicate = TRUE) {
		mysql_query("SET NAMES UTF8");
		mysql_query("INSERT INTO `see_files` (`num`, `indice`, `agence`, `agent`, `affaire`, `datearrive`, `statut`, `manque`, `urgence`) VALUES ('$filenum', '$fileind', '$fileagence', '$fileag', '$filename', NOW(), '$filestatut', '$filemanque', '$fileurgence[0]')") or die(mysql_error());
		
		$agent = explode(" ", $fileag);
		$prenomagent = $agent[0];
		$nomagent = $agent[1];
		
		$querynomagent = "SELECT `email` FROM `see_agent` WHERE `nom` = '$nomagent' AND `prenom` = '$prenomagent'";
		mysql_query("SET NAMES UTF8");
		$resultnomagent = mysql_query($querynomagent) or die(mysql_error());
		$rownomagent = mysql_fetch_row($resultnomagent);
		
		$subject = "Suivi des Etudes d'Eclairage - Ajout de dossier";
		$headers = "From: webmaster@indal-france.com\r\n";
		//$headers .= "Bcc: ".$_SESSION['email']."\r\n";
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
									<h1 style=' font-size: 14px; font-weight: bold;' >Ajout de dossier,</h1>
									<p>Un dossier vous concernant a été ajouté au programme de Suivi des Etudes d'Eclairage Indal.</p><br />
									<div style='padding: 100px; background: #eee; width: 300px;' align='center'>
										<form action='http://www.indal-france.com/indal/see/' method='get'><br />";
		if($filenum != "") { 
			$message .=						"<div><b>N° de dossier : </b>".$filenum.$fileind."</div>";
		}																
		$message .=							"<div><b>Nom d'affaire : </b>".$filename."</div>
											<div><b>Statut : </b>".$filestatut."</div>";
		if ($filestatut	== "En attente") {
			$manquetxt = nl2br($filemanque);
			$message .= "<div><b>Eléments manquants : </b>".$manquetxt."</div><br />";
		}
		if ($fileurgence[0] == "1") {
			$message .= "<div style='color: #be0e0e;'>Traitement en urgence</div>";
		}
		$message .=									"<br />
											<div style='padding: 0 50px; background-color: #FF820A; color: #FFF; font-weight: bold; border: 0; font-size:15px; width: 200px;'>
												<a href='http://www.indal-france.com/see/index.php' style='color: #FFF; text-decoration: none;'> Accéder au programme >></a><br />
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
				</body>";


		date_default_timezone_set("Europe/Paris");
		mail($rownomagent[0], $subject, $message, $headers);
		
		header(sprintf("Location: %s", $domain."see/index.php"));	
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
			
			var value = "agIndal";
			$("#dossier div.agence div.jqTransformSelectWrapper ul li a").click(function() {
				value = "ag" + $("#dossier div.agence div.jqTransformSelectWrapper span").text();
				$("div.listag").removeClass("listvis");
				$("div.listag").addClass("listhid");
				$("div[id="+value+"]").addClass("listvis");
				$("#fileag").val($("div[id="+value+"] div.jqTransformSelectWrapper span").text());
				return false;
			});
			
			$("#dossier div.agents div.jqTransformSelectWrapper ul li a").click(function() {
				$("#fileag").val($(this).parents("div.jqTransformSelectWrapper").find("span").text());
				return false;
			});
			
			$("#dossier div.statut div.jqTransformSelectWrapper ul li a").click(function() {
				var statut = $("#dossier div.statut div.jqTransformSelectWrapper span").text();
				if(statut == "En attente") {
					$("#manque").slideDown("fast");
				} else {
					$("#manque").slideUp("fast");
				}
				return false;
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
							<a href="archives.php" class="btn" ><span><span class="archives">Accéder archives</span></span></a>
							<a href="setdelay.php" class="btn" ><span><span class="setdelay">Modifier retard</span></span></a>
						<?php } ?>
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="dossier">
				<p><a href="index.php" class="btn" ><span><span class="retouraccueil">Retour accueil</span></span></a></p><br /><br /><br />
				<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
					<div class="forminput"><label>N° de dossier : </label><input type="text" id="filenum" name="filenum"/></div>
					<div class="forminput"><label>Indice : </label><input type="text" id="fileind" name="fileind" value="" size="3"/></div>
					<div class="forminput"><label>Nom de l'affaire : </label><input type="text" id="filename" name="filename"/></div>
					<div class="clear"><br /></div>
					<div class="forminput statut">
						<label>Statut : </label>
						<div class="left"><select name="filestatut">
							<option >En cours</option>
							<option >En attente</option>
						</select></div>
						<div class="paddingleft50 left"><label class="rouge" >Traitement en urgence : </label><input type="checkbox" name="fileurgence[]" value="1" /></div>
					</div>
					<div class="clear"><br /></div>
					<div class="forminput listhide" id="manque">
						<label>Eléments manquants : </label>
						<textarea id="filemanque" name="filemanque" cols=80 rows=6></textarea>
					</div>
					<div class="clear"><br /></div>
					<?php echo $listagence; ?>
					<div class="clear"></div>
					<?php echo $listagent; ?>
					<div class="forminput"><input type="hidden" id="fileag" name="fileag"/></div>
					<div class="clear"><br /><br /><br /></div>
					<div class="forminput"><input type="submit" value="Ajouter"/></div>
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