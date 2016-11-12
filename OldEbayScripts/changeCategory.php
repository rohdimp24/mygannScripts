<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 2/20/15
 * Time: 6:30 AM
 */


require_once 'loginDetails.php';
//require_once 'CategoryData.php';


$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

//print_r($_GET);

if(isset($_GET['category'])&&isset($_GET['productNum']))
{
//    echo "hi";
    $category=trim($_GET['category']);
    $redirectCategory=trim($_GET['originalCat']);

    list($categoryId,$overallCategory)=explode(";",$_GET['category']);

    $Product_Num=trim($_GET['productNum']);

    $query="SELECT product_id FROM jos_vm_product WHERE product_sku = '".$_GET['productNum']."'";
//    echo $query."<br/>";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    $Product_ID=$row[0];
//    echo $Product_ID;
//    echo $CatID;


    $query="UPDATE `jos_vm_product_category_xref` SET  `category_id`='".$categoryId."' WHERE `product_id`='".$Product_ID."'";
//    echo $query."<br/>";
    $result = mysql_query($query);
    //$message='';
    if(! $result )
    {
        $message = " Category Update Failed \n";
        print_r(mysql_error());
    }
    else
    {
        $message = "Category Update Sucessful \n";
    }

    echo $message;
    $returnAddress="ManageDBProducts.php?category=".$redirectCategory;

}
?>
<script>
setTimeout(function() {
    window.location.href = "<? echo $returnAddress ?>";
}, 100);
</script>