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
require_once('keys.php') ;
require_once('ebaySession.php');
require_once('Orders.php');
require_once ('login.php');
require_once ('reportHeader.php');
require_once('helperFunctions.php');

set_time_limit(0);


//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-04-01'));
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
$queryMaxMinDateYear="SELECT max(CreationDate), min(CreationDate) FROM `EbayTransactions`";
$resultMaxMinDateYear=mysql_query($queryMaxMinDateYear);
$rowsMaxMinYear = mysql_fetch_row($resultMaxMinDateYear);
$maxDate=$rowsMaxMinYear[0];
$minDate=$rowsMaxMinYear[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$maxYear=intval($maxYear);
$minYear=intval($minYear);
$minYear=2015;

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




//displays the top hundred data
function displayTop100Data($startDate,$endDate,$month){

   /*
	$lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty) FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
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
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

        $count=1;
        $total=0.0;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;

            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
            $total+=floatval($row[4]);
        }
         $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', ',')."$</b></td></tr>";
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    //return;
	*/
	$msg.="<H2>Displaying top results for ".$month ."</H2><br/>";
	$lines='';
    $lines.=getResult($startDate,$endDate,$msg);
    return $lines;
	
}




?>