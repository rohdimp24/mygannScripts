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
require_once ('helperFunctions.php');


set_time_limit(0);

//get the number of years from the data base
$queryMaxMinDateYear="SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
$resultMaxMinDateYear=mysql_query($queryMaxMinDateYear);
$rowsMaxMinYear = mysql_fetch_row($resultMaxMinDateYear);
$maxDate=$rowsMaxMinYear[0];
$minDate=$rowsMaxMinYear[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$maxYear=intval($maxYear);
$minYear=intval($minYear);

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


    //get the max date from the database
    $queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `quickpro` Where Year(SellingDate)='".$year."'";
    $resultMaxMinDate=mysql_query($queryMaxMinDate);
    $rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
    $maxDate=$rowsMaxMin[0];
    $minDate=$rowsMaxMin[1];
    $maxYear=date('Y', strtotime($maxDate));
    $minYear=date('Y',strtotime($minDate));
    $startDate=$minDate;
    $endDate=date('Y-m-d',strtotime($startDate.'+7 days'));
    //echo "Max date".$maxDate;
    echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Weekly Sales from Ebay upto ". $maxDate."</h1></span><br/>";
    $week=1;
    // $finalOutput='';

    $arrProductsWithSPs=fetchSellingPrices();
    while($startDate<$maxDate)
    {
       
    
        // echo $week."-->".$startDate."-->".$endDate."<br/>";

        echo displayTop100Data($startDate,$endDate,$arrProductsWithSPs);
        $startDate=date('Y-m-d', strtotime($endDate. ' + 1 days'));
        $endDate=date('Y-m-d', strtotime($endDate. ' + 7 days'));
        $week++;
        // if($week>2)
        //     break;
        //echo $startDate."-".$endDate."<br/>";

    }

}
//echo $finalOutput;

//exit();
//displays the top hundred data
function displayTop100Data($startDate,$endDate,&$arrProductsWithSPs){

    $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for Week  between ".$startDate." and ".$endDate."</H2><br/>";

//    $query="SELECT ItemId,ProductName,SUM(Qty) as output, group_concat(distinct(SellingPrice)), SUM(SellingPrice*Qty) from quickpro
//            where SellingDate>='".$startDate."' and SellingDate <= '".$endDate."' GROUP by ItemId order by output DESC";
//    //echo $query;

    //first set the group_concat_seesion so that the long strings are not truncated at 1024
    $querySession="SET SESSION group_concat_max_len = 1000000";
    mysql_query($querySession);

    //I dont know but in case of group_concat when I see it in mygann database i see blob.in order to get rid of that 
    // use convert(group_concat(sellingprice) using utf8)..see http://www.mike250.com/blog/2010/26/05/mysql-group_concat-returns-blob-14-b
    
    $queryProducts="SELECT ItemId,ProductName,GROUP_concat(Qty), group_concat(SellingPrice),sum(qty) as output,sum(qty*SellingPrice)
                        from quickpro where SellingDate>='".$startDate."' and SellingDate <= '".$endDate."' group by ItemId order by output DESC";
    $resultProducts=mysql_query($queryProducts);
    $rowsProductNum=mysql_num_rows($resultProducts);
    //echo $queryProducts;
    //echo $rowsProductNum;


    if($rowsProductNum==0)
    {
        $lines.= "No results found";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th>
        <th>Selling Price(s)</th><th>Amount</th><th>Total Cost</th><th>Profit</th><th>Profit%</th></tr>";


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

       $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>$".number_format($total, 2, '.', ',')."</b></td><td></td><td><b>$".number_format($totalProfit,2,'.',',')."</b></td><td></td></tr>";
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    // return $lines;
    //return;
}

?>



