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

#$endDate="2016-07-31";
#$startDate="2016-07-01";

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
    //print_r($jsonString);
    $jsonObj=json_decode($jsonString);
    array_push($arrJsons,$jsonObj);
    //$strfinal.=formatData($jsonObj,$keyValueHash);

}

formatData($arrJsons,$startDate,$endDate,false);


#now update the selling price
getAccountDetails($startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);


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
    //print_r($responseXml);

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
                    $sku=getItemSKU($trx->ItemTitle);
                    addEntryToDB($trx->TransactionID,$trx->ItemID,$trx->ItemTitle,$trx->QuantitySold,$obj->CreationTime,$sku);
                }
            }
            else
            {
                //print_r($obj);
                $itemId=$obj->SellingManagerSoldTransaction->ItemID;
                $qty=$obj->SellingManagerSoldTransaction->QuantitySold;
                $itemTitle=$obj->SellingManagerSoldTransaction->ItemTitle;
                $sku=getItemSKU($itemTitle);
                //$sellingPrice=$obj->SellingManagerSoldTransaction->StartPrice;
                //echo $itemId."=>".$qty."=>".$itemTitle."=>".$sellingPrice."<br/>";
                addEntryToDB($obj->SellingManagerSoldTransaction->TransactionID,$itemId,$itemTitle,$qty,$obj->CreationTime,$sku);

            }


        }

    }

}



function getAccountDetails($startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel)
{


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
						//added as the previous logic was not working ..look at the mailchain from 10-oct-2016.
						//earlier I had assumed the string to be 162040369920","Memo":"printlinx Final price: $14.00, 2 of 10 items @ $7.00 (Fixed Price) but 
						//most of the times it is not the case
						$posOfatsymbol=strpos($strParts[1],"@");
						if(!$posOfatsymbol)
                        //if(strlen($strParts[1])<20)
                        {
							//the word store is not correct test
                            /*$finalParts=explode("(Store)",$strParts[1]);
                            $sellingPrice=trim($finalParts[0]);
                            $sellingPrice=substr($sellingPrice,1);
							*/
							echo "inside without @ logic <br/>";
							$finalParts=explode("(",$strParts[1]);
							print_r($finalParts);
							$sellingPriceString=trim($finalParts[0]);
							$pricePart=explode("$",$sellingPriceString);
							$sellingPrice=$pricePart[1];
							
                        }
                        else
                        {
                            //$finalParts=
                           /* $tempFinalParts=explode("@",$strParts[1]);
                            $finalParts=explode("(Store)",$tempFinalParts[1]);
                            $sellingPrice=trim($finalParts[0]);
                            $sellingPrice=substr($sellingPrice,1);
                            //print_r($finalParts);
							*/
							echo "inside @ logic"."<br/>";
							echo strlen($strParts[1])."<br/>";
							//$finalParts=
                            $tempFinalParts=explode("@",$strParts[1]);
                            /*$finalParts=explode("(Store)",$tempFinalParts[1]);
                            $sellingPrice=trim($finalParts[0]);
                            $sellingPrice=substr($sellingPrice,1);
                            print_r($finalParts);
							*/
							$finalParts=explode("(",$tempFinalParts[1]);
							$sellingPriceString=trim($finalParts[0]);
							$pricePart=explode("$",$sellingPriceString);
							$sellingPrice=$pricePart[1];
							
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
function addEntryToDB($transactionId,$itemId,$title,$qty,$creationDate,$sku)
{
    $timestamp = strtotime($creationDate);
    $timestamp=date('Y-m-d',$timestamp);

    //INSERT INTO `EbayTransactions`(`Sno`, `ItemId`, `Title`, `CreationDate`, `Qty`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
    $query="INSERT INTO `EbayTransactions` ( `TransactionId`,`ItemId`,`Title`,`CreationDate`,`Qty`,`SKU`) VALUES
        ( '".$transactionId."','".$itemId."','".mysql_real_escape_string($title)."','".$timestamp."','".$qty."','".$sku."')";
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
