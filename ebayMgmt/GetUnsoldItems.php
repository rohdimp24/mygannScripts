<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>

</head>
<!--
Add details of the mysql database

We can fetch the data only from 120 days onwards. Earlier data shall be possible to manually enter by excel download or something like that

-->
<?php
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
require_once ('login.php');
require_once ('reportHeader.php');
require_once ('Items.php');

set_time_limit(0);



$queryMaxMinDate="Select Max(Distinct(CreationDate)),Min(Distinct(CreationDate)) from EbayTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
//$minDate=$rowsMaxMin[1];
//echo $maxDate;

//explode the data usin gthe -
$dateParts=explode("-",$minDate);
//print_r($maxd);

//$maxd=date_parse_from_format("Y-m-d", $maxDate);

$maxMonth=$dateParts[1];

//echo $maxMonth;
//echo $minDate;
//echo $maxMonth;







//exit();



//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-04-01'));
//$endDate=date('Y-m-d',strtotime('2015-04-30'));
$currentYear=date('Y');
$currentMonth=date('m');
//echo $currentYear."=>".$currentMonth;
$arrMonth=array();
$arrMonth['01']="1 month:30";
$arrMonth['02']="3 months:90";
$arrMonth['03']="5 months:150";
$arrMonth['04']="7 months:210";
$arrMonth['05']="9 months:270";
$arrMonth['06']="12 months:365";

?>



<!--<form action= "--><?php //echo $_SERVER['PHP_SELF']; ?><!--" method="POST" >-->
<!--    <div>-->
<!--        --><?php
//
//        $monthSelect='<select name="Month" id="selectMonth">';
//        for($i=1;$i<=6;$i++)
//        {
////echo intval($catArray[$i]->getCatID());
//            if($i<10)
//                $index="0".$i;
//            else
//                $index=$i;
//
//            #$parts=explode(';',$arrMonth[$index]);
//            $monthData=explode(":",$arrMonth[$index]);
//
//            $monthSelect .='<option value="'.$arrMonth[$index].'">'.$monthData[0].'</option>';
//        }
//        $monthSelect .= '</select></td></tr>';
//
//        echo $monthSelect;
//        echo "<input type='submit' class='btn-success btn-small' style='margin-bottom: 10px;' name='dateSubmit' value='Get Data'>";
//
//        ?>
<!--</div>-->
<!--</form>-->



<?php
//if(isset($_POST['dateSubmit']))
//{

   // print_r($_POST);
//    $todaysDate=date('Y-m-d');
//
//    $limitData=explode(':',$_POST['Month']);
//    $limitDays=$limitData[1];
//    $startDate=date('Y-m-d', strtotime($todaysDate. ' -'.$limitDays.'days'));//."T01:01:03Z";

   // echo $todaysDate."<br/>";
   // echo "last".$startDate."<br/>";


    //print_r($_POST);
//    $monthArr=explode(";",$_POST["Month"]);
//    $monthNumeric=explode(":",$monthArr[0]);
//
//    $startDate=date('Y-m-d', strtotime($startDate));
//    $endDate=date('Y-m-d', strtotime($endDate));
//
//    //echo $startDate;
//    //echo $endDate;
//
//    echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay for "
//        .$monthNumeric[0].", ".$year." (".$startDate.")-(".$endDate.")</h1></span><br/>";
//
    //echo displayTop100Data($startDate,$limitData[0]);

//}

echo "<br/><br/>";
echo displayTop100Data();
//select the skus that are sold
//select the total sku



//Select * from EbayTransactions where sku not in (SELECT SKU FROM `EbayProductsForTx`) ORDER BY `CreationDate` DESC
//select distinct(sku),Title,EbayItemId from EbayProductsForTx where sku not in ( SELECT DISTINCT(SKU) FROM `EbayTransactions` where creationdate > '2015-06-12' )

//SELECT SKU,max(CreationDate) FROM EbayTransactions Group By SKU

//SELECT ItemId,Title,CreationDate,Qty,SellingPrice,SKU, DATEDIFF('2015-08-10',CreationDate) AS DiffDate from EbayTransactions where SKU='3030' ORDER BY `CreationDate` DESC

