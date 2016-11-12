<span style="margin-right:30px;float: right;font-weight: bold">
    <a href="getWeeklyAmazonReport.php">Weekly</a> |
    <a href="getMonthWiseAmazonReport.php">Monthly</a> |
    <a href="getMonthlyAmazonReport.php">Consolidated Monthly</a> |
    <a href="getYearlyAmazonReport.php">Yearly</a> |
	<a href="selectYearForMonthlyDist.php" ><b>Monthly Distributed for All Products</b></a> |
	<a href="getMonthySpecificProductDistribution.php" >Monthly Distributed for Specific Products</a> |
    <a href="getCustomizedAmazonReport.php">Customized</a>|
	<a href="updateAmazonProducts.php" >Update Shipping</a> |
	<a href="drawMonthlyDistribution.php" >Charts (Monthly Distribution)</a> |
	<a href="GetReturningItems.php" style="color:red"><b>Get Repeating/Non-Repeating Items (NEW)</b></a>
</span>
<?php
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
?>