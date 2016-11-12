<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>
<?php
/**
*database access
**/
require_once 'login.php';
require_once ('reportHeader.php');



echo "<h3>Enter the selling price details </h3>";
echo "<table border='0'>";

echo "<form action='". $_SERVER['PHP_SELF']."'  method='post' >";	
echo "<tr><td><h3>Product Id</h3></td><td><input type='text' name='productId' id='productId'></td></tr>";
echo "<tr><td><h3>Product Name</h3></td><td><input type='text' name='productName' id='productName'></td></tr>";
echo "<tr><td><h3>Selling Price $</h3></td><td><input type='text' name='sellingPrice' id='sellingPrice'></td></tr>";
echo "<tr><td><h3>Cost Price $</h3></td><td><input type='text' id='costPrice' name='costPrice'></td></tr>";
echo "<tr><td></td><td><input type='submit' name='insertDetails' value='Add Product'></td></tr>";
echo "</table>";
echo "</form>";
		


if(isset($_POST['insertDetails']))
{
	

	print_r($_POST);
	$itemId=$_POST['productId'];
	$sellingPrice=$_POST['sellingPrice'];
	$productName=$_POST['productName'];
	$costPrice=$_POST['costPrice'];

	$query="INSERT INTO `quickpromultiprice`(`ItemId`, `SellingPrice`, `CostPrice`, `ProductName`) VALUES ('".$itemId."','".$sellingPrice."','".$costPrice."','".mysql_real_escape_string($productName)."')";

	$result = mysql_query($query);
	if(!$result)
	{
		echo "insert failed".mysql_error();
		
	}
	else{
		echo "Price updated successfully";
	}	

}	


?>







