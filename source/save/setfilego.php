<?php
require('authenticate.php');

if((isset($_POST["filenum"])) && (isset($_POST["filename"])) && (isset($_POST["fileagence"])) && (isset($_POST["fileag"]))) {

	function sanitize($data){
		$data=trim($data);
		$data=htmlspecialchars($data);
		$data=mysql_real_escape_string($data);
		return $data;
	};

	$fileid = $_POST["fileid"];
	$filenum = sanitize($_POST["filenum"]);
	$fileind = sanitize($_POST["fileind"]);
	$filename = sanitize($_POST["filename"]);
	$fileagence = sanitize($_POST["fileagence"]);
	$fileag = sanitize($_POST["fileag"]);
	$filestatut = $_POST["filestatut"];
	$fileurgence = $_POST["fileurgence"];
	$filemanque = sanitize($_POST["filemanque"]);


	if(isset($_POST["delete"])) {
		$query = "DELETE FROM `see_files` WHERE `id` = '$fileid' LIMIT 1";
		mysql_query("SET NAMES UTF8");
		$result = mysql_query($query) or die(mysql_error());
		
		header(sprintf("Location: %s", $domain."see/index.php"));	
		exit;
	} else if(isset($_POST["reload"])) {
		$query = "UPDATE `see_files` SET `datearrive`= NOW() WHERE `id` = '$fileid'";
		mysql_query("SET NAMES UTF8");
		$result = mysql_query($query) or die(mysql_error());
		
		header(sprintf("Location: %s", $domain."see/index.php"));	
		exit;
	} else {
	
	$query = "UPDATE `see_files` SET `num`= '$filenum', `indice`= '$fileind', `agence`= '$fileagence', `agent`= '$fileag', `affaire`= '$filename', `statut` = '$filestatut', `manque`= '$filemanque', `urgence`= '$fileurgence[0]', `dateenvoi`= NOW() WHERE `id` = '$fileid'";
	mysql_query("SET NAMES UTF8");
	$result = mysql_query($query) or die(mysql_error());
	
	
	$agent = explode(" ", $fileag);
	$prenomagent = $agent[0];
	$nomagent = $agent[1];
		
	$querynomagent = "SELECT `email` FROM `see_agent` WHERE `nom` = '$nomagent' AND `prenom` = '$prenomagent'";
	mysql_query("SET NAMES UTF8");
	$resultnomagent = mysql_query($querynomagent) or die(mysql_error());
	$rownomagent = mysql_fetch_row($resultnomagent);
	
	$subject = "Suivi des Etudes d'Eclairage - Ajout de dossier";
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
								<h1 style=' font-size: 14px; font-weight: bold;' >Modification de dossier,</h1>
								<p>Un dossier vous concernant a été modifié au programme de Suivi des Etudes d'Eclairage Indal.</p><br />
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
	$message .=							"<br /><div style='padding: 0 50px; background-color: #FF820A; color: #FFF; font-weight: bold; border: 0; font-size:15px; width: 200px;'>
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