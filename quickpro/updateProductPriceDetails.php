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


$query="Select * from quickpromultiprice";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

$oldNum='';
$lines='';
$lines="<div style='margin-left:10px'>";
$lines.="<table border='1'>";
$lines.="<tr><th>Sno</th><th>ItemId</th><th>Product Name</th><th>Selling Price</th><th>Cost Price</th><th>Profit</th><th>Profit Percent</th></tr>";
$count=1;
for($i=0;$i<$rowsnum;$i++)
{
	$row=mysql_fetch_row($result);
	$itemId=$row[0];
	$sellingPrice=$row[1];
	$costPrice=$row[2];
	$productName=$row[3];
	$profit=($sellingPrice-$costPrice);
	if($costPrice<.01)
	{
		$profit=0;
		$profitPerc=0;
	}
	else
		$profitPerc=($profit/$costPrice)*100;
	if($oldNum!=$itemId)
	{
		$oldNum=$itemId;
		$lines.="<tr style='background:red'><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
	}
	
	$urlItem=urlencode($itemId);
	
	$lines.="<tr><td>".$count++."</td><td>".$itemId."</td><td>".$productName."</td><td>
	".$sellingPrice."</td><td>".$costPrice."</td><td>".number_format($profit,2,'.',',')."</td><td>".number_format($profitPerc,2,'.',',')."%</td><td><a href='updatePriceDetailsInDB.php?ItemId=$urlItem & SP=$sellingPrice' >update</a>
	</td></tr>";

}
$lines.="</table>";
$lines.="</div><br/>";
echo $lines;


?>

