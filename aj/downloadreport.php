<?php
session_start();

require_once "Classes/php_writeexcel/class.writeexcel_workbook.inc.php";
require_once "Classes/php_writeexcel/class.writeexcel_worksheet.inc.php";
header('Content-Type: text/html; charset=utf-8');

if (isset($_SESSION['root']) == false)
{
	header ('Location: ../index.php');
	session_destroy();
	exit;
}

$otdel = $_GET["otdel"];
$top = $_GET["top"];
$film = $_GET["film"];
$finame= $_GET["finame"];
$fikol = $_GET["fikol"];
$fird = $_GET["fird"];
$firm = $_GET["firm"];
$fiem = $_GET["fiem"];
$fils = $_GET["fils"];
$fistock = $_GET["fistock"];
$fipolka = $_GET["fipolka"];
$ficap = $_GET["ficap"];
$fifree = $_GET["fifree"];
$fisale = $_GET["fisale"];
$fizapas = $_GET["fizapas"];


//============подключение к MS SQL===================================================
$link2=mssql_connect("10.84.78.4", "sa", "russi@") or die("Невозможно подключиться к серверу!");
mssql_select_db("XPERT", $link2) or die("Невозможно подключиться к Базе Данных!"); 
//-----------------------------------------------------------------------------------
//============подключение к MySQL====================================================
$link=mysql_connect("localhost","leroy","locadm") or die("Не могу соединиться с MySQL.");
mysql_select_db("leroy_stocks",$link) or die("Не могу подключиться к базе.");
mysql_query("SET NAMES 'utf8'");
ini_set('max_execution_time', 720);
//-----------------------------------------------------------------------------------

$curdate=date("Ymd");
$lastdate=date("Ymd",strtotime("-1 month"));
$daysbetween = abs(strtotime($curdate) - strtotime($lastdate)) / (3600 * 24); 
// echo ("<script>alert(" . $daysbetween . ");</script>");

$query_str = ("SELECT pr.product_code as lm, name.short_name as name, (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0)) as kol,ISNULL(stock_rd.kol, 0) as rd, ISNULL(stock_rm.kol, 0) as rm, ISNULL(stock_em.kol, 0) as em, ISNULL(stock_ls.kol, 0) as ls, ISNULL(AVG_SALE.avg_sale, 0) as avg_sale 
				FROM LMXPERT.dbo.lm_artmag as art
				LEFT JOIN xpert.dbo.product pr ON pr.product_id=art.product_id
				LEFT JOIN XPERT.dbo.product_group prod_g on prod_g.product_group_id=pr.level1_id
				LEFT JOIN xpert.dbo.product_name name ON name.product_id=art.product_id AND name.Language_id=322
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='Reserve deportee' Group by stock.product_id) stock_rd ON stock_rd.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='RM' Group by stock.product_id) stock_rm ON stock_rm.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='EM' Group by stock.product_id) stock_em ON stock_em.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='Libre Service' Group by stock.product_id) stock_ls ON stock_ls.pr_id = art.product_id
				LEFT JOIN (SELECT product_id, sum(qty)/" . $daysbetween . " as avg_sale FROM XPERT.dbo.lm_RETAIL_TRAN_FIN_SESS_DT_PROD WHERE (transtype='sale') and (open_date between '" . $lastdate . "' and '" . $curdate . "') and totalamount>0 Group by product_id) AVG_SALE ON AVG_SALE.product_id = art.product_id
				WHERE prod_g.group_code = '1' and art.top_repl = '1' and pr.product_code LIKE '%" . $film . "%' and name.short_name LIKE '%" . $finame . "%' and (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0) > 0)");
				
				if (strlen($fikol) > 1)
				{
					$query_str = $query_str . " and (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0) " . $fikol . ")";
				}				
				if (strlen($fird) > 1)
				{
					$query_str = $query_str . " and ISNULL(stock_rd.kol, 0) " . $fird;
				}
				if (strlen($firm) > 1)
				{
					$query_str = $query_str . " and ISNULL(stock_rm.kol, 0) " . $firm;
				}
				if (strlen($fiem) > 1)
				{
					$query_str = $query_str . " and ISNULL(stock_rm.kol, 0) " . $fiem;
				}						 
				if (strlen($fils) > 1)
				{
					$query_str = $query_str . " and ISNULL(stock_ls.kol, 0) " . $fils;
				}
				if (strlen($fisale) > 1)
				{
					$query_str = $query_str . " and ISNULL(AVG_SALE.avg_sale, 0) " . $fisale;
				}						 
				$query_str = $query_str . " ORDER BY prod_g.group_code";

$sql2=mssql_query($query_str);

