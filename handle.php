<?php
session_start();
if (isset($_SESSION["ldap"]) == False){
	session_destroy();
	header ('Location: index.php');
}
else
{
	$ldap = $_SESSION["ldap"];	
	$username = $_SESSION['username'];
	$root = $_SESSION['root'];
	$job = $_SESSION['job'];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
 <html>
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="css/index.css">
		<script src="jquery-file-upload/js/jquery.js"></script>
		<script src="jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
		<script src="jquery-file-upload/js/jquery.iframe-transport.js"></script>
		<script src="jquery-file-upload/js/jquery.fileupload.js"></script>
        <title>Отчет по стокам</title>
		<style type = text/css>
			#jobs {
				width: 250px;
				font: 10px Helvetica, Arial, sans-serif;
				text-align: right;
				color: blue;
			}
			#userinfo {
				width: 250px;
				font: 12px Helvetica, Arial, sans-serif;
				text-align: right;
			}
			
			.buttons {
				border: 1px solid #008000;
				padding: 5px;
				display: inline-block;
				text-align: center;
				background: #00b300;
				color: #ffffff;
				cursor: pointer;
				
			}
			.buttons:hover {
				background: #89d676;
			}

			.generalbutton {
				border: 1px solid #99001f;
				padding: 5px;
				display: inline-block;
				text-align: center;
				background: #ff2e2e;
				color: #ffffff;
				cursor: pointer;
			}
			.generalbutton:hover {
				background: #ff6685;
			}
			
			
			label.uploadbutton input {
			  display: none;
			}

			label.uploadbutton .button {
				border: 1px solid #008000;
				padding: 5px;
				display: inline-block;
				text-align: center;
				background: #00b300;
				color: #ffffff;
			}

			label.uploadbutton .button:active {
			  background: green;
			}
			
			.bar {
				height: 4px;
				background: blue;
			}
			
			#progress {
				top: 0px;
				heigth: 4px;
				background: white;
				top: 0px;
				visibility: hidden;
			}
						
		</style>
    </head>
	<body align="center">
<?php
//ширина столбцов
$lmw='70px';
$namew='200px';
$groupw='60px';
$typew='60px';
$subtypew='60px';
$kolw='60px';
$rdw='60px';
$rmw='60px';
$emw='60px';
$lsw='60px';
$topstockw='60px';
$polkaw='60px';
$facew='60px';
$freeplacew='60px';
$avg_salew='60px';
$zapasw='60px';
//-----------------

function sel($con){
	echo "<select id='".$con."' onchange='change()'>"
		."	<option></option>"
		."	<option>></option>"
		."	<option><</option>"
		."	<option>=</option>"
		."</select>";
}



