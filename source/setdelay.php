<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<liste>";

require("config.php");

$retard = (isset($_POST["retard"])) ? $_POST["retard"] : NULL;

if($retard) {
	$query = "UPDATE `see_manage` SET `retard`= '$retard' WHERE `id`=1 LIMIT 1";
	mysql_connect($hostname, $username, $password);
	mysql_select_db($database);
	mysql_query("SET NAMES UTF8");
	$result = mysql_query($query) or die(mysql_error());
	
	echo "<retard jour='".$retard."' />";
}

echo "</liste>";
?>