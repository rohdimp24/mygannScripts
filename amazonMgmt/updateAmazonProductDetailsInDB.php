<?php
/**
*database access
**/
require_once 'loginDetails.php';



	if ((isset($_GET)&&isset($_GET['ItemId'])))
	{

	// Check to see if the product ID exists
		$query="SELECT `ItemId`,`Title`, `AmazonId`, `SellingDate`, `Qty`, `SellingPrice`, `Shipping`,`CostPrice` FROM `AmazonTransactions` WHERE `ItemId` = '".$_GET['ItemId']."'";
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
		
			$queryCP="SELECT `ItemId`, `CostPrice`, `Shipping` from AmazonProductCost where ItemId='".$_GET['ItemId']."'";
			
			$resultCP=mysql_query($queryCP);
			$rowsnum=mysql_num_rows($resultCP);
			$found=1;
			if($rowsnum==0)
			{
				echo "NEW PROD";
				$costPrice=0.0;
				$shipping=0.0;
				$found=0;
			}
			else
			{
				$rowCP=mysql_fetch_row($resultCP);
				$costPrice=$rowCP[1];
				$shipping=$rowCP[2];
				$found=1;
			}
			
			
			echo "<h3>The following are the details for ".$_GET['ItemId']."</h3>";
			echo "<table border='0'>";
			
			echo "<form action='". $_SERVER['PHP_SELF']."'  method='post' >";	
			echo "<tr><td><input type='hidden' name='productId' value='".$_GET['ItemId']."'></td></tr>";
			echo "<tr><td><input type='hidden' name='foundInTable' value='".$found."'></td></tr>";

			echo "<tr><td><h3>Product Id</h3></td><td>".$row[0]."</td></tr>";
			
			echo "<tr><td><h3>Product Name</h3></td><td>".$row[1]."</td></tr>";
			echo "<tr><td><h3>Amazon Id</h3></td><td>".$row[2]."</td></tr>";
			echo "<tr><td><h3>SellingPrice $</h3></td><td>".$row[5]."</td></tr>";
			echo "<tr><td><h3>CostPrice $</h3></td><td><input type='text' id='costPrice' name='costPrice' value='".$costPrice."'></td></tr>";
			echo "<tr><td><h3>Shipping $</h3></td><td><input type='text' id='shipping' name='shipping' value='".$shipping."'></td></tr>";
			echo "<tr><td></td><td><input type='submit' name='updateDetails' value='Update Product'></td></tr>";
			echo "</table>";
			echo "</form>";
			
		}
	
	}		


	if(isset($_POST['updateDetails']))
	{
	// update the mygann database
		
		$Product_ID=$_POST['productId'];
		if($_POST['foundInTable']==1)
		{
			$query="UPDATE `AmazonProductCost` SET  `Shipping`='".$_POST['shipping']."', CostPrice='".$_POST['costPrice']."' WHERE `ItemId`='".$Product_ID."' ";
		//echo $query;
		}
		else
		{
			$query="INSERT INTO `AmazonProductCost`(`ItemId`, `CostPrice`, `Shipping`) VALUES ('".$Product_ID."','".$_POST['costPrice']."','".$_POST['shipping']."')";
		}
		$result = mysql_query($query);
		if(!$result)
		{
			echo "Update failed".mysql_error();
			exit();
		}
		else{
			echo "<h2>Shipping for ".$Product_ID." updated successfully</h2>";
			?>
			<script>
				setTimeout(function() {
				  window.location.href = "updateAmazonProducts.php";
				}, 300);
				</script>
			<?php
		}
		

	}	
	

?>
	
	
 




