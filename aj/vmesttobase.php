<?php
require_once "../connect.php";

$config_file = 'config.ini';

$lm=$_POST["lm"];
$kol=$_POST["kol"];

connect_to_mysql($config_file);

mysql_query("INSERT INTO capacity (lm, kol) VALUES ('".$lm."', ".$kol.") ON DUPLICATE KEY UPDATE kol=".$kol);
?>