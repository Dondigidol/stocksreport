<?php
session_start();

require_once('../connect.php');

$root = 0;
if ($_SESSION['root'] == 1)
{
	$root = 1;
}


$otdel=$_POST["otdel"];
$top=$_POST["top"];
//$topw=$_POST["topw"];
$lmw="85px";//$_POST["lmw"];
$namew="200px";//$_POST["namew"];
$kolw="80px";//$_POST["kolw"];
$rdw="80px";//$_POST["rdw"];
$rmw="80px";//$_POST["rmw"];
$emw="80px";//$_POST["emw"];
$lsw="80px";//$_POST["lsw"];
$topstockw="80px";//$_POST["topstockw"];
$polkaw="80px";//$_POST["polkaw"];
$capacityw="80px";//$_POST["capacityw"];
$freeplacew="80px";//$_POST["freeplacew"];
$avg_salew="80px";//$_POST["avg_salew"];
$zapasw="80x";//$_POST["zapasw"];
//============подключение к MS SQL===================================================
connect_to_mssql('connect.ini');
//-----------------------------------------------------------------------------------
//============подключение к MySQL====================================================
connect_to_mysql('connect.ini');
//-----------------------------------------------------------------------------------

$curdate=date("Ymd");
$lastdate=date("Ymd",strtotime("-1 month"));
$daysbetween = abs(strtotime($curdate) - strtotime($lastdate)) / (3600 * 24); 

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
				WHERE prod_g.group_code = '1' and art.top_repl = '1' and (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0) > 0)");
				

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
	
	$query_str2 = ("SET @ls = (SELECT IFNULL(" . $ls . ", 0));
					SET @avg_sale = (SELECT IFNULL(" . $avg_sale . ", 0));
					SET @cap = (SELECT IFNULL(SUM(kol), 0) FROM leroy_stocksreport.capacity WHERE lm= " . $lm . ");
					SELECT IFNULL(SUM(kol), 0) as top_stocks, (@ls - IFNULL(SUM(kol), 0)) as polka, @cap as capacity, @cap - (@ls - IFNULL(SUM(kol), 0)) as freeplace, ((@ls - IFNULL(SUM(kol), 0)) / @avg_sale) as zapas
					FROM leroy_stocks.stocks
					WHERE lm = " . $lm . " AND otdel = " . $otdel);
			
	$sql=mysql_query($query_str2);
	
	$top_stocks=mysql_fetch_array($sql);
	echo "<script>alert('" . $top_stocks . "');</script>";
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
}

$str='';
for ($k=0; $k<=count($rep); $k++){
	if ($k%2==0){
		$bgcolor="#dedede";
	}
	else{
		$bgcolor="#ffffff";
	}
	
	if ($root == 1)
	{
		$cap = "<td width=".$capacityw."><div><div id='".$k."position".$rep[$k]["lm"]."' ondblclick='addvmest(this)' tabindex='".$k."' style='outline: none;'><input type='number' class='cap".$k."' id='".$k."vmest".$rep[$k]["lm"]."' style='width:60px;' onblur='savevmest(this);' disabled value=".$rep[$k]["capacity"]."></input></div></div></td>";
	}
	else
	{
		$cap = "<td width=".$capacityw."><div id='cap".$k."'>".$rep[$k]["capacity"]."</div></td>";
	}
	
	$str=$str."<div id='row".$k."' class='rowel'>
		<table style='background: ".$bgcolor.";'><tr height='16px'>
		<td width=".$lmw."><div id='lm".$k."'>".trim($rep[$k]["lm"])."</div></td>
		<td width=".$namew."><div id='name".$k."'>".iconv("cp1251","UTF8",$rep[$k]["name"])."</div></td>
		<td width=".$kolw."><div id='kol".$k."'>".$rep[$k]["kol"]."</div></td>
		<td width=".$rdw."><div id='rd".$k."'>".$rep[$k]["rd"]."</div></td>
		<td width=".$rmw."><div id='rm".$k."'>".$rep[$k]["rm"]."</div></td>
		<td width=".$emw."><div id='em".$k."'>".$rep[$k]["em"]."</div></td>
		<td width=".$lsw."><div id='ls".$k."'>".$rep[$k]["ls"]."</div></td>
		<td width=".$topstockw."><div id='stock".$k."'>".$rep[$k]["top_stocks"]."</div></td>
		<td width=".$polkaw."><div id='polka".$k."'>".$rep[$k]["polka"]."</div></td>"
		. $cap .
		"<td width=".$freeplacew."><div id='free".$k."'>".$rep[$k]["freeplace"]."</div></td>
		<td width=".$avg_salew."><div id='sale".$k."'>".$rep[$k]["AVG_SALE"]."</div></td>
		<td width=".$zapasw."><div id='zapas".$k."'>".$rep[$k]["zapas"]."</div></td>
		</tr></table></div>";	
}				
echo $str;
echo ("<script>$('#footer').html('".$k." артикула(ов)')</script>");				
				




?>