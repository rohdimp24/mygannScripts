<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 2/13/15
 * Time: 10:10 AM
 */ /*********************************************************************************************************************
 *This script will read from the DB the values of the product for a particular category
 * This can be used to check if the entries were made properly
 **********************************************************************************************************************/
// Thsi script will find all the categories from the ebay

// Turn on all errors, warnings and notices for easier PHP debugging
//error_reporting(E_ALL);
# Include http library
include("LIB_http.php");
#include parse library
include("LIB_parse.php");

include("loginDetails.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

set_time_limit(0);

// Define global variables
$StoreName="Seashells-Plus-More";

echo $StoreName."<br/>";

//read all the duplicate ebay ids
//$arrDuplicateEbay=array();

$query="SELECT product_id,csv_product.product_name,csv_product.product_ebay_name,ebayItemCode,product_thumb_image,product_mygann_category,product_subcategory FROM csv_product
INNER JOIN (SELECT product_ebay_name FROM csv_product
GROUP BY product_ebay_name HAVING count(product_ebay_name) > 1) dup ON csv_product.product_ebay_name = dup.product_ebay_name
ORDER BY `csv_product`.`product_name` ASC ";

$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

$retna = "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";

$arrDuplicates=array();
$arrEbayProductName=array();
echo $rowsnum;
for($i=0;$i<$rowsnum;$i++)
{
    $row=mysql_fetch_row($result);
    $productNum=$row[0];
    $productName=$row[1];
    $ebayProductName=$row[2];
    $ebayItemCode=$row[3];
    $thumbNail=$row[4];
    $mygannCategory=$row[5];
    $subCategory=$row[6];

    if(in_array($ebayProductName,$arrEbayProductName))
    {
        array_push($arrDuplicates,$productNum);
    }
    else
    {
        array_push($arrEbayProductName,$ebayProductName);
    }

    $retna.= "<tr> \n";
    $retna .= "<td> \n";
    $retna .= "<img src=\"$thumbNail\"> <br/>";

    $retna .= '<b>EbayName:</b>'.$ebayProductName."<br/>";
    //$retna .= "<p><a href=\"" . $productLink . "\">" . $ebayProductName . "</a></p>\n";
    $retna .= '<b>ProductSKU: </b>' . $productNum . "<br> \n";
    $retna .= '<b>EbayItemCode: </b>' . $ebayItemCode . "<br> \n";
    $retna .= '<b>ProductName: </b>' . $productName . "<br> \n";
    $retna .= '<b>Mygann Catgeory: </b>' .$mygannCategory. "<br> \n";
    $retna .= '<b>Mygann Sub Catgeory: </b>' .$subCategory. "<br> \n";
    $retna .= "</td> \n";
    $retna .= "</tr> \n";

 }
    $retna .= "</table>";

    echo $retna;
    echo "<hr/>";
    echo "<h2>the duplicates are </h2>";

    $len=sizeof($arrDuplicates);
    for($j=0;$j<$len;$j++)
    {
    if($j==($len-2))
        echo $arrDuplicates[$j];
    else
        echo $arrDuplicates[$j].",";
    }



    echo "<br/><br/> copy the list and perform a bulk delete using  <a href='http://mygann.com/EbayScripts/DeleteProductsFromDatabase.php'> DeleteProductsFromDatabase </a>";


?>