function readDelay(retard) {
	var nodes = $('retard', retard);
	$("#delay-text").val(nodes[0].getAttribute("jour"));
}

function readFiles(dossiers, session) {
	var nodes = $('dossier', dossiers);
	var newfile = "";
	
	for (var i = 0, c = nodes.length; i < c; i++) {
		newfile += "<tr class=\"tablefile ";
		if(i == c-1) {
			newfile += "tablefilelast";
		}
		newfile +=	"\">";
		if (session != "agent") {
			newfile += "<td class=\"tdfirst\"><a href=\"setfile.php?id=" + nodes[i].getAttribute("id") + "\"><img src=\"images/go_blu_20x20.png\" /></a></td><td>";
		} else {
			newfile += "td class=\"tdfirst\">";
		}
		newfile += nodes[i].getAttribute("numero") + "</td>";
		newfile += "<td>" + nodes[i].getAttribute("agence") + "</td>";
		newfile += "<td>" + nodes[i].getAttribute("agent") + "</td>";
		newfile += "<td>" + nodes[i].getAttribute("affaire");
		if(nodes[i].getAttribute("urgence") == "1") {
			newfile += "<br /><span class=\"rouge\">Traitement en urgence</span>";
		}
		newfile += "</td>";
		newfile += "<td>" + nodes[i].getAttribute("datearrive") + "</td>";
		if(nodes[i].getAttribute("statut") == "En cours") {
			newfile += "<td class=\"statuts\" title=\"En cours\"><img src=\"images/play_blu_in_28x28.png\" alt=\"En cours\" />";
		} else if(nodes[i].getAttribute("statut") == "En attente"){
			newfile += "<td class=\"statuts\" title=\"Il manque les &eacute;l&eacute;ments ci-dessous &agrave; ce dossier. Le traitement en est interrompu.&nbsp;" + nodes[i].getAttribute("manque") + "\"><img src=\"images/wrong_red_in_28x28.png\" alt=\"En attente\" /> ";
			//newfile += "<img src=\"images/info_6x12.png\" />";
		} else if(nodes[i].getAttribute("statut") == "Envoye"){
			newfile += "<td class=\"statuts\" title=\"Envoy&eacute;\"><img src=\"images/right_gre_in_28x28.png\" alt=\"Envoy&eacute;\" />";
		}
		newfile += "</td>";
		if(nodes[i].getAttribute("statut") == "Envoye") {
			newfile += "<td class=\"tdlast\">" + nodes[i].getAttribute("dateenvoi") + "</td>";
		} else {
			newfile += "<td class=\"tdlast\"></td>";
		}
		newfile += "</tr>";
	}
	$("#tableau tr.tableload").slideUp(500);
	$("#tableau tr.tablesep").after(newfile);
	$(".table tr.tablefile:odd").css("background-color", "#ddd");
}

function loadAgences(idAgence) {
	$.ajax({
		url: "getagences.php",
		type: "POST",
		cache: false,
		success: function(data) {
			readAgences(data, idAgence);
		}
	});
	return true;
}
			
