<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 2/11/15
 * Time: 4:08 PM
 */
include("loginDetails.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

?>

<h1>Enter the date for which you want to check the synced up products in yyyy-mm-dd format</h1>

	<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
	<table border='0'>
		<?php
		$cbo = '<tr><td>Date From (yyyy-mm-dd)*</td><td><input id="startDate" name="startDate" /></td>';
        $cbo.= '<tr><td>Date To (yyyy-mm-dd)*</td><td><input id="endDate" name="endDate" /></td>';
		echo $cbo;

		?>
<tr><td><input type='submit' name='submitDetails' value='Find Products For this catgeory'></td></tr>
</table>

</form>
<hr />
<br />

<?php

if($_POST['submitDetails'])
{
    if(strlen(trim($_POST['endDate'])<5))
    {
        echo "end date not provided";
        exit();
    }

    if(strlen(trim($_POST['startDate'])<5))
    {
        echo "start date not provided";
        exit();
    }

    $endDate=trim($_POST['endDate']);
    $startDate=trim($_POST['startDate']);


    $query="SELECT * FROM ebaySyncUp,csv_product WHERE ebaySyncUp.ebayItemCode=csv_product.ebayItemCode and ebaySyncUp.addedOnDate <='".$endDate."'
    and ebaySyncUp.addedOnDate >='".$startDate."'";
//    echo $query;
    //and  ebaySyncUp.ebayItemCode='171661758167'";
    $result=mysql_query($query);
    $rowsNum=mysql_num_rows($result);
    $retna='';

    if($rowsNum==0)
        echo "<h3> No products were synced up between ".$startDate." and ".$endDate."</h3>";
    else
    {
        echo "<h2>Total Products Added from ".$startDate." to ".$endDate." => ".$rowsNum."</h2>";
        $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"1\"> \n";
        $retna .= "<tr><th>EbayId</th><th>MygannId</th><th>ItemLink</th><th>productName</th>
        <th>Mygann Category</th><th>Subcategory</th><th>Added on</th>";

        for($i=0;$i<$rowsNum;$i++)
        {
            $row=$productRow=mysql_fetch_assoc($result);
            $itemURL=$row[0];
            $productNum=$productRow['product_id'];
            $productName=str_replace("&quot;","\"",$productRow['product_name']);
            $productLink=$productRow['product_url'];
            $price=$productRow['product_price'];
            $ebayItemCode=$productRow['ebayItemCode'];
            $thumbNail=$productRow['product_thumb_image'];
            $combinedShipping=$productRow['combined_shopping'];
            $shippingRate=$productRow['shipping_rate'];
            $category=$productRow['product_mygann_category'];
            $subcategory=$productRow['product_subcategory'];
            $categoryId=$productRow['product_categoryId'];
            $retna.= "<tr>";
            //$retna .= "<img src=\"$thumbNail\"> \n";
            $retna .= '<td>' .$ebayItemCode. "</td>";
            $retna .= '<td>' . $productNum . "</td>";

            $retna .= "<td><p><a href=\"" . $productLink . "\">".$productName.  "</a></p></td>";
            $retna .= '<td>' . $productName . "</td>";
           // $retna .= '<td>' .$price. "</td>";
            $retna .= '<td>' .$category. "</td>";
            $retna .= '<td>' .$subcategory. "</td>";
            $retna .= '<td>' .$productRow['addedOnDate']. "</td>";
            //$retna .= '<td>' .$size. "</td>";
            //$retna .= '<td>' .$qty. "</td>";
            $retna .= "</tr> \n";

        }
        $retna .= "</table>";
        echo $retna;
    }


}



?>