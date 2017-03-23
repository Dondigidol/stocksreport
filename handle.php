<?php
session_start();
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
if (isset($_SESSION["ldap"]) == False){
	header ('Location: index.php');
	session_destroy();
}
else
{
	$ldap = $_SESSION["ldap"];	
	$username = $_SESSION['username'];
	$root = $_SESSION['root'];
	$job = $_SESSION['job'];
}

//ширина столбцов
//$topw='50px';
$lmw='85px';
$namew='200px';
$kolw='80px';
$rdw='80px';
$rmw='80px';
$emw='80px';
$lsw='80px';
$topstockw='80px';
$polkaw='80px';
$capacityw='80px';
$freeplacew='80px';
$avg_salew='80px';
$zapasw='80px';
//-----------------

function sel($con){
	echo "<select class='filters' id='".$con."' onchange='change()'>"
		."	<option></option>"
		."	<option>больше</option>"
		."	<option>меньше</option>"
		."	<option>равно</option>"
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
		<table>
		<tr style='background: #9cb1f0;'>			
			<td rowspan=3 width='<?php echo $lmw;?>'>ЛМ</td>
			<td rowspan=3 width='<?php echo $namew;?>'>Наименование</td>
			<td colspan=6>Размещение</td>
			<td rowspan=3 width='<?php echo $polkaw;?>'>Полка</td>
			<td rowspan=3 width='<?php echo $capacityw;?>'>Вместимость полки</td>
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
			<td><?php sel("conKol");?></td>
			<td><?php sel("conRd");?></td>
			<td><?php sel("conRm");?></td>
			<td><?php sel("conEm");?></td>
			<td><?php sel("conLs");?></td>
			<td><?php sel("conStock");?></td>
			<td><?php sel("conPolka");?></td>
			<td><?php sel("conCap");?></td>
			<td><?php sel("conFree");?></td>
			<td><?php sel("conSale");?></td>
			<td><?php sel("conZapas");?></td>
		</tr>
		<tr style='background: #cbd6f7;'>
			<td><input type=number style='width: 75px;' id='film' oninput='change()'></input></td>
			<td><input type=text style='width: 190px;' id='finame' oninput='change()'></input></td>
			<td><input type=number id='fikol' oninput='change()'></td>
			<td><input type=number id='fird' oninput='change()'></td>
			<td><input type=number id='firm' oninput='change()'></td>
			<td><input type=number id='fiem' oninput='change()'></td>
			<td><input type=number id='fils' oninput='change()'></td>
			<td><input type=number id='fistock' oninput='change()'></td>
			<td><input type=number id='fipolka' oninput='change()'></td>
			<td><input type=number id='ficap' oninput='change()'></td>
			<td><input type=number id='fifree' oninput='change()'></td>
			<td><input type=number id='fisale'oninput='change()'></td>
			<td><input type=number id='fizapas' oninput='change()'></td>
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
					url: "aj/uploadvmest.php",
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
	$("#main").css({"height":$(document).height() - $("#menupanel").height() - $("#footer").height() + "px"});
	$("#tablecontent").css({"height":$("#main").height() - $("#tableheader").height() + "px"});
	$(".modal_bg, .modal_window").hide();
});

function oper(oper, val)
{
	if (val.length > 0)
	{
		switch(oper)
		{
			case 'больше':
				return '>' + val;
				break;
			case 'меньше':
				return '<' + val;
				break;
			case 'равно':
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
	var fikol = oper($("#conKol").val(), $("#fikol").val());
	var fird = oper($("#conRd").val(), $("#fird").val());
	var firm = oper($("#conRm").val(), $("#firm").val());
	var fiem = oper($("#conEm").val(), $("#fiem").val());
	var fils = oper($("#conLs").val(), $("#fils").val());
	var fistock = oper($("#conStock").val(), $("#fistock").val());
	var fipolka = oper($("#conPolka").val(), $("#fipolka").val());
	var ficap = oper($("#conCap").val(), $("#ficap").val());
	var fifree = oper($("#conFree").val(), $("#fifree").val());
	var fisale = oper($("#conSale").val(), $("#fisale").val());
	var fizapas = oper($("#conZapas").val(), $("#fizapas").val());

	document.location.href="aj/downloadreport.php?otdel=" + otdel + "&top=" + top + "&film=" + film + "&finame=" + finame + "&fikol=" + fikol + "&fird=" + fird + "&firm=" + firm + "&fiem=" + fiem + "&fils=" + fils + "&fistock=" + fistock + "&fipolka=" + fipolka + "&ficap=" + ficap + "&fifree=" + fifree + "&fisale=" + fisale + "&fizapas=" + fizapas;

	
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
		"kolw": "<?php echo $kolw;?>", 
		"rdw": "<?php echo $rdw;?>", 
		"rmw": "<?php echo $rmw;?>",
		"emw": "<?php echo $emw;?>",
		"lsw": "<?php echo $lsw;?>",
		"topstockw": "<?php echo $topstockw;?>",
		"polkaw": "<?php echo $polkaw;?>",
		"capacityw": "<?php echo $capacityw;?>",
		"freeplacew": "<?php echo $freeplacew;?>",
		"avg_salew": "<?php echo $avg_salew;?>",
		"zapasw": "<?php echo $zapasw;?>" },
		success: function(data){
			$("#tablecontent").html(data);
			change();
			$(".modal_bg, .modal_window").hide();
		}
	});

	
}

