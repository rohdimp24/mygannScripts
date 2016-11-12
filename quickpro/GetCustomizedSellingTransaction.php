<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php
require_once ('login.php');
require_once ('reportHeader.php');

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
//$arrYear['2016']="2016";

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

$arrWeek=array();
$arrWeek['1']="Week1";
$arrWeek['2']="Week2";
$arrWeek['3']="Week3";
$arrWeek['4']="Week4";
$arrWeek['5']="Week5";

?>

<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
<div>
<?php
$yearSelect='<select name="Year" id="selectYear">';
//for($i=1;$i<=12;$i++)
//{
//echo intval($catArray[$i]->getCatID());
    $yearSelect .='<option value="2015">'.$arrYear['2015'].'</option>';
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
    $weekSelect .='<option value="'.$i.'">'.$arrWeek[$i].'</option>';
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
    $week=$_POST["Week"];
    //echo $week;
    $dateRange=explode(":",$monthArr[$week]);
    //print_r($dateRange);
    if($dateRange[0]==0)
        echo "<h1>Week".$week." does not exist for month of ".$monthNumeric[0].", ".$year."</h1>";
    else
    {
        $startDate=$year."-".$monthNumeric[1]."-".$dateRange[0];
        $endDate=$year."-".$monthNumeric[1]."-".$dateRange[1];
        //echo $startDate."=>".$endDate."<br/>";

        $startDate=date('Y-m-d', strtotime($startDate));
        $endDate=date('Y-m-d', strtotime($endDate));

        //echo $startDate;
        //echo $endDate;

        echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Top Sales from Ebay for
                        Week". $week." of ".$monthNumeric[0].", ".$year." (".$startDate.")-(".$endDate.")</h1></span><br/>";

       echo displayTop100Data($startDate,$endDate);

    }


}


function displayTop100Data($startDate,$endDate){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    //$lines.="<H2>Displaying top results for ".$month ."</H2><br/>";

    // $lines='';
    $query="SELECT ItemId,Title,SUM(QTY) AS output FROM EbayTransactions Where CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
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
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th></tr>";

        $count=1;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;

            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
        }
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
    //return;
}



?>