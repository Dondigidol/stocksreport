<?php
$lm=$_POST["lm"];
$kol=$_POST["kol"];

//============����������� � MySQL====================================================
$link=mysql_connect("localhost","leroy","locadm") or die("�� ���� ����������� � MySQL.");
mysql_select_db("leroy_stocksreport",$link) or die("�� ���� ������������ � ����.");
mysql_query("SET NAMES 'utf8'");

//-----------------------------------------------------------------------------------


mysql_query("INSERT INTO capacity (lm, kol) VALUES ('".$lm."', ".$kol.") ON DUPLICATE KEY UPDATE kol=".$kol);


?>