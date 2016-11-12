<?php
$file="test.xls";
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");

?>
<!DOCTYPE html>

<?php
require_once ('login.php');
//require_once ('reportHeader.php');

set_time_limit(0);

if(isset($_GET["itemId"])) {

//echo $currentYear."=>".$currentMonth;
    $arrMonth = array();
    $arrMonth['1'] = "January";
    $arrMonth['2'] = "Feburary";
    $arrMonth['3'] = "March";
    $arrMonth['4'] = "April";
    $arrMonth['5'] = "May";
    $arrMonth['6'] = "June";
    $arrMonth['7'] = "July";
    $arrMonth['8'] = "August";
    $arrMonth['9'] = "September";
    $arrMonth['10'] = "October";
    $arrMonth['11'] = "November";
    $arrMonth['12'] = "December";

    $arrMonthToVal = array();
    $arrMonthToVal['January'] = 1;
    $arrMonthToVal['Feburary'] = 2;
    $arrMonthToVal['March'] = 3;
    $arrMonthToVal['April'] = 4;
    $arrMonthToVal['May'] = 5;
    $arrMonthToVal['June'] = 6;
    $arrMonthToVal['July'] = 7;
    $arrMonthToVal['August'] = 8;
    $arrMonthToVal['September'] = 9;
    $arrMonthToVal['October'] = 10;
    $arrMonthToVal['November'] = 11;
    $arrMonthToVal['December'] = 12;


//get the max date from the database
    $queryMaxMinDate = "SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
    $resultMaxMinDate = mysql_query($queryMaxMinDate);
    $rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
    $maxDate = $rowsMaxMin[0];
    $minDate = $rowsMaxMin[1];
    $maxYear = date('Y', strtotime($maxDate));
    $minYear = date('Y', strtotime($minDate));
    $itemId=$_GET["itemId"];
    //$itemId="7366";
    echo "Results for the Item ".$itemId;

    for ($k = $minYear; $k <= $maxYear; $k++) {


        $year = $k;
        global $arrMonth;
        $arrSubSales = array();
        //this will contain the customer
        $arrofArrListCustomers = array();
        //this will contain the individaula qty
        $arrOfArrQtyDistCustomer = array();
        //
        $aryOfArrSubQtyDistribution = array();
        //arr to contain subtotals
        $arrIndividualSalesStat = array();

        $arrMonthDataFound = array();

        $maxSubQtyIndex = 0;
        $maxSubCustIndex = 0;


        $customerLine = '';
        //echo "hi i am in ".$month;
        $lines = "<div style='margin-left:10px'>";
        // $lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

        //first set the group_concat_seesion
        $querySession = "SET SESSION group_concat_max_len = 1000000";
        mysql_query($querySession);


        $queryProducts = "Select ItemId,ProductName,GROUP_concat(Qty), group_concat(SellingPrice),
                  sum(qty),sum(qty*SellingPrice),group_concat(CustomerName),Month(SellingDate),Year(SellingDate)
                  from quickpro where ItemId='".$itemId."'
                  and Year(SellingDate)='" . $year . "' group by Month(SellingDate)";
        //echo $queryProducts;
        $resultProducts = mysql_query($queryProducts);
        $rowsProductNum = mysql_num_rows($resultProducts);

        $lines = '';
        if ($rowsProductNum == 0) {
            $lines .= "<table border='1'><tr><td>No result found for the year " . $year . "</td></tr></table>";
            //return;
        } else {
            $count = 1;
            $total = 0.0;
            //echo "totalrow".$rowsProductNum;
            for ($i = 0; $i < $rowsProductNum; $i++) {


                $lines .= '';
                $rowProduct = mysql_fetch_row($resultProducts);
                $itemId = $rowProduct[0];
                $month = $rowProduct[7];
                //echo $month;
                $monthName = $arrMonth[$month];

                $arrMissing = array();
                //calciulate the missing months..and add the data for them
                if ($month > 1) {
                    //get the last entry in the arrMonth Data found
                    $lenMonthData = sizeof($arrMonthDataFound);
                    if ($lenMonthData >= 1) {
                         //print_r($arrMonthDataFound);
                        $lastMonth = $arrMonthToVal[$arrMonthDataFound[$lenMonthData - 1]];
                        //echo "lastMonth" . $lastMonth . "<br/>";
                        //need to enter data for

                        for ($hh = $lastMonth + 1; $hh < $month; $hh++) {
                           //  echo "missing" . $hh . "<br/>";
                            array_push($arrMissing, $hh);
                        }
                    }
                    if ($lenMonthData == 0) {

                        //need to enter data for

                        for ($hh = 1; $hh < $month; $hh++) {
                            // echo "missing" . $hh . "<br/>";
                            array_push($arrMissing, $hh);
                        }
                    }


                }


                array_push($arrMonthDataFound, $monthName);
                //$lines.="<td>".$itemId."</td>";
                $productName = $rowProduct[1];
                //$lines.="<td>".$productName."</td>";


                $listQty = explode(",", $rowProduct[2]);

                $listSP = explode(",", $rowProduct[3]);
                $listCustomers = explode(",", $rowProduct[6]);

                $totQty = $rowProduct[4];
                $subtotal = $rowProduct[5];
                $subSellingString = '';

                $arrQtyDistribution = array();
                // this will give the qty wise distributioin for a particular month
                for ($jj = 0; $jj < sizeof($listQty); $jj++) {
                    $itemSp = $listSP[$jj];
                    $itemCount = $listQty[$jj];
                    if (array_key_exists($itemSp, $arrQtyDistribution)) {
                        $arrQtyDistribution[$itemSp] += $itemCount;
                    } else {

                        $arrQtyDistribution[$itemSp] = $itemCount;

                    }
                }

                //need to convert the distribution to a string
                $arrTemp = array();
                $sumIndSalesQty = 0;
                $sumIndSalesTotal = 0.0;

                foreach ($arrQtyDistribution as $key => $value) {
                    $str = $key . ":" . $value;
                    array_push($arrTemp, $str);
                    $sumIndSalesQty += intval($value);
                    $sumIndSalesTotal += floatval($key) * intval($value);
                    //echo $sumIndSalesTotal."<br/>";
                }

                //enter for the missing terms
                for ($hh = 0; $hh < sizeof($arrMissing); $hh++) {
                    $arrTemp1 = array();
                    array_push($arrTemp1, "-" . ":" . "-");

                    array_push($aryOfArrSubQtyDistribution, $arrTemp1);
                    array_push($arrofArrListCustomers, $arrTemp1);
                    array_push($arrIndividualSalesStat, "-" . ":" . "-");
                }


                array_push($arrIndividualSalesStat, $sumIndSalesQty . ":" . $sumIndSalesTotal);

                //this keeps track of the number of entries to be printed for the qty and total
                if ($maxSubQtyIndex < sizeof($arrTemp))
                    $maxSubQtyIndex = sizeof($arrTemp);

                array_push($aryOfArrSubQtyDistribution, $arrTemp);
                //add customers
                $arrCustTemp = array();
                for ($kk = 0; $kk < sizeof($listQty); $kk++) {
                    array_push($arrCustTemp, $listQty[$kk] . ":" . $listCustomers[$kk]);
                }

                array_push($arrofArrListCustomers, $arrCustTemp);
                if ($maxSubCustIndex < sizeof($arrCustTemp))
                    $maxSubCustIndex = sizeof($arrCustTemp);
            }

            //before printing lets check if all the months aftewr the last found months are also covered
            $lastMonth = $arrMonthToVal[$arrMonthDataFound[sizeof($arrMonthDataFound) - 1]];
            //echo "last month is".$lastMonth;
            if($lastMonth<12)
            {
                for($hh=$lastMonth+1;$hh<=12;$hh++)
                {
                    $arrTemp1 = array();
                    array_push($arrTemp1, "-" . ":" . "-");

                    array_push($aryOfArrSubQtyDistribution, $arrTemp1);
                    array_push($arrofArrListCustomers, $arrTemp1);
                    array_push($arrIndividualSalesStat, "-" . ":" . "-");
                }


            }



            //now print the items
            //print_r($aryOfArrSubQtyDistribution);
            $lines .= "<table border='1'><tr><td>" . $year . "</td>";
            // echo sizeof($arrMonth);
            for ($j = 1; $j <= sizeof($arrMonth); $j++) {

                $lines .= "<td></td><td>" . $arrMonth[$j] . "</td><td></td><td></td>";

            }
            //$lines.="<td></td>";
            $lines .= "</tr>";
            //$lines.="</table></div><br/>";
            //echo $lines;


            //this is the line of qty..
            $lines .= "<tr><td>" . $itemId . "</td>";
            for ($j = 1; $j <= sizeof($arrMonth); $j++) {

                $lines .= "<td>Qty</td><td>SalePrice</td><td>Total</td>";
                $lines .= "<td></td>";

            }
            $lines .= "</tr>";

            //print_r($aryOfArrSubQtyDistribution);
            // $lines.="</tr>";

            //maxsubqtyIndex will detrmine how many rows at the max will be for the qty for any given month
            for ($i = 0; $i < $maxSubQtyIndex; $i++) {
                $lines .= "<tr>";
                for ($j = 0; $j < sizeof($aryOfArrSubQtyDistribution); $j++) {
                    if (isset($aryOfArrSubQtyDistribution[$j][$i])) {
                        //echo $aryOfArrSubQtyDistribution[$j][$i] . "<br/>";
                        $values = explode(':', $aryOfArrSubQtyDistribution[$j][$i]);

                        if ($values[0] == "-")
                            $subqtyTot = "-";
                        else
                            $subqtyTot = floatval($values[0]) * floatval($values[1]);

                        $lines .= "<td></td><td>" . $values[1] . "</td><td>" . $values[0] . "</td><td>" . $subqtyTot . "</td>";

                    } else
                        $lines .= "<td></td><td>-</td><td>-</td><td>-</td>";

                }
                $lines .= "<td></td></tr>";
            }


            //Printinjg of the total line

            $lines .= "<tr><b><td>Total</td>";
            for ($i = 0; $i < sizeof($arrIndividualSalesStat); $i++) {
                $values = explode(":", $arrIndividualSalesStat[$i]);
                if ($i == 0)
                    $lines .= "<td>" . $values[0] . "</td><td><td>" . $values[1] . "</td>";
                else if ($i == (sizeof($arrIndividualSalesStat) - 1))
                    $lines .= "<td></td><td>" . $values[0] . "</td><td><td>" . $values[1] . "</td><td></td>";
                else
                    $lines .= "<td></td><td>" . $values[0] . "</td><td><td>" . $values[1] . "</td>";

            }
            $lines .= "</b></tr>";

            $lines .= "<tr><td>Customers</td></tr>";
            //echo "maxcust".$maxSubCustIndex;
            for ($i = 0; $i < $maxSubCustIndex; $i++) {
                $lines .= "<tr>";
                for ($j = 0; $j < sizeof($arrofArrListCustomers); $j++) {
                    if (isset($arrofArrListCustomers[$j][$i])) {
                        //echo $aryOfArrSubQtyDistribution[$j][$i] . "<br/>";
                        $values = explode(':', $arrofArrListCustomers[$j][$i]);
                        //$subqtyTot = floatval($values[0]) * floatval($values[1]);

                        $lines .= "<td></td><td>" . $values[0] . "</td><td>" . $values[1] . "</td><td>";

                    } else
                        $lines .= "<td></td><td></td><td></td><td></td>";

                }
                $lines .= "<td></td></tr>";

            }


        }
        $lines .= "</table></div><br/>";
        //echo $lines;

        echo $lines;
    }


}

?>
