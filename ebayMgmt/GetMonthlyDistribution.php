<?php
ob_start();
?>
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
require_once ('reportHeader.php');
require_once ('itemMonthly.php');

set_time_limit(0);

if(isset($_GET["year"])){  



 $year = $_GET["year"];

 $startDate = $year . "-01-01";
 $endDate = $year . "-12-31";

$queryProducts="select distinct(title) from EbayTransactions";
$resultProducts=mysql_query($queryProducts);
$rowsnumProducts=mysql_num_rows($resultProducts);
//echo $rowsnumProducts;

$arrData=array();
for($i=0;$i<$rowsnumProducts;$i++)
{
	$rowProduct=mysql_fetch_row($resultProducts);
	$title=$rowProduct[0];
	//echo $title.",";
	if(strlen($title)<1)
		continue;
	$obj=new ItemMonthly('','',$title,0,0,0,0,0,0,0,0,0,0,0,0,$year,0);
    $total=0;
	$queryDist="select sum(qty),MONTH(CreationDate),itemId,title,sku from EbayTransactions where
                title ='".$title."' and creationDate>='".$startDate."' and creationDate<='".$endDate."' group by MONTH(CreationDate)";
				
    $resultDist=mysql_query($queryDist);
    $rowsnumDist=mysql_num_rows($resultDist);
	//echo $rowsnumDist."<br/>";
	
	
	for($j=0;$j<$rowsnumDist;$j++)
    {
        $rowDist=mysql_fetch_row($resultDist);
        //print_r($rowDist[3]);
        if($j==0) {
            $obj->setItemId($rowDist[2]);
            $obj->setSKU($rowDist[4]);
        }
        $monthId=$rowDist[1];
        if($monthId==1){
            $obj->setJan($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==2){
            $obj->setFeb($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==3){
            $obj->setMar($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==4){
            $obj->setApr($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==5){
            $obj->setMay($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==6){
            $obj->setJun($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==7){
            $obj->setJul($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==8){
            $obj->setAug($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==9){
            $obj->setSep($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==10){
            $obj->setOct($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==11){
            $obj->setNov($rowDist[0]);
            $total+=intval($rowDist[0]);
        }
        if($monthId==12){
            $obj->setDec($rowDist[0]);
            $total+=intval($rowDist[0]);
        }

        $obj->total=$total;
	}
	array_push($arrData,$obj);
	 
	}
}

echo "<span><h1  style='padding-left:20px;margin-top:30px;'> Monthly distribution of sales</h1></span><br/>";
echo "<div style='padding-left:20px;'>";
//print_r($arrData);
$length=sizeof($arrData);
for($i=0;$i<$length;$i++){
    $arrData[$i]->display();
}
echo "</div>";


//for the excel sheet based on https://gist.github.com/ihumanable/929039
function GenerateExcelSheet($temparray)
{
    include_once("Excel/ExcelWriter.php");
    $title = "Sheet1";
    $xls = new Excel($title);
    $xls->top();
    $xls->home();
    //add titles
    $xls->label("SKU");
    $xls->right();
    $xls->label("Title");
    $xls->right();
    $xls->label("Jan");
    $xls->right();
    $xls->label("Feb");
    $xls->right();
    $xls->label("Mar");
    $xls->right();
    $xls->label("Apr");
    $xls->right();
    $xls->label("May");
    $xls->right();
    $xls->label("Jun");
    $xls->right();
    $xls->label("Jul");
    $xls->right();
    $xls->label("Aug");
    $xls->right();
    $xls->label("Sep");
    $xls->right();
    $xls->label("Oct");
    $xls->right();
    $xls->label("Nov");
    $xls->right();
    $xls->label("Dec");
    $xls->right();
    $xls->label("Total");
    $xls->right();

    $xls->down();
    $xls->home();



	$length=sizeof($temparray);

    for($i=0;$i<$length;$i++)
    {
        $obj=$temparray[$i];
        $xls->label($obj->sku);
        $xls->right();
        $xls->label($obj->title);
        $xls->right();
        $xls->label($obj->jan);
        $xls->right();
        $xls->label($obj->feb);
        $xls->right();
        $xls->label($obj->mar);
        $xls->right();
        $xls->label($obj->apr);
        $xls->right();
        $xls->label($obj->may);
        $xls->right();
        $xls->label($obj->jun);
        $xls->right();
        $xls->label($obj->jul);
        $xls->right();
        $xls->label($obj->aug);
        $xls->right();
        $xls->label($obj->sep);
        $xls->right();
        $xls->label($obj->oct);
        $xls->right();
        $xls->label($obj->nov);
        $xls->right();
        $xls->label($obj->dec);
        $xls->right();
        $xls->label($obj->total);




        //next line
        $xls->down();
        //start
        $xls->home();
    }

    $data = ob_get_clean();
    $xls->send();
    file_put_contents('report.xls', $data);


}

GenerateExcelSheet($arrData);






?>
