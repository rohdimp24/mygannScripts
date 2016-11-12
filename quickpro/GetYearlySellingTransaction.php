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
require_once('LIB_parse.php');
require_once('helperFunctions.php');

set_time_limit(0);
setlocale(LC_MONETARY, 'en_US');

//get the number of years from the data base
$queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
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


//echo $maxYear.",".$minYear;

echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Yearly Sales from Ebay for year ". $year."</h1></span><br/>";

//for($i=$minYear;$i<=$maxYear;$i++)
//{
    //echo $i."<br/>";
    //get the display output for each year
  //  $year=$i;
    $startDate=$year."-01-01";
    $endDate=$year."-12-31";
    //fill in the array with the selling prices
    $arrProductsWithSPs=fetchSellingPrices();
    //print_r($arrProductsWithSPs);    

    echo displayTop100Data($startDate,$endDate,$year,$arrProductsWithSPs);

}



function displayTop100Data($startDate,$endDate,$year,&$arrProductsWithSPs){

    //print_r($arrProductsWithSPs);
    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th><th>Total Cost</th><th>Profit</th><th>Profit%</th></tr>";

    // $lines.="<H2>Displaying top results for ".$year ."</H2><br/>";

    //first set the group_concat_seesion
    $querySession="SET SESSION group_concat_max_len = 1000000";
    mysql_query($querySession);
    
 //   $queryProducts="SELECT ItemId,ProductName,GROUP_concat(Qty), group_concat(SellingPrice),sum(qty) as output,sum(qty*SellingPrice)
   //                     from quickpro where Year(SellingDate)='".$year."' group by ItemId order by output desc";
   
    $queryProducts="SELECT quickpro.ItemId,quickpro.ProductName,GROUP_concat(Qty), group_concat(SellingPrice),sum(qty) as output,sum(qty*SellingPrice) from quickpro  where Year(SellingDate)='".$year."' GROUP by ItemId order by output desc";
   
    //echo $queryProducts;
    $resultProducts=mysql_query($queryProducts);
    $rowsProductNum=mysql_num_rows($resultProducts);
    $count=1;
    $total=0.0;
    $totalProfit=0.0;

    //$subtotal=0.0;
//    $subqty=0;
//    $subSellingString='';
//    $oldItem='';
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
    $lines.="</div><br/>";
    return $lines;
}


?>