function addvmest(editid)
{
	var position = editid.id.split("position");
	document.getElementById(position[0]+"vmest"+position[1]).disabled=0;
	document.getElementById(position[0]+"vmest"+position[1]).focus();
	
}

function savevmest(editid)
{
	
	var position=editid.id.split("vmest");
	lm=position[1];
	pos=position[0];
	
	var kol=document.getElementById(editid.id).value;
	if (kol.length=0){
		kol='0';
	}	
	
	$.ajax({
		type: "POST",
		url: "aj/vmesttobase.php",
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
		op1 = parseInt(op1);
		op2 = parseInt(op2);
		if (oper=="равно"){
			var res = op1==op2;
		}else if (oper=="больше"){
			var res = op1 > op2;
		}else if(oper=="меньше"){
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
	var conkol=$("#conKol").val();
	var conrd=$("#conRd").val();
	var conrm=$("#conRm").val();
	var conem=$("#conEm").val();
	var conls=$("#conLs").val();
	var constock=$("#conStock").val();
	var conpolka=$("#conPolka").val();
	var concap=$("#conCap").val();
	var confree=$("#conFree").val();
	var consale=$("#conSale").val();
	var conzapas=$("#conZapas").val();
	
	
	var film=$("#film").val();
	var finame=$("#finame").val();
	var fikol=$("#fikol").val();
	var fird=$("#fird").val();
	var firm=$("#firm").val();
	var fiem=$("#fiem").val();
	var fils=$("#fils").val();
	var fistock=$("#fistock").val();
	var fipolka=$("#fipolka").val();
	var ficap=$("#ficap").val();
	var fifree=$("#fifree").val();
	var fisale=$("#fisale").val();
	var fizapas=$("#fizapas").val();
	
	var templm;
	var tempname;
	var tempkol;
	var temprd;
	var temprm;
	var tempem;
	var templs;
	var tempstock;
	var temppolka;
	var tempcap;
	var tempfree;
	var tempsale;
	var tempzapas;
	
	var sort_kol = 0;
	var rows=document.querySelectorAll(".rowel");
		
	for (var i=0; i<rows.length; i++){
		templm=$("#lm"+i).html();
		tempname=$("#name"+i).html();
		tempkol=$("#kol"+i).html();
		temprd=$("#rd"+i).html();
		temprm=$("#rm"+i).html();
		tempem=$("#em"+i).html();
		templs=$("#ls"+i).html();
		tempstock=$("#stock"+i).html();
		temppolka=$("#polka"+i).html();
		tempcap=$(".cap"+i).val();
		tempfree=$("#free"+i).html();
		tempsale=$("#sale"+i).html();
		tempzapas=$("#zapas"+i).html();
		
		if ((templm.indexOf(film)!=-1) // поиск по лм
			&& (tempname.toLowerCase().indexOf(finame.toLowerCase())!=-1) // поиск по наименованию
			&& (qwe(tempkol, fikol, conkol)==true) //поиск по общему количеству
			&& (qwe(temprd, fird, conrd)==true) //поиск по количеству rd
			&& (qwe(temprm, firm, conrm)==true) //поиск по количеству rd
			&& (qwe(tempem, fiem, conem)==true) //поиск по количеству rd
			&& (qwe(templs, fils, conls)==true) //поиск по количеству rd
			&& (qwe(tempstock, fistock, constock)==true) //поиск по количеству rd
			&& (qwe(temppolka, fipolka, conpolka)==true) //поиск по количеству rd
			&& (qwe(tempcap, ficap, concap)==true) //поиск по количеству rd
			&& (qwe(tempfree, fifree, confree)==true) //поиск по количеству rd
			&& (qwe(tempsale, fisale, consale)==true) //поиск по количеству rd
			&& (qwe(tempzapas, fizapas, conzapas)==true) //поиск по количеству rd
			)
		{
			sort_kol = sort_kol + 1;
			$("#row"+i).css({"display":"block"});			
		}
		else 
		{
			$("#row"+i).css({"display":"none"});
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



