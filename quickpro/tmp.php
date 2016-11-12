<?php require_once('keys.php') ?>
<?php require_once('ebaySession.php') ?>
<?php require_once('Orders.php') ?>


<!--
This script will act as a cronjob to fetch the data for todaya and previous day.
-->
<?php

require_once ('login.php');
set_time_limit(0);


$endDate=date('Y-m-d');
$startDate=date('Y-m-d', strtotime('-1 days'));


$jsonString=fetchAccountDetails(1,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
print_r($jsonString);
getSellingDataForItems($jsonString);
$jsonObj=json_decode($jsonString);

if($jsonObj->Ack!="Success")
{
    echo "<h3>The call failed</h3>";
    echo "Reasons:<br/>";
    echo "Short Message:".$jsonObj->Errors->ShortMessage."<br/>";
    echo "Long Message:".$jsonObj->Errors->LongMessage."<br/>";


    exit();
}

$numPages=$jsonObj->PaginationResult->TotalNumberOfPages;
echo "number of pages".$numPages;
#array_push($arrJsons,$jsonObj);
//get the data fro the pages
for($i=2;$i<=$numPages;$i++)
{
    echo "fetching new data".$i;
    $jsonString=fetchAccountDetails($i,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
    getSellingDataForItems($jsonString);
    //$strfinal.=formatData($jsonObj,$keyValueHash);

}


function fetchAccountDetails($pageNumber,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel)
{
    $siteID = 0;
    //the call being made:
    $verb = 'GetAccount';
    //Time with respect to GMT
    //by default retreive orders in last 30 minutes
    $CreateTimeFrom = $startDate;//'2015-05-04'; //current time minus 30 minutes
    $CreateTimeTo = $endDate;//'2015-06-10';
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetAccountRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= '<AccountHistorySelection>BetweenSpecifiedDates</AccountHistorySelection>';
    $requestXmlBody .= '<BeginDate>'.$CreateTimeFrom.'</BeginDate>';
    $requestXmlBody .= '<EndDate>'.$CreateTimeTo.'</EndDate>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= "<Pagination>";
    $requestXmlBody .= "<PageNumber>".$pageNumber."</PageNumber>";
    $requestXmlBody .= "</Pagination>";
    $requestXmlBody .= '</GetAccountRequest>';
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
    //print($jsonString);
}


function getSellingDataForItems($jsonString)
{

    $itemArray=array();
    //fetch the items whose selling price is 0
    $query="SELECT ItemId FROM EbayTransactions WHERE SellingPrice='0.00'";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);

    for($i=0;$i<$rowsnum;$i++)
    {
        $row=mysql_fetch_row($result);
        array_push($itemArray,$row[0]);
    }
    $len=sizeof($itemArray);
    $jsonObj=json_decode($jsonString);
    if($jsonObj->Ack=="Success")
    {

        $arr=$jsonObj->AccountEntries->AccountEntry;
        foreach($arr as $obj){
            //print_r($obj);
            if($obj->AccountDetailsEntryType=="FeeFinalValue")
            {
                $itemId=$obj->ItemID;
                for($i=0;$i<$len;$i++)
                {
                    if($itemId==$itemArray[$i])
                    {
                        //parse
                        $sellingPrice=0.0;
                        $strParts=explode("Final price:",$obj->Memo);
                        // print_r($strParts);

                        if(strlen($strParts[1])<20)
                        {
                            $finalParts=explode("(Store)",$strParts[1]);
                            $sellingPrice=trim($finalParts[0]);
                            $sellingPrice=substr($sellingPrice,1);

                        }
                        else
                        {
                            //$finalParts=
                            $tempFinalParts=explode("@",$strParts[1]);
                            $finalParts=explode("(Store)",$tempFinalParts[1]);
                            $sellingPrice=trim($finalParts[0]);
                            $sellingPrice=substr($sellingPrice,1);
                            //print_r($finalParts);

                        }
                        echo $itemId."=>".$sellingPrice."<br/>";
                        //update it to the database
                         $updateQuery="UPDATE `EbayTransactions` SET `SellingPrice`='".$sellingPrice."' WHERE ItemId='".$itemId."'";
                        $resultQuery=mysql_query($updateQuery);

                    }
                }

            }
        }
    }
}



?>
