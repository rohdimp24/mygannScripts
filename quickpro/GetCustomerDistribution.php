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
require_once ('login.php');
require_once ('reportHeader.php');

set_time_limit(0);

$queryProducts = "SELECT ItemId,ProductName,count(DISTINCT(CustomerName)) as output  FROM `quickpro` group by ItemId order by output DESC";
$resultProducts = mysql_query($queryProducts);
$rowsnumProducts = mysql_num_rows($resultProducts);

echo "<span><h1  style='padding-left:20px;margin-top:30px;'>Customer Distribution Data</h1></span><br/>";

echo "<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Title</th><th>Total Customers</th><th>Customer Name</th></tr>";

$count=1;
for ($i = 0; $i < $rowsnumProducts; $i++) {

    $rowProduct=mysql_fetch_row($resultProducts);
    $itemId=$rowProduct[0];
    $productName=$rowProduct[1];
    echo "<tr>";
    echo "<td>".$count++."</td>";
    echo "<td>".$itemId."</td>";
    echo "<td>".$productName."</td>";
    //get the customer details for this product
    $queryCustomer="Select DISTINCT (CustomerName) from quickpro where ItemId='".$itemId."'";
    $resultCustomer=mysql_query($queryCustomer);
    $rowsnumCustomer=mysql_num_rows($resultCustomer);
    echo "<td>".$rowsnumCustomer."</td>";
    echo "<td></td>";
    echo "</tr>";

    for($j=0;$j<$rowsnumCustomer;$j++)
    {
        echo "<tr><td></td><td></td><td></td><td></td>";
        $rowCustomer=mysql_fetch_row($resultCustomer);
        echo "<td>".$rowCustomer[0]."</td>";
        echo "</tr>";
    }






}

echo "</table>";


?>