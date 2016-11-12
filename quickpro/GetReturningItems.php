<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php

if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
/**
 * Created by PhpStorm.
 * User: 305015992
 * Date: 11/8/2015
 * Time: 7:23 AM
 */
require_once ('login.php');
require_once ('reportHeader.php');


set_time_limit(0);
$querySession="SET SESSION group_concat_max_len = 1000000";
mysql_query($querySession);

//get the number of years from the data base
$queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$currentYear=intval($maxYear);
$prevYear=$currentYear-1;

?>
<br/>
<br/>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="GET">
    <table>
        <tr><td><b>Select the year from:</b></td>
            <td>
                <select id="fromYear" name="fromYear">
                    <?php
                    if(isset($_GET["fromYear"]))
                        $selFromYear=intval($_GET["fromYear"]);
                    else
                        $selFromYear=$maxYear;
                    for($i=$minYear;$i<=$maxYear;$i++){

                        if($i==$selFromYear){
                            ?>
                            <option value="<?php echo $i ?>" selected><?php echo $i ?></option>
                            <?php
                        }
                        else {
                            ?>
                            <option value="<?php echo $i ?>"><?php echo $i ?></option>


                            <?php
                        }
                    }
                    ?>

                </select>
            </td>
            <td>
                <b>Select the year to:</b>
            </td><td>
                <select id="toYear" name="toYear">
                    <?php
                    if(isset($_GET["toYear"]))
                        $selToYear=intval($_GET["toYear"]);
                    else
                        $selToYear=$maxYear;
                    for($i=$minYear;$i<=$maxYear;$i++){

                        if($i==$selToYear){
                            ?>
                            <option value="<?php echo $i ?>" selected><?php echo $i ?></option>
                            <?php
                        }
                        else {
                            ?>
                            <option value="<?php echo $i ?>"><?php echo $i ?></option>

                            <?php
                        }
                    }
                    ?>

                </select>
            </td>
            <td><b>Choose Item Type</b></td><td>

                <input type="radio" name="itemtype" value="1" <?php if(!isset($_GET['itemtype']) ||
                    (isset($_GET['itemtype']) && $_GET['itemtype'] == '1')) echo 'checked="checked"'?>>Repeating

                <input type="radio" name="itemtype" value="0" <?php if((isset($_GET['itemtype']) && $_GET['itemtype'] == '0'))
                    echo 'checked="checked"'?>>Non Repeating

            </td>
            <td>
                <input type="submit" value="Search" />
            </td></tr>
    </table>
</form>

<?php

if(isset($_GET['fromYear'])&& isset($_GET['toYear'])) {


    $startYear=$_GET['fromYear'];
    $endYear=$_GET['toYear'];
    $showRepeat=$_GET['itemtype'];

    if($endYear==$startYear){
        echo "<h2 style='color:red'>Please choose different years </h2>";
        return;
    }

    if($endYear<$startYear){
        echo "<h2 style='color:red'>Please choose end year greater than start year </h2>";
        return;
    }

    $totalYears = intval($endYear) - intval($startYear) + 1;
    // echo "totalYears" . $totalYears;
    $queryItem = "SELECT group_concat(DISTINCT(year(SellingDate))),ItemId,group_concat(qty),group_concat(SellingPrice),ProductName
                FROM `quickpro` where year(SellingDate)>='" . $startYear . "' and year(SellingDate)<='" . $endYear . "'
                group by ItemId";
    $resultItem = mysql_query($queryItem);
    $rownum = mysql_num_rows($resultItem);

    $arrItems = array();
    for ($i = 0; $i < $rownum; $i++) {
        $row = mysql_fetch_row($resultItem);
        if($showRepeat) {
            $years = explode(",", $row[0]);

            if (sizeof($years) == $totalYears) //then the custome ris present for both 2014 and 2015
            {
                $totalSale = 0.0;
                $totalQty = 0;
                //now you need to do the calculations
                $itemId = $row[1];
                $listQty = explode(",", $row[2]);
                $listSP = explode(",", $row[3]);
                $productName=$row[4];
                for ($jj = 0; $jj < sizeof($listQty); $jj++) {
                    $totalQty += intval($listQty[$jj]);
                    $totalSale += floatval($listSP[$jj]) * intval($listQty[$jj]);
                    //          echo $totalQty.",".$totalSale."<br/>";
                }

                //need to set the limit of the group_concat max value

                $obj = new stdClass();
                $obj->itemId = $itemId;
                $obj->totalSale = $totalSale;
                $obj->totalQty = $totalQty;
                $obj->productName=$productName;
                $obj->years=$row[0];
                array_push($arrItems, $obj);

            }
        }
        else //non repeating items
        {

            $years=explode(",", $row[0]);
            //echo $years."<br/>";
            // echo "endyear".$endYear;

            if(!in_array($endYear,$years))
            {
                $totalSale = 0.0;
                $totalQty = 0;
                //now you need to do the calculations
                $itemId = $row[1];
                $listQty = explode(",", $row[2]);
                $listSP = explode(",", $row[3]);
                $productName=$row[4];
                for ($jj = 0; $jj < sizeof($listQty); $jj++) {
                    $totalQty += intval($listQty[$jj]);
                    $totalSale += floatval($listSP[$jj]) * intval($listQty[$jj]);
                    //          echo $totalQty.",".$totalSale."<br/>";
                }

                //need to set the limit of the group_concat max value

                $obj = new stdClass();
                $obj->itemId = $itemId;
                $obj->totalSale = $totalSale;
                $obj->totalQty = $totalQty;
                $obj->productName=$productName;
                $obj->years=$row[0];
                array_push($arrItems, $obj);
            }


        }


    }

    if($showRepeat)
        $suffix=" who also repeated in ";
    else
        $suffix=" who did not repeat in ";

    $headString= "List of items from ".$startYear." to ".$endYear.$suffix.$endYear;

    echo "<br><br/>";

    echo "<h1>".$headString."</h1>";

//this is the list of non repeating items
    $lines = "<div style='margin-left:10px'>";
    $lines .= "<table border='1'>";
    $lines .= "<tr><th>Sno</th><th>ItemId</th><th>Product Name</th><th>Total Item Bought</th><th>Total Spent</th><th>Years Bought</th></tr>";
    $count = 1;
    for ($i = 0; $i < sizeof($arrItems); $i++) {

        $lines .= "<tr><td>" . $count++ . "</td><td>" . $arrItems[$i]->itemId . "</td><td>".$arrItems[$i]->productName."</td><td>"
            . $arrItems[$i]->totalQty . "</td><td>" . $arrItems[$i]->totalSale . "</td><td>".$arrItems[$i]->years."</td></tr>";
    }
    $lines .= "</table></div>";

    echo $lines;
}

else
{
    echo "<h2 style='color:red'>Please select the filters </h2>";
}

