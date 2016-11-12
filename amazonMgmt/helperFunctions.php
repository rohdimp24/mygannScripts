<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/17/15
 * Time: 6:18 AM
 */

require_once ('loginDetails.php');


function getResult($startDate,$endDate,$msg){
	$lines="<div style='margin-left:10px'>";
    $lines.=$msg;

    // $lines='';
    //$query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),Shipping,CostPrice,SUM(CostPrice*Qty) FROM /AmazonTransactions Where SellingDate <='".$endDate."'and SellingDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
	
	//-------------------------------------------------------------------------------------------------------------
	//Added on 19th June 2016: The goruping by sellingPrice as well so that if the item has different selling price it shows two different entires
	// refer to ali mail on 16th June 2016
	//Note: there are few entries in Db which have SP=0; I am not sure why that is the entry. May be at the time of querying  Amazon API it did not fill 
	//the values. anyways we need to remove these entries.
	
   $query="SELECT  A.ItemId,`Title`,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),B.Shipping,B.CostPrice FROM `AmazonTransactions` as A LEFT JOIN AmazonProductCost as B ON A.ItemId=B.ItemId WHERE SellingDate <='".$endDate."'and SellingDate >='".$startDate."' and SellingPrice>'0' GROUP BY A.ItemId, SellingPrice ORDER BY `output` DESC";
	
	//echo $query;
    $result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
        $lines.= "No results found";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>ItemId</th><th>Item</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th><th>Shipping</th><th>Amazon fees</th><th>CostPrice</th><th>Profit</th><th>Profit %</th></tr>";

        $count=1;
        $total=0.0;
		$totalProfit=0.0;
		$totalAmazonFees=0.0;
		$totalShipping=0.0;
		$totalQty=0;
		$totalCostPrice=0.0;
       for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;
			$amazonFees=floatval($row[3])*.20; //20% fees
			$sellingPrice=$row[3];
			$costPrice=$row[6];
			
			//check for the null since there can be case that the item is not in the amazonproductcost table so the cost price will come as null due to Left join
			if(is_null($costPrice))
				$costPrice=0.00;
			
			//echo $costPrice.";";
			$shipping=$row[5];
			if(is_null($shipping))
				$shipping=0.0;
			$qty=$row[2];
			
			
			//profit=selling-cost * qty..not taking the shipping 
			//$profit=(($row[3]-$row[6])*$row[2])-$amazonFees;
            $profit=($sellingPrice-($costPrice+$shipping+$amazonFees))*$qty;
			$denom=($costPrice+$shipping+$amazonFees)*$qty;
			//echo $denom;
			if($costPrice<.01)
			{
				$profit=0;
				$profitPerc=0;
			}
			else
				$profitPerc=($profit/$denom)*100;
			//$lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".number_format($shipping, 2, '.', ',')."</td><td>".number_format($amazonFees, 2, '.', ',')."</td><td>".number_format($costPrice, 2, '.', ',')."</td><td>".number_format($profit,2,'.',',')."</td><td>".number_format($profitPerc,2,'.',',')."%</td></tr>";
            $total+=floatval($row[4]);
			$totalProfit+=$profit;
			$totalAmazonFees+=$amazonFees*$qty;
			$totalShipping+=$shipping*$qty;
			$totalQty+=$qty;
			$totalCostPrice+=$row[6]*$qty;
            
        }
         $lines.="<tr><td></td><td></td><td><b>Total:</b></td><td><b>".$totalQty."</b></td><td></td><td><b>".number_format($total, 2, '.', ',')."$</b></td><td><b>".number_format($totalShipping, 2, '.', ',')."$</b></td><td><b>".number_format($totalAmazonFees, 2, '.', ',')."$</b></td><td><b>".number_format($totalCostPrice,2,'.',',')."$</b></td><td><b>".number_format($totalProfit,2,'.',',')."$</b></td><td></td></tr>";

		 
			 
		 
        $lines.="</table>";
		if($total<$totalCostPrice)
			$lines.="<br/><b>".WRONG."</b>";
    }
    $lines.="</div><br/>";
	return $lines;
}


?>


