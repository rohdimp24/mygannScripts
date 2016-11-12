
<!--
This script will act as a cronjob to fetch the data for todaya and previous day.
-->
<?php

require_once ('loginDetails.php');
set_time_limit(0);


//get the max date in the db
$queryProducts="Select Distinct(ItemId) from AmazonTransactions";
$resultProducts=mysql_query($queryProducts);
$rowsnumProducts=mysql_num_rows($resultProducts);
for($i=0;$i<$rowsnumProducts;$i++)
{

    $rowProducts=mysql_fetch_row($resultProducts);
    $itemId=$rowProducts[0];
    
    //find out if it has got any cp

    echo $itemId."=>";


    $queryDetails="Select Shipping,CostPrice from AmazonTransactions where ItemId='".$itemId."' ORDER BY  `CostPrice` DESC";
    //echo $queryDetails;
    $resultDetails=mysql_query($queryDetails);
    //$rowsNumDetails=mysql_num_rows($resultDetails);
    //for($j=0;$j<$rowsNumDetails;$j++)
    //{
    $rowsDetails=mysql_fetch_row($resultDetails);
    //print_r($rowsDetails);
    //}

    $shipping=$rowsDetails[0];
    $costPrice=$rowsDetails[1];
    //$title=$rowsDetails[2];

    echo $costPrice."=>".$shipping."<br/>";

    //insert the data in the table
    $queryInsert="INSERT INTO `AmazonProductCost`(`ItemId`, `CostPrice`, `Shipping`) VALUES 
                    ('".$itemId."','".$costPrice."','".$shipping."')";

    echo $queryInsert."<br/>";
    $resultInsert=mysql_query($queryInsert);
    if(!$resultInsert)
    {
        echo mysql_error();
    }


}






?>
