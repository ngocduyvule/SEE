<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<liste>";

require("config.php");

$agence = (isset($_POST["agence"])) ? $_POST["agence"] : NULL;
if($agence) {
	mysql_connect($hostname, $username, $password);
	mysql_select_db($database);
	
	$query = "SELECT * FROM `see_agent` WHERE `agence` = '$agence' ORDER BY `nom`";
	mysql_query("SET NAMES UTF8");
	$result = mysql_query($query) or die(mysql_error());
	while($row = mysql_fetch_assoc($result)) {
		echo "<agent nom=\"".$row["prenom"]." ".$row["nom"]."\"/>";
	}
}
echo "</liste>";
?>