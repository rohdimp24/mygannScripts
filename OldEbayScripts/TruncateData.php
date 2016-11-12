<?php
if(isset($_COOKIE['ID_my_site']))
{
?>

<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fz015992
 * Date: 10/1/12
 * Time: 5:32 PM
 * Use this to delete the product data
 */

require_once 'loginDetails.php';
echo "please contact rohit to perform this operation";
exit();
//require_once'ProductDetails.php';
/*$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
    or die("Unable to select database: " . mysql_error());


echo "deleting the Extra images for the product from jos_vm_product_files<br/>";
$query="TRUNCATE TABLE `jos_vm_product_files`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product_files table". mysql_error();
else
    echo "jos_vm_product_files successfully truncated <br/>";


echo "deleting the jos_vm_product_category_xref table to remove the category-product link <br/>";
$query="TRUNCATE TABLE `jos_vm_product_category_xref`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product_category_xref table". mysql_error();
else
    echo "jos_vm_product_category_xref successfully truncated <br/>";


echo "deleting the jos_vm_product_mf_xref table to remove the manufacturer-product link <br/>";
$query="TRUNCATE TABLE `jos_vm_product_mf_xref`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product_mf_xref table". mysql_error();
else
    echo "jos_vm_product_mf_xref successfully truncated <br/>";


echo "deleting the jos_vm_product_qty_shipping table to remove the shipping rate-product link <br/>";
$query="TRUNCATE TABLE `jos_vm_product_qty_shipping`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product_qty_shipping table". mysql_error();
else
    echo "jos_vm_product_qty_shipping successfully truncated <br/>";

echo "deleting the jos_vm_product_price table to remove the price rate-product link <br/>";
$query="TRUNCATE TABLE `jos_vm_product_price`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product_price table". mysql_error();
else
    echo "jos_vm_product_price successfully truncated <br/>";


echo "deleting the ShippingPreferences table to remove the combined shipping-product link <br/>";
$query="TRUNCATE TABLE `ShippingPreferences`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the ShippingPreferences table". mysql_error();
else
    echo "ShippingPreferences successfully truncated <br/>";


echo "deleting the ebayspecificdetails table to remove the combined ebay-product link <br/>";
$query="TRUNCATE TABLE `ebayspecificdetails`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the ebayspecificdetails table". mysql_error();
else
    echo "ebayspecificdetails successfully truncated <br/>";




echo "deleting the jos_vm_product table to remove the combined products <br/>";
$query="TRUNCATE TABLE `jos_vm_product`";
$result=mysql_query($query);
if(!$result)
    echo "could not delete the jos_vm_product table". mysql_error();
else
    echo "jos_vm_product successfully truncated <br/>";
*/
?>
<?php
}
else
{
	header("Location: ProductLogin.php");
}
?>