<?php
require_once 'Excel/reader.php';
require_once 'login.php';
$analysisData = new Spreadsheet_Excel_Reader();
set_time_limit(0);

error_reporting(0);
// Set output Encoding.
$analysisData->setOutputEncoding('CP1251');
$inputFileName = 'Files/price.xls';
// $inputFileName = Files/'.$_POST['filename'];
$analysisData->read($inputFileName);
error_reporting(E_ALL ^ E_NOTICE);
$numRows=$analysisData->sheets[0]['numRows'];
$numCols=$analysisData->sheets[0]['numCols'];
//28683
echo $numCols.",".$numRows;
//$arr=array();
$currentProductNum='';
$currentProductName='';

 for($i=5;$i<=$numRows;$i++) {
	$row=$analysisData->sheets[0]['cells'][$i];
//	print_r($row);
	
	//echo "<br/><hr/>";
	$productNum=$row[3];
	$price=$row[6];
	$productName='';
	if(isset($row[5]))
		$productName=$row[5];

	
	$query="INSERT INTO `quickproPrice`(`ItemId`, `Price`, `ProductName`) VALUES ('".$productNum."','".$price."','".mysql_real_escape_string($productName)."')";
	echo $query."<br/>";
	//$result=mysql_query($query);
	//if(!$result)
		//echo $query. mysql_error()."<br/>";
	
}

?>