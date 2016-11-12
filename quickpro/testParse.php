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
require_once ('login.php');
require_once ('reportHeader.php');
require_once('LIB_parse.php');

set_time_limit(0);

//get the number of years from the data base
$queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `quickpro`";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));

$maxYear=intval($maxYear);
$minYear=intval($minYear);



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


//echo $maxYear.",".$minYear;

echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Yearly Sales from Ebay for year ". $year."</h1></span><br/>";

//for($i=$minYear;$i<=$maxYear;$i++)
//{
    //echo $i."<br/>";
    //get the display output for each year
  //  $year=$i;
    $startDate=$year."-01-01";
    $endDate=$year."-12-31";
    echo displayTop100Data($startDate,$endDate,$year);

}

function displayTop100Data($startDate,$endDate,$year){

    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";
    $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th></tr>";

    // $lines.="<H2>Displaying top results for ".$year ."</H2><br/>";

    //first get the products
    $queryProducts="SELECT ItemId,ProductName,GROUP_concat(Qty), group_concat(SellingPrice),sum(qty),sum(qty*SellingPrice)
                        from quickpro where Year(SellingDate)='".$year."' group by ItemId";
    $resultProducts=mysql_query($queryProducts);
    $rowsProductNum=mysql_num_rows($resultProducts);
    $count=1;
    $total=0.0;


    //$subtotal=0.0;
//    $subqty=0;
//    $subSellingString='';
//    $oldItem='';
    for($i=0;$i<$rowsProductNum;$i++)
    {

        $rowProduct=mysql_fetch_row($resultProducts);
        $itemId=$rowProduct[0];

        $productName=$rowProduct[1];
        $listQty=explode(",",$rowProduct[2]);
        //print_r($listDistinctSP);
        $listSP=explode(",",$rowProduct[3]);

        /*if($i>2106)
        {
           // print_r($rowProduct);
            //print_r($listQty);
            echo strlen($rowProduct[2]);
            echo ",";
            echo strlen($rowProduct[3]);
            //echo "<br/>".$rowProduct[3]."<br/>";
            //print_r($listSP);
            echo ",".$itemId;

        }*/
        $totQty=$rowProduct[4];
        $subtotal=$rowProduct[5];
        $subSellingString='';
        $arr=array();
        for($jj=0;$jj<sizeof($listQty);$jj++)
        {
            $itemSp=$listSP[$jj];
            $itemCount=$listQty[$jj];
            if(array_key_exists($itemSp,$arr))
            {
                $arr[$itemSp]+=$itemCount;
            }
            else {

                $arr[$itemSp] = $itemCount;
            }
        }
        foreach($arr as $key => $value)
        {
            $subSellingString.=$value." of ".$key."<br/>";
        }
        //basically somehow get all the occurences of a string this should be the count
        $lines.="<tr><td>".$count++."</td><td>".$itemId."</td><td>".$productName."</td><td>".$totQty."</td>
            <td>".$subSellingString."</td><td>".$subtotal."</td></tr>";
        $total+=$subtotal;

    }
    $lines.="<tr><td></td><td></td><td></td><td></td><td><b>Total Selling:</b></td><td><b>".number_format($total, 2, '.', '')."$</b></td></tr>";
    $lines.="</table>";
    $lines.="</div><br/>";
    return $lines;
}


?>