function displayTop100Data(){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    //$lines.="<H2>Displaying top results for ".$month ."</H2><br/>";
    $arrData=array();
    // $lines='';
    $todaysDate=date('Y-m-d');

    //check for the products which have been discontinued..we need to remove them. also need to see what to do for the products
    //which are not in the ebay table..need to see if they are valid or not.

    $queryInactiveProducts="Select EbayItemId from EbayProductsForTx where continueDiscontinue=1";
    $resultInactiveProducts=mysql_query($queryInactiveProducts);
    $rowsnumInactive=mysql_num_rows($resultInactiveProducts);
    $arrayInactiveProducts=array();
    for($j=0;$j<$rowsnumInactive;$j++)
    {
        $row=mysql_fetch_row($resultInactiveProducts);
         array_push($arrayInactiveProducts, $row[0]);    
    }   



    //get the items which are not existing enaymore in ebay store, but we have done the sales earlier
    $queryNonExisting="SELECT distinct(ItemId) from EbayTransactions where itemId not in (select EbayItemId from EbayProductsForTx)";
    $resultNonExisting=mysql_query($queryNonExisting);
    $rowsnumNonExisting=mysql_num_rows($resultNonExisting);
    $arrayNonExisting=array();
    for($j=0;$j<$rowsnumNonExisting;$j++)
    {
        $row=mysql_fetch_row($resultNonExisting);
         array_push($arrayNonExisting, $row[0]);    
    } 

    //print_r($arrayNonExisting);




    $query="SELECT ItemId,Title,SKU,max(CreationDate), min(DATEDIFF('".$todaysDate."',CreationDate)) AS DiffDate from EbayTransactions Where sku!='' group by ItemId  ORDER BY `DiffDate`";

    //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    $debug=0;
    for($j=0;$j<$rowsnum;$j++)
    {

        $row=mysql_fetch_row($result);
        $numMonths=intval($row[4]/30);
        $itemId=$row[0];
        //!in_array($row[0],$arrayInactiveProducts)|| 
        if(!in_array($itemId, $arrayNonExisting))
        {
            if(!in_array($itemId, $arrayInactiveProducts))
            {
                $obj=new Item($itemId,$row[1],$row[2],$row[3],$row[4],$numMonths,false);
                //print_r($obj);
                array_push($arrData,$obj);
            }
            else
            {
                if($debug)
                    echo "inactive".$itemId."<br/>";
            }
        }
        else
        {
            if($debug)
                echo "NF".$itemId."<br/>";
        }
    }

    //now adding the items which are unsold as per our database

    $query2="SELECT EbayItemId,Title,SKU,DATEDIFF('".$todaysDate."',startDate),startDate FROM EbayProductsForTx WHERE sku not in (select distinct(SKU) from EbayTransactions) and continueDiscontinue=0";
   // echo $query2;
    $result2=mysql_query($query2);
    $rowsnum2=mysql_num_rows($result2);
    for($j=0;$j<$rowsnum2;$j++)
    {
        $row=mysql_fetch_row($result2);
        //print_r($row);

        //get the month
        $numMonths=intval($row[3]/30);

        $obj=new Item($row[0],$row[1],$row[2],$row[4],$row[3],$numMonths,true);
        //print_r($obj);
        array_push($arrData,$obj);

    }





    $length=sizeof($arrData);
    $lines='';
    //get all the items that are of 1 month
    for($k=1;$k<14;$k++)
    {
        $count=1;
        if($k==13)
        {
            $lines.="<h2> Never sold items </h2>";

        }
        else
        {
            $lines.="<h2> Unsold items since ".$k." month</h2>";

        }
        $lines.="<table border='1'><tr><th>Sno</th><th>EbayItemId</th><th>Title</th><th>SKU</th><th>Selling Date</th><th>DaysUnsold</th><th>Selling History</th></tr>";

        for($j=0;$j<$length;$j++)
        {
            $obj=$arrData[$j];
            if($obj->monthsUnsold==$k)
            {

            //testSKU.php?SKU='.$obj->sku.'
                $link='echo <a href=""></a>';

                $lines.="<tr><td>".$count++."</td><td>".$obj->itemId."</td><td>".$obj->title."</td><td>".$obj->sku."
                </td><td>".$obj->lastSellingDate."</td><td>".$obj->daysUnSold."</td>";
                if($obj->neverSold)
                    $lines.="<td><span style='color:red'>Never Sold</span></td>";
                else
                {
                    $reviewLink="testSKU.php?ItemId=".$obj->itemId;
                    $lines.="<td><a href=".$reviewLink.">history</a></td></tr>";
                }
            }

        }
        $lines.="<tr><td></td><td></td><td></td><td><td><b>Total Unsold</b></td><td>".intval($count-1)."</td><td></td></tr>";
        $lines.="</table>";
    }



        //$lines.="</table>";

    //$lines.="</div><br/>";

    //print_r($arrData);


    return $lines;
    //return;
}


?>