?>
<div id='menupanel'>
	<table border = 0px><tr>
		<td width=35px>Отдел</td>
		<td width=35px>
			<select id="otdel" width="40px">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
			</select>
		</td>
		<td width=10px></td>
		<td width=30px>ТОП</td>
		<td width=30px>
			<select id="top">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="0">0</option>
			</select>
		</td>
		<td width = 120px>
			<div onclick="getData();" class = "generalbutton">Сформировать</div>
		</td>
		<td>
			

			<?php 
				if ($root == 1)
				{
					echo ('	<div style = "display: inline-block; padding-top:6px;"><label class="uploadbutton">
									<div class="buttons">Загрузить вместимость</div>
								  	<input id="fileupload" type="file" name="files[]" data-url="jquery-file-upload/server/php/" accept = ".xls,.xlsx" multiple>					  
							</label>
							<div id="progress">
								<div class="bar" style="width: 0%;"></div>
							</div>
							</div>
							<div class="buttons" style = "display: inline-block;" onclick="exportData()">Выгрузить отчет</div>			
							'); 
					
					
				}
			?>
		</td>
		<td width=250px>
			<div id = "userinfo"><?php echo($ldap . "(" . $username . ")");?></div>
			<div id = "jobs"><?php echo($job);?></div> 
		</td>
		<td width = 30px>
			<a href = "index.php">выйти</a>
		</td>
	</tr></table>
</div>
<div id='main'>
<div id="tableheader">
		<table class = "table">
		<tr style='background: #9cb1f0;'>			
			<td rowspan=3 width='<?php echo $lmw;?>'>ЛМ</td>
			<td rowspan=3 width='<?php echo $namew;?>'>Наименование</td>
			<td rowspan=3 width='<?php echo $groupw;?>'>Группа</td>
			<td rowspan=3 width='<?php echo $typew;?>'>Тип</td>
			<td rowspan=3 width='<?php echo $subtypew;?>'>Подтип</td>
			<td colspan=6>Размещение</td>
			<td rowspan=3 width='<?php echo $polkaw;?>'>На полке</td>
			<td rowspan=3 width='<?php echo $facew;?>'>Кол-во в одном фейсе</td>
			<td rowspan=3 width='<?php echo $freeplacew;?>'>Кол-во свободного места, шт.</td>
			<td rowspan=3 width='<?php echo $avg_salew;?>'>Среднедневная продажа</td>
			<td rowspan=3 width='<?php echo $zapasw;?>'>Запас в днях на полке</td>
		</tr>
		<tr style='background: #9cb1f0;'>
			<td rowspan=2 width='<?php echo $kolw;?>'>Запас всего</td>
			<td colspan=3>Склад</td>
			<td colspan=2>ТЗ</td>
		</tr>
		<tr  style='background: #9cb1f0;'>
			<td width='<?php echo $rdw;?>'>RD</td>
			<td width='<?php echo $rmw;?>'>RM</td>
			<td width='<?php echo $emw;?>'>EM</td>
			<td width='<?php echo $lsw;?>'>LS</td>
			<td width='<?php echo $topstockw;?>'>Верхний сток</td>
		</tr>
		<tr style='background: #cbd6f7; '>
			<td>=</td>
			<td>содержит</td>
			<td><?php sel("conGroup");?></td>
			<td><?php sel("conType");?></td>
			<td><?php sel("conSubtype");?></td>
			<td><?php sel("conKol");?></td>
			<td><?php sel("conRd");?></td>
			<td><?php sel("conRm");?></td>
			<td><?php sel("conEm");?></td>
			<td><?php sel("conLs");?></td>
			<td><?php sel("conStock");?></td>
			<td><?php sel("conPolka");?></td>
			<td><?php sel("conFace");?></td>
			<td><?php sel("conFree");?></td>
			<td><?php sel("conSale");?></td>
			<td><?php sel("conZapas");?></td>
		</tr>
		<tr style='background: #cbd6f7;'>
			<td><input type=number style='width: <?php echo (((int)explode('px', $lmw)[0])-6); ?>px' id='film' oninput='change()'></input></td>
			<td><input type=text style='width: <?php echo (((int)explode('px', $namew)[0])-6); ?>px' id='finame' oninput='change()'></input></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $groupw)[0])-6); ?>px' id='figroup' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $typew)[0])-6); ?>px' id='fitype' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $subtypew)[0])-6); ?>px' id='fisubtype' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $kolw)[0])-6); ?>px' id='fikol' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $rdw)[0])-6); ?>px' id='fird' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $rmw)[0])-6); ?>px' id='firm' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $emw)[0])-6); ?>px' id='fiem' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $lsw)[0])-6); ?>px' id='fils' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $topstockw)[0])-6); ?>px' id='fistock' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $polkaw)[0])-6); ?>px' id='fipolka' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $facew)[0])-6); ?>px' id='fiface' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $freeplacew)[0])-6); ?>px' id='fifree' oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $avg_salew)[0])-6); ?>px' id='fisale'oninput='change()'></td>
			<td><input type=number style='width: <?php echo (((int)explode('px', $zapasw)[0])-6); ?>px' id='fizapas' oninput='change()'></td>
		</tr>
		</table>
</div>
<div id="tablecontent"></div>
</div>
<div id="footer"></div>	

	



<script>
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        add: function (e, data) {
			$("#progress").css({"visibility": "visible"});
            data.submit();			
        },
        done: function (e, data) {
			mod_mes("<img src='img/progress.gif' width=100 height=100>");
            $("#progress").css({"visibility": "hidden"});
			$.each(data.files, function (index, file) {
				$.ajax({
					type: "POST",
					url: "aj/uploadface.php",
					data: {filename: file.name},
					success: function(data){
						$(".modal_bg, .modal_window").hide();
						if ($("#tablecontent").html() != "")
						{
							getData();
						}
						
					},
					error: function(){
						$(".modal_bg, .modal_window").hide();
					}
					
				});
			});
        },
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#progress .bar').css(
				'width',
				progress + '%'
			);
		}
			
    });	
});

$(document).ready(function(){
	$("#main").css({"height":$(document).height() - $("#menupanel").height() - $("#footer").height() + "px", "width":$("#tableheader").width() + "px"});
	$("#tableheader").css({"width":$(".table").width() + 17 + "px"});
	$("#tablecontent").css({"height":$("#main").height() - $("#tableheader").height() + "px", "width":$("#tableheader").width() + "px"});
	$(".modal_bg, .modal_window").hide();
});

function oper(oper, val)
{
	if (val.length > 0)
	{
		switch(oper)
		{
			case '>':
				return '>' + val;
				break;
			case '<':
				return '<' + val;
				break;
			case '=':
				return '=' + val;
				break;
			default:
				return '';
				break;
		}
	}
	else return '';
		
	
}

