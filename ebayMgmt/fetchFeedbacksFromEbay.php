<?php
include("LIB_http.php");
#include parse library
include("LIB_parse.php");
require_once('keys.php');
require_once('ebaySession.php');


/*
$action="http://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&ftab=FeedbackAsSeller&userid=mygann&iid=-1&de=off&items=200&interval=0&mPg=2432&page=1";
$method="GET";                                    // GET method
$ref = "";                                        // Referer variable
$response="";
$data_array="";
$response = http($target=$action, $ref, $method, $data_array, EXCL_HEAD);
//print_r($response);
//echo $response;
// etract the content
//$after_removing_extra_html = return_between($response['FILE'], "Search Store", "</center>", EXCL);

//get the images
$after_removing_extra_html = return_between($response['FILE'], "feedbackFilterDiv", "</table>",EXCL);
print_r($after_removing_extra_html);
//$total=substr($product_total,2);
//$intTotal= intval(str_replace(",","",$total));
//echo $intTotal;

//$numberOfPages=(int)($intTotal/100)+1;
//echo "pages".$numberOfPages;
//return $numberOfPages;
*/



require_once ('login.php');
set_time_limit(0);

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
echo "numpages".$numPages;
array_push($arrJsons,$jsonObj);

for($i=2;$i<=50;$i++)
{
    $jsonString=fetchJson($i,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel);
    //print_r($jsonString);
    $jsonObj=json_decode($jsonString);
    array_push($arrJsons,$jsonObj);
    //$strfinal.=formatData($jsonObj,$keyValueHash);

}

formatData($arrJsons,$startDate,$endDate,false);




function fetchJson($pageNumber,$startDate,$endDate,$userToken,$devID,$appID, $certID, $serverUrl, $compatabilityLevel){
    $siteID = 0;
    //the call being made:
    $verb = 'GetFeedback';
    //Time with respect to GMT
    //by default retreive orders in last 30 minutes
    $CreateTimeFrom = $startDate;//'2015-05-04'; //current time minus 30 minutes
    $CreateTimeTo = $endDate;//'2015-06-10';
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetFeedbackRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    //$requestXmlBody .= '<SaleDateRange>';
    $requestXmlBody .= '<UserID>mygann</UserID>';
    $requestXmlBody .= ' <FeedbackType>FeedbackReceived</FeedbackType>';
    $requestXmlBody .= '  <DetailLevel>ReturnAll</DetailLevel>';
    //$requestXmlBody .= '</SaleDateRange>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= "<Pagination>";
    $requestXmlBody .= "<PageNumber>".$pageNumber."</PageNumber>";
    $requestXmlBody .= "</Pagination>";
    $requestXmlBody .= '</GetFeedbackRequest>';
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



function formatData($arrJSONs)
{
    $numJSON=sizeof($arrJSONs);
    for($i=0;$i<$numJSON;$i++)
    {
        $feedbacks=$arrJSONs[$i]->FeedbackDetailArray->FeedbackDetail;
        //print_r($orders);

        //if ($orders != null) {
        foreach ($feedbacks as $feedback) {
            echo "-------------------------<br/>";
            // echo "OrderID ->" . $order->OrderID . "<br/>";

            $comment=$feedback->CommentText;
            $feedbackId=$feedback->FeedbackID;
            $itemId=$feedback->ItemID;
            $commentingUser=$feedback->CommentingUser;
            $commentTime=$feedback->CommentTime;
            $feedbackType=$feedback->CommentType;
            if($feedbackType=="Positive")
                $feedbackType=1;
            else
                $feedbackType=0;
            $itemTitle=$feedback->ItemTitle;
            $score=$feedback->CommentingUserScore;

            $query="INSERT INTO `EbayFeedbacks`(`FeedbackID`, `FeedbackUser`, `Comment`, `CommentTime`, `FeedbackType`, `FeedbackScore`, `ItemId`, `ItemTitle`)
            VALUES ('".$feedbackId."','".$commentingUser."','".mysql_real_escape_string($comment)."','".$commentTime."',
            '".$feedbackType."','".$score."','".$itemId."','".mysql_real_escape_string($itemTitle)."')";
            $result=mysql_query($query);
            if(!$result)
            {
                echo mysql_error();
                echo $query."<br>";
            }
            else
            {
                 echo $query."<br/>";
            }

            print_r($feedback);

            /*foreach($order->TransactionArray as $transaction)
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
            }*/
        }

    }
    //}

}



?>