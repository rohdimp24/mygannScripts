<?php
if(isset($_COOKIE['ID_my_site']))
{
?>

<?php
/*********************************************************************************************************************
*This script will read from the DB the values of the product for a particular category
* This can be used to check if the entries were made properly
**********************************************************************************************************************/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Finding Products</title>
<style type="text/css">body { font-family: arial,sans-serif; font-size: small; } </style>
</head>
<body>

<a href="http://mygann.com/EbayScripts/Dashboard.php">Back to Dashboard</a>

<?php
// Thsi script will find all the categories from the ebay

  // Turn on all errors, warnings and notices for easier PHP debugging
  //error_reporting(E_ALL);
  # Include http library
	include("LIB_http.php");
	#include parse library
	include("LIB_parse.php");

	include("loginDetails.php");
	require_once 'CategoryData.php';

	$db_server = mysql_connect($db_hostname, $db_username, $db_password);
	if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

	mysql_select_db($db_database)
	or die("Unable to select database: " . mysql_error());
  
	set_time_limit(0);
  
  
	//read all the categories
	$arrCategory=array();
// read the file containing the list of the categories
	$fw=fopen("categoryListForUpdateProducts.txt",'r');
	$catArray=array();
	while(($theData = fgets($fw))!=null)
	{
			$array = split(';', $theData);					
			$catObj = new CategoryData($array[1],$array[0]);
			//array_push($catArray,$theData);
			array_push($arrCategory,$catObj);
	}
	fclose($fw);
	
 
 

 if(isset($_GET))
 {
	 if(isset($_GET['category']))
	 {
		list($categoryId,$overallCategory)=explode(";",$_GET['category']);
		list($subcategory,$mainCategory)=explode(":",$overallCategory);
	 }
 }

 ?>
 <h1>Products</h1>
 <h3> Select the category from which the products to be fetched </h3>
	<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="get" >	
	<table border='0'>
		<?php
		$cat = '<tr><td><h3>Category </h3></td><td><select name="category" id="select">';
		for($i=0;$i<count($arrCategory);$i++)
		{
		
			if(isset($_GET['category']))
			{
				if($categoryId==$arrCategory[$i]->getCatID())
					$cat .='<option value="'.$arrCategory[$i]->getCatID().";".$arrCategory[$i]->getCategoryName().'" selected >'.$arrCategory[$i]->getCategoryName().'</option>';
				else
					$cat .='<option value="'.$arrCategory[$i]->getCatID().";".$arrCategory[$i]->getCategoryName().'" >'.$arrCategory[$i]->getCategoryName().'</option>';
			}
			else
			{
				if($i==0)
					$cat .='<option value="'.$arrCategory[$i]->getCatID().";".$arrCategory[$i]->getCategoryName().'" selected >'.$arrCategory[$i]->getCategoryName().'</option>';
				else
				//echo intval($arrCategory[$i]->getCatID());
					$cat .='<option value="'.$arrCategory[$i]->getCatID().";".$arrCategory[$i]->getCategoryName().'" >'.$arrCategory[$i]->getCategoryName().'</option>';
			}
		}
		$cat .= '</select></td></tr>';
		echo $cat;
		?>	 
		<tr><td><input type='submit' name='submitDetails' value='Find Products For this catgeory'></td></tr>
	</table>
	
	</form> 
	<hr />
	<br />


<?

if(isset($_GET['category']))
 {
    $categoryName=$_GET['category'];
	list($categoryId,$overallCategory)=explode(";",$categoryName);
	list($mainCategory,$subcategory)=explode(":",$overallCategory);
	if(trim($mainCategory)=="NA")
	 {
		$mainCategory=$subcategory;
		$subcategory="NA";
	 }
	echo "<h2>Finding Product for ".$mainCategory.",".$subcategory."</h2>";
	$count=0;	
	 $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";
	$query="SELECT  `product_sku`,`product_name`,`product_thumb_image` FROM  `jos_vm_product_category_xref` , `jos_vm_product` WHERE jos_vm_product_category_xref.`product_id` = jos_vm_product.`product_id` AND  `category_id` = '".$categoryId."'";
	//echo $query;
	$result=mysql_query($query);
	$rowsnum=mysql_num_rows($result);
	echo "<h3>Total Products: ".$rowsnum."</h3><br/>";
	for($i=0;$i<$rowsnum;$i++)
	{
		$row=mysql_fetch_row($result);
		$productNum=$row[0];
		$productName=$row[1];
		//$ebayProductName=$row[2];
		//$productLink=$row[3];
		//$price=$row[4];
		//$ebayCategory=$row[5];
		$mygannCategory=$mainCategory;
		$subCategory=$subcategory;
		$thumbNail=$row[2];
		//$ebayItemCode=$row[11];
		
		$retna.= "<tr> \n";
		$retna .= "<td> \n";
		$retna .= "<img src=\"$thumbNail\" width=150 height=150> <br/>\n";
		$retna .= 'ProductSKU: <b>' . $productNum . "</b><br> \n"; 
		//$retna .= 'EbayItemCode: <b>' . $ebayItemCode . "</b><br> \n";
		$retna .= 'ProductName: <b>' . $productName . "</b><br> \n"; 
		//$retna .= 'Current price: <b>$' .$price. "</b><br> \n";
		//$retna .= 'Ebay Category: <b>' .$ebayCategory. "</b><br> \n";
		$retna .= 'Mygann Catgeory: <b>' .$mygannCategory. "</b><br> \n";
		$retna .= 'Mygann Sub Catgeory: <b>' .$subCategory. "</b><br> \n";
		$retna .= "<b><a href=\"updateSubCategory.php?productNum=$productNum&category=$categoryName\">Click to Update Product</a></b>";
		$retna .= "</td> \n";
		$retna .= "</tr> \n";
	}
	 $retna .= "</table>";
	
	echo $retna;
 }
 ?>
 
 <?php
}
else
{
	header("Location: ProductLogin.php");
}
?>