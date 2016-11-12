
<?php 
require_once("keys.php");
require_once("ebaySession.php"); 
//echo "rohit";
//exit();
?>

<h1>Enter the date for which you want to check the Ebay Account yyyy-mm-dd (e.g. 2015-04-06) format</h1>

<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
    <table border='0'>
        <?php
        $cbo = '<tr><td>Date From*</td><td><input id="startDate" name="startDate" /></td>';
        $cbo.= '<tr><td>Date To*</td><td><input id="endDate" name="endDate" /></td>';
        echo $cbo;

        ?>
        <tr><td><input type='submit' name='submitDetails' value='Get Ebay Account'></td></tr>
    </table>

</form>
<hr />
<br />



<?php
//exit();
if($_POST['submitDetails'])
{
    if(strlen(trim($_POST['endDate'])<5))
    {
        echo "end date not provided";
        exit();
    }

    if(strlen(trim($_POST['startDate'])<5))
    {
        echo "start date not provided";
        exit();
    }

    $endDate=trim($_POST['endDate']);
    $startDate=trim($_POST['startDate']);
    //echo "sdsd";
    //SiteID must also be set in the Request's XML
    //SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
    //SiteID Indicates the eBay site to associate the call with
    $siteID = 0;
    //the call being made:
    $verb = 'GetAccount';
    //Time with respect to GMT
    //by default retreive orders in last 30 minutes
    $CreateTimeFrom = $startDate;//'2015-05-04'; //current time minus 30 minutes
    $CreateTimeTo = $endDate;//'2015-06-10';
    //If you want to hard code From and To timings, Follow the below format in "GMT".
    //$CreateTimeFrom = YYYY-MM-DDTHH:MM:SS; //GMT
    //$CreateTimeTo = YYYY-MM-DDTHH:MM:SS; //GMT
    ///Build the request Xml string

    /*$requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
    $requestXmlBody .= "<CreateTimeFrom>$CreateTimeFrom</CreateTimeFrom><CreateTimeTo>$CreateTimeTo</CreateTimeTo>";
    $requestXmlBody .= '<OrderRole>Seller</OrderRole><OrderStatus>Active</OrderStatus>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= '</GetOrdersRequest>';
    print_r($requestXmlBody);
    */
    $requestXmlBody = '<?xml version="1.0" encoding="utf-8" ?>';
    $requestXmlBody .= '<GetAccountRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $requestXmlBody .= '<AccountHistorySelection>BetweenSpecifiedDates</AccountHistorySelection>';
    $requestXmlBody .= '<BeginDate>'.$CreateTimeFrom.'</BeginDate>';
    $requestXmlBody .= '<EndDate>'.$CreateTimeTo.'</EndDate>';
    $requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$userToken</eBayAuthToken></RequesterCredentials>";
    $requestXmlBody .= '</GetAccountRequest>';

    //print_r($requestXmlBody);


    //Create a new eBay session with all details pulled in from included keys.php
    $session = new eBaySession($userToken, $devID, $appID, $certID, $serverUrl, $compatabilityLevel, $siteID, $verb);
    //send the request and get response
    $responseXml = $session->sendHttpRequest($requestXmlBody);

    $response=simplexml_load_string($responseXml);

    //print_r($reponse->AccountEntries->AccountEntry);
    $jsonString=json_encode($response);
    //print_r($jsonString);

    $jsonObj=json_decode($jsonString);

    if($jsonObj->Ack=="Success")
    {

        $arr=$jsonObj->AccountEntries->AccountEntry;

        $str='';
        $str.="<table border='1'><tr><th>Sno</th><th>TransactionDate</th><th>ItemId</th><th>Item</th><th>Amount</th><th>TransactionId</th></tr>";
        $count=1;

        //stdClass Object ( [AccountDetailsEntryType] => FeeFinalValue
        //[Description] => Final Value Fee
        //[Date] =>2015-06-16T07:02:25.000Z
        //[GrossDetailAmount] => 3.6
        //[ItemID] => 161730405795
        //[Memo] => venturajulz Final price: $40.00 (Store)
        //[NetDetailAmount] => 3.6
        //[RefNumber] => 1006369092312
        //[VATPercent] => 0
        //[Title] => 12 PCS ASSORT SEA SHELL STORAGE TRINKET BOX COIN PURSES #CP-11
        //[OrderLineItemID] => 161730405795-1271258146006 [TransactionID] => 1271258146006 )
        foreach($arr as $obj){
            //print_r($obj);
            if($obj->AccountDetailsEntryType=="FeeFinalValue")
            {
                $str.="<tr>";
                $str.="<td>".$count."</td>";
                $str.="<td>".$obj->Date."</td>";
                $str.="<td>".$obj->ItemID."</td>";
                $str.="<td>".$obj->Title."</td>";
                $str.="<td>".$obj->Memo."</td>";
                $str.="<td>".$obj->TransactionID."</td>";
                $str.="</tr>";
                $count++;
            }
            //echo $obj->Title;
            //echo "<hr/>";
        }

        $str.="</table>";
        echo $str;
    }
    else
    {
        echo "<h3>The call failed</h3>";
        echo "Reasons:<br/>";
        echo "Short Message:".$jsonObj->Errors->ShortMessage."<br/>";
        echo "Long Message:".$jsonObj->Errors->LongMessage."<br/>";

    }
//    exit();
//    if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
//        die('<P>Error sending request');
//    Xml string is parsed and creates a DOM Document object
//    $responseDoc = new DomDocument();
//    $responseDoc->loadXML($responseXml);
    //get any error nodes
//    $errors = $responseDoc->getElementsByTagName('Errors');
//    $response = simplexml_import_dom($responseDoc);
    //print($errors);
    //$response = simplexml_load_string($responseDoc);

    //print_r($response);
    //foreach($response->AccountEntries as $obj){
    //    print_r($obj);
    //}
    //foreach($response->AccountEntries as $obj){
    //    print_r($obj->Title);
    //}

    //$response->GetAccountResponse->AccountEntries

    /*$entries = $response->PaginationResult->TotalNumberOfEntries;
    //if there are error nodes
    if ($errors->length > 0) {
        echo '<P><B>eBay returned the following error(s):</B>';
        //display each error
        //Get error code, ShortMesaage and LongMessage
        $code = $errors->item(0)->getElementsByTagName('ErrorCode');
        $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');
        $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

        //Display code and shortmessage
        echo '<P>', $code->item(0)->nodeValue, ' : ', str_replace(">", "&gt;", str_replace("<", "&lt;", $shortMsg->item(0)->nodeValue));

        //if there is a long message (ie ErrorLevel=1), display it
        if (count($longMsg) > 0)
            echo '<BR>', str_replace(">", "&gt;", str_replace("<", "&lt;", $longMsg->item(0)->nodeValue));
    }else { //If there are no errors, continue
        //if(isset($_GET['debug']))
        //{
          //  header("Content-type: text/xml");
            print_r($responseXml);
    //    }else
    //    {  //$responseXml is parsed in view.php
    //        include_once 'view.php';
    //    }
    }*/

}

?>
