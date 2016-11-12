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
?>
<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/13/15
 * Time: 10:27 PM
 */

$arrYear=array();

$arrYear['2015']="2015";
$arrYear['2016']="2016";

$arrMonth=array();
$arrMonth['01']="January:01;31";
$arrMonth['02']="February:02;28";
$arrMonth['03']="March:03;31";
$arrMonth['04']="April:04;30";
$arrMonth['05']="May:05;31";
$arrMonth['06']="June:06;30";
$arrMonth['07']="July:07;31";
$arrMonth['08']="August:08;31";
$arrMonth['09']="September:09;30";
$arrMonth['10']="October:10;31";
$arrMonth['11']="November:11;30";
$arrMonth['12']="December:12;31";

?>
<br/>
<br/>
<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
    <div>
        <?php
        $yearSelect='<select name="Year" id="selectYear">';
        //for($i=1;$i<=12;$i++)
        //{
        //echo intval($catArray[$i]->getCatID());
        $yearSelect .='<option value="2015">'.$arrYear['2015'].'</option>';
		$yearSelect .='<option value="2016">'.$arrYear['2016'].'</option>';
        //}
        $yearSelect .= '</select></td></tr>';

        echo $yearSelect;


        $monthSelect='<select name="Month" id="selectMonth">';
        for($i=1;$i<=12;$i++)
        {
//echo intval($catArray[$i]->getCatID());
            if($i<10)
                $index="0".$i;
            else
                $index=$i;

            $parts=explode(';',$arrMonth[$index]);
            $monthData=explode(":",$parts[0]);

            $monthSelect .='<option value="'.$arrMonth[$index].'">'.$monthData[0].'</option>';
        }
        $monthSelect .= '</select></td></tr>';

        echo $monthSelect;
        echo "<input type='submit' class='btn-success btn-small' style='margin-bottom: 10px;' name='dateSubmit' value='Get Data'>";

        ?>
    </div>
</form>


<?php

if(isset($_POST['dateSubmit']))
{
    //print_r($_POST);
    $year=$_POST["Year"];
    $monthArr=explode(";",$_POST["Month"]);
    $monthNumeric=explode(":",$monthArr[0]);
    $lastDayOfMonth=$monthArr[1];
    $startDate=$year."-".$monthNumeric[1]."-"."01";
    $endDate=$year."-".$monthNumeric[1]."-".$lastDayOfMonth;
    //echo $startDate."=>".$endDate."<br/>";

    $startDate=date('Y-m-d', strtotime($startDate));
    $endDate=date('Y-m-d', strtotime($endDate));

    //echo $startDate;
    //echo $endDate;

    echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Amazon for "
        .$monthNumeric[0].", ".$year." (".$startDate.")-(".$endDate.")</h1></span><br/>";

    echo displayTop100Data($startDate,$endDate);


 

}


function displayTop100Data($startDate,$endDate){

    //echo "hi i am in ".$month;
   /* $lines="<div style='margin-left:10px'>";
    //$lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

    // $lines='';
    $query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),Shipping  FROM AmazonTransactions Where SellingDate <='".$endDate."'and SellingDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
   //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
        $lines.= "<h3 style='color: #bf0000'>No results found</h3>";
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
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".$row[5]."</td><td>".number_format($amazonFees, 2, '.', ',')."</td></tr>";
            $total+=floatval($row[4]);
          
        }
        $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', ',')."$</b></td></tr>";

        $lines.="</table>";
    }
    $lines.="</div><br/>";
	*/
	
	$msg="";
	$lines='';
    $lines.=getResult($startDate,$endDate,$msg);
    return $lines;
    //return;
}



?>