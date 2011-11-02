<?php
require('authenticate.php');

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
	<link rel="stylesheet" type="text/css" href="jquery-ui-smoothness.css" />
	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.jqtransform.js"></script>
	<script type="text/javascript" src="js/jquery.tipTip.minified.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
	<script type="text/javascript" src="js/indal.js"></script>
	<script type="text/javascript">
		$(function(){
			var sessionName = "<?php echo $_SESSION['type']; ?>";
			var timer = setInterval(reloadFiles, 60000);
			
			$("form").jqTransform({imgPath:'images/jqtransform/'});
			$(".tip").tipTip();
			
			
			// Retard
			if(sessionName != "agent") {
				$("#delay-text").change(function() {
					$.post("setdelay.php", { retard: $(this).val() },
					function success(data) {
						readDelay(data);
					});
					return false;
				});
			} else {
				$("#filters").css("display", "none");
			}
			
			// Agences
			loadAgences("Agc");
			$("ul li a").click(function() {
				alert("click");
				return false;
			});
			$("#tableau div.agence ul li a").click(function() {
				alert("click");
				reloadFiles();
				return false;
			});
			/*function loadAgences(idAgence) {
				$.ajax({
					url: "getagences.php",
					type: "POST",
					cache: false,
					success: function(data) {
						readAgences(data, idAgence);
					}
				});
				return false;
			}*/
			
			// Period
			$("#period-range").slider({
				range: true,
				min: 0,
				max: 30, 
				values: [ 0, 30 ],
				step: 1,
				slide: function(event, ui) {
					$("#period-text").val("de " + ui.values[0] + " à " + ui.values[1] + " jours");
				},
				stop: function(event, ui) {
					reloadFiles();
				}
			});
			$("#period-text").val("de " + $("#period-range").slider("values", 0) + " à " + $("#period-range").slider("values", 1) + " jours");
			
			$("#plusfile").click(function() {
				clearInterval(timer);
				if(addNewFile($("#tableau tr.tabletitle"))) {
					$("form").jqTransform({imgPath:'images/jqtransform/'});
					loadAgences("Agcn");
					if($("#Agcn")) {
					alert("clock");
						$("#Agcn ul li a").click(function() {
						alert("click");
							$.post("getagents.php", { agence: $(this).text() },
							function success(data) {
								readAgents(data, "Ags");
							});
						return false;
						});
					}
					//timer = setInterval(reloadFiles, 60000);
				};
			});
			
			// Dossiers
			reloadFiles();
			function reloadFiles() {
				if($("#newTR") !== undefined) {
					$("#newTR").slideUp(500);
				}
				$.ajax({
					url: "getfiles.php",
					type: "GET",
					data: { agence: $("select#Agc option:selected").val(), jourfrom: $("#period-range").slider("values", 0), jourto: $("#period-range").slider("values", 1) },
					cache: false,
					beforeSend: function() {
						$("#tableau tr.tablefile").remove();
						$("#tableau tr.tableload").slideDown(500);
					},
					success: function(data) {
						readFiles(data, sessionName);
					}
				});
				return false;
			}
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
						<a href="changepassword.php" class="btn" ><span><span class="changepassword">Changer mot de passe</span></span></a>
					</p>
				<?php } ?>
			</div>
			
			<div id="info">
				<p>Délai normal de 7 jours</p>
				<p>Retard estimatif de 
				<input type="text" id="delay-text" class="ignore" size="2" value="<?php 
				$querydelay= "SELECT * FROM `see_manage` WHERE `id` = 1 LIMIT 1";
				mysql_query("SET NAMES UTF8");
				$resultdelay = mysql_query($querydelay) or die(mysql_error());
				$manage = mysql_fetch_object($resultdelay);
				echo $manage->retard; ?>" /> jours</p>
			</div>
			
			<div id="tableau">
				<div id="filters"><form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
					<div id="Agc" class='forminput agence left'>
						<label>Agence : </label>
						<select name='fileagence'>
							<option ></option>
						</select>
					</div>
					<div class="forminput left"><label>Période : </label><br /><input type="text" id="period-text" class="ignore" /></div>
					<div id="period-range" class="left"></div>
				<div class="clear"><br /><br /><br /></div>
				</form>
				</div>
				
				<div class="table-frame">
					<div class="table-frame-header">
						<h4 class="table-frame-up">
							<span></span>
						</h4>
					</div>
					<div class="table-frame-content">
						<div class="table-frame-center">
							<div class="table-frame-main">
								<table class="table" cellpadding="0" cellspacing="0"border="0">
								<colgroup>
									<col class="table-col-plus"/>
									<col class="table-col-num"/>
									<col class="table-col-agence"/>
									<col class="table-col-agent"/>
									<col class="table-col-affaire"/>
									<col class="table-col-datearrive"/>
									<col class="table-col-statut"/>
									<col class="table-col-dateenvoi"/>
								</colgroup>
								<tbody>
									<tr class="tabletitle">
										<?php if ($_SESSION['type'] != "agent") { ?>
										<th class="plus"><img id="plusfile" src="images/plus_blu_20x20.png" alt="Plus" style="cursor: pointer" /></th>
										<?php } ?>
										<th>N° dossier</th>
										<th>Agence</th>
										<th>Agent</th>
										<th>Nom de l'affaire</th>
										<th>Date arrivée</th>
										<th>Statut</th>
										<th>Date envoi</th>
									</tr>
									<tr class="tableload"><td colspan="8"><img src="images/loading-bar.gif" alt="loading" /></td></tr>
									<tr class="tablesep"><td colspan="8"></td></tr>
									<tr class="tablefile"><td></td></tr>
								</tbody>
							</table>
							</div>
						</div>
					</div>
				</div>		
			</div>

		</div>
		<div id="footer">
			<p class="vide">Vide</p>
			<p>Copyright © 2011 Indal France - Zone de Pompey Industries 54670 Custines FRANCE - Tous droits réservés</p>
		</div>
	</div>
</body>
</html>