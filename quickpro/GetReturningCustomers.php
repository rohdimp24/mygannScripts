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
            <td><b>Choose Customer Type</b></td><td>

                <input type="radio" name="customertype" value="1" <?php if(!isset($_GET['customertype']) ||
                        (isset($_GET['customertype']) && $_GET['customertype'] == '1')) echo 'checked="checked"'?>>Repeating

                <input type="radio" name="customertype" value="0" <?php if((isset($_GET['customertype']) && $_GET['customertype'] == '0'))
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
    $showRepeat=$_GET['customertype'];

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
    $queryCustomer = "SELECT group_concat(DISTINCT(year(SellingDate))),CustomerName,group_concat(qty),group_concat(SellingPrice)
                FROM `quickpro` where year(SellingDate)>='" . $startYear . "' and year(SellingDate)<='" . $endYear . "' group by CustomerName";
    $resultCustomer = mysql_query($queryCustomer);
    $rownum = mysql_num_rows($resultCustomer);

    $arrCustomers = array();
    for ($i = 0; $i < $rownum; $i++) {
        $row = mysql_fetch_row($resultCustomer);
        if($showRepeat) {
            $years = explode(",", $row[0]);

            if (sizeof($years) == $totalYears) //then the custome ris present for both 2014 and 2015
            {
                $totalSale = 0.0;
                $totalQty = 0;
                //now you need to do the calculations
                $customerName = $row[1];
                $listQty = explode(",", $row[2]);
                $listSP = explode(",", $row[3]);
                for ($jj = 0; $jj < sizeof($listQty); $jj++) {
                    $totalQty += intval($listQty[$jj]);
                    $totalSale += floatval($listSP[$jj]) * intval($listQty[$jj]);
                    //          echo $totalQty.",".$totalSale."<br/>";
                }

                //need to set the limit of the group_concat max value

                $obj = new stdClass();
                $obj->customerName = $customerName;
                $obj->totalSale = $totalSale;
                $obj->totalQty = $totalQty;
                $obj->years=$row[0];
                array_push($arrCustomers, $obj);

            }
        }
        else //non repeating customers
        {

            $years=explode(",", $row[0]);
            //echo $years."<br/>";
           // echo "endyear".$endYear;

            if(!in_array($endYear,$years))
            {
                $totalSale = 0.0;
                $totalQty = 0;
                //now you need to do the calculations
                $customerName = $row[1];
                $listQty = explode(",", $row[2]);
                $listSP = explode(",", $row[3]);
                for ($jj = 0; $jj < sizeof($listQty); $jj++) {
                    $totalQty += intval($listQty[$jj]);
                    $totalSale += floatval($listSP[$jj]) * intval($listQty[$jj]);
                    //          echo $totalQty.",".$totalSale."<br/>";
                }

                //need to set the limit of the group_concat max value

                $obj = new stdClass();
                $obj->customerName = $customerName;
                $obj->totalSale = $totalSale;
                $obj->totalQty = $totalQty;
                $obj->years=$row[0];
                array_push($arrCustomers, $obj);
            }


        }


    }

    if($showRepeat)
        $suffix=" who also repeated in ";
    else
        $suffix=" who did not repeat in ";

    $headString= "List of customers from ".$startYear." to ".$endYear.$suffix.$endYear;

    echo "<br><br/>";

    echo "<h1>".$headString."</h1>";

//this is the list of non repeating customers
    $lines = "<div style='margin-left:10px'>";
    $lines .= "<table border='1'>";
    $lines .= "<tr><th>Sno</th><th>Customer Name</th><th>Total Item Bought</th><th>Total Spent</th><th>Years Bought</th></tr>";
    $count = 1;
    for ($i = 0; $i < sizeof($arrCustomers); $i++) {

        $lines .= "<tr><td>" . $count++ . "</td><td>" . $arrCustomers[$i]->customerName . "</td><td>"
            . $arrCustomers[$i]->totalQty . "</td><td>" . $arrCustomers[$i]->totalSale . "</td><td>".$arrCustomers[$i]->years."</td></tr>";
    }
    $lines .= "</table></div>";

    echo $lines;
}

else
{
    echo "<h2 style='color:red'>Please select the filters </h2>";
}

//print_r($arrRepeatingCustomers);