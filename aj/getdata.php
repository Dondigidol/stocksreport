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
$facew=$_POST["facew"];
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

$query_str = ("SELECT TOP 30 pr.product_code as lm, name.short_name as name, (ISNULL(stock_rd.kol, 0) + ISNULL(stock_rm.kol, 0) + ISNULL(stock_em.kol, 0) + ISNULL(stock_ls.kol, 0)) as kol,ISNULL(stock_rd.kol, 0) as rd, ISNULL(stock_rm.kol, 0) as rm, ISNULL(stock_em.kol, 0) as em, ISNULL(stock_ls.kol, 0) as ls, ISNULL(AVG_SALE.avg_sale, 0) as avg_sale 
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
$out .= '<table class = "table">';
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
	
	if ($root == 1)
	{
		$outface = '<td width = ' . $facew . '>
					<div>
						<div id="' . $arr . 'position' . $row['lm'] . '" ondblclick = "addface(this)" style = "outline: none;">
							<input type = "number" class = "face' . $arr . '" id="' . $arr . 'face' . $row['lm'] . '" style = "width:60px;" onblur = "saveface(this);" disabled value = "' . $face .'"></input>
						</div>
					</div>
				</td>';
	}
	else
	{
		$outface = '<td width = ' . $facew . '><div class="face' . $arr . '">' . $face . '</div></td>';
	}
	
	$out .= '<tr bgcolor = ' . $bgcolor . ' class = "rowel" id = "row' . $arr . '">';
	$out .= '<td width = ' . $lmw . '><div id = "lm' . $arr . '">' . trim($row['lm']) . '</div></td>';
	$out .= '<td width = ' . $namew . '><div id = "name' . $arr . '">' . iconv("windows-1251", "UTF-8", $row['name']) . '</div></td>';
	$out .= '<td width = ' . $kolw . '><div id = "kol' . $arr . '">' . round($row['kol'], 2) . '</div></td>';
	$out .= '<td width = ' . $rdw . '><div id = "rd' . $arr .'">' . round($row['rd'], 2) . '</div></td>';
	$out .= '<td width = ' . $rmw . '><div id = "rm' . $arr . '">' . round($row['rm'], 2) . '</div></td>';
	$out .= '<td width = ' . $emw . '><div id = "em' . $arr . '">' . round($row['em'], 2) . '</div></td>';
	$out .= '<td width = ' . $lsw . '><div id = "ls' . $arr . '">' . round($row['ls'], 2) . '</div></td>';
	$out .= '<td width = ' . $topstockw . '><div id = "stock' . $arr . '">' . $top_stocks . '</div></td>';
	$out .= '<td width = ' . $polkaw . '><div id = "polka' . $arr. '">' . $polka . '</div></td>';
	$out .= $outface;
	$out .= '<td width = ' . $freeplacew . '><div id = "free' . $arr. '">' . $freeplace . '</div></td>';		
	$out .= '<td width = ' . $avg_salew . '><div id = "sale' . $arr . '">' . round($row['avg_sale'], 1) . '</div></td>';
	$out .= '<td width = ' . $zapasw . '><div id = "zapas' . $arr . '">' . $zapas . '</div></td>';
	$out .= '</tr>';
}
$out .= '</table>';

echo $out;
?>