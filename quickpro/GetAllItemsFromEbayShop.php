<?
/********************************************************************************************************************
 * This script is used to fetch the raw xml from the ebay
 ********************************************************************************************************************/
?>

    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title>Finding Products</title>
        <style type="text/css">body { font-family: arial,sans-serif; font-size: small; } </style>
    </head>
    <body>

<?php
// Thsi script will find all the categories from the ebay

// Turn on all errors, warnings and notices for easier PHP debugging
//error_reporting(E_ALL);
# Include http library
#include("LIB_http.php");
#include parse library
include("LIB_parse.php");
require_once ('login.php');

set_time_limit(0);

// Define global variables
$m_endpoint = "http://svcs.ebay.com/services/search/FindingService/v1?";  // shpping URL to call
$cellColor = "bgcolor=\"#dfefff\"";  // Light blue background used for selected items
$appid = 'rohit8cdf-21d7-48e8-965f-8582146cc49';
$responseEncoding = 'XML';  // Type of response we want back
$urlForCSV='';
$StoreName="Seashells-Plus-More";

//echo $StoreName;


function getProductsFromStore($totalPages)
{

    /*$fp=fopen("CategoriesExample.csv",'w');
    $fs=fopen("CatCount.txt",'w');
    $fh=fopen("CategoryExamples.txt","w");
    */

    //$arrCategory=array();

   // $totalPages=20;
    for($i=2;$i<$totalPages;$i++)
    {
        $fileName="Products//ProductList_Page_new_".$i.".xml";
        echo $fileName."<br/>";
        //$fileName="ProductList_Page_new.xml";
        $fp=fopen($fileName,'w');
        //$resp = simplexml_load_file("v1.xml");
        //print_r($resp);
        $resp=getXMLFromEBay($i);
        //echo $resp;
        fwrite($fp,$resp);
        fclose($fp);
        updateEbayDBWithFile($fileName,'');

//        $resp=simplexml_load_file($fileName);
//        print_r($resp);
//
//        echo "total Pages".$resp->paginationOutput->totalPages;

//
    }


} // End of getMostWatchedItemsResults function


function updateEbayDBWithFile($fileName,$updateString)
{


    $resp=simplexml_load_file($fileName);

    //print_r($resp);

    if ($resp)
    {
        // Set return value for the function to null
        $retna = '';

        // Verify whether call was successful
        if ($resp->ack == "Success")
        {
            // If there were no errors, build the return response for the function
            // $retna .= "<h1>Total Items Found from $StoreName (".$resp->paginationOutput->totalEntries.")</h1>";

            //echo $retna;
            // Build a table for the products found
            //$retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";

            // For each item node, build a table cell and append it to $retna
            foreach($resp->searchResult->item as $item)
            {

              //  $retna.="<tr>".$item->title."</tr>";

                //$sku=split_string($item->title, '#', AFTER, EXCL);
                //$title=split_string($item->title, '#', BEFORE, EXCL);
                $sku=getItemSKU($item->title);
                if(strlen($sku)>1)
                {
                    $title=$item->title;
                    $itemId=$item->itemId;
                    $sellingStatus=$item->sellingStatus->sellingState;
                    $thumbnail=$item->galleryURL;
                    $itemUrl=$item->viewItemURL;
                    $fullPhoto=$item->galleryPlusPictureURL;
                    $sellingPrice=$item->sellingStatus->currentPrice;
                    $timeLeft=$item->sellingStatus->timeLeft;
                    echo $item->title."=>".$item->itemId."=>".$sku."=>".$sellingStatus."<br/>";
                    /*$query="INSERT INTO `EbayProductsForTx`(`EbayItemId`, `Title`, `SKU`, `SellingStatus`)
                                VALUES ('".$itemId."','".mysql_real_escape_string($title)."','".$sku."','".$sellingStatus."')";
                    */
                    $startDate=date('Y-m-d', strtotime($item->listingInfo->startTime));
                    $endDate=date('Y-m-d', strtotime($item->listingInfo->endTime));
                    $now=date('Y-m-d');
                    if($endDate<$now)
                        $isDiscontinued=1;
                    else
                        $isDiscontinued=0;


                    $queryCheck="Select * from EbayProductsForTx  where EbayItemId='".$itemId."'";
                    $resultCheck=mysql_query($queryCheck);
                    $rowsnumCheck=mysql_num_rows($resultCheck);
                    if($rowsnumCheck>0)
                        $query="UPDATE `EbayProductsForTx` SET `Title`= '".mysql_real_escape_string($title)."',
                        `SKU`='".$sku."',`StartDate`='".$startDate."',
                        `EndDate`='".$endDate."',
                        `continueDiscontinue`='".$isDiscontinued."',thumbnail='".$thumbnail."',
                        fullPhoto='".$fullPhoto."',itemUrl='".$itemUrl."',sellingPrice='".$sellingPrice."',timeLeft='".$timeLeft."' where EbayItemId='".$itemId."'";
                    else
                        $query="INSERT INTO `EbayProductsForTx`(`EbayItemId`, `Title`, `SKU`, `SellingStatus`, `StartDate`, `EndDate`, `continueDiscontinue`)
                                VALUES ('".$itemId."','".mysql_real_escape_string($title)."','".$sku."','".$sellingStatus."','".$startDate."','".$endDate."','".$isDiscontinued."')";

                    $result=mysql_query($query);
                    if(!$result)
                    {
                        echo mysql_error();
                        echo $query."<br>";
                    }
                    else
                    {
                        //array_push($arrayUpdatedProducts,$itemId);
                        $updateString.=":".$itemId;
                       // echo $query."<br/>";
                    }


                }





            }
            //$retna .= "</table>";

        }
        else
        {
            echo "call response has some errror";

        }  // if errors
        return $updateString;
    }
    else
    {
        // If there was no response, print an error
        echo "skdsljdl";
       // $retna .= "Call used was: $apicalla";
    }  // End if response exists

    // Return the function's value
   /// echo $retna;


}



