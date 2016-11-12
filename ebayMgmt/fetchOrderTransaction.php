<?php require_once('keys.php') ?>
<?php require_once('ebaySession.php') ?>
<?php require_once('Orders.php') ?>


<!--
I am now using the GetOrders API to get the details about the items sold. Not sure why I did not use this API earlier and used the GetSellingManagerSoldListings
API. Anyways this can help in finding any items if not listed by the fetchSellingTranscations scirpt
the data obtained from the GetOrders API is in a transactional data. There are two kinds of structures. An order containing a single transaction or a order containing array
of transactions
we have taken care of both

the script compares if the particular item and transaction is already present in the db or not. If not then it was a miss and it will be added to the database

One check: Not sure why sometimes I am not able to get the data if teh date diff is more than 10 days. Check the API for that
-->
<?php

require_once ('login.php');
set_time_limit(0);


//get the max date in the db
$queryMaxMinDate="Select Max(Distinct(CreationDate)),Min(Distinct(CreationDate)) from EbayTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$DBMaxDate=$rowsMaxMin[0];
$DBMinDate=$rowsMaxMin[1];

$endDate=date('Y-m-d');

$startDate=date('Y-m-d', strtotime('-2 day'));

#$endDate="2016-09-30";
#$startDate="2016-09-28";


