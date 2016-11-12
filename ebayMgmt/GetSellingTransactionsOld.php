<?php 
require_once("keys.php"); 
require_once("ebaySession.php");
require_once("Orders.php");
require_once ('login.php');


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

set_time_limit(0);


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

     //get the max date in the db
    $queryMaxMinDate="Select Max(Distinct(CreationDate)),Min(Distinct(CreationDate)) from EbayTransactions";
    $resultMaxMinDate=mysql_query($queryMaxMinDate);
    $rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
    $DBMaxDate=$rowsMaxMin[0];
    $DBMinDate=$rowsMaxMin[1];

    $displayEndDate=$endDate;
    $displayStartDate=$startDate;

    echo "---Debug Information--<br/> ";
    
    echo "Initial dates..".$displayStartDate."=>".$displayEndDate."<br/>";
    echo "DB dates".$DBMaxDate."=>".$DBMinDate."<br/>";


    //already in the database completely
    // S------a----b-----E
    if($DBMaxDate>=$endDate && $DBMaxDate>=$startDate && $DBMinDate <=$endDate && $DBMinDate <=$startDate)
    {
        echo "case0 S------a----b-----E <br/> ";
        echo "---Debug Information Ends--<br/> ";
       
        displayTop100Data($displayStartDate,$displayEndDate);
        return;
    }
    //we have the data from the begining till some point
    //a--S---b---E
    else if($DBMaxDate>=$endDate && $DBMaxDate>=$startDate && $DBMinDate >=$startDate && $DBMinDate <=$endDate)
    {
        echo "Case one:a--S---b---E <br/>";
        $startDate=$startDate;
        $endDate=$DBMinDate;

    }
    //S--a--E--b
    else if($DBMaxDate<=$endDate && $DBMaxDate>=$startDate && $DBMinDate <=$startDate && $DBMinDate <=$endDate)
    {
        echo "Case two: S--a--E--b <br/>";
        $startDate=$DBMaxDate;
        $endDate=$endDate;

    }
    //a--b--S--E
    // basically from earlier dates
    else if($DBMaxDate>=$endDate && $DBMaxDate>=$startDate && $DBMinDate >=$startDate && $DBMinDate >=$endDate)
    {
        echo "Case three: a--b--S--E <br/>";
        $endDate=$endDate;
        $startDate=$startDate;

    }
    //S--E--a--b
    // basically from newer dates
    else if($DBMaxDate<=$endDate && $DBMaxDate<=$startDate && $DBMinDate <=$startDate && $DBMinDate <=$endDate)
    {
        echo "Case four: S--E--a--b  <br/>";
        $endDate=$endDate;
        $startDate=$startDate;

    }
    //a--S--E--b
    //the exixtijg DB is a subset
    else if($DBMaxDate<=$endDate && $DBMaxDate>=$startDate && $DBMinDate >=$startDate && $DBMinDate <=$endDate)
    {
        echo "Case five: a--S--E--b  <br/>";
        $endDate=$endDate;
        $startDate=$startDate;
    }


    echo "The final dates..."."StartDate:".$startDate."EndDate:".$endDate."<br/>";
    //exit();

    echo "---Debug Information Ends--<br/> ";
  



    //echo "sdsd";
    //SiteID must also be set in the Request's XML
    //SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
    //SiteID Indicates the eBay site to associate the call with
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

    //echo $strfinal;

    //print_r($keyValueHash);
    formatData($arrJsons,$startDate,$endDate,false);

    displayTop100Data($startDate,$endDate);


   // print_r(json_encode($arrJsons));



}

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


