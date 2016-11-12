<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php
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



$query="Select Title,SKU, ItemId,CreationDate from EbayTransactions where `CreationDate`>'2016-01-01' and sellingPrice='0' order by CreationDate DESC";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

echo "<h1> Products with SP 0 </h1>";
$str="<table border='1'>";
$str.="<tr><th>Sno</th><th>Title</th><th>SKU</th><th>ItemId</th><th>CreationDate</th></tr>";
$count=0;
for($i=0;$i<$rowsnum;$i++)
{
	$count+=1;
    $row=mysql_fetch_row($result);
    $str.="<tr><td>".$count."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td></tr>";
      
}
$str.="</table>";

echo $str."<br/>";




?>