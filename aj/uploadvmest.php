<?php
session_start();
if (isset($_SESSION['root']) == false)
{
	header ('Location: index.php');
	session_destroy();
}
require_once 'Classes/PHPExcel.php';
require_once '../connect.php';

$config_file = 'config.ini';

$filename = $_POST["filename"];

$path = "../jquery-file-upload/server/php/files/" . $filename;
if (file_exists($path))
{
	connect_to_mysql($config_file);
	
	$j = 0;
	$objPHPExcel= new PHPExcel();
	$xls=PHPExcel_IOFactory::load($path);
	$xls->setActiveSheetIndex(0);
	$sheet=$xls->getActiveSheet();
	$lastRow=$sheet->getHighestRow();
	for ($row = 1; $row<=$lastRow; $row++)
	{
		$lm=$sheet->getCell('A'.$row);
		$kol=$sheet->getCell('B'.$row);
		$query=mysql_query('INSERT INTO capacity (lm, kol) VALUES (' . $lm . ', ' . $kol .') ON DUPLICATE KEY UPDATE kol = ' . $kol);
		if ($query) {$j=$j+1;}
	
	}
	
	unlink($path);
}
?>