function readAgences(agences, id) {
	var nodes = $('agence', agences);
	var $agDiv = $("#"+ id +"");
	var $agLabel = $("#"+ id +" label");
	var $agUl = $("#"+ id +" ul");
	var $agWrap = $("#"+ id +" div.jqTransformSelectWrapper");
	var $agSpan = $("#"+ id +" span");
	var $agSelect = $("#"+ id +" select");
	var agSelectInner, agLiInner;
			
	$agUl.empty();
	$agSpan.empty();
	$agSelect.empty();
	for (var i = 0, c = nodes.length; i < c; i++) {
		agLiInner = "<li><a href=\"#\" index=\"" + i + "\"";
		if(i == 0) {
			agLiInner += "class=\"selected\"";
			$agSpan.append(nodes[i].getAttribute("nom"));
		}
		agLiInner += ">" + nodes[i].getAttribute("nom") + "</a></li>";
		$agUl.append(agLiInner);
		
		agSelectInner = "<option value=\"" + nodes[i].getAttribute("nom") + "\"";
		if(i == 0) {
			agSelectInner += "selected=\"selected\"";
		}
		agSelectInner += ">" + nodes[i].getAttribute("nom") + "</option>";
		$agSelect.append(agSelectInner);
	}
			
	$agUl.find('a').click(function() {
		$('a.selected', $agWrap).removeClass('selected');
		$(this).addClass('selected');
		if($agSelect[0].selectedIndex != $(this).attr('index') && $agSelect[0].onchange) {
			$agSelect[0].selectedIndex = $(this).attr('index');
			$agSelect[0].onchange();
		}
		$agSelect[0].selectedIndex = $(this).attr('index');
		$('span:eq(0)', $agWrap).html($(this).html());
		$agUl.hide();
		return false;
	});
	
	$('a:eq('+ $agSelect.selectedIndex +')', $agUl).click();
	//$('span:first', $agWrap).click(function(){$("a.jqTransformSelectOpen",$agWrap).trigger('click');});
	
	var hideSelect = function(oTarget){
		var ulVisible = $('.jqTransformSelectWrapper ul:visible');
		ulVisible.each(function(){
			var oSelect = $(this).parents(".jqTransformSelectWrapper:first").find("select").get(0);
			//do not hide if click on the label object associated to the select
			if( !(oTarget && oSelect.agLabel && oSelect.agLabel.get(0) == oTarget.get(0)) ){$(this).hide();}
		});
	};
	
	var oLinkOpen = $('a.jqTransformSelectOpen', $agWrap).click(function(){
		if( $agUl.css('display') == 'none' ) {hideSelect();} 
		if($agSelect.attr('disabled')){return false;}

		$agUl.slideToggle('fast', function(){					
			var offSet = ($('a.selected', $agUl).offset().top - $agUl.offset().top);
			$agUl.animate({scrollTop: offSet});
		});
		return false;
	});
			
	var agWidth = $agSelect.outerWidth();
	var oSpan = $('span:first',$agWrap);
	//var newWidth = (agWidth > oSpan.innerWidth())?agWidth+oLinkOpen.outerWidth():$agWrap.width();
	
			
	$agUl.css({display:"block", visibility:"hidden"});
	var agUlHeight = nodes.length * $("#"+ id +" li:eq(1)").height();
	//(agUlHeight < $agUl.height()) && $agUl.css({height:agUlHeight,'overflow':'hidden'});
	$agUl.css({height:agUlHeight,'overflow':'hidden'});
	
	$agWrap.animate({width: agWidth}, 500);
	$agUl.animate({width: agWidth-2}, 500);
	$agSpan.animate({width: agWidth}, 500);
	$agUl.css({display:"none", visibility:"visible"});
	$agDiv.css("visibility", "visible");
}

