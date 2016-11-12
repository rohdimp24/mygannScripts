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
require_once ('login.php');
require_once ('reportHeader.php');
require_once ('helperFunctions.php');

set_time_limit(0);
?>
<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/13/15
 * Time: 10:27 PM
 */



$query="Select DISTINCT(Title),SKU from EbayTransactions where sellingPrice='0' and CreationDate>'2016-01-01'";
echo $query;
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);
$arrNotFound=array();
echo "<h1> Price to verify </h1>";
$str="<table border='1'>";
$str.="<tr><th>Title</th><th>SKU</th><th>SellingPrice</th><th>Last known SellingPrice Date</th></tr>";
for($i=0;$i<$rowsnum;$i++)
{
    $row=mysql_fetch_row($result);
    //echo $row[0]."--->";
    #get the selling price
    $lastPriceQuery="SELECT Title,SKU, SellingPrice,CreationDate FROM `EbayTransactions` WHERE  SellingPrice>0 and Title='".$row[0]."' order by CreationDate DESC";
    $lastPriceResult=mysql_query($lastPriceQuery);
    $rowLastPrceResult=mysql_fetch_row($lastPriceResult);
	if($rowLastPrceResult[2]>0)
		$str.="<tr><td>".$rowLastPrceResult[1]."</td><td>".$rowLastPrceResult[0]."</td><td>".$rowLastPrceResult[2]."</td><td>".$rowLastPrceResult[3]."</td></tr>";
	
	else
		array_push($arrNotFound,$row);
	//if($rowLastPrceResult[2]>0)
	//echo $rowLastPrceResult[1]."=>".$rowLastPrceResult[0]."=>".$rowLastPrceResult[2]."=>".$rowLastPrceResult[3]."<br/>";
}
$str.="</table>";

echo $str;

echo "<br/>";
echo "<h1>The products without selling price ever registered </h1>";
$str="<table border='1'>";
for($i=0;$i<sizeof($arrNotFound);$i++)
{
	$rowLastPrceResult=$arrNotFound[$i];
	$str.="<tr><td>".$rowLastPrceResult[1]."</td><td>".$rowLastPrceResult[0]."</td></tr>";
	
	
}

$str.="</table>";

echo $str;






?>