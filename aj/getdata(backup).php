<?php
$otdel=$_POST["otdel"];
$top=$_POST["top"];
$topw=$_POST["topw"];
$lmw=$_POST["lmw"];
$namew=$_POST["namew"];
$kolw=$_POST["kolw"];
$rdw=$_POST["rdw"];
$rmw=$_POST["rmw"];
$emw=$_POST["emw"];
$lsw=$_POST["lsw"];
$topstockw=$_POST["topstockw"];
$polkaw=$_POST["polkaw"];
$capacityw=$_POST["capacityw"];
$freeplacew=$_POST["freeplacew"];
$avg_salew=$_POST["avg_salew"];
$zapasw=$_POST["zapasw"];
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
$lastmonth=date("Ymd",strtotime("-1 month"));

$sql2=mssql_query("SELECT art.top_repl as top_repl, pr.product_code as lm, name.short_name as name, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='Reserve deportee'),0) as RD, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='RM'),0) as RM, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='EM'),0) as EM, "
				."ISNULL((SELECT SUM(bin_qty_stocks) FROM xpert.dbo.bin_detail AS stock LEFT JOIN xpert.dbo.bin_header bin ON bin.bin_id=stock.bin_id WHERE product_id=art.product_id AND bin.description='Libre Service'),0) as LS, "
				."ISNULL((SELECT sum(qty)/30 FROM XPERT.dbo.lm_RETAIL_TRAN_FIN_SESS_DT_PROD WHERE (transtype='sale') and (open_date between '".$lastmonth."' and '".$curdate."') and totalamount>0 and product_id=art.product_id), 0) as AVG_SALE "
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
	
	if (($rd+$rm+$em+$ls)>0 or ($top>0)){
		$sql=mysql_query("SELECT IFNULL(SUM(kol), 0), 
						(SELECT IFNULL(SUM(kol), 0) FROM leroy_stocksreport.capacity WHERE lm= '".$lm."' AND otdel=".$otdel.")
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
		$rep["AVG_SALE"][$i]=$avg_sale;
		$i++;
	}	
}				

//echo "<script>alert(".$i.");</script>";
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
		$zapas=number_format($polka/$rep["AVG_SALE"][$k]);
	} else {
		$zapas=0;
	}
	
	$str=$str."<div id='row".$k."' style='background: ".$bgcolor.";'>
		<table><tr>
		<td width=".$topw."><div class='rowel'>".$rep["top"][$k]."</div></td>
		<td width=".$lmw."><div class='rowel'>".$rep["lm"][$k]."</div></td>
		<td width=".$namew.">".iconv("cp1251","UTF8",$rep["name"][$k])."</td>
		<td width=".$kolw."><div class='rowel'>".$kol."</div></td>
		<td width=".$rdw."><div class='rowel'>".$rep["rd"][$k]."</div></td>
		<td width=".$rmw."><div class='rowel'>".$rep["rm"][$k]."</div></td>
		<td width=".$emw."><div class='rowel'>".$rep["em"][$k]."</div></td>
		<td width=".$lsw."><div class='rowel'>".$rep["ls"][$k]."</div></td>
		<td width=".$topstockw."><div class='rowel'>".$rep["top_stocks"][$k]."</div></td>
		<td width=".$polkaw."><div class='rowel'>".$polka."</div></td>
		<td width=".$capacityw."><div class='rowel'><div id='".$rep["lm"][$k]."' ondblclick='addvmest(this)' tabindex='".$k."' style='outline: none;'><input type='number' id='vmest".$rep["lm"][$k]."' style='width:60px;' onblur='savevmest(this, ".$otdel.");' disabled value=".$rep["capacity"][$k]."></input></div></div></td>
		<td width=".$freeplacew."><div class='rowel'>".$freeplace."</div></td>
		<td width=".$avg_salew."><div class='rowel'>".number_format($rep["AVG_SALE"][$k], 1, '.', '')."</div></td>
		<td width=".$zapasw."><div class='rowel'>".$zapas."</div></td>
		</tr></table></div>";	
}				
echo $str;					
				




?>