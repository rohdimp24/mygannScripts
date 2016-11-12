<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>


<!--
Add details of the mysql database

We can fetch the data only from 120 days onwards. Earlier data shall be possible to manually enter by excel download or something like that

-->
<?php
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
require_once ('login.php');
require_once ('reportHeader.php');
require_once('helperFunctions.php');

set_time_limit(0);



//echo $currentYear."=>".$currentMonth;
$arrYear=array();

$arrYear['2010']="2010";
$arrYear['2011']="2011";
$arrYear['2012']="2012";
$arrYear['2013']="2013";
$arrYear['2014']="2014";
$arrYear['2015']="2015";
$arrYear['2016']="2016";


$arrMonth=array();
$arrMonth['01']="January:01;31";
$arrMonth['02']="February:02;28";
$arrMonth['03']="March:03;31";
$arrMonth['04']="April:04;30";
$arrMonth['05']="May:05;31";
$arrMonth['06']="June:06;30";
$arrMonth['07']="July:07;31";
$arrMonth['08']="August:08;31";
$arrMonth['09']="September:09;30";
$arrMonth['10']="October:10;31";
$arrMonth['11']="November:11;30";
$arrMonth['12']="December:12;31";




?>
<br/>
<br/>
<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
    <div>
	<label>Select the Year & month for which you want to see the data</label>
        <?php
        $yearSelect='<select name="Year" id="selectYear">';
		//echo "sdshdj";
		
        for($i=2010;$i<=2016;$i++)
        {
			//echo $i;
			//echo $arrYear[$i];
        //echo intval($catArray[$i]->getCatID());
        $yearSelect .='<option value="'.$i.'">'.$i.'</option>';
		//$yearSelect .='<option value="2016">'.$arrYear['2016'].'</option>';
        }
        $yearSelect .= '</select></td></tr>';

        echo $yearSelect;


        $monthSelect='<select name="Month" id="selectMonth">';
        for($i=1;$i<=12;$i++)
        {
//echo intval($catArray[$i]->getCatID());
            if($i<10)
                $index="0".$i;
            else
                $index=$i;

            $parts=explode(';',$arrMonth[$index]);
            $monthData=explode(":",$parts[0]);

            $monthSelect .='<option value="'.$arrMonth[$index].'">'.$monthData[0].'</option>';
        }
        $monthSelect .= '</select></td></tr>';

        echo $monthSelect;
        echo "<input type='submit' class='btn-success btn-small' style='margin-bottom: 10px;' name='dateSubmit' value='Get Data'>";

        ?>
    </div>
</form>

<?php




if(isset($_POST['dateSubmit']))
{
    //print_r($_POST);
    $year=$_POST["Year"];
    $monthArr=explode(";",$_POST["Month"]);
    $monthNumeric=explode(":",$monthArr[0]);
    $lastDayOfMonth=$monthArr[1];
    $startDate=$year."-".$monthNumeric[1]."-"."01";
    $endDate=$year."-".$monthNumeric[1]."-".$lastDayOfMonth;
    //echo $startDate."=>".$endDate."<br/>";

    $startDate=date('Y-m-d', strtotime($startDate));
    $endDate=date('Y-m-d', strtotime($endDate));

    //echo $startDate;
    //echo $endDate;

    echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales  for "
        .$monthNumeric[0].", ".$year." (".$startDate.")-(".$endDate.")</h1></span><br/>";



	$arrProductsWithSPs=fetchSellingPrices();
    
	echo displayTop100Data($startDate,$endDate,$arrProductsWithSPs);

    
}



//displays the top hundred data
function displayTop100Data($startDate,$endDate,&$arrProductsWithSPs){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
   // $lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

    //first set the group_concat_seesion
    $querySession="SET SESSION group_concat_max_len = 1000000";
    mysql_query($querySession);


//    $query="SELECT ItemId,ProductName,SUM(Qty) as output, group_concat(distinct(SellingPrice)), SUM(SellingPrice*Qty) from quickpro
//            where SellingDate>='".$startDate."' and SellingDate <= '".$endDate."' GROUP by ItemId order by output DESC";
//    //echo $query;

    $queryProducts="SELECT ItemId,ProductName,GROUP_concat(Qty), group_concat(SellingPrice),sum(qty),sum(qty*SellingPrice)
                        from quickpro where SellingDate>='".$startDate."' and SellingDate <= '".$endDate."' group by ItemId";
    $resultProducts=mysql_query($queryProducts);
    $rowsProductNum=mysql_num_rows($resultProducts);

     if($rowsProductNum==0)
    {
        $lines.= "No results found";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th>
        <th>Item</th><th>Quantity Sold</th><th>Selling Price(s)</th><th>Amount</th><th>Total Cost</th><th>Profit</th><th>Profit%</th></tr>";
        $count=1;
        $total=0.0;
        $totalProfit=0.0;
        for($i=0;$i<$rowsProductNum;$i++)
        {

            $rowProduct=mysql_fetch_row($resultProducts);
            $retProfitTotal=displayHelperFunction($rowProduct,$arrProductsWithSPs,$count);
            $count++;
            //print_r($retProfitTotal);
            $arrValues=explode("^",$retProfitTotal);
            $totalProfit+=floatval($arrValues[0]);
            $total+=floatval($arrValues[1]);
            $lines.=$arrValues[2];
            

        }

        //$lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".$total."$</b></td></tr>";
        $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>$".number_format($total, 2, '.', ',')."</b></td><td></td><td><b>$".number_format($totalProfit,2,'.',',')."</b></td><td></td></tr>";
   

        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    //return;
}




?>
