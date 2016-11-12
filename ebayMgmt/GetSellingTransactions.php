<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php require_once('keys.php') ?>
<?php require_once('ebaySession.php') ?>
<?php require_once('Orders.php') ?>


<!--
Add details of the mysql database

We can fetch the data only from 120 days onwards. Earlier data shall be possible to manually enter by excel download or something like that

-->
<?php
require_once ('login.php');

set_time_limit(0);


//find the various weeks till date
$startDate=date('Y-m-d',strtotime('2015-04-05'));
$endDate=date('Y-m-d',strtotime('2015-04-11'));
//get the max date from the database
$queryMaxMinDate="Select Max(Distinct(CreationDate)) from EbayTransactions";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
//echo "Max date".$maxDate;
echo "<span><h1  style='padding-left:20px;'> Top Sales from Ebay upto ". $maxDate."</h1></span><br/>";
$week=1;
// $finalOutput='';
while($startDate<$maxDate)
{

   // echo $week."-->".$startDate."-->".$endDate."<br/>";
    echo displayTop100Data($startDate,$endDate,$week);
    $startDate=date('Y-m-d', strtotime($endDate. ' + 1 days'));
    $endDate=date('Y-m-d', strtotime($endDate. ' + 7 days'));
    $week++;
    // if($week>2)
    //     break;


}

echo $finalOutput;


//displays the top hundred data
function displayTop100Data($startDate,$endDate,$week){

    $lines="<div style='margin-left:10px'>";
    $lines.="<H2>Displaying top results for Week ".$week ." between ".$startDate." and ".$endDate."</H2><br/>";

   // $lines='';
    $query="SELECT ItemId,Title,SUM(QTY) AS output FROM EbayTransactions Where CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
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
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th></tr>";

        $count=1;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
            if($j==100)
                break;

            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
        }
        $lines.="</table>";
    }
    $lines.="</div><br/>";
    return $lines;
   //return;
}




?>
