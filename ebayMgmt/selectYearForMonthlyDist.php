
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>
<?php
/**
 * Created by PhpStorm.
 * User: 305015992
 * Date: 10/27/2015
 * Time: 8:26 AM
 */

if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
require_once ('login.php');
require_once ('reportHeader.php');

echo "<br/><br/>";
echo "<h1>Select the year for which you want to get the distribution</h1>";
//get the year detials
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

for($k=$minYear;$k<=$maxYear;$k++) {
$year = $k;
?>

<a href="GetMonthlyDistribution.php?year=<?php echo $year ?>" style="margin-left:30px;"><?php echo $year ?></a>
<?php
}

?>


