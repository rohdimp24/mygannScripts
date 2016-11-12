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

	//echo $arrMonth[$minMonth];
	echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay for ". $year."</h1></span><br/>";


	$startDate=$year."-01-01";
	$endDate=$year."-12-31";
	//fill in the array with the selling prices
	//print_r($arrProductsWithSPs);    

	echo displayTop100Data($startDate,$endDate,$year);

}
//displays the top hundred data
function displayTop100Data($startDate,$endDate,$year){

    //echo "hi i am in ".$month;
   /* $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for ".$year ."</H2><br/>";

    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty),ItemId FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
    
	$query="SELECT SKU,Title,GROUP_concat(Qty) AS output,group_concat(SellingPrice),sum(qty) as output,sum(qty*SellingPrice)
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
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>ItemId</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

        $count=1;
        $total=0.0;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;

			$listSP=explode(",",$row[3]);
			if(sizeof(array_unique($listSP))>1)
			{
				
				$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
			}

           // $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[5]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
            //$total+=floatval($row[4]);
        }

        $lines.="<tr><td></td><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', ',')."$</b></td></tr>";
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    //return;
	
	*/
	//$lines="<div style='margin-left:10px'>";
   $msg="<H2>Displaying top results for ".$year ."</H2><br/>";
	$lines='';
    $lines.=getResult($startDate,$endDate,$msg);
    return $lines;
	
	
}




?>