function formatData($arrJSON,$startDate,$endDate,$DEBUG)
{
   // print_r($arrJSON);
   // echo "<h2>Displaying the results between ".$startDate." and ".$endDate."</h2><br/>";
    $str='';
    $str.="<table border='1'><tr><th>Sno</th><th>TransactionDate</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th><th>TransactionId</th></tr>";
    $count=1;
    $keyValueHash=array();

    foreach($arrJSON as $jsonObj)
    {

        $arr=$jsonObj->SaleRecord;



        foreach($arr as $obj){
            //print_r($obj);
            //if($obj->AccountDetailsEntryType=="FeeFinalValue")
            //{
            if(sizeof($obj->SellingManagerSoldTransaction)>1)
            {
                foreach($obj->SellingManagerSoldTransaction as $trx)
                {
                    $str.="<tr>";
                    $str.="<td>".$count."</td>";
                    $str.="<td>".$obj->CreationTime."</td>";
                    $str.="<td>".$trx->ItemID."</td>";
                    $str.="<td>".$trx->ItemTitle."</td>";
                    $str.="<td>".$trx->QuantitySold."</td>";
                    $str.="<td>".$trx->TransactionID."</td>";

                    $str.="</tr>";
                    //add to DB
                    addEntryToDB($trx->TransactionID,$trx->ItemID,$trx->ItemTitle,$trx->QuantitySold,$obj->CreationTime);

                    $count++;
                    if($keyValueHash[$trx->ItemID])
                    {
                        //$tt=$keyValueHash[$trx->ItemID]->getQty();
                        $keyValueHash[$trx->ItemID]->qty=$keyValueHash[$trx->ItemID]->qty+$trx->QuantitySold;
                        if($DEBUG)
                            echo "secondtime multi".$trx->ItemID."<br/>";
                        //$keyValueHash[$trx->ItemID]+=$trx->QuantitySold;
                    }
                    else
                    {
                        $txObj=new order();
                        $txObj->itemId=$trx->ItemID;
                        $txObj->qty=$trx->QuantitySold;
                        $txObj->title=$trx->ItemTitle;
                        $keyValueHash[$txObj->itemId]=$txObj;
                        //$keyValueHash[$trx->ItemID]=$trx->QuantitySold;
                    }
                }
            }
            else
            {
                $itemId=$obj->SellingManagerSoldTransaction->ItemID;
                $qty=$obj->SellingManagerSoldTransaction->QuantitySold;
                $itemTitle=$obj->SellingManagerSoldTransaction->ItemTitle;

                $str.="<tr>";
                $str.="<td>".$count."</td>";
                $str.="<td>".$obj->CreationTime."</td>";
                $str.="<td>".$itemId."</td>";
                $str.="<td>".$itemTitle."</td>";
                $str.="<td>".$qty."</td>";
                $str.="<td>".$obj->SellingManagerSoldTransaction->TransactionID."</td>";

                $str.="</tr>";
                $count++;

                addEntryToDB($obj->SellingManagerSoldTransaction->TransactionID,$itemId,$itemTitle,$qty,$obj->CreationTime);

                if($keyValueHash[$itemId]){
                    $keyValueHash[$itemId]->qty=$keyValueHash[$itemId]->qty+$qty;
                    if($DEBUG)
                        echo "secondtime single".$itemId."<br/>";
                    //$keyValueHash[$obj->SellingManagerSoldTransaction->ItemID]+=$obj->SellingManagerSoldTransaction->QuantitySold;
                }
                else{
                    $txObj=new order();
                    $txObj->itemId=$itemId;
                    $txObj->qty=$qty;
                    $txObj->title=$itemTitle;
                    $keyValueHash[$itemId]=$txObj;
                    //$keyValueHash[$obj->SellingManagerSoldTransaction->ItemID]=$obj->SellingManagerSoldTransaction->QuantitySold;
                }

            }


        }

       // return $str;


   }

   if($DEBUG)
    echo $str;
   //echo "<hr/>";
   //echo "Total sales: "."<br/>";
    
    /*$lines='';
    $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th></tr>";
    $count=1;
    foreach ($keyValueHash as $key => $val){
        //print_r($keyValueHash[$key]);
        $lines.="<tr><td>".$count++."</td><td>".$val->itemId."</td><td>".$val->title."</td><td>".$val->qty."</td></tr>";

    }

    $lines.="</table>";

    echo $lines;
    */

}

//displays the top hundred data 
function displayTop100Data($startDate,$endDate){

     echo "<h2>Displaying top results between ".$startDate." and ".$endDate."</h2><br/>";
   
    $lines='';
    $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th></tr>";
    $query="SELECT ItemId,Title,SUM(QTY) AS output FROM EbayTransactions Where CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
    //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    $count=1;
    for($j=0;$j<$rowsnum;$j++)
    {
        if($row[2]==1)
            break;
        $row=mysql_fetch_row($result);
        $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
    }

    echo $lines;
   
}


function addEntryToDB($transactionId,$itemId,$title,$qty,$creationDate)
{
    $timestamp = strtotime($creationDate);
    $timestamp=date('Y-m-d',$timestamp);

    //INSERT INTO `EbayTransactions`(`Sno`, `ItemId`, `Title`, `CreationDate`, `Qty`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
    $query="INSERT INTO `EbayTransactions` ( `TransactionId`,`ItemId`,`Title`,`CreationDate`,`Qty`) VALUES
        ( '".$transactionId."','".$itemId."','".htmlspecialchars($title)."','".$timestamp."','".$qty."')";
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
