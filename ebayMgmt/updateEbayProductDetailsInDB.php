<?php
/**
*database access
**/
require_once 'login.php';


	//print_r($_GET);
	
	if ((isset($_GET)&&isset($_GET['title']) &&isset($_GET['sku'])))
	{

	// Check to see if the product ID exists
		//$query="SELECT ItemId,SKU,Title,SellingPrice,Shipping,CostPrice FROM EbayTransactions WHERE `SKU` = '".$_GET['sku']."'";
		
		$query="SELECT ItemId,SKU,Title,SellingPrice,Shipping,CostPrice FROM EbayTransactions WHERE `title` = '".base64_decode ($_GET['title'])."'";
		
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
			
			$queryCP="SELECT `Title`, `CostPrice`, `Shipping` from EbayProductCost where `Title` = '".base64_decode ($_GET['title'])."'";
			
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
			
	
			echo "<h3>The following are the details for ".$_GET['sku']."</h3>";
			echo "<table border='0'>";
			
			echo "<form action='". $_SERVER['PHP_SELF']."'  method='post' >";	
			echo "<tr><td><input type='hidden' name='productSku' value='".$_GET['sku']."'></td></tr>";
			echo "<tr><td><input type='hidden' name='productTitle' value='".$_GET['title']."'></td></tr>";
			//echo "<tr><td><input type='hidden' name='sellingPrice' value='".$_GET['SP']."'></td></tr>";
			echo "<tr><td><input type='hidden' name='foundInTable' value='".$found."'></td></tr>";

			echo "<tr><td><h3>Product SKU</h3></td><td>".$row[1]."</td></tr>";
			
			echo "<tr><td><h3>Product Name</h3></td><td>".$row[2]."</td></tr>";
			echo "<tr><td><h3>Ebay Id</h3></td><td>".$row[0]."</td></tr>";
			echo "<tr><td><h3>SellingPrice $</h3></td><td>".$row[3]."</td></tr>";
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
		
		$productSku=$_POST['productSku'];
		$productTitle=base64_decode($_POST['productTitle']);
		
		if($_POST['foundInTable']==1)
		{
			$query="UPDATE `EbayProductCost` SET  `Shipping`='".$_POST['shipping']."', CostPrice='".$_POST['costPrice']."' WHERE `Title`='".$productTitle."' ";
		//echo $query;
		}
		else
		{
			$query="INSERT INTO `EbayProductCost`(`Title`, `SKU`, `CostPrice`, `Shipping`) VALUES ('".$productTitle."','".productSku."','".$_POST['costPrice']."','".$_POST['shipping']."')";
		}
		
		
		//$query="UPDATE `EbayTransactions` SET  `Shipping`='".$_POST['shipping']."', CostPrice='".$_POST['costPrice']."' WHERE //`SKU`='".$productSku."' and Title='".$productTitle."'";
		//echo $query;
		//exit();
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
				  window.location.href = "updateEbayProducts.php";
				}, 300);
				</script>
			<?php
		}
		

	}	
	

?>
	
	
 