function exportData()
{	
	var otdel=$("#otdel").val();
	var top=$("#top").val();
	var film = $("#film").val();
	var finame = $("#finame").val();
	var figroup = oper($("#conGroup").val(), $("#figroup").val());
	var fitype = oper($("#conType").val(), $("#fitype").val());
	var fisubtype = oper($("#conSubtype").val(), $("#fisubtype").val());
	var fikol = oper($("#conKol").val(), $("#fikol").val());
	var fird = oper($("#conRd").val(), $("#fird").val());
	var firm = oper($("#conRm").val(), $("#firm").val());
	var fiem = oper($("#conEm").val(), $("#fiem").val());
	var fils = oper($("#conLs").val(), $("#fils").val());
	var fistock = oper($("#conStock").val(), $("#fistock").val());
	var fipolka = oper($("#conPolka").val(), $("#fipolka").val());
	var fiface = oper($("#conFace").val(), $("#fiface").val());
	var fifree = oper($("#conFree").val(), $("#fifree").val());
	var fisale = oper($("#conSale").val(), $("#fisale").val());
	var fizapas = oper($("#conZapas").val(), $("#fizapas").val());

	document.location.href="aj/downloadreport.php?otdel=" + otdel + "&top=" + top + "&film=" + film + "&finame=" + finame + "&figroup=" + figroup + "&fitype=" + fitype + "&fisubtype=" + fisubtype + "&fikol=" + fikol + "&fird=" + fird + "&firm=" + firm + "&fiem=" + fiem + "&fils=" + fils + "&fistock=" + fistock + "&fipolka=" + fipolka + "&fiface=" + fiface + "&fifree=" + fifree + "&fisale=" + fisale + "&fizapas=" + fizapas;

	
}

function mod_window_position()
{
	var Top_modal_window = $(document).height()/2-$(".modal_window").height()/2;
	var Left_modal_window = $(document).width()/2-$(".modal_window").width()/2;
	$(".modal_window").css({"top":Top_modal_window+"px","display":"block", "left":Left_modal_window+"px"});
}

function getData()
{
	var otdel=$("#otdel").val();
	var top=$("#top").val();
	mod_mes("<img src='img/progress.gif' width=100 height=100>");
	$.ajax({
		type: "POST",
		url: "aj/getdata.php",
		data: {"otdel": otdel, "top": top,
		"topw": "<?php echo $topw;?>", 
		"lmw": "<?php echo $lmw;?>",
		"namew": "<?php echo $namew;?>", 
		"groupw": "<?php echo $groupw;?>",
		"typew": "<?php echo $typew;?>",
		"subtypew": "<?php echo $subtypew;?>",
		"kolw": "<?php echo $kolw;?>", 
		"rdw": "<?php echo $rdw;?>", 
		"rmw": "<?php echo $rmw;?>",
		"emw": "<?php echo $emw;?>",
		"lsw": "<?php echo $lsw;?>",
		"topstockw": "<?php echo $topstockw;?>",
		"polkaw": "<?php echo $polkaw;?>",
		"facew": "<?php echo $facew;?>",
		"freeplacew": "<?php echo $freeplacew;?>",
		"avg_salew": "<?php echo $avg_salew;?>",
		"zapasw": "<?php echo $zapasw;?>" },
		success: function(data){
			try
			{
				$("#tablecontent").html(data);
				change();
			}
			finally
			{
				$(".modal_bg, .modal_window").hide();				
			}
			
			
		}
	});

	
}

function addface(editid)
{
	var position = editid.id.split("position");
	document.getElementById(position[0]+"face"+position[1]).disabled=0;
	document.getElementById(position[0]+"face"+position[1]).focus();
	
}

function saveface(editid)
{
	
	var position=editid.id.split("face");
	lm=position[1];
	pos=position[0];
	
	var kol=document.getElementById(editid.id).value;
	if (kol.length=0){
		kol='0';
	}	
	
	$.ajax({
		type: "POST",
		url: "aj/facetobase.php",
		data: {"lm":lm, "kol": kol},
		success: function(){
			document.getElementById(editid.id).disabled=1;
			$("#free" + pos).html(Number(kol) - Number($("#polka" + pos).html()));
			
		}	
	});
	
}

function mod_mes(mes)
{
	$(".modal_bg").css({//устанвливается высота и ширина фона модального окна, значения равны ширине и высоте окна браузера
		"width":$(document).width(), 
		"height":$(document).height()
	});
	document.getElementById("mes").innerHTML=mes;
	$(".modal_bg").fadeTo("fast", 0.6); //заполнение фона быстрое на 50% прозрачности
	mod_window_position();
	$("body").css({"overflow":"hidden"});	
}
	
