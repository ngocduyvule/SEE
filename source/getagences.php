<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<liste>";

require("config.php");

mysql_connect($hostname, $username, $password);
mysql_select_db($database);
	
$query = "SELECT * FROM `see_agence` ORDER BY `id` ASC";
mysql_query("SET NAMES UTF8");
$result = mysql_query($query) or die(mysql_error());
echo "<agence nom='' />";
while($row = mysql_fetch_assoc($result)) {
	echo "<agence nom='".$row["nom"]."' />";
}

echo "</liste>";
?>