<?php
ob_start();
?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>

<?php
require_once ('loginDetails.php');
require_once ('reportHeader.php');
require_once ('itemMonthly.php');



if(isset($_GET["year"])){  



 $year = $_GET["year"];

 $startDate = $year . "-01-01";
 $endDate = $year . "-12-31";


$queryProducts="select distinct(ItemId) from AmazonTransactions where SellingDate >='" . $startDate . "' and SellingDate <='" . $endDate . "'";

$resultProducts=mysql_query($queryProducts);
$rowsnumProducts=mysql_num_rows($resultProducts);

//print_r($resultProducts);

$arrData=array();
	for($i=0;$i<$rowsnumProducts;$i++)
	{

		$rowProduct=mysql_fetch_row($resultProducts);
		//print_r($rowProduct);
		$itemId=$rowProduct[0];
	   // echo $itemId."<br/>";

	//    $title=$rowProduct[1];
	//    $itemId=$rowProduct[2];

		$obj=new ItemMonthly($itemId,'',0,0,0,0,0,0,0,0,0,0,0,0,$year,0);
		$total=0;
		//for each of this product we need to get teh distribution
		$queryDist="select sum(Qty),MONTH(SellingDate),ItemId,Title from AmazonTransactions where
					ItemId ='".$itemId."' and SellingDate>='".$startDate."' and SellingDate<'".$endDate."' group by MONTH(SellingDate)";
		$resultDist=mysql_query($queryDist);

		$rowsnumDist=mysql_num_rows($resultDist);
		for($j=0;$j<$rowsnumDist;$j++)
		{
			$rowDist=mysql_fetch_row($resultDist);
			//print_r($rowDist[3]);
			if($j==0) {
				$obj->setTitle($rowDist[3]);
				//$obj->setSKU($rowDist[4]);
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

			//print_r($obj);

		}
		array_push($arrData,$obj);

	}
}

GenerateExcelSheet($arrData);
ob_end_flush();





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
        $xls->label($obj->itemId);
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

    /*$title = "Sheet1";
    $colors = array("red", "blue", "green", "yellow", "orange", "purple");



    $xls = new Excel($title);
    $xls->top();
    $xls->home();
    foreach ($colors as $color)
    {
        $xls->label($color);
        $xls->right();
#$xls->down();
    };*/
    $data = ob_get_clean();
    $xls->send();
    file_put_contents('report.xls', $data);


}

//print_r($arrData);
$length=sizeof($arrData);
for($i=0;$i<$length;$i++){
    $arrData[$i]->display();
}



?>
