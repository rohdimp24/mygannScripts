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
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/13/15
 * Time: 10:27 PM
 */


?>

<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" >
    <div>
        <?php
        echo '<input name="ItemId" id="ItemId">';
        echo "<input type='submit' class='btn-success btn-small' style='margin-bottom: 10px;' name='dateSubmit' value='Get Data'>";
        ?>
    </div>
</form>


<?php

if(isset($_GET['dateSubmit'])||isset($_GET['ItemId']))
{
    //print_r($_POST);
    $itemId=$_GET["ItemId"];
    $todaysDate=date('Y-m-d');

    $query="Select ItemId,Title,SKU,CreationDate, DATEDIFF('".$todaysDate."',CreationDate) from EbayTransactions Where ItemId='".$itemId."' order by CreationDate";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    $lines.="<h2>Data for item ".$itemId."</h2>";
    if($rowsnum==0)
    {
        echo "<h3>This item ".$itemId." has never been sold </h3>";
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>EbayItemId</th><th>Title</th><th>SKU</th><th>CreationDate</th><th>DaysUnsold</th></tr>";
        $count=1;
        for($i=0;$i<$rowsnum;$i++)
        {
            $row=mysql_fetch_row($result);
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."
            </td><td>".$row[4]."</td></tr>";

        }
        $lines.="</table>";

        echo $lines;
    }



}


?>