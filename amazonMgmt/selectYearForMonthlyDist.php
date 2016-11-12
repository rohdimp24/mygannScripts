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
require_once ('loginDetails.php');
require_once ('reportHeader.php');


set_time_limit(0);


echo "<br/><br/>";
echo "<h1>Select the year for which you want to get the distribution</h1>";
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



for($k=$minYear;$k<=$maxYear;$k++) {
$year = $k;
?>

<a href="getMonthyProductDistribution.php?year=<?php echo $year ?>" style="margin-left:30px;"><?php echo $year ?></a>
<?php
}

?>


