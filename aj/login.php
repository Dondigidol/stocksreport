<?php
session_start();
$server = 'blogon3.hq.ru.corp.leroymerlin.com';
$port = '389';
$user = $_POST["user"];
$userPW = $_POST["userPW"];
$dn = 'OU=Shops,OU=Leroy Merlin Vostok,DC=hq,DC=ru,DC=corp,DC=leroymerlin,DC=com';

// Подключаемся к серверу
$conn = ldap_connect($server, $port) or die('Ошибка: невозможно подключиться к LDAP-серверу');
// Устанавливаем версию протокола (странно, но по умолчанию не 3-я версия)
ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

// Подсоединение
ldap_bind($conn, "ru1000\\".$user, $userPW) or die('Ошибка: введен некорректный логин, либо пароль');

$result = ldap_search($conn,$dn, "(sAMAccountName=$user)") or die ("Ошибка поиска: ".ldap_error($conn));

//echo $data[0]["title"][0] . "||" . $data[0]["displayname"][0];
$jobs = array('специалист по информационным технологиям',
				'менеджер отдела',
				'контролер управления',
				'Директор магазина',
				'руководитель торгового сектора',
				'руководитель сектора логистики');

if ($result)
{
	$data = ldap_get_entries($conn, $result);
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
	echo("true");
}


        
ldap_close($conn);
?>