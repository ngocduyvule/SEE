<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<liste>";

require("config.php");

$numero = (isset($_POST["numero"])) ? $_POST["numero"] : NULL;
$affaire = (isset($_POST["affaire"])) ? $_POST["affaire"] : NULL;
$date = (isset($_POST["date"])) ? $_POST["date"] : NULL;
$agence = (isset($_POST["agence"])) ? $_POST["agence"] : NULL;
$agent= (isset($_POST["agent"])) ? $_POST["agent"] : NULL;
$session = (isset($_POST["session"])) ? $_POST["session"] : "agent";

if($numero || $affaire || $date || $agence || $agent) {
	$query = "SELECT * FROM `see_files` WHERE ";
	if($numero) {
		$query .= "`num` = '$numero' AND ";
	}
	if($affaire) {
		$query .= "`affaire` = '$affaire' AND ";
	}
	if($agence) {
		$query .= "`agence` = '$agence' AND ";
	}
	if($agent) {
		$query .= "`agent` = '$agent' AND ";
	}
	if($date) {
		date_default_timezone_set("Europe/Paris");
		$date = date("Y-m-d", strtotime($date));
		$query .= "`datearrive` = '$date' ORDER BY `datearrive` DESC";
	} else {
		$query .= "`datearrive` <= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
	}
} else {
	$query = "SELECT * FROM `see_files` WHERE `datearrive` <= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
}

mysql_connect($hostname, $username, $password);
mysql_select_db($database);

mysql_query("SET NAMES UTF8");
$result = mysql_query($query) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo "<dossier session='".$session."'
		id='".$row["id"]."' 
		numero='".$row["num"]."".$row["indice"]."' 
		agence='".$row["agence"]."' 
		agent='".$row["agent"]."' 
		affaire='".$row["affaire"]."' 
		urgence='".$row["urgence"]."' 
		datearrive='".$row["datearrive"]."' 
		statut='".$row["statut"]."' 
		manque='".$row["manque"]."' 
		dateenvoi='".$row["dateenvoi"]."' />";
}

echo "</liste>";
?>