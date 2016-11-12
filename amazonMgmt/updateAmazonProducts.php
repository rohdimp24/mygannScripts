<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>


<?php

require_once ('loginDetails.php');
require_once ('reportHeader.php');

set_time_limit(0);
?>
<br/>
<br/>

<?php
echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Price list of the products</h1></span><br/>";

/*//get the number of years from the data base
$query="SELECT * from quickproPrice";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);
$lines="<div style='margin-left:10px'>";
   
$lines.="<table border='1'>";
$lines.="<tr><th>Sno</th><th>ItemId</th><th>Product Name</th><th>Price</th></tr>";
$count=1;
for($i=0;$i<$rowsnum;$i++)
{
	$row=mysql_fetch_row($result);
	$itemId=$row[0];
	$price=$row[1];
	$productName=$row[2];
	$urlItem=urlencode($itemId);
	//basically somehow get all the occurences of a string this should be the count
	$lines.="<tr><td>".$count++."</td><td>".$itemId."</td><td>".$productName."</td><td>
	".$price."</td><td><a href='updatePriceDetailsInDB.php?ItemId=$urlItem' >update</a></td></tr>";
	
}
$lines.="</table>";
$lines.="</div><br/>";
echo $lines;

*/


#$query="Select `Id`, `Title`, `ItemId`, `AmazonId`, `SellingPrice`, `Shipping`,`CostPrice` from AmazonTransactions GROUP BY ItemId";
$query="Select `Id`, `Title`, A.ItemId, `AmazonId`, `SellingPrice`, B.Shipping,B.CostPrice from AmazonTransactions as A LEFT JOIN  AmazonProductCost as B ON A.ItemId=B.ItemId GROUP BY A.ItemId";
#$query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),Shipping,CostPrice,SUM(CostPrice*Qty) FROM #AmazonTransactions GROUP BY ItemId ORDER BY `output` DESC";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

$oldNum='';
$lines='';
$lines="<div style='margin-left:10px'>";
$lines.="<table border='1'>";
$lines.="<tr><th>Sno</th><th>ItemId</th><th>Amazon Id</th><th>Product Name</th><th>Selling Price</th><th>Cost Price</th><th>Amazon Fees</th><th>Shipping</th><th>Profit per unit</th><th>Profit%</th></tr>";
$count=1;
for($i=0;$i<$rowsnum;$i++)
{
	$row=mysql_fetch_row($result);
	$itemId=$row[2];
	$title=$row[1];
	$sellingPrice=$row[4];
	$amazonId=$row[3];
	$shipping=$row[5];
	if(is_null($shipping))
		$shipping=0.0;
	$costPrice=$row[6];
	if(is_null($costPrice))
		$costPrice=0.00;
	
	$amazonFees=floatval($sellingPrice)*0.20;
	if($costPrice<.1){
				$profitPerc=0;
				$profit=0;
	}
	else
	{
		$denom=$costPrice+$shipping+$amazonFees;
		$profit=$sellingPrice-$denom;		
		$profitPerc=($profit/$denom)*100;
	}
	
	$lines.="<tr><td>".$count++."</td><td>".$itemId."</td><td>".$amazonId."</td><td>
	".$title."</td><td>".$sellingPrice."</td><td>".number_format($costPrice, 2, '.', ',')."</td><td>".$amazonFees."</td><td>".number_format($shipping, 2, '.', ',')."</td><td>".$profit."</td><td>".number_format($profitPerc,2,'.',',')."%</td><td><a href='updateAmazonProductDetailsInDB.php?ItemId=$itemId' >update</a>
	</td></tr>";


}
$lines.="</table>";
$lines.="</div><br/>";
echo $lines;


?>

