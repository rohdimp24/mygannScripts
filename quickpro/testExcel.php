<?php
require_once 'Excel/reader.php';
require_once 'login.php';
$analysisData = new Spreadsheet_Excel_Reader();


error_reporting(0);


// Set output Encoding.
$analysisData->setOutputEncoding('CP1251');
$inputFileName = 'Files/Book4.xls';
// $inputFileName = Files/'.$_POST['filename'];
$analysisData->read($inputFileName);
error_reporting(E_ALL ^ E_NOTICE);
$numRows=$analysisData->sheets[0]['numRows'];
$numCols=$analysisData->sheets[0]['numCols'];

echo $numCols.",".$numRows;
//$arr=array();
$currentProductNum='';
for($i=1;$i<=1978;$i++) {
    $row=$analysisData->sheets[0]['cells'][$i];
    $tempDate=$row[8];
    $tempDate=str_replace('/','-',$tempDate);
    echo $tempDate."<br/>";
    echo date('Y-m-d', strtotime($tempDate));
  print_r($row);
//    $sellingDate=date($row[8]);
//    echo $sellingDate;
    echo "<br/>";
    echo "<hr/>";

}
?>