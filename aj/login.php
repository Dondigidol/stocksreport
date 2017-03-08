<?php
session_start();
require_once '../connect.php';

$user = $_POST["user"];
$userPW = $_POST["userPW"];

$data = connect_to_ldap($user, $userPW, 'connect.ini');

$jobs = array('специалист по информационным технологиям',
				'менеджер отдела',
				'контролер управления',
				'Директор магазина',
				'руководитель торгового сектора',
				'руководитель сектора логистики');

if ($data)
{
	$_SESSION['ldap'] = $user;
	$_SESSION['username'] = $data[0]["displayname"][0];
	$_SESSION['job'] = $data[0]["title"][0];
	
	if (in_array($data[0]["title"][0], $jobs))
	{
		$_SESSION['root'] = 1;
	}
	else
	{
		$_SESSION['root'] = 0;
	}
	echo 'true';
}
?>