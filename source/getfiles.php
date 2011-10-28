<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<liste>";

require("config.php");

$agence = (isset($_GET["agence"])) ? $_GET["agence"] : "";
$jourfrom = (isset($_GET["jourfrom"])) ? $_GET["jourfrom"] : "-1";
$jourto = (isset($_GET["jourto"])) ? $_GET["jourto"] : "30";

if($agence || $jourfrom || $jourto) {
	$query = "SELECT * FROM `see_files` WHERE ";
	if($agence) {
		$query .= "`agence` = '$agence' AND";
	}
	//$query .= " `datearrive` BETWEEN DATE_SUB(CURDATE(), INTERVAL '$jourfrom' DAY) AND DATE_SUB(CURDATE(), INTERVAL '$jourto' DAY) ORDER BY `datearrive` DESC";
	//$query .= " `datearrive` >= DATE_SUB(CURDATE(), INTERVAL '$jourto' DAY) AND `datearrive` <= DATE_SUB(CURDATE(), INTERVAL '$jourfrom' DAY) ORDER BY `datearrive` DESC";
	$query .= " ('$jourto' >= TO_DAYS(NOW()) - TO_DAYS(`datearrive`)) AND (TO_DAYS(NOW()) - TO_DAYS(`datearrive`) >= '$jourfrom') ORDER BY `datearrive` DESC";
}/* else {
	$query = "SELECT * FROM `see_files` WHERE `datearrive` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY `datearrive` DESC";
}*/


mysql_connect($hostname, $username, $password);
mysql_select_db($database);

mysql_query("SET NAMES UTF8");
$result = mysql_query($query) or die(mysql_error());
while($row = mysql_fetch_assoc($result)) {
	echo "<dossier id=\"".$row["id"]."\" 
		numero=\"".$row["num"]."".$row["indice"]."\" 
		agence=\"".$row["agence"]."\" 
		agent=\"".$row["agent"]."\" 
		affaire=\"".$row["affaire"]."\" 
		urgence=\"".$row["urgence"]."\" 
		datearrive=\"".$row["datearrive"]."\" 
		statut=\"".$row["statut"]."\" 
		manque=\"".$row["manque"]."\" 
		dateenvoi=\"".$row["dateenvoi"]."\" />";
}

echo "</liste>";
?>