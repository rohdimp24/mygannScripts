<?php
/*
This script is used to delete a single product from the database. the user will click on the button from the ManageDBProducts.php script
*/
require_once 'loginDetails.php';
//require_once 'CategoryData.php';


$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());


if(isset($_GET['productNum']))
{

    $redirectCategory=trim($_GET['originalCat']);
    $ProductNum=trim($_GET['productNum']);

    $message='';
    $showDebug=true;
    // Check to see if the product ID exists
    $query="SELECT product_id FROM jos_vm_product WHERE product_sku = '".$ProductNum."'";
    //echo $query;
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    //echo "The productID: ".$row[0];
    if(strlen($row[0])<=0)
    {
        $isProductNumExists=false;
        //if($showDebug)
        $message= $message. "Product not available <br/>";
        echo "Product #".$ProductNum ."Not Available Cannot be deleetd<br/>";
        // it should redirect to the new entry form..I think just make the below else cover the entire logic
    }
    else
    {
        $isProductNumExists=true;
        if($showDebug)
            $message= $message. "Product status=".$isProductNumExists."\n";

        // get the product Id from the jos_vm_product
        $query="SELECT `product_id` FROM `jos_vm_product` WHERE `product_sku` = '".$ProductNum."'";
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        $ProductID=$row[0];
        if($showDebug)
            $message= $message. "The product ID for".$ProductNum."is".$ProductID;



        // delete
        $query="DELETE FROM `jos_vm_product` WHERE `product_id` ='".$ProductID."'LIMIT 1";
        //echo $query."<br/>";
        $result =mysql_query($query);
        //echo "result of delete".$result;
        if(!$result)
        {
            echo "The product was not deleted";
            return;
        }

        if($showDebug)
            $message= $message. "deleted from product table"."\n";

        //delete from ebayspecificdetails
        $query="DELETE FROM `ebayspecificdetails` WHERE `productNum` ='".$ProductNum."'LIMIT 1";
        //echo $query."<br/>";
        $result =mysql_query($query);
        //echo "result of delete".$result;
        if(!$result)
        {
            echo "The product was not deleted from ebay table";
            return;
        }

        if($showDebug)
            $message= $message. "deleted from ebay table"."\n";



        //delete the shipping combined cost
        $query="DELETE FROM `ShippingPreferences` WHERE `ProductNum` ='".$ProductNum."'LIMIT 1";
        //echo $query."<br/>";
        $result =mysql_query($query);
        if($showDebug)
            $message= $message. "deleted from shipping prefernece table"."\n";


        // delete the shipping rate information
        $query="DELETE FROM `jos_vm_product_qty_shipping` WHERE `product_sku` ='".$ProductNum."'LIMIT 1";
        //echo $query."<br/>";
        $result =mysql_query($query);
        if($showDebug)
            $message= $message. "deleted from product shipping table"."\n";


        //Delete the Price
        $query="DELETE FROM `jos_vm_product_price` WHERE `product_id` = '".$ProductID."'LIMIT 1";
        $result = mysql_query($query);
        if($showDebug)
            $message= $message. "Deleted the price \n";

        // Dleet the Manufacturer product link
        $query="DELETE FROM `jos_vm_product_mf_xref` WHERE `product_id` = '".$ProductID."'LIMIT 1";
        $result = mysql_query($query);
        if($showDebug)
            $message= $message. "Deleted the Manufacturer product link \n";

        //Delete the vm_product_category_ref
        $query="DELETE FROM `jos_vm_product_category_xref` WHERE `product_id` ='".$ProductID."'LIMIT 1 ";
        $result=mysql_query($query);
        if($showDebug)
            $message= $message. "Deleted the product category link\n";

        // delete extra images
        $query="DELETE FROM `jos_vm_product_files` WHERE `file_product_id` ='".$ProductID."' ";
        $result=mysql_query($query);
        if($showDebug)
            $message= $message. "Deleted the extra images for the product \n";

        $message= $message. "Product".$ProductNum."was successfully deleted from Database <br/>";

    }//end of else  if the product is not there

    echo $message;
    $returnAddress="ManageDBProducts.php?category=".$redirectCategory;

}

?>

<script>
    setTimeout(function() {
        window.location.href = "<? echo $returnAddress ?>";
    }, 3000);
</script>