$i=0;
while ($result2=mssql_fetch_array($sql2)){
	$query_str2 = '';
	$lm = $result2["lm"];
	$name = $result2["name"];
	$kol = $result2["kol"];
	$rd = $result2["rd"];
	$rm = $result2["rm"];
	$em = $result2["em"];
	$ls = $result2["ls"];
	$avg_sale = $result2["avg_sale"];
	
	echo ('<script>alert(' . $fipolka . ');</script>');
	
	$query_str2 = ("SET @ls = (SELECT IFNULL(" . $ls . ", 0));
					SET @avg_sale = (SELECT IFNULL(" . $avg_sale . ", 0));
					SET @cap = (SELECT IFNULL(SUM(kol), 0) FROM leroy_stocksreport.capacity WHERE lm= " . $lm . ");
					SELECT @ls, IFNULL(SUM(kol), 0) as top_stocks, (@ls - IFNULL(SUM(kol), 0)) as polka, @cap as capacity, @cap - (@ls - IFNULL(SUM(kol), 0)) as freeplace,
						((@ls - IFNULL(SUM(kol), 0)) / @avg_sale) as zapas
					FROM leroy_stocks.stocks
					WHERE lm = " . $lm . " AND otdel = " . $otdel . "
					HAVING @ls ");
					if (strlen($fipolka) > 1)
					{
						$query_str2 = $query_str2 . " and polka" . $fipolka;
					}
					if (strlen($ficap) > 1)
					{
						$query_str2 = $query_str2 . " and capacity" . $ficap;
					}
					if (strlen($fifree) > 1)
					{
						$query_str2 = $query_str2 . " and freeplace" . $fifree;
					}
					if (strlen($fizapas) > 1)
					{
						$query_str2 = $query_str2 . " and zapas" . $fizapas;
					}
	
	$sql=mysql_query($query_str2);
	
	$top_stocks=mysql_fetch_array($sql);
	
	/* if ($top_stocks[0])
	{ */
		$rep[$i]["lm"]=$lm;
		$rep[$i]["name"]=$name;
		$rep[$i]["kol"] = $kol;
		$rep[$i]["rd"] =$rd;
		$rep[$i]["rm"] =$rm;
		$rep[$i]["em"] =$em;
		$rep[$i]["ls"] =$ls;
		$rep[$i]["AVG_SALE"] =round($avg_sale,1);
		$rep[$i]["top_stocks"] =$top_stocks["top_stocks"];
		$rep[$i]["polka"] = $top_stocks["polka"];
		$rep[$i]["capacity"] =$top_stocks["capacity"];
		$rep[$i]["freeplace"] = $top_stocks["freeplace"];
		$rep[$i]["zapas"] = $top_stocks["zapas"];
		
		$i++;
	/* } */
	

}				
	
$fname = tempnam("/tmp", "simple.xls");
$workbook = &new writeexcel_workbook($fname);
$worksheet = &$workbook->addworksheet();

$worksheet->write(0, 0, iconv("utf-8", "cp1251", "ЛМ"));
$worksheet->write(0, 1, iconv("utf-8", "cp1251", "Наименование"));
$worksheet->write(0, 2, iconv("utf-8", "cp1251", "Всего шт."));
$worksheet->write(0, 3, iconv("utf-8", "cp1251", "RD"));
$worksheet->write(0, 4, iconv("utf-8", "cp1251", "RM"));
$worksheet->write(0, 5, iconv("utf-8", "cp1251", "EM"));
$worksheet->write(0, 6, iconv("utf-8", "cp1251", "LS"));
$worksheet->write(0, 7, iconv("utf-8", "cp1251", "Верхние стоки"));
$worksheet->write(0, 8, iconv("utf-8", "cp1251", "На полке"));
$worksheet->write(0, 9, iconv("utf-8", "cp1251", "Вместимость полки"));
$worksheet->write(0, 10, iconv("utf-8", "cp1251", "Места свободно"));
$worksheet->write(0, 11, iconv("utf-8", "cp1251", "Среднедневная продажа"));
$worksheet->write(0, 12, iconv("utf-8", "cp1251", "Запас в днях на полке"));

for ($j=0; $j<count($rep); $j++)
{
	$worksheet->write($j+1, 0, iconv("utf-8", "cp1251", $rep[$j]["lm"]));
	$worksheet->write($j+1, 1, $rep[$j]["name"]);
	$worksheet->write($j+1, 2, iconv("utf-8", "cp1251", $rep[$j]["kol"]));
	$worksheet->write($j+1, 3, iconv("utf-8", "cp1251", $rep[$j]["rd"]));
	$worksheet->write($j+1, 4, iconv("utf-8", "cp1251", $rep[$j]["rm"]));
	$worksheet->write($j+1, 5, iconv("utf-8", "cp1251", $rep[$j]["em"]));
	$worksheet->write($j+1, 6, iconv("utf-8", "cp1251", $rep[$j]["ls"]));
	$worksheet->write($j+1, 7, iconv("utf-8", "cp1251", $rep[$j]["top_stocks"]));
	$worksheet->write($j+1, 8, iconv("utf-8", "cp1251", $rep[$j]["polka"]));
	$worksheet->write($j+1, 9, iconv("utf-8", "cp1251", $rep[$j]["capacity"]));
	$worksheet->write($j+1, 10, iconv("utf-8", "cp1251", $rep[$j]["freeplace"]));
	$worksheet->write($j+1, 11, iconv("utf-8", "cp1251", $rep[$j]["AVG_SALE"]));
	$worksheet->write($j+1, 12, iconv("utf-8", "cp1251", $rep[$j]["zapas"]));
	
}


$workbook->close();
header("Content-Type: application/x-msexcel; name='qwe.xls'");
header("Content-Disposition: inline; filename='qwe.xls'");
$fh=fopen($fname, "rb");
fpassthru($fh);
unlink($fname); 
die($i);
die($j);

?>