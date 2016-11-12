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
/**
*database access
**/
require_once 'login.php';



	if ((isset($_GET)&&isset($_GET['ItemId'])))
	{

	// Check to see if the product ID exists
		$query="SELECT * FROM quickpromultiprice WHERE ItemId = '".$_GET['ItemId']."' and SellingPrice='".$_GET['SP']."'";
		//echo $query;
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);
		//echo "The productID: ".$row[0];
		if(strlen($row[0])<=0)
		{
			echo "<h2>Product not Available</h2> ";
			// it should redirect to the new entry form..I think just make the below else cover the entire logic
		}
		else
		{
	
			echo "<h3>The following are the details for ".$_GET['ItemId']."</h3>";
			echo "<table border='0'>";
			
			echo "<form action='". $_SERVER['PHP_SELF']."'  method='post' >";	
			echo "<tr><td><input type='hidden' name='productId' value='".$_GET['ItemId']."'></td></tr>";
			echo "<tr><td><input type='hidden' name='sellingPrice' value='".$_GET['SP']."'></td></tr>";

			echo "<tr><td><h3>Product Id</h3></td><td>".$row[0]."</td></tr>";
			
			echo "<tr><td><h3>Product Name</h3></td><td>".$row[3]."</td></tr>";
			echo "<tr><td><h3>SellingPrice $</h3></td><td>".$row[1]."</td></tr>";
			echo "<tr><td><h3>Cost Price $</h3></td><td><input type='text' id='price' name='price' value='".$row[2]."'></td></tr>";
			echo "<tr><td></td><td><input type='submit' name='updateDetails' value='Update Product'></td></tr>";
			echo "</form>";
			echo "</table>";
			
		}
		
		echo "<a href='updateProductPriceDetails.php' >Back to list</a>";
	
	}		


	if(isset($_POST['updateDetails']))
	{
	// update the mygann database
		
		$Product_ID=$_POST['productId'];
		$sellingPrice=$_POST['sellingPrice'];
		$query="UPDATE `quickpromultiprice` SET  `CostPrice`='".$_POST['price']."' WHERE `ItemId`='".$Product_ID."' 
		and sellingPrice='".$sellingPrice."'";
		//echo $query;
		$result = mysql_query($query);
		if(!$result)
		{
			echo "Update failed".mysql_error();
			exit();
		}
		else{
			echo "Price updated successfully";
			?>
			<script>
				setTimeout(function() {
				  window.location.href = "updateProductPriceDetails.php";
				}, 300);
				</script>
			<?php
		}
		

	}	
	

?>
	