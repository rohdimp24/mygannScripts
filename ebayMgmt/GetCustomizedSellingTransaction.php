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
require_once ('login.php');
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
/*
$arrMonth=array();
$arrMonth['01']="January:01;1:3;4:10;11:17;18:24;25:31";
$arrMonth['02']="February:02;1:7;8:14;15:21;22:28;0:0";
$arrMonth['03']="March:03;1:7;8:14;15:21;22:28;29:31";
$arrMonth['04']="April:04;1:4;5:11;12:18;19:25;26:30";
$arrMonth['05']="May:05;1:2;3:9;10:16;17:23;24:31";
$arrMonth['06']="June:06;1:6;7:13;14:20;21:27;28:30";
$arrMonth['07']="July:07;1:4;5:11;12:18;19:25;26:31";
$arrMonth['08']="August:08:1:8;9:15;16:22;23:29;30:31";
$arrMonth['09']="September:09:1:5;6:12;13:19;20:26;27:30";
$arrMonth['10']="October:10:1:3;4:10;11:17;18:24;25:31";
$arrMonth['11']="November:11;1:7;8:14;15:21;22:28;29:30";
$arrMonth['12']="December:12;1:5;6:12;13:19;20:26;27:31";
*/

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

$arrWeek=array();
$arrWeek['1']="Week1:01:07";
$arrWeek['2']="Week2:08:14";
$arrWeek['3']="Week3:15:21";
$arrWeek['4']="Week4:22:28";
$arrWeek['5']="Week5:28:31";

?>
<br/>
<br/>
<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
<div>
<?php
echo "<b>Select the month and week</b>";
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


$weekSelect='<select name="Week" id="selectWeek">';
for($i=1;$i<=5;$i++)
{
//echo intval($catArray[$i]->getCatID());
    $weekData=explode(":",$arrWeek[$i]);
    $weekSelect .='<option value="'.$arrWeek[$i].'">'.$weekData[0].'</option>';
}
$weekSelect .= '</select></td></tr>';
echo $weekSelect;

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
    //print_r($monthNumeric);
    $weekArray=explode(":",$_POST["Week"]);
	$weekStart=$weekArray[1];
	$weekEnd=$weekArray[2];
	$week=$weekArray[0];
    //echo $week;
    //$dateRange=explode(":",$monthArr[$week]);
    //print_r($dateRange);
    if($monthNumeric[1]==2 && $week=="Week5")
        echo "<h1>".$week." does not exist for month of ".$monthNumeric[0].", ".$year."</h1>";
    else
    {
        $startDate=$year."-".$monthNumeric[1]."-".$weekStart;
        $endDate=$year."-".$monthNumeric[1]."-".$weekEnd;
        //echo $startDate."=>".$endDate."<br/>";

        $startDate=date('Y-m-d', strtotime($startDate));
        $endDate=date('Y-m-d', strtotime($endDate));

        //echo $startDate;
        //echo $endDate;

        echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay for
                        ". $week." of ".$monthNumeric[0].", ".$year." (".$startDate.")-(".$endDate.")</h1></span><br/>";

       echo displayTop100Data($startDate,$endDate);

    }


}


function displayTop100Data($startDate,$endDate){

    //echo "hi i am in ".$month;
    /*$lines="<div style='margin-left:10px'>";
    //$lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty) FROM EbayTransactions Where SKU !='' AND SellingPrice > 0.00  AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
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
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

        $count=1;
        $total=0.0;
		//echo "hi rohit".$rowsnum;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            

            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";
            $total+=floatval($row[4]);
			//echo $lines;
        }
        $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".$total."$</b></td></tr>";
        $lines.="</table>";
		
    }
    $lines.="</div><br/>";
	//echo $lines;
    return $lines;
    //return;
	*/
	
	$lines='';
	$msg='';
    $lines.=getResult($startDate,$endDate,$msg);
    return $lines;
}



?>