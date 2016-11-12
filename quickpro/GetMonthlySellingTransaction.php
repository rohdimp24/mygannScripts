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
$arrMonth=array();
$arrMonth['01']="January:31";
$arrMonth['02']="Feburary:28";
$arrMonth['03']="March:31";
$arrMonth['04']="April:30";
$arrMonth['05']="May:31";
$arrMonth['06']="June:30";
$arrMonth['07']="July:31";
$arrMonth['08']="August:31";
$arrMonth['09']="September:30";
$arrMonth['10']="October:31";
$arrMonth['11']="November:30";
$arrMonth['12']="December:31";




//get the max date from the database
$queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));
?>
<br/>
<br/>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
    <label>Select the Year for which you want to see the data</label>
    <select name="year">
        <?php
        if(isset($_GET["year"]))
            $selYear=intval($_GET["year"]);
        else
            $selYear=$maxYear;
        for($i=$minYear;$i<=$maxYear;$i++){

            if($i==$selYear){
                ?>
                <option value="<?php echo $i ?>" selected><?php echo $i ?></option>
                <?php
            }
            else {
                ?>
                <option value="<?php echo $i ?>"><?php echo $i ?></option>

                <?php
            }
        }

        ?>

    </select>
    <input type="submit">


</form>

<?php




if(isset($_GET["year"]))
{

    $year=$_GET["year"];
    echo "<span><h1  style='padding-left:20px;margin-top:30px;'>Monthly sales data for the year ".$year."</h1></span><br/>";
    for($i=1;$i<=12;$i++)
    {
        //skip the earlier months as we dont have the data before april 2015

        if($i<10)
        {
            $i="0".$i;

        }
        //echo $arrMonth[$i]."<br/>";
        $parts=explode(":",$arrMonth[$i]);
        $startDate=$year."-".$i."-01";
        $endDate=$year."-".$i."-".$parts[1];

        $arrProductsWithSPs=fetchSellingPrices();
    
        //echo $startDate.";".$endDate."<br/>";
        $monthName=$parts[0];
        echo displayTop100Data($startDate,$endDate,$monthName,$arrProductsWithSPs);

    }
}



//displays the top hundred data
function displayTop100Data($startDate,$endDate,$month,&$arrProductsWithSPs){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

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