$keyValueHash=array();
$jsonString=fetchJson(1,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
$jsonObj=json_decode($jsonString);
print_r($jsonObj);
if($jsonObj->Ack!="Success")
{
    echo "<h3>The call failed</h3>";
    echo "Reasons:<br/>";
    echo "Short Message:".$jsonObj->Errors->ShortMessage."<br/>";
    echo "Long Message:".$jsonObj->Errors->LongMessage."<br/>";


    exit();
}
//$strfinal=formatData($jsonObj,$keyValueHash);
//print_r($jsonObj);
$arrJsons=array();
//check for the paginations

$numPages=$jsonObj->PaginationResult->TotalNumberOfPages;
echo "numpages".$numPages;
array_push($arrJsons,$jsonObj);
//get the data fro the pages
for($i=1;$i<=$numPages;$i++)
{
    $jsonString=fetchJson($i,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
    //print_r($jsonString);
    $jsonObj=json_decode($jsonString);
    array_push($arrJsons,$jsonObj);
    //$strfinal.=formatData($jsonObj,$keyValueHash);

}

formatData($arrJsons,$startDate,$endDate,false);


#now update the selling price
//getAccountDetails($startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);


function fetchJson($pageNumber,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel){
    $siteID = 0;
    //the call being made:
    $verb = 'GetOrders';
    //Time with respect to GMT
    //by default retreive orders in last 30 minutes
    $CreateTimeFrom = $startDate;//'2015-05-04'; //current time minus 30 minutes
    $CreateTimeTo = $endDate;//'2015-06-10';
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    //$requestXmlBody .= '<SaleDateRange>';
    $requestXmlBody .= '<CreateTimeFrom>'.$CreateTimeFrom.'</CreateTimeFrom>';
    $requestXmlBody .= '<CreateTimeTo>'.$CreateTimeTo.'</CreateTimeTo>';
    //$requestXmlBody .= '</SaleDateRange>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= "<Pagination>";
    $requestXmlBody .= "<PageNumber>".$pageNumber."</PageNumber>";
    $requestXmlBody .= "</Pagination>";
    $requestXmlBody .= '</GetOrdersRequest>';
    //echo $requestXmlBody;
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);
    //print_r($responseXml);

    $response=simplexml_load_string($responseXml);


    //print_r($reponse->AccountEntries->AccountEntry);
    $jsonString=json_encode($response);
    return $jsonString;
}


//add the entry to the data base



function formatData($arrJSONs,$startDate,$endDate,$DEBUG)
{
    $numJSON=sizeof($arrJSONs);
    for($i=0;$i<$numJSON;$i++)
    {
    $orders=$arrJSONs[$i]->OrderArray->Order;
    //print_r($orders);

    //if ($orders != null) {
        foreach ($orders as $order) {
            echo "-------------------------<br/>";
           // echo "OrderID ->" . $order->OrderID . "<br/>";

            foreach($order->TransactionArray as $transaction)
            {

              //  echo "sizeof tx".sizeof($transaction)."<br/>";
                if(sizeof($transaction)>1)
                {
                    foreach($transaction as $trx)
                    {
//                        echo "itemId->".$trx->Item->ItemID."<br/>";
//                        echo "Title->".$trx->Item->Title."<br/>";
//                        echo "qty->".$trx->QuantityPurchased."<br/>";
//                        echo "txId->".$trx->TransactionID."<br/>";
//                        echo "creationDate".$trx->CreatedDate."<br/>";
//                        echo "transacPrice".$trx->TransactionPrice."<br/>";

                        checkDB($trx->TransactionID,$trx->Item->ItemID,$trx->CreatedDate,$trx->Item->Title,$trx->QuantityPurchased,$trx->TransactionPrice);

                    }
                }
                else
                {
//                    echo "itemId->".$transaction->Item->ItemID."<br/>";
//                    echo "Title->".$transaction->Item->Title."<br/>";
//                    echo "qty->".$transaction->QuantityPurchased."<br/>";
//                    echo "txId->".$transaction->TransactionID."<br/>";
//                    echo "creationDate".$transaction->CreatedDate."<br/>";
//                    echo "transacPrice".$transaction->TransactionPrice."<br/>";
                    checkDB($transaction->TransactionID,$transaction->Item->ItemID,$transaction->CreatedDate,$transaction->Item->Title,
                        $transaction->QuantityPurchased,$transaction->TransactionPrice);
                }
            }
        }

    }
    //}

}


function checkDB($txId,$itemId,$createdDate,$title,$qty,$price){

    $query="Select * from EbayTransactions where TransactionId='".$txId."' and ItemId='".$itemId."'";
    echo $query."<br/>";
	$result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    if($rowsnum==0)
    {
        echo "Item ".$itemId. " transaction ".$txId. " created on ".$createdDate. " not found <br/>";
        addEntryToDB($txId,$itemId,$title,$qty,$createdDate,getItemSKU($title),$price);

    }
	//need to add the update logic ..in case the item is present but need to be updated as in sp..this is after 25-09-2016 ali mail
	else
	{
		echo "Item ".$itemId. " transaction ".$txId. " created on ".$createdDate. " is getting updated <br/>";
		updateEntryToDB($txId,$itemId,$price);
	}

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


//we have the transaction id as the primary key
function addEntryToDB($transactionId,$itemId,$title,$qty,$creationDate,$sku,$price)
{
    $timestamp = strtotime($creationDate);
    $timestamp=date('Y-m-d',$timestamp);

    //INSERT INTO `EbayTransactions`(`Sno`, `ItemId`, `Title`, `CreationDate`, `Qty`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
    $query="INSERT INTO `EbayTransactions` ( `TransactionId`,`ItemId`,`Title`,`CreationDate`,`Qty`,`SellingPrice`,`SKU`) VALUES
        ( '".$transactionId."','".$itemId."','".mysql_real_escape_string($title)."','".$timestamp."','".$qty."','".$price."','".$sku."')";
    echo $query."<br/>";
    $result = mysql_query($query);
    //echo "result of insert".$result;
    if(!$result)
    {
        echo mysql_error();
        return;
    }
    else
    {
        echo "inserted into Ebay Transactions"."<br/>";
    }
}

function updateEntryToDB($transactionId,$itemId,$price)
{
	#$timestamp = strtotime($creationDate);
    #$timestamp=date('Y-m-d',$timestamp);

    //INSERT INTO `EbayTransactions`(`Sno`, `ItemId`, `Title`, `CreationDate`, `Qty`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
    $query="Update `EbayTransactions` set `SellingPrice`='".$price."' where TransactionId='".$transactionId."' and ItemId='".$itemId."'";
    echo $query."<br/>";
    $result = mysql_query($query);
    //echo "result of insert".$result;
    if(!$result)
    {
        echo mysql_error();
        return;
    }
    else
    {
        echo "updated ebaytransactions"."<br/>";
    }
}


?>
