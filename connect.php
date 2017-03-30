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
	var $database;
	var $user;
	var $userPW;
	var $conn;
	function get_params($ini_file)
	{
		$params_arr = parse_ini_file($ini_file);
		isset($params_arr['mssql_server']) ? $this->server = $params_arr['mssql_server'] : die('В файле конфигурации нет данных о MSSQL сервере');
		isset($params_arr['mssql_database']) ? $this->database = $params_arr['mssql_database'] : die('В файле конфигурации нет данных о подключаемой базе MSSQL сервера');
		isset($params_arr['mssql_user']) ? $this->user = $params_arr['mssql_user'] : die('В файле конфигурации нет данных о логине подключения к MSSQL серверу');
		isset($params_arr['mssql_password']) ? $this->userPW = $params_arr['mssql_password'] : die('В файле конфигурации нет данных о пароле подключения к MSSQL серверу');
	}
	
	function set_connection()
	{
		try
		{
			$this->conn = odbc_connect('Driver={SQL Server};Server=' . $this->server . ';Database=' . $this->database . ';', $this->user, $this->userPW);
		
		}
		catch (Exception $e)
		{
			echo 'Ошибка при подключении к MSSQL серверу:' . $e->getMessage() . '\n';
		}
		
	}
	
	function sql_query($query_str)
	{
		if (isset($this->conn))
		{
			try 
			{					
				ini_set('max_execution_time', 2000);
				$arr = odbc_exec($this->conn, $query_str);
				$query_type = explode(' ', $query_str);
				if (strtoupper($query_type[0]) == 'SELECT')
				{
					$result = array();
					while($val = odbc_fetch_array($arr))
					{
						array_push($result, $val);
					}		
					return $result;
				}					
			}
			catch (Exception $e)
			{
				echo 'Ошибка при выполнении запроса' . $e->getmessage() . '\n';
			}
			finally
			{
				// return $result;//$data[];
			}
		}
	}
}

class mysql_connection
{
	var $server;
	var $database;
	var $user;
	var $userPW;
	var $conn;
	function get_params($ini_file)
	{
		$params_arr = parse_ini_file($ini_file);
		isset($params_arr['mysql_server']) ? $this->server = $params_arr['mysql_server'] : die('В файле конфигурации нет данных о MySQL сервере');
		isset($params_arr['mysql_database']) ? $this->database = $params_arr['mysql_database'] : die('В файле конфигурации нет данных о подключаемой базе MySQL сервера');
		isset($params_arr['mysql_user']) ? $this->user = $params_arr['mysql_user'] : die('В файле конфигурации нет данных о логине подключения к MySQL серверу');
		isset($params_arr['mysql_password']) ? $this->userPW = $params_arr['mysql_password'] : die('В файле конфигурации нет данных о пароле подключения к MySQL серверу');
	}
	
	function set_connection()
	{
		$this->conn = mysqli_connect($this->server, $this->user, $this->userPW, $this->database) or die("Невозможно подключиться к серверу MySQL!");
		mysqli_query($this->conn, "SET NAMES 'utf8'");
		ini_set('max_execution_time', 720);
		return $this->conn;
	}
	
	function sql_query($query_str)
	{
		if (isset($this->conn))
		{
			try 
			{					
				ini_set('max_execution_time', 2000);
				$arr = mysqli_query($this->conn, $query_str);
				$query_type = explode(' ', $query_str);
				if (strtoupper($query_type[0]) == 'SELECT')
				{
					$result = array();
					while($val = mysqli_fetch_array($arr))
					{
						array_push($result, $val);
					}
					return $result;
				}						
			}
			catch (Exception $e)
			{
				echo 'Ошибка при выполнении запроса' . $e->getmessage() . '\n';
			}
			finally
			{
				//$data[];
			}
		}		
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
	return $mssql;
}

function connect_to_mysql($ini_file)
{
	$mysql = new mysql_connection;
	$mysql->get_params($ini_file);
	$mysql->set_connection();
	return $mysql;
}
 
?>