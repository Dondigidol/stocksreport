<?php
session_start();
if (isset($_SESSION['ldap']))
{
	unset($_SESSION['ldap']);
}
if (isset($_SESSION['username']))
{
	unset($_SESSION['username']);
}
if (isset($_SESSION['job']))
{
	unset($_SESSION['job']);
}
if (isset($_SESSION['root']))
{
	unset($_SESSION['root']);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
    <html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="css/index.css">
        <title>Отчет по стокам</title>
		<style>
			.logo{
				width: 260px;
				height: 170px;
			}
			.logotext{
				color: #000000;
				font: 25px Tahoma, Arial;
			}
			
			.loginform {
				margin-top: -7px;
				width: 150px;
				background: #abe39d;
				padding: 15px;
			}
			
			.inputs{
				width: 150px;
				margin-bottom: 10px;
				font: 20px Helvetica, Arial, sans-serif;
			}
			
			.loginform form div {				
				position: relative;
			}
			
			.loginform form label{
				position: absolute;
				top: 5px;
				margin-left: 10px;
				color: #999999;
				font: 14px Helvetica, Arial, sans-serif;
			}
			
			.buttons{
				background: #4db035;
				border:1px solid #4cb034;
				font: 16px Helvetica, Arial, sans-serif;
				color: #ffffff;
				width: 140px;
			}
			.buttons:hover{
				background: #89d676;
			}
			
			.mainblock{
				width: 260px;
				height: 330px;
				position: absolute;
				top: 30%;
				left: 50%;
				margin-left: -130px;
				margin-top: -165px;
			}
			#alert{
				background: red;
				font: 14px Helvetica, Arial, sans-serif;
			}
			

		</style>
    </head>

    <body align="center">
        <script type='text/javascript' src='js/jquery.js'></script>

        <div align="center" class="mainblock">
            <img src="img/logo.png" class="logo">
            <div class="loginform">
                <form align="center" method="POST">
					<div>
						<label for="login">логин</label>
						<input name="login" id="login" type="text" class="inputs"/>
					</div>
					<div>
						<label for="pass">пароль</label>
						<input name="pass" id="pass" type="password" class="inputs"/>
					</div>
					<input type="button" onclick="btnlogin();" value="Войти" class="buttons"/>
                </form>				
            </div>
			<div class="logotext">ОТЧЕТ ПО СТОКАМ</div>
			<div id="alert"></div>
        </div>


<script>
function btnlogin(){
	
	$("#alert").html('');
	var ldap = $("#login").val();
	var ldappw = $("#pass").val();
	
	
	if ((ldap != "") && (ldappw != "")){
		
		$.ajax({
			type: 'POST',
			url: 'aj/login.php',
			data: {'user': ldap, 'userPW': ldappw},
			success: function(data) {
				if (data == "true")
				{
					location.replace("./handle.php");
				}
				else
				{
					$("#alert").html(data);
				}
			}
		});
	} else {
		$("#alert").html("Заполните все поля");
	}
}

$(document).ready(function(){
	//при загрузке страницы проверям заполненность полей (на случай если запоминалка паролей вставила уже значения)
	//если значения вставлены, удаляем текст у label для логина и пароля
	if($("#login").val()!='')
	{
		$("#login").prev().text('');
		$("#pass").prev().text('');
	}
	//тут привязано одно событие на получение фокуса полю в форме
	$("div > input").focus(
	function(e)
	{
		var clicked = $(e.target),
		clickedId = clicked.attr("id");		
		//если поле логин получило фокус, удаляем текст в label для логин		
		if(clickedId=="login")
		{
			 clicked.prev().text('');
		}
		//если поле пароль получило фокус, удаляем текст в label для пароль
		else if(clickedId=="pass")
		{
			 clicked.prev().text('');
		 }
	});

	// тут привязано одно событие на потерю фокуса полем в форме
	$("div > input").blur(
	function(e)
	{
		var clicked = $(e.target),
		clickedId = clicked.attr("id");
		// если ушли из поля логин и его значение пусто, добавляем текст в label для логин
		if(clickedId=="login")
		{
			if(clicked.val()=='') clicked.prev().text('логин');
		}
		//если ушли из поля пароль и его значение пусто, добавляем текст в label для пароль
		else if(clickedId=="pass")
		{
			if(clicked.val()=='') clicked.prev().text('пароль');
		}
	});
});

 </script>



        </div>
    </body>

    </html>