function qwe(op1, op2, oper)
{
	if (op2!=""){
		op1 = parseFloat(op1);
		op2 = parseFloat(op2);
		if (oper=="="){
			var res = op1==op2;
		}else if (oper==">"){
			var res = op1 > op2;
		}else if(oper=="<"){
			var res = op1<op2;
		}else res=true;
	} else {
		res=true;
	}
	
	
	return res;
}

function change()
{
	// получаем значения select у столбцов с количеством
	var congroup=$("#conGroup").val();
	var contype=$("#conType").val();
	var consubtype=$("#conSubtype").val();
	var conkol=$("#conKol").val();
	var conrd=$("#conRd").val();
	var conrm=$("#conRm").val();
	var conem=$("#conEm").val();
	var conls=$("#conLs").val();
	var constock=$("#conStock").val();
	var conpolka=$("#conPolka").val();
	var conface=$("#conFace").val();
	var confree=$("#conFree").val();
	var consale=$("#conSale").val();
	var conzapas=$("#conZapas").val();
	
	
	
	var film=$("#film").val();
	var finame=$("#finame").val();
	var fifroup=$("#figroup").val();
	var fitype=$("#fitype").val();
	var fisubtype=$("#fisubtype").val();
	var fikol=$("#fikol").val();
	var fird=$("#fird").val();
	var firm=$("#firm").val();
	var fiem=$("#fiem").val();
	var fils=$("#fils").val();
	var fistock=$("#fistock").val();
	var fipolka=$("#fipolka").val();
	var fiface=$("#fiface").val();
	var fifree=$("#fifree").val();
	var fisale=$("#fisale").val();
	var fizapas=$("#fizapas").val();
	
	
	var templm;
	var tempname;
	var tempgroup;
	var temptype;
	var tempsubtype;
	var tempkol;
	var temprd;
	var temprm;
	var tempem;
	var templs;
	var tempstock;
	var temppolka;
	var tempface;
	var tempfree;
	var tempsale;
	var tempzapas;
	
	var sort_kol = 0;
	var rows=document.querySelectorAll(".rowel");
		
	for (var i=0; i<rows.length; i++){
		templm=$("#lm"+i).html();
		tempname=$("#name"+i).html();
		tempgroup=$("#group"+i).html();
		temptype=$("#type"+i).html();
		tempsubtype=$("#subtype"+i).html();
		tempkol=$("#kol"+i).html();
		temprd=$("#rd"+i).html();
		temprm=$("#rm"+i).html();
		tempem=$("#em"+i).html();
		templs=$("#ls"+i).html();
		tempstock=$("#stock"+i).html();
		temppolka=$("#polka"+i).html();
		if ($("input").is(".face"+i))
		{
			tempface=$(".face"+i).val();
		}
		else
		{
			tempface=$("#face"+i).html();
		}
		
		tempfree=$("#free"+i).html();
		tempsale=$("#sale"+i).html();
		tempzapas=$("#zapas"+i).html();
		
		if ((templm.indexOf(film)!=-1) // поиск по лм
			&& (tempname.toLowerCase().indexOf(finame.toLowerCase())!=-1) // поиск по наименованию
			&& (qwe(tempgroup, figroup, congroup)==true) //поиск по товарной группе
			&& (qwe(temptype, fitype, contype)==true) //поиск по товарному типу
			&& (qwe(tempsubtype, fisubtype, consubtype)==true) //поиск по товарному подтипу
			&& (qwe(tempkol, fikol, conkol)==true) //поиск по общему количеству
			&& (qwe(temprd, fird, conrd)==true) //поиск по количеству rd
			&& (qwe(temprm, firm, conrm)==true) //поиск по количеству rd
			&& (qwe(tempem, fiem, conem)==true) //поиск по количеству rd
			&& (qwe(templs, fils, conls)==true) //поиск по количеству rd
			&& (qwe(tempstock, fistock, constock)==true) //поиск по количеству rd
			&& (qwe(temppolka, fipolka, conpolka)==true) //поиск по количеству rd
			&& (qwe(tempface, fiface, conface)==true) //поиск по количеству rd
			&& (qwe(tempfree, fifree, confree)==true) //поиск по количеству rd
			&& (qwe(tempsale, fisale, consale)==true) //поиск по количеству rd
			&& (qwe(tempzapas, fizapas, conzapas)==true) //поиск по количеству rd
			)
		{
			sort_kol = sort_kol + 1;
			$("#row"+i).show();			
		}
		else 
		{
			$("#row"+i).hide();
		}
		
	}
	
	$('#footer').html(sort_kol + ' артикула(ов)');
}


</script>

<div class="modal_bg"></div>
<div class="modal_window">
	<div id="mes"></div>
</div>



</body>
</html>



