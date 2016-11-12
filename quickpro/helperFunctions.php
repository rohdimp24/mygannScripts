<?php

require_once("login.php");

function fetchSellingPrices(){
    $query="Select * from quickpromultiprice";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);

    $arrProductsWithSPs=array();

    $oldNum='';

    for($i=0;$i<$rowsnum;$i++)
    {
        $row=mysql_fetch_row($result);
        $itemId=$row[0];
        $sellingPrice=$row[1];
        $costPrice=$row[2];
        $productName=$row[3];

        if($oldNum!=$itemId)
        {
            $obj=new stdClass();
            $obj->itemId=$itemId;
            $arrSPs=array();
            $arrCPs=array();
            array_push($arrSPs,$sellingPrice);
            array_push($arrCPs,$costPrice);
            
            //$obj->sellingPrices=$arrSPs;
            //$obj->costPrices=
            //array_push($arrProducts, $obj);
            $arrProductsWithSPs[$itemId]["SP"]=$arrSPs;
            $arrProductsWithSPs[$itemId]["CP"]=$arrCPs;
            $oldNum=$itemId;
        }
        else
        {
            if(isset($arrProductsWithSPs[$itemId])){
                array_push($arrProductsWithSPs[$itemId]["SP"], $sellingPrice);
                array_push($arrProductsWithSPs[$itemId]["CP"], $costPrice);
            }

        }

    }

    return $arrProductsWithSPs;
}


function getCostPriceForSP($itemId,&$arrProductsWithSPs,$sellingPrice){

	//echo $itemId;

    $listProductSPs=$arrProductsWithSPs[$itemId]["SP"];
    $listProductCPs=$arrProductsWithSPs[$itemId]["CP"];
    //print_r($listProductSPs);
    $key = array_search($sellingPrice, $listProductSPs);
    return $listProductCPs[$key];

}

function displayHelperFunction(&$rowProduct,&$arrProductsWithSPs,$count){
		//print_r($arrProductsWithSPs);
        $itemId=$rowProduct[0];

        $productName=$rowProduct[1];
        $listQty=explode(",",$rowProduct[2]);
        //print_r($listDistinctSP);
        $listSP=explode(",",$rowProduct[3]);

        //get the cps for the products
        $listCP=$arrProductsWithSPs[$itemId]["CP"];
      //  $tempListSP=$arrProductsWithSPs[$itemId]["SP"];


        $totQty=$rowProduct[4];
        $subtotal=$rowProduct[5];
        $subSellingString='';
        $arr=array();
        $arrCP=array();
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

                $arrCP[$itemSp]=getCostPriceForSP($itemId,$arrProductsWithSPs,$itemSp);

            }
        }
        
        $profit=0.0;
        $totalExpenditure=0.0;
		$profitPercent=0.0;
        $subCostString='';
        foreach($arr as $key => $value)
        {
            $subSellingString.=$value." of $".number_format($key,'2','.',',')."<br/>";
            $subCostString.=number_format($key,'2','.',',')."@".number_format($arrCP[$key],'2','.',',')."<br/>";
            $totalExpenditure+=intval($value)*floatval($arrCP[$key]);
        }
        
        $profit=floatval($subtotal)-$totalExpenditure;
		if($totalExpenditure==0)
			$profitPercent=0;
		else
			$profitPercent=floatval($profit/$totalExpenditure)*100;
        //echo $totalExpenditure;

        //$profit=floatval($subtotal)-intval($rowProduct[4])*floatval($rowProduct[6]);
        $costPriceString=$subCostString."TotalCost:$".number_format($totalExpenditure,'2','.',',');

        //$totalProfit+=$profit;
        //basically somehow get all the occurences of a string this should be the count
        $lines="<tr><td>".$count."</td><td>".$itemId."</td><td>".$productName."</td><td>".$totQty."</td>
            <td>".$subSellingString."</td><td>$".number_format($subtotal,2,'.',',')."</td><td>".$costPriceString.
            "</td><td>$".number_format($profit,2,'.',',')."</td><td>".number_format($profitPercent,2,'.','')."</td></tr>";
        //echo $lines."<hr/>";
        //$total+=$subtotal;
        return $profit."^".$subtotal."^".$lines;

}



?>