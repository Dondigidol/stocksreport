<?php
session_start();
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

$sql2=mssql_query("SELECT art.top_repl as top_repl, pr.product_code as lm, name.short_name as name, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='Reserve deportee'),0) as RD, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='RM'),0) as RM, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='EM'),0) as EM, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='Libre Service'),0) as LS, "
				."ISNULL((SELECT sum(qty)/" . $daysbetween . " FROM XPERT.dbo.lm_RETAIL_TRAN_FIN_SESS_DT_PROD WHERE (transtype='sale') and (open_date between '" . $lastdate . "' and '" . $curdate . "') and totalamount>0 and product_id=art.product_id), 0) as AVG_SALE "
				."FROM LMXPERT.dbo.lm_artmag as art "
				."LEFT JOIN xpert.dbo.product pr ON pr.product_id=art.product_id "
				."LEFT JOIN XPERT.dbo.product_group prod_g on prod_g.product_group_id=pr.level1_id "
				."LEFT JOIN xpert.dbo.product_name name ON name.product_id=art.product_id AND name.Language_id=322 "
				."WHERE prod_g.group_code LIKE '".$otdel."' and art.top_repl LIKE '".$top."' "
				."ORDER BY prod_g.group_code");

$i=0;
while ($result2=mssql_fetch_array($sql2)){
	$lm=$result2["lm"];
	$top=$result2["top_repl"];
	$name=$result2["name"];
	$rd=$result2["RD"];
	$rm=$result2["RM"];
	$em=$result2["EM"];
	$ls=$result2["LS"];
	$avg_sale=$result2["AVG_SALE"];
	
	if (($rd+$rm+$em+$ls)>0){
		$sql=mysql_query("SELECT IFNULL(SUM(kol), 0), 
						(SELECT IFNULL(SUM(kol), 0) FROM leroy_stocksreport.capacity WHERE lm= '".$lm."')
						FROM leroy_stocks.stocks
						WHERE lm= '".$lm."' AND otdel=".$otdel);
		
		$top_stocks=mysql_fetch_array($sql);

		$rep["lm"][$i]=$lm;
		$rep["top"][$i]=$top;
		$rep["name"][$i]=$name;
		$rep["rd"][$i]=$rd;
		$rep["rm"][$i]=$rm;
		$rep["em"][$i]=$em;
		$rep["ls"][$i]=$ls;
		$rep["top_stocks"][$i]=$top_stocks[0];
		$rep["capacity"][$i]=$top_stocks[1];
		$rep["AVG_SALE"][$i]=round($avg_sale, 1);
		$i++;
	}	
}				

$str='';
for ($k=0; $k<=$i-1; $k++){
	if ($k%2==0){
		$bgcolor="#dedede";
	}
	else{
		$bgcolor="#ffffff";
	}
	$kol=$rep["rd"][$k]+$rep["rm"][$k]+$rep["em"][$k]+$rep["ls"][$k];
	$polka=$rep["ls"][$k]-$rep["top_stocks"][$k];
	$freeplace=$rep["capacity"][$k]-$polka;
	if ($polka!=0 && $rep["AVG_SALE"][$k]!=0){
		$zapas=round($polka/$rep["AVG_SALE"][$k]);
	} else {
		$zapas=0;
	}
	
	if ($root == 1)
	{
		$cap = "<td width=".$capacityw."><div><div id='".$k."position".$rep["lm"][$k]."' ondblclick='addvmest(this)' tabindex='".$k."' style='outline: none;'><input type='number' class='cap".$k."' id='".$k."vmest".$rep["lm"][$k]."' style='width:60px;' onblur='savevmest(this);' disabled value=".$rep["capacity"][$k]."></input></div></div></td>";
	}
	else
	{
		$cap = "<td width=".$capacityw."><div id='cap".$k."'>".$rep["capacity"][$k]."</div></td>";
	}
	
	$str=$str."<div id='row".$k."' class='rowel'>
		<table style='background: ".$bgcolor.";'><tr height='16px'>
		<td width=".$lmw."><div id='lm".$k."'>".trim($rep["lm"][$k])."</div></td>
		<td width=".$namew."><div id='name".$k."'>".iconv("cp1251","UTF8",$rep["name"][$k])."</div></td>
		<td width=".$kolw."><div id='kol".$k."'>".$kol."</div></td>
		<td width=".$rdw."><div id='rd".$k."'>".$rep["rd"][$k]."</div></td>
		<td width=".$rmw."><div id='rm".$k."'>".$rep["rm"][$k]."</div></td>
		<td width=".$emw."><div id='em".$k."'>".$rep["em"][$k]."</div></td>
		<td width=".$lsw."><div id='ls".$k."'>".$rep["ls"][$k]."</div></td>
		<td width=".$topstockw."><div id='stock".$k."'>".$rep["top_stocks"][$k]."</div></td>
		<td width=".$polkaw."><div id='polka".$k."'>".$polka."</div></td>"
		. $cap .
		"<td width=".$freeplacew."><div id='free".$k."'>".$freeplace."</div></td>
		<td width=".$avg_salew."><div id='sale".$k."'>".number_format($rep["AVG_SALE"][$k], 1, '.', '')."</div></td>
		<td width=".$zapasw."><div id='zapas".$k."'>".$zapas."</div></td>
		</tr></table></div>";	
}				
echo $str;
echo ("<script>$('#footer').html('".$k." артикула(ов)')</script>");				
				




?>