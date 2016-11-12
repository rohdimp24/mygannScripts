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
require_once ('reportHeader.php');

echo "<br/><br/>";
echo "<h1>Enter the item for which you want to generate the distribution</h1>";

?>
<!--    <form method="GET" action="getItemAllDetailsNew.php">-->
<!---->
<!--        <input type="text" name="itemId" id="itemId" />-->
<!--        <input type="submit" value="Generate Excel" />-->
<!---->
<!--    </form>-->

<form enctype="multipart/form-data"
      action="getItemAllDetailsForMultipleItems.php" method="post">
     <table>
        <!--<tr><td>Names of Delete Product file:</td><td><input type="file" name="file" /></td><td><strong>OR</strong></td>-->
        <tr><td>Enter the Product Nums (Comma seperated)</td>
            <td><textarea name='items' cols='200' rows='10'></textarea></td></tr>
        <tr><td>Select the year for which the report to be generated</td><td>
             <select name="year">
                 <option value="2015" selected>2015</option>
                 <option value="2014">2014</option>
                 <option value="2013">2013</option>
                 <option value="2012">2012</option>
                 <option value="2011">2011</option>
                 <option value="2010">2010</option>
             </select>
         </td></tr>
         <tr><td><input type="submit" name="submit" value="Generate Excel" /></td></tr>
    </table>
</form>
