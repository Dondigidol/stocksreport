<?php
class ldap_connection
{
	var $server;
	var $port;
	var $conn;
	var $user;
	var $dn;
	
	function get_params($ini_file)
	{
		$params_arr = parse_ini_file($ini_file);
		isset($params_arr['ldap_server']) ? $this->server = $params_arr['ldap_server'] : die('В файле конфигурации нет данных о ldap-сервере');
		isset($params_arr['ldap_port']) ? $this->port = $params_arr['ldap_port'] : die('В файле конфигурации нет данных о порте ldap-сервера');
		isset($params_arr['ldap_dn']) ? $this->dn = $params_arr['ldap_dn'] : die('В файле конфигурации нет данных о структуре ldap-сервера');
	}
	
	function set_connection()
	{	
		isset($this->server) and isset($this->port) ? $this->conn = ldap_connect($this->server, $this->port) : die('Не указан сервер, либо порт подключения');
	}
	
	function set_bind($user, $userPW)
	{
		$this->user = $user;
		if (isset($this->conn))
		{
			ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_bind($this->conn, "ru1000\\".$user, $userPW) or die('Ошибка: введен некорректный логин, либо пароль');		
		}
		else die('Подключение не создано!');
		
	}
	
	function get_result()
	{	
		if (isset($this->conn) and isset($this->dn))
		{
			$result = ldap_search($this->conn, $this->dn, "(sAMAccountName=$this->user)") or die ("Ошибка поиска: ".ldap_error($this->conn));
			return ldap_get_entries($this->conn, $result);
		}
		else die('Подключение к ldap-серверу не готово!');
		
	}	
}

class mssql_connection
{
	var $server;
	var $base;
	var $user;
	var $userPW;
	function get_params($ini_file)
	{
		$params_arr = parse_ini_file($ini_file);
		isset($params_arr['mssql_server']) ? $this->server = $params_arr['mssql_server'] : die('В файле конфигурации нет данных о MSSQL сервере');
		isset($params_arr['mssql_base']) ? $this->base = $params_arr['mssql_base'] : die('В файле конфигурации нет данных о подключаемой базе MSSQL сервера');
		isset($params_arr['mssql_user']) ? $this->user = $params_arr['mssql_user'] : die('В файле конфигурации нет данных о логине подключения к MSSQL серверу');
		isset($params_arr['mssql_password']) ? $this->userPW = $params_arr['mssql_password'] : die('В файле конфигурации нет данных о пароле подключения к MSSQL серверу');
	}
	
	function set_connection()
	{
		$link = mssql_connect($this->server, $this->user, $this->userPW) or die("Невозможно подключиться к серверу MSSQL!");
		mssql_select_db($this->base, $link) or die("Невозможно подключиться к Базе Данных MSSQL!"); 
	}
}

function connect_to_ldap($user, $userPW, $ini_file)
{
	$ldap = new ldap_connection;
	$ldap->get_params($ini_file);
	$ldap->set_connection();
	$ldap->set_bind($user, $userPW);
	return $ldap->get_result();
}

function connect_to_mssql($ini_file)
{
	$mssql = new mssql_connection;
	$mssql->get_params($ini_file);
	$mssql->set_connection();
}
 
?>