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
$lmw=$_POST["lmw"];
$namew=$_POST["namew"];
$kolw=$_POST["kolw"];
$rdw=$_POST["rdw"];
$rmw=$_POST["rmw"];
$emw=$_POST["emw"];
$lsw=$_POST["lsw"];
$topstockw=$_POST["topstockw"];
$polkaw=$_POST["polkaw"];
$facew=$_POST["capacityw"];
$freeplacew=$_POST["freeplacew"];
$avg_salew=$_POST["avg_salew"];
$zapasw=$_POST["zapasw"];
//============подключение к MS SQL===================================================
$mssql = connect_to_mssql('config.ini');
//-----------------------------------------------------------------------------------
//============подключение к MySQL====================================================
$mysqli = connect_to_mysql('config.ini');
//-----------------------------------------------------------------------------------

$curdate=date("Ymd");
$lastdate=date("Ymd",strtotime("-1 month"));
$daysbetween = abs(strtotime($curdate) - strtotime($lastdate)) / (3600 * 24); 

$query_str = ("SELECT TOP 50 pr.product_code as lm, name.short_name as name, (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0)) as kol,ISNULL(stock_rd.kol, 0) as rd, ISNULL(stock_rm.kol, 0) as rm, ISNULL(stock_em.kol, 0) as em, ISNULL(stock_ls.kol, 0) as ls, ISNULL(AVG_SALE.avg_sale, 0) as avg_sale 
				FROM LMXPERT.dbo.lm_artmag as art
				LEFT JOIN xpert.dbo.product pr ON pr.product_id=art.product_id
				LEFT JOIN XPERT.dbo.product_group prod_g on prod_g.product_group_id=pr.level1_id
				LEFT JOIN xpert.dbo.product_name name ON name.product_id=art.product_id AND name.Language_id=322
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='Reserve deportee' Group by stock.product_id) stock_rd ON stock_rd.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='RM' Group by stock.product_id) stock_rm ON stock_rm.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='EM' Group by stock.product_id) stock_em ON stock_em.pr_id = art.product_id
				LEFT JOIN (SELECT stock.product_id as pr_id, SUM(bin_qty_stocks) as kol FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE bin.description='Libre Service' Group by stock.product_id) stock_ls ON stock_ls.pr_id = art.product_id
				LEFT JOIN (SELECT product_id, sum(qty)/" . $daysbetween . " as avg_sale FROM XPERT.dbo.lm_RETAIL_TRAN_FIN_SESS_DT_PROD WHERE (transtype='sale') and (open_date between " . $lastdate . " and " . $curdate . ") and totalamount>0 Group by product_id) AVG_SALE ON AVG_SALE.product_id = art.product_id
				WHERE prod_g.group_code = " . $otdel . " and art.top_repl = 1 and (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0) > 0)");
				

$result = $mssql->sql_query($query_str);


$out = '';
$out .= '<table class = "contable">';
foreach ($result as $arr=>$row)
{	
	$query_str = ('SELECT IFNULL(cap.kol, 0) as face, IFNULL(stock.kol, 0) as top_stocks, (' . $row['ls'] . ' - IFNULL(SUM(stock.kol), 0)) as polka, IFNULL(cap.kol, 0) - (' . $row['ls'] . ' - IFNULL(SUM(stock.kol), 0)) as freeplace, ((' . $row['ls'] . ' - IFNULL(SUM(stock.kol), 0)) / ' . round($row['avg_sale'], 1) . ') as zapas
						FROM leroy_stocksreport.capacity as cap
						LEFT JOIN (SELECT lm, kol FROM leroy_stocks.stocks WHERE otdel = ' . $otdel . ') stock ON stock.lm = ' . $row['lm'] . '
						WHERE cap.lm = '. $row['lm']);
	
	$result2 = $mysqli->sql_query($query_str);
	
	foreach ($result2 as $arr2=>$row2)
	{
		$face = $row2['face'];
		$top_stocks = $row2['top_stocks'];
		$polka = round($row2['polka'], 1);
		$freeplace = round($row2['freeplace'], 1);
		$zapas = round($row2['zapas'], 1);
	}
	
	if ($arr%2==0){
		$bgcolor="#dedede";
	}
	else{
		$bgcolor="#ffffff";
	}
	
	$out .= '<tr bgcolor = ' . $bgcolor . ' class = "rowel">';
	$out .= '<td width = ' . $lmw . '>' . trim($row['lm']) . '</td>';
	$out .= '<td width = ' . $namew . '>' . iconv("windows-1251", "UTF-8", $row['name']) . '</td>';
	$out .= '<td width = ' . $kolw . '>' . round($row['kol'], 2) . '</td>';
	$out .= '<td width = ' . $rdw . '>' . round($row['rd'], 2) . '</td>';
	$out .= '<td width = ' . $rmw . '>' . round($row['rm'], 2) . '</td>';
	$out .= '<td width = ' . $emw . '>' . round($row['em'], 2) . '</td>';
	$out .= '<td width = ' . $lsw . '>' . round($row['ls'], 2) . '</td>';
	$out .= '<td width = ' . $topstockw . '>' . $top_stocks . '</td>';
	$out .= '<td width = ' . $polkaw . '>' . $polka . '</td>';
	$out .= '<td width = ' . $facew . '>' . $face . '</td>';
	$out .= '<td width = ' . $freeplacew . '>' . $freeplace . '</td>';		
	$out .= '<td width = ' . $avg_salew . '>' . round($row['avg_sale'], 1) . '</td>';
	$out .= '<td width = ' . $zapasw . '>' . $zapas . '</td>';
	$out .= '</tr>';
}
$out .= '</table>';
echo $out;
// $i=0;
/* while ($result=mssql_fetch_array($sql)){
	$query_str2 = '';
	$lm = $result["lm"];
	$name = $result["name"];
	$kol = $result["kol"];
	$rd = $result["rd"];
	$rm = $result["rm"];
	$em = $result["em"];
	$ls = $result["ls"];
	$avg_sale = round($result["avg_sale"], 2);
		
	$sql2 = mysqli_query;
	
	$top_stocks=mysqli_fetch_array($sql2);
	
	$rep[$i]["lm"]=$lm;
	$rep[$i]["name"]=$name;
	$rep[$i]["kol"] = $kol;
	$rep[$i]["rd"] =$rd;
	$rep[$i]["rm"] =$rm;
	$rep[$i]["em"] =$em;
	$rep[$i]["ls"] =$ls;
	$rep[$i]["AVG_SALE"] = $avg_sale;
	$rep[$i]["top_stocks"] =$top_stocks["top_stocks"];
	$rep[$i]["polka"] = $top_stocks["polka"];
	$rep[$i]["capacity"] =$top_stocks["capacity"];
	$rep[$i]["freeplace"] = $top_stocks["freeplace"];
	$rep[$i]["zapas"] = round($top_stocks["zapas"], 1);
	
	$i++;
} */

/* $str='';
$str = '<table class = "contable">';
for ($k=0; $k<count($rep); $k++){
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
	 */
/* 	$str=$str."<div id='row".$k."' class='rowel'>
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
		</tr></table></div>";	 */
	
	
/* 	$str=$str."<tr bgcolor='" . $bgcolor . "' class = 'rowel'>
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
		</tr>";	
		
	
		
}
$str = $str . '</table>';	
echo $str; */
echo ("<script>$('#footer').html('".$k." артикула(ов)')</script>");				
				




?>