function readAgents(agents, id) {
	var nodes = $('agent', agents);
	var $agDiv = $("#"+ id +"");
	var $agUl = $("#"+ id +" ul");
	var $agWrap = $("#"+ id +" div.jqTransformSelectWrapper");
	var $agSpan = $("#"+ id +" span");
	var $agSelect = $("#"+ id +" select");
	var agSelectInner, agLiInner;
			
	$agUl.empty();
	$agSpan.empty();
	$agSelect.empty();
	for (var i = 0, c = nodes.length; i < c; i++) {
		agLiInner = "<li><a href=\"#\" index=\"" + i + "\"";
		if(i == 0) {
			agLiInner += "class=\"selected\"";
			$agSpan.append(nodes[i].getAttribute("nom"));
		}
		agLiInner += ">" + nodes[i].getAttribute("nom") + "</a></li>";
		$agUl.append(agLiInner);
		
		agSelectInner = "<option value=\"" + nodes[i].getAttribute("nom") + "\"";
		if(i == 0) {
			agSelectInner += "selected=\"selected\"";
		}
		agSelectInner += ">" + nodes[i].getAttribute("nom") + "</option>";
		$agSelect.append(agSelectInner);
	}
			
	$agUl.find('a').click(function() {
		$('a.selected', $agWrap).removeClass('selected');
		$(this).addClass('selected');
		if($agSelect[0].selectedIndex != $(this).attr('index') && $agSelect[0].onchange) {
			$agSelect[0].selectedIndex = $(this).attr('index');
			$agSelect[0].onchange();
		}
		$agSelect[0].selectedIndex = $(this).attr('index');
		$('span:eq(0)', $agWrap).html($(this).html());
		$agUl.hide();
		return false;
	});
			
	var agWidth = $agSelect.outerWidth() + 50;
	$agWrap.animate({
		width: agWidth
	}, 500);
	$agSpan.animate({
		width: agWidth
	}, 500);
	$agUl.animate({
		width: agWidth
	}, 500);
			
	$agUl.css({display:"block", visibility:"hidden"});
	var agUlHeight = nodes.length * $("#"+ id +" li:first").height();
	$agUl.css("height", agUlHeight);
	$agUl.css({display:"none", visibility:"visible"});
	$agDiv.css("visibility", "visible");
}

function selectStatut(statut) {
	if(statut == "En attente") {
		$("#manque").slideDown(500);
	} else {
		$("#manque").slideUp(500);
	}
}

function addNewFile(element) {
	var newfile = "<tr id=\"newTR\" style=\"display: none;\"><td colspan=\"8\" >";
	newfile += "<div class=\"plus-frame-left\">";
	newfile += "<div class=\"plus-frame-right\">";
	newfile += "<div class=\"plus-frame-top\"><div></div></div>";
	newfile += "<form action=\"#\" method=\"POST\" >";
	newfile += "<div class=\"forminput\"><label>N&deg; de dossier : </label><input type=\"text\" id=\"filenum\" name=\"filenum\"/></div>";
	newfile += "<div class=\"forminput\"><label>Indice : </label><input type=\"text\" id=\"fileind\" name=\"fileind\" size=\"3\"/></div>";
	newfile += "<div class=\"forminput\"><label>Nom de l'affaire : </label><input type=\"text\" id=\"filename\" name=\"filename\"/></div>";
	newfile += "<div class=\"clear\"><br /></div>";
	newfile += "<div class=\"forminput statut\"><label>Statut : </label><div class=\"left\"><select name=\"filestatut\"><option >En cours</option><option >En attente</option></select></div></div>";
	newfile += "<div class=\"forminput\"><label class=\"rouge\" >Traitement en urgence : </label><input type=\"checkbox\" name=\"fileurgence[]\" value=\"1\" /></div>";
	newfile += "<div class=\"clear\"><br /></div>";
	newfile += "<div class=\"forminput listhide\" id=\"manque\"><label>El&eacute;ments manquants : </label><textarea id=\"filemanque\" name=\"filemanque\" cols=80 rows=6></textarea></div>";
	newfile += "<div class=\"clear\"><br /></div>";
	newfile += "<div id=\"Agcn\" class=\"forminput agence left\"><label>Agence : </label><select name='fileagence'><option ></option></select></div>";
	newfile += "<div class=\"forminput agents\" id=\"Ags\" style=\"visibility:hidden;\"><label>Agent : </label><select name=\"fileagent\" ></select></div>";
	newfile += "<div class=\"right\"><img id=\"validateNewFile\" src=\"images/alu-validate_50x50.png\" alt=\"Valider\" style=\"cursor: pointer; margin: 0 10px;\" /><img id=\"cancelNewFile\" src=\"images/alu-cancel_50x50.png\" alt=\"Annuler\" style=\"cursor: pointer; margin: 0 10px;\" /></div>";
	newfile += "<div class=\"clear\"><br /></div>";
	newfile += "</div></div></form></td></tr>";
	
	element.after(newfile);
	//$("#newTR td").css("padding", "20px");
	$("#newTR").slideDown(500);
	return true;
}