function getXMLFromEBay($pageNumber='1')
{

    global $m_endpoint;
    global $responseEncoding;
    global $appid;
    global $StoreName;
    /// add some global variables
    $apicalla  = "$m_endpoint";
    $apicalla .= "OPERATION-NAME=findItemsIneBayStores"; // name of the API we are interested in
    $apicalla .= "&SERVICE-VERSION=1.8.0";
    $apicalla .= "&SECURITY-APPNAME=$appid"; // the app id
    $apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
    $apicalla .= "&storeName=$StoreName";
    $apicalla .= "&paginationInput.pageNumber=$pageNumber"; // The page to view
    $apicalla .= "&paginationInput.entriesPerPage=100";  // name of the category
    $apicalla .= "&sortOrder=StartTimeNewest";  // name of the category

    //echo "the call used is ".$apicalla;
    $shopName= $StoreName;
    echo $apicalla;
    // finally send this call to the server and the response will be collected in the $resp
    //$resp = simplexml_load_file($apicalla);
    //print_r($resp);
    $resp=file_get_contents($apicalla);
    //print_r($resp);
    return $resp;
    //$resp=simplexml_load_file($filename);
    //return $resp;

}

/**
Helper function to convert the array to a csv string
 **/
function convertArrayToCSVstring($temparray)
{
    $csvstring='';

    foreach ($temparray as $value)
    {
        if ($csvstring <> '')
        {
            $csvstring .= ',';
        }
        if (is_numeric($value))
        {
            $csvstring .= $value;
        }
        else
        {
            $csvstring .= "$value";
        }

    }
    $csvstring .= ','."\n";
    return $csvstring;

}

function getItemSKU($title)
{
    $itemSKU='';
    $pos=strrpos($title,'#');
    if($pos>0){
        //echo substr($title,$pos+1)."<br/>";
        $itemSKU=substr($title,$pos+1);
    }

    else
    {
        echo $title."=>";
        $pos2=strrpos(trim($title),' ');
        if($pos2>0)
        {
            $output= substr($title,$pos2+1);
            //$output='6 CUPS - ASSORT POLISHED WATER BUFFALO HORN MEDIEVAL DRINKING CUP MUG 6"';
            $pos3=strpos($output,'&quot;');

            #echo "pos=>".$pos2;
            if($pos3>0)
            {
                //echo "jhjhjhj";
                echo $output."=>Cannont<br/>";

            }
            else
            {
                echo $output."<br/>";
                //basically you need to check if the string contains any digits or is it totally a string.
                if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $output))
                {
                    echo 'Does not Contains at least one letter and one number'."<br/>";
                }
                else
                {
                    $itemSKU=$output;
                }
//                if($x)
//                    echo "TRUE";
//                else
//                    echo "FALSE";
//                echo "<br/>";
            }


        }
        else
            echo "CANNOT"."<br/>";
    }

    return $itemSKU;

}



//this code is to make sure we get all the products, till data. But later on we will modify it
//to give only the latest products.

/*$fileName="Products//ProductList_Page_new_1.xml";
$fp=fopen($fileName,'w');
$resp=getXMLFromEBay();
//echo $resp;
fwrite($fp,$resp);
fclose($fp);
$resp=simplexml_load_file($fileName);
//print_r($resp);
$totalPages=$resp->paginationOutput->totalPages;
#from now on it will be largely 10 pages or so
getProductsFromStore($totalPages);
*/


//get all the items in the database
$queryProducts="Select ebayItemId,continueDiscontinue from EbayProductsForTx";
$resultProducts=mysql_query($queryProducts);
$rowsnumProducts=mysql_num_rows($resultProducts);
$arrAllProducts=array();
for($i=0;$i<$rowsnumProducts;$i++)
{
    $row=mysql_fetch_row($resultProducts);
    $obj=new stdClass();
    $obj->itemId=$row[0];
    $obj->valid=$row[1];
    array_push($arrAllProducts,$row[0]);
}

//$arrayUpdatedProducts=array();
$updateString='';
for($i=0;$i<40;$i++)
{
    $fileName="Products//ProductList_Page_new_".$i.".xml";
    echo "reading the file".$fileName."<br/>..................<br/>";
    $updateString=updateEbayDBWithFile($fileName,$updateString);
}

$arrayUpdatedProducts=explode(":",$updateString);

print_r($arrayUpdatedProducts);



//compare
$lenOriginal=sizeof($arrAllProducts);
$lenUpdates=sizeof($arrayUpdatedProducts);
$arrInvalid=array();
for($i=0;$i<$lenOriginal;$i++)
{
    $temp=$arrAllProducts[$i];
    $flag=0;
    for($j=0;$j<$lenUpdates;$j++)
    {
        if($temp==$arrayUpdatedProducts[$j])
        {
            $flag=1;
            break;
        }
    }
    if($flag==0)
    {
        array_push($arrInvalid,$temp);
        echo "invalid entered";
    }
}

echo "invalid products<br/>";
print_r($arrInvalid);

//for these products need to update the database continuediscontinue..as these products are no longer available

//update the db for these invlaid items
$lenInvalid=sizeof($arrInvalid);
for($i=0;$i<$lenInvalid;$i++)
{
    $itemId=$arrInvalid[$i];
    $query="UPDATE `EbayProductsForTx` SET `continueDiscontinue`='1' where EbayItemId='".$itemId."'";
    $result=mysql_query($query);


}

?>