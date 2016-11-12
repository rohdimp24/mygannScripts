<?php
require_once ('login.php');


    //echo "hi i am in ".$month;
    $lines="<div style='margin-left:10px'>";

    $endDate="2015-12-31";
    $startDate="2015-01-01";
    // $lines='';
    $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty),ItemId
    FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND
     CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
    //echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
        //return;
    }
    else
    {
        $arrData=array();
        $count=1;
        $total=0.0;
        for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
            //print_r($row);
            // $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            //$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[5]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td></tr>";

            $itemId=trim($row[5]);

            $sku=$row[0];
            $sellingPrice=$row[3];
            $title=$row[1];

            $queryPhoto="Select thumbnail,itemUrl from EbayProductsForTx where EbayItemId='".$itemId."'";
            $resultPhoto=mysql_query($queryPhoto);
            $rowsPhotoNum=mysql_num_rows($resultPhoto);
            //echo $rowsPhotoNum;
            if($rowsPhotoNum==0)
            {
                $thumbnail='';
                $itemUrl='';
                continue;
            }
            else
            {
               // echo $queryPhoto;
            
                $rowPhoto=mysql_fetch_row($resultPhoto);
                $thumbnail=$rowPhoto[0];
                $itemUrl=$rowPhoto[1];
            }






            $obj = new StdClass();
            $obj->itemId=$itemId;
            $obj->sku=$sku;
            $obj->title=$title;
            $obj->sellingPrice=$sellingPrice;
            $obj->thumbnail=$thumbnail;
            $obj->itemUrl=$itemUrl;

           // print_r($obj);
            array_push($arrData,$obj);


        }

    }

    echo json_encode($arrData);
    //return;




?>
