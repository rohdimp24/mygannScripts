<?php
require_once 'Excel/reader.php';
require_once 'login.php';
date_default_timezone_set('UTC');
$analysisData = new Spreadsheet_Excel_Reader();
set_time_limit(0);

error_reporting(0);

$fp = fopen("Files/dup20101.txt", "w");
    // Set output Encoding.
    $analysisData->setOutputEncoding('CP1251');
    $inputFileName = 'Files/Oct 24_31.xls';
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

//28683:book4
//4993:2010
//23486:2011
//23967:2012
//24654:2013
//24491:2014
//23174:2015


    for($i=6;$i<=4993;$i++) {
        $row=$analysisData->sheets[0]['cells'][$i];
        print_r($row);
        if(sizeof($row)==1){
            echo "product found";
            $len=strlen($row[3]);
            //need to insert in the data base or update for the existsing product
            $pos=strpos($row[3],"(");

            $productNum=substr($row[3],0,$pos-1);
            $productName=trim(substr($row[3],$pos+1,$len-$pos-2));
            echo $productNum."#".$productName;
            $currentProductNum=$productNum;
            $currentProductName=$productName;

            //$queryCheck="Select * from quickpro where ItemId='".$productNum."'";
            //$resultCheck=mysql_query($queryCheck);
            //if(mysql_num_rows($resultCheck)==0)
            //{
              /*  $queryInsert="INSERT INTO `quickpro`(`ItemId`, `ProductName`)
                    VALUES ('".$productNum."','".mysql_real_escape_string($productName)."')";
                $resultInsert=mysql_query($queryInsert);
                if(!$resultInsert)
                {
                    echo mysql_error();
                    continue;
                }
                else
                {
                    echo "inserted successfully"."<br/>";
                }
            //}
                */


        }

        else
            if($row[6]!=null && $row[6]=="Invoice" ) {
                echo "Invoice";
                $customerName=$row[14];
                $sellingPrice=$row[18];
                $qty=$row[16];
                $total=$row[20];
                $num=$row[10];
                //$invoice=$row[10];
                $id=$currentProductNum."_".$num;

                ///
                //in order to read the date column properly, select the column then click on the text to columns default default entry
                $tempDate=$row[8];


                $tempDate=str_replace('/','-',$tempDate);
                $sellingDate=date('Y-m-d', strtotime($tempDate.'-1 days'));
              //  $startDate=date('Y-m-d', strtotime($endDate. ' + 1 days'));
                echo $sellingDate;

                //$productName=trim($ow[13]);
               /* $queryInsertDetails="UPDATE `quickpro` SET `SellingDate`='".$sellingDate."',`Num`='".$num."',
                `CustomerName`='".mysql_real_escape_string($customerName)."',
                    `Qty`='".$qty."',`SellingPrice`='".$sellingPrice."',`Total`='".$total."',
                `testProductName`='".mysql_real_escape_string($row[12])."' WHERE ItemId='".$currentProductNum."'";
               $resultInsertDetails=mysql_query($queryInsertDetails);
                */

               $queryInsertDetails="INSERT INTO `quickpro`(`Id`, `ItemId`, `ProductName`, `SellingDate`, `Num`, `CustomerName`, `Qty`,
                                        `SellingPrice`, `Total`, `testProductName`)
                                      VALUES ('".$id."','".$currentProductNum."','".mysql_real_escape_string($currentProductName)."','".$sellingDate."',
                                      '".$num."','".mysql_real_escape_string($customerName)."','".$qty."','".$sellingPrice."','".$total."','".mysql_real_escape_string($row[12])."')";

               print_r($queryInsertDetails);
                /*$resultInsertDetails=mysql_query($queryInsertDetails);
                if(!$resultInsertDetails) {
                    echo "errror found" . mysql_error();

                    fprintf($fp,$queryInsertDetails);
                    fprintf($fp,"\n");

                }*/




            }
        else
            echo "Total";

        echo "<br/><hr/>";



    }
    fclose($fp);
?>