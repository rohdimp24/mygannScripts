<?php require_once('keys.php') ?>
<?php require_once('ebaySession.php') ?>
<?php require_once('Orders.php') ?>


<!--
This script will act as a cronjob to fetch the data for todaya and previous day.
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

$startDate=date('Y-m-d', strtotime('-1 day'));


$keyValueHash=array();
$jsonString=fetchJson(1,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
$jsonObj=json_decode($jsonString);
//print_r($jsonObj);
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
array_push($arrJsons,$jsonObj);
//get the data fro the pages
for($i=2;$i<=$numPages;$i++)
{
    $jsonString=fetchJson($i,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
    $jsonObj=json_decode($jsonString);
    array_push($arrJsons,$jsonObj);
    //$strfinal.=formatData($jsonObj,$keyValueHash);

}
formatData($arrJsons,$startDate,$endDate,false);



function fetchJson($pageNumber,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel){
    $siteID = 0;
    //the call being made:
    $verb = 'GetSellingManagerSoldListings';
    //Time with respect to GMT
    //by default retreive orders in last 30 minutes
    $CreateTimeFrom = $startDate;//'2015-05-04'; //current time minus 30 minutes
    $CreateTimeTo = $endDate;//'2015-06-10';
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetSellingManagerSoldListingsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= '<SaleDateRange>';
    $requestXmlBody .= '<TimeFrom>'.$CreateTimeFrom.'</TimeFrom>';
    $requestXmlBody .= '<TimeTo>'.$CreateTimeTo.'</TimeTo>';
    $requestXmlBody .= '</SaleDateRange>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= "<Pagination>";
    $requestXmlBody .= "<PageNumber>".$pageNumber."</PageNumber>";
    $requestXmlBody .= "</Pagination>";
    $requestXmlBody .= '</GetSellingManagerSoldListingsRequest>';
    //echo $requestXmlBody;
    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);

    $response=simplexml_load_string($responseXml);


    //print_r($reponse->AccountEntries->AccountEntry);
    $jsonString=json_encode($response);
    return $jsonString;
}


//add the entry to the data base



function formatData($arrJSON,$startDate,$endDate,$DEBUG)
{
    foreach($arrJSON as $jsonObj)
    {
        $arr=$jsonObj->SaleRecord;
        foreach($arr as $obj)
        {
            if(sizeof($obj->SellingManagerSoldTransaction)>1)
            {
                foreach($obj->SellingManagerSoldTransaction as $trx)
                {
                    addEntryToDB($trx->TransactionID,$trx->ItemID,$trx->ItemTitle,$trx->QuantitySold,$obj->CreationTime);
                }
            }
            else
            {
                $itemId=$obj->SellingManagerSoldTransaction->ItemID;
                $qty=$obj->SellingManagerSoldTransaction->QuantitySold;
                $itemTitle=$obj->SellingManagerSoldTransaction->ItemTitle;
                addEntryToDB($obj->SellingManagerSoldTransaction->TransactionID,$itemId,$itemTitle,$qty,$obj->CreationTime);

            }


        }

    }

}


//we have the transaction id as the primary key
function addEntryToDB($transactionId,$itemId,$title,$qty,$creationDate)
{
    $timestamp = strtotime($creationDate);
    $timestamp=date('Y-m-d',$timestamp);

    //INSERT INTO `EbayTransactions`(`Sno`, `ItemId`, `Title`, `CreationDate`, `Qty`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
    $query="INSERT INTO `EbayTransactions` ( `TransactionId`,`ItemId`,`Title`,`CreationDate`,`Qty`) VALUES
        ( '".$transactionId."','".$itemId."','".mysql_real_escape_string($title)."','".$timestamp."','".$qty."')";
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


?>
