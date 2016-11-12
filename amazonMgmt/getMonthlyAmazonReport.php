<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php

require_once ('loginDetails.php');
require_once ('reportHeader.php');
require_once('helperFunctions.php');

set_time_limit(0);


//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-03-01'));
//$endDate=date('Y-m-d',strtotime('2015-04-30'));
$currentYear=date('Y');
//echo $currentYear;
$currentMonth=date('m');
//echo $currentYear."=>".$currentMonth;
$arrMonth=array();
$arrMonth['01']="January:31";
$arrMonth['02']="Feburary:28";
$arrMonth['03']="March:31";
$arrMonth['04']="April:30";
$arrMonth['05']="May:31";
$arrMonth['06']="June:30";
$arrMonth['07']="July:31";
$arrMonth['08']="August:31";
$arrMonth['09']="September:30";
$arrMonth['10']="October:31";
$arrMonth['11']="November:30";
$arrMonth['12']="December:31";



//get the number of years from the data base
$queryMaxMinDateYear="SELECT max(SellingDate), min(SellingDate) FROM `AmazonTransactions`";
$resultMaxMinDateYear=mysql_query($queryMaxMinDateYear);
$rowsMaxMinYear = mysql_fetch_row($resultMaxMinDateYear);
$maxDate=$rowsMaxMinYear[0];
$minDate=$rowsMaxMinYear[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$maxYear=intval($maxYear);
$minYear=intval($minYear);
?>
<br/>
<br/>


<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
    <label>Select the Year for which you want to see the data</label>
    <select name="year">
        <?php
        if(isset($_GET["year"]))
            $selYear=intval($_GET["year"]);
        else
            $selYear=$maxYear;
        for($i=$minYear;$i<=$maxYear;$i++){

            if($i==$selYear){
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
    <input type="submit">


</form>

<?php




if(isset($_GET["year"]))
{

    $year=$_GET["year"];
    echo "<span><h1  style='padding-left:20px;margin-top:30px;'>Monthly sales data for the year ".$year."</h1></span><br/>";
    for($i=1;$i<=12;$i++)
    {
        //skip the earlier months as we dont have the data before april 2015

        if($i<10)
        {
            $i="0".$i;

        }
        //echo $arrMonth[$i]."<br/>";
        $parts=explode(":",$arrMonth[$i]);
        $startDate=$year."-".$i."-01";
        $endDate=$year."-".$i."-".$parts[1];

        
        //echo $startDate.";".$endDate."<br/>";
        $monthName=$parts[0];
        echo displayTop100Data($startDate,$endDate,$monthName);

    }
}



//get the max date from the database
/*$queryMaxMinDate="Select Max(Distinct(SellingDate)),Min(Distinct(SellingDate)) from AmazonTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
//$minDate=$rowsMaxMin[1];
//echo $maxDate;

//explode the data usin gthe -
$dateParts=explode("-",$maxDate);
//print_r($maxd);

//$maxd=date_parse_from_format("Y-m-d", $maxDate);

$maxMonth=$dateParts[1];

//echo $maxMonth;
$maxMonthNameParts=explode(":",$arrMonth[$maxMonth]);
$maxMonthName=$maxMonthNameParts[0];
//echo $maxMonthName;
echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Amazon upto ". $maxMonthName." ".$currentYear."</h1></span><br/>";


for($k=2015;$k<=$currentYear;$k++)
{
    $year=$k;
    for($i=1;$i<=$maxMonth;$i++)
    {
        //skip the earlier months as we dont have the data before april 2015
        if($year==2015 && $i<3)
            continue;
        if($i<10)
        {
            $i="0".$i;

        }
        //echo $arrMonth[$i]."<br/>";
        $parts=explode(":",$arrMonth[$i]);
        $startDate=$year."-".$i."-01";
        $endDate=$year."-".$i."-".$parts[1];

        //echo $startDate.";".$endDate."<br/>";
        $monthName=$parts[0]." ".$k;
        echo displayTop100Data($startDate,$endDate,$monthName);

    }
}
*/


//displays the top hundred data
function displayTop100Data($startDate,$endDate,$month){

   /* $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying results for ".$month ."</H2><br/>";

    // $lines='';
    $query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),Shipping FROM AmazonTransactions Where SellingDate <='".$endDate."'and SellingDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
    //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
        $lines.= "No results found";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th><th>Shipping</th><th>Amazon fees</th></tr>";

        $count=1;
        $total=0.0;
       for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;
			$amazonFees=floatval($row[3])*.15;
            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".$row[5]."</td><td>".number_format($amazonFees, 2, '.', ',')."</td></tr>";
            $total+=floatval($row[4]);
            
        }
         $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', ',')."$</b></td></tr>";

        $lines.="</table>";
    }
    $lines.="</div><br/>";
	*/
	
	$msg="<H2>Displaying results for ".$month ."</H2><br/>";
	$lines='';
    $lines.=getResult($startDate,$endDate,$msg);
	return $lines;
    //return;
}




?>