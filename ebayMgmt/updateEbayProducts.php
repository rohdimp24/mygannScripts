<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>


<?php

require_once ('login.php');
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


#$query="SELECT ItemId,SKU,Title,SellingPrice,Shipping,CostPrice FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 GROUP #BY Title order by SKU";
$query="Select ItemId,A.SKU,A.Title,SellingPrice,B.Shipping,B.CostPrice from EbayTransactions as A LEFT JOIN  EbayProductCost as B ON A.Title=B.Title Where A.SKU!='' AND SellingPrice > 0.00 GROUP BY A.Title order by A.SKU";


/*$query="SELECT ItemId,SKU,Title,SellingPrice,Shipping,CostPrice FROM EbayTransactions Where SKU='T-490' AND SellingPrice > 0.00 GROUP BY Title";
*/
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

$oldNum='';
$lines='';
$lines="<div style='margin-left:10px'>";
$lines.="<table border='1'>";
$lines.="<tr><th>Sno</th><th>SKU</th><th>Ebay Id</th><th>Product Name</th><th>Selling Price</th><th>Cost Price</th><th>Ebay Fees</th><th>Shipping</th><th>Profit per unit</th><th>Profit%</th></tr>";
$count=1;
for($i=0;$i<$rowsnum;$i++)
{
	$row=mysql_fetch_row($result);
	$ebayId=$row[0];
	$title=$row[2];
	$sellingPrice=$row[3];
	$sku=$row[1];
	$shipping=$row[4];
	$costPrice=$row[5];
	$urlTitle=base64_encode ($title);
	
	$ebayFees=floatval($sellingPrice)*0.20;
	if($costPrice<.1){
				$profitPerc=0;
				$profit=0;
	}
	else
	{
		$denom=$costPrice+$shipping+$ebayFees;
		$profit=$sellingPrice-$denom;		
		$profitPerc=($profit/$denom)*100;
	}
	
	
	$lines.="<tr><td>".$count++."</td><td>".$sku."</td><td>".$ebayId."</td><td>
	".$title."</td><td>".$sellingPrice."</td><td>".$costPrice."</td><td>".$ebayFees."</td><td>".$shipping."</td><td>".$profit."</td><td>".number_format($profitPerc,2,'.',',')."%</td><td><a href='updateEbayProductDetailsInDB.php?title=$urlTitle & sku=$sku' >update</a></td></tr>";


}
$lines.="</table>";
$lines.="</div><br/>";
echo $lines;


?>

