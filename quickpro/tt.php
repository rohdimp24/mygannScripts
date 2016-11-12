<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>



<?php require_once('keys.php') ?>
<?php require_once('ebaySession.php') ?>
<?php require_once('Orders.php') ?>


<!--
Add details of the mysql database

We can fetch the data only from 120 days onwards. Earlier data shall be possible to manually enter by excel download or something like that

-->
<?php
require_once ('login.php');
require_once ('reportHeader.php');
require_once ('itemMonthly.php');

set_time_limit(0);




//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-04-01'));
//$endDate=date('Y-m-d',strtotime('2015-04-30'));
$currentYear=date('Y');
$currentMonth=date('m');



//get the max date from the database
$queryMaxMinDate="Select Max(Distinct(CreationDate)),Min(Distinct(CreationDate)) from EbayTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
//$minDate=$rowsMaxMin[1];
//echo $maxDate;
$dateParts=explode("-",$maxDate);
$maxYear=$dateParts[0];


//echo $arrMonth[$minMonth];
echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay upto ". $maxYear."</h1></span><br/>";

for($k=2015;$k<=$maxYear;$k++)
{
    $year=$k;
    $startDate=$year."-01-01";
    $endDate=$year."-12-01";
    echo displayTop100Data($startDate,$endDate,$year);


}



//displays the top hundred data
function displayTop100Data($startDate,$endDate,$year){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for ".$year ."</H2><br/>";

    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty),ItemId FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
    //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
        $lines.= "No results found";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>ItemId</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

        $count=1;
        $total=0.0;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;

            // $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[5]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
            $total+=floatval($row[4]);
        }

        $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".$total."$</b></td></tr>";
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    //return;
}




?>
