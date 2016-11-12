<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>
<?php
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
?>


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
require_once('helperFunctions.php');

set_time_limit(0);

//get the number of years from the data base
$queryMaxMinDateYear="SELECT max(CreationDate), min(CreationDate) FROM `EbayTransactions`";
$resultMaxMinDateYear=mysql_query($queryMaxMinDateYear);
$rowsMaxMinYear = mysql_fetch_row($resultMaxMinDateYear);
$maxDate=$rowsMaxMinYear[0];
$minDate=$rowsMaxMinYear[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$maxYear=intval($maxYear);
$minYear=intval($minYear);
$minYear=2015;


/*
//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-04-05'));
$endDate=date('Y-m-d',strtotime('2015-04-11'));
//get the max date from the database
$queryMaxMinDate="Select Max(Distinct(CreationDate)) from EbayTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
//echo "Max date".$maxDate;
echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay upto ". $maxDate."</h1></span><br/>";
$week=1;
// $finalOutput='';
while($startDate<$maxDate)
{

    // echo $week."-->".$startDate."-->".$endDate."<br/>";
    echo displayTop100Data($startDate,$endDate,$week);
    $startDate=date('Y-m-d', strtotime($endDate. ' + 1 days'));
    $endDate=date('Y-m-d', strtotime($endDate. ' + 7 days'));
    $week++;
    // if($week>2)
    //     break;


}

echo $finalOutput;
*/


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
	$queryMaxMinDate="SELECT max(CreationDate), min(CreationDate) FROM `EbayTransactions` Where Year(CreationDate)='".$year."'";
   // echo $queryMaxMinDate;
	$resultMaxMinDate=mysql_query($queryMaxMinDate);
    $rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
    $maxDate=$rowsMaxMin[0];
    $minDate=$rowsMaxMin[1];
    $maxYear=date('Y', strtotime($maxDate));
    $minYear=date('Y',strtotime($minDate));
    $startDate=$minDate;
    $endDate=date('Y-m-d',strtotime($startDate.'+7 days'));
	
	echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Weekly Sales from Ebay upto ". $maxDate."</h1></span><br/>";
    $week=1;
	
	 while($startDate<=$maxDate)
	 {
		 // echo $week."-->".$startDate."-->".$endDate."<br/>";
		echo displayTop100Data($startDate,$endDate,$week);
		$startDate=date('Y-m-d', strtotime($endDate. ' + 0 days'));
		$endDate=date('Y-m-d', strtotime($endDate. ' + 6 days'));
		$week++;
	 }
	
	
}

//displays the top hundred data
function displayTop100Data($startDate,$endDate,$week){

    //$lines="<div style='margin-left:10px'>";
    /*$lines.="<H2>Displaying top results for Week ".$week ." between ".$startDate." and ".$endDate."</H2><br/>";

    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty) FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
   
	 //$querySession="SET SESSION group_concat_max_len = 1000000";
	 //mysql_query($querySession);
	 //query to check for the multiple rates
	 //$query="SELECT SKU,Title,GROUP_concat(Qty) AS output,group_concat(SellingPrice),sum(qty) as output,sum(qty*SellingPrice)
                        FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
		


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
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

        $count=1;
        $total=0.0;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
			//$listSP=explode(",",$row[3]);
			
			//if(sizeof(array_unique($listSP))>1)
			//{
			
            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
            $total+=floatval($row[4]);
			//}
        }
        $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', ',')."$</b></td></tr>";
        $lines.="</table>";
    }
    $lines.="</div><br/>";
	
	*/
	
	
	$msg.="<H2>Displaying top results for Week ".$week ." between ".$startDate." and ".$endDate."</H2><br/>";
	$lines='';
    $lines.=getResult($startDate,$endDate,$msg);
    return $lines;
   
    //return;
}




?>
