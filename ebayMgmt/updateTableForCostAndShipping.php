
<!--
This script will act as a cronjob to fetch the data for todaya and previous day.
-->
<?php

require_once ('login.php');
set_time_limit(0);


//get the max date in the db
$queryProducts="Select Title from EbayTransactions group by Title";
$resultProducts=mysql_query($queryProducts);
$rowsnumProducts=mysql_num_rows($resultProducts);
$arrProductTitle=array();
for($i=0;$i<$rowsnumProducts;$i++)
{
    $rowProducts=mysql_fetch_row($resultProducts);
    $title=$rowProducts[0];
    array_push($arrProductTitle, $title);
}

//print_r($arrProductTitle);
$arrObjects=array();
$len=sizeof($arrProductTitle);
for($i=1;$i<$len;$i++)
{
    $title=$arrProductTitle[$i];
    $queryDetails="Select Shipping,CostPrice,SKU from EbayTransactions where title='".$title."' ORDER BY  `CostPrice` DESC";
    //echo $queryDetails."<br/>";
    $resultDetails=mysql_query($queryDetails);
    $rowsnum=mysql_num_rows($resultDetails);
    //echo $rowsnum;
    $rowsDetails=mysql_fetch_row($resultDetails);
    //print_r($rowsDetails);
    $shipping=$rowsDetails[0];
    $costPrice=$rowsDetails[1];
    $sku=$rowsDetails[2];
    //echo $sku."=>".$costPrice."=>".$shipping."<br/>";


    /*$obj=new stdClass();
    $obj->sku=$sku;
    $obj->title=$title;
    $obj->costPrice=$costPrice;
    $obj->shipping=$shipping;
    array_push($arrObjects, $obj);
	*/
    //print_r($obj);
    $queryInsert="INSERT INTO `EbayProductCost`(`Title`, `SKU`, `CostPrice`, `Shipping`) VALUES 
                  ('".$title."','".$sku."','".$costPrice."','".$shipping."')";

    echo $queryInsert."<br/>";

    $resultInsert=mysql_query($queryInsert);
    if(!$resultInsert)
    {
         echo mysql_error();
    }

}

/*print_r($arrObjects);
#require_once ('login.php');
$len=sizeof($arrObjects);
for($i=0;$i<$len;$i++)
{
    $obj=$arrObjects[$i];
    $title=$obj->title;
    $sku=$obj->sku;
    $costPrice=$obj->costPrice;
    $shipping=$obj->shipping;
   // echo $sku."=>".$costPrice."=>".$shipping."<br/>";
    $queryInsert="INSERT INTO `EbayProductCost`(`Title`, `SKU`, `CostPrice`, `Shipping`) VALUES 
                  ('".$title."','".$sku."','".$costPrice."','".$shipping."')";

    echo $queryInsert."<br/>";

    $resultInsert=mysql_query($queryInsert);
    if(!$resultInsert)
    {
         echo mysql_error();
    }

}*/


//print_r($arrObjects);


/*for($i=0;$i<$rowsnumProducts;$i++)
{

    

    $rowProducts=mysql_fetch_row($resultProducts);
    $title=$rowProducts[0];
    
    if($i==0)
        continue;
    //find out if it has got any cp

    echo $title."<br/>";

    
    $queryDetails="Select Shipping,CostPrice,SKU from ebayTransactions where title='".$title."' ORDER BY  `CostPrice` DESC";
    echo $queryDetails;
    $resultDetails=mysql_query($queryDetails);
    print_r($resultDetails);
    $rowsNumDetails=mysql_num_rows($resultDetails);
    echo $rowsNumDetails;
    //for($j=0;$j<$rowsNumDetails;$j++)
    //{
    $rowsDetails=mysql_fetch_row($resultDetails);
    print_r($rowsDetails);
    //}

   // $shipping=$rowsDetails[0];
   // $costPrice=$rowsDetails[1];
    //$sku=$rowsDetails[2];
    //$title=$rowsDetails[2];
    
    //echo $sku."=>".$costPrice."=>".$shipping."<br/>";
    
    //insert the data in the table
    // $queryInsert="INSERT INTO `amazonproductcost`(`ItemId`, `CostPrice`, `Shipping`) VALUES 
    //                 ('".$itemId."','".$costPrice."','".$shipping."')";

    //echo $queryInsert."<br/>";
    // $resultInsert=mysql_query($queryInsert);
    // if(!$resultInsert)
    // {
    //     echo mysql_error();
    // }


}

*/




?>
