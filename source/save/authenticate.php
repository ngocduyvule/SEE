<?php
/*
 * This is a PHP Secure login system.
 *    - Documentation and latest version
 *          Refer to readme.txt
 *    - To download the latest copy:
 *          http://www.php-developer.org/php-secure-authentication-of-user-logins/
 *    - Discussion, Questions and Inquiries
 *          email codex_m@php-developer.org
 *
 * Copyright (c) 2011 PHP Secure login system -- http://www.php-developer.org
 * AUTHORS:
 *   Codex-m
 * Refer to license.txt to how code snippets from other authors or sources are attributed.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
//require user configuration and database connection parameters
//Start PHP session

session_start(); 

//require user configuration and database connection parameters
require('config.php');

if (($_SESSION['logged_in'])==TRUE) {
//valid user has logged-in to the website

//Check for unauthorized use of user sessions

$iprecreate= $_SERVER['REMOTE_ADDR'];
$useragentrecreate=$_SERVER["HTTP_USER_AGENT"];
$signaturerecreate=$_SESSION['signature'];

//Extract original salt from authorized signature

$saltrecreate = substr($signaturerecreate, 0, $length_salt);

//Extract original hash from authorized signature

$originalhash = substr($signaturerecreate, $length_salt, 40);

//Re-create the hash based on the user IP and user agent
//then check if it is authorized or not

$hashrecreate= sha1($saltrecreate.$iprecreate.$useragentrecreate);

if (!($hashrecreate==$originalhash)) {

//Signature submitted by the user does not matched with the
//authorized signature
//This is unauthorized access
//Block it

header(sprintf("Location: %s", $forbidden_url));	
exit;    
}

//Session Lifetime control for inactivity
//Credits: http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes

if ((isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessiontimeout)))  {

session_destroy();   
session_unset();  

//redirect the user back to login page for re-authentication

$redirectback=$domain.'see/';
header(sprintf("Location: %s", $redirectback));
}
$_SESSION['LAST_ACTIVITY'] = time(); 

}

//Pre-define validation
$validationresults=TRUE;
$registered=TRUE;
$recaptchavalidation=TRUE;

//Trapped brute force attackers and give them more hard work by providing a captcha-protected page

$iptocheck= $_SERVER['REMOTE_ADDR'];
$iptocheck= mysql_real_escape_string($iptocheck);

if ($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `see_ipcheck` WHERE `loggedip`='$iptocheck'"))) {

//Already has some IP address records in the database
//Get the total failed login attempts associated with this IP address

$resultx = mysql_query("SELECT `failedattempts` FROM `see_ipcheck` WHERE `loggedip`='$iptocheck'");
$rowx = mysql_fetch_array($resultx);
$loginattempts_total = $rowx['failedattempts'];

If ($loginattempts_total>$maxfailedattempt) {
	
//too many failed attempts allowed, redirect and give 403 forbidden.

header(sprintf("Location: %s", $forbidden_url));	
exit;
}
}

//Check if a user has logged-in

if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = FALSE;
}

//Check if the form is submitted

if ((isset($_POST["pass"])) && (isset($_POST["user"])) && ($_SESSION['LAST_ACTIVITY']==FALSE)) {

//Username and password has been submitted by the user
//Receive and sanitize the submitted information

function sanitize($data){
$data=trim($data);
$data=htmlspecialchars($data);
$data=mysql_real_escape_string($data);
return $data;
}

$user=sanitize($_POST["user"]);
$pass= sanitize($_POST["pass"]);

//validate username
if (!($fetch = mysql_fetch_array( mysql_query("SELECT `username` FROM `see_authentification` WHERE `username`='$user'")))) {

//no records of username in database
//user is not yet registered

$registered=FALSE;
}

if ($registered==TRUE) {

//Grab login attempts from MySQL database for a corresponding username
$result1 = mysql_query("SELECT `loginattempt` FROM `see_authentification` WHERE `username`='$user'");
$row = mysql_fetch_array($result1);
$loginattempts_username = $row['loginattempt'];

}


//Get correct hashed password based on given username stored in MySQL database

if ($registered==TRUE) {
	
//username is registered in database, now get the hashed password

$result = mysql_query("SELECT `password`,`type`,`nom`,`prenom`,`email` FROM `see_authentification` WHERE `username`='$user'");
$row = mysql_fetch_array($result);
$correctpassword = $row['password'];
$usernom = $row['nom'];
$userprenom = $row['prenom'];
$usertype = $row['type'];
$usermail = $row['email'];
$salt = substr($correctpassword, 0, 64);
$correcthash = substr($correctpassword, 64, 64);
$userhash = hash("sha256", $salt . $pass);
}
if ((!($userhash == $correcthash)) || ($registered==FALSE) || ($recaptchavalidation==FALSE)) {

//user login validation fails

$validationresults=FALSE;

//log login failed attempts to database

if ($registered==TRUE) {
$loginattempts_username= $loginattempts_username + 1;
$loginattempts_username=intval($loginattempts_username);

//update login attempt records

mysql_query("UPDATE `see_authentification` SET `loginattempt` = '$loginattempts_username' WHERE `username` = '$user'");

//Possible brute force attacker is targeting registered usernames
//check if has some IP address records

if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `see_ipcheck` WHERE `loggedip`='$iptocheck'")))) {
	
//no records
//insert failed attempts

$loginattempts_total=1;
$loginattempts_total=intval($loginattempts_total);
mysql_query("INSERT INTO `see_ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$loginattempts_total')");	
} else {
	
//has some records, increment attempts

$loginattempts_total= $loginattempts_total + 1;
mysql_query("UPDATE `see_ipcheck` SET `failedattempts` = '$loginattempts_total' WHERE `loggedip` = '$iptocheck'");
}
}

//Possible brute force attacker is targeting randomly

if ($registered==FALSE) {
if (!($fetch = mysql_fetch_array( mysql_query("SELECT `loggedip` FROM `see_ipcheck` WHERE `loggedip`='$iptocheck'")))) {
	
//no records
//insert failed attempts

$loginattempts_total=1;
$loginattempts_total=intval($loginattempts_total);
mysql_query("INSERT INTO `see_ipcheck` (`loggedip`, `failedattempts`) VALUES ('$iptocheck', '$loginattempts_total')");	
} else {
	
//has some records, increment attempts

$loginattempts_total= $loginattempts_total + 1;
mysql_query("UPDATE `see_ipcheck` SET `failedattempts` = '$loginattempts_total' WHERE `loggedip` = '$iptocheck'");
}
}
} else {
	
//user successfully authenticates with the provided username and password

//Reset login attempts for a specific username to 0 as well as the ip address

$loginattempts_username=0;
$loginattempts_total=0;
$loginattempts_username=intval($loginattempts_username);
$loginattempts_total=intval($loginattempts_total);
mysql_query("UPDATE `see_authentification` SET `loginattempt` = '$loginattempts_username' WHERE `username` = '$user'");
mysql_query("UPDATE `see_ipcheck` SET `failedattempts` = '$loginattempts_total' WHERE `loggedip` = '$iptocheck'");

//Generate unique signature of the user based on IP address
//and the browser then append it to session
//This will be used to authenticate the user session 
//To make sure it belongs to an authorized user and not to anyone else.
//generate random salt
function genRandomString() {
//credits: http://bit.ly/a9rDYd
    $length = 50;
    $characters = "0123456789abcdef";      
    for ($p = 0; $p < $length ; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    
    return $string;
}
$random=genRandomString();
$salt_ip= substr($random, 0, $length_salt);

//hash the ip address, user-agent and the salt
$useragent=$_SERVER["HTTP_USER_AGENT"];
$hash_user= sha1($salt_ip.$iptocheck.$useragent);

//concatenate the salt and the hash to form a signature
$signature= $salt_ip.$hash_user;

//Regenerate session id prior to setting any session variable
//to mitigate session fixation attacks

session_regenerate_id();

//Finally store user unique signature in the session
//and set logged_in to TRUE as well as start activity time

$_SESSION['prenom'] = $userprenom;
$_SESSION['nom'] = $usernom;
$_SESSION['type'] = $usertype;
$_SESSION['email'] = $usermail;
$_SESSION['signature'] = $signature;
$_SESSION['logged_in'] = TRUE;
$_SESSION['LAST_ACTIVITY'] = time(); 
}
} 

if (!$_SESSION['logged_in']): 

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
		<!-- START OF LOGIN FORM -->
		<form class="formlogin" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
			<div class="forminput"><label>Identifiant : </label><input type="text" class="<?php if ($validationresults==FALSE) echo "non-valide"; ?>" id="user" name="user"></div>
			<div class="forminput"><label>Mot de Passe : </label><input name="pass" type="password" class="<?php if ($validationresults==FALSE) echo "non-valide"; ?>" id="pass" ></div>
			<?php if ($validationresults==FALSE) echo '<font color="red">Veuillez renseigner un identifiant et mot de passe valide.</font>'; ?>
			<div class="forminput"><input type="submit" value="Valider"></div>        
			</form>
			<!-- END OF LOGIN FORM -->
		</div>
		<div id="footer">
			<p class="vide">Vide</p>
			<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
		</div>
	</div>
</body>
</html>
<?php
exit();
endif;
?>