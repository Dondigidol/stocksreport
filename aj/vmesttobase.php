<?php
$lm=$_POST["lm"];
$kol=$_POST["kol"];

//============подключение к MySQL====================================================
$link=mysql_connect("localhost","leroy","locadm") or die("Не могу соединиться с MySQL.");
mysql_select_db("leroy_stocksreport",$link) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES 'utf8'");

//-----------------------------------------------------------------------------------


mysql_query("INSERT INTO capacity (lm, kol) VALUES ('".$lm."', ".$kol.") ON DUPLICATE KEY UPDATE kol=".$kol);


?>