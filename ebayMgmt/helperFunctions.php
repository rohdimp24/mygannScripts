<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/17/15
 * Time: 6:18 AM
 */

require_once ('login.php');


function getResult($startDate,$endDate,$msg){
	$lines="<div style='margin-left:10px'>";
    $lines.=$msg;

    // $lines='';
    //$query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty),Shipping,CostPrice FROM AmazonTransactions Where //SellingDate <='".$endDate."'and SellingDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
    //echo $query;
   /* $query="SELECT SKU,Title,SUM(QTY) AS output,SellingPrice,SUM(SellingPrice*Qty),ItemId,Shipping,CostPrice,SUM(CostPrice*Qty) FROM EbayTransactions Where SKU!='' AND SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY Title ORDER BY `output` DESC";
	*/
	
	//Added on 19th June 2016: The goruping by sellingPrice as well so that if the item has different selling price it shows two different entires also removing the A.SKU!='' AND
	// refer to ali mail on 16th June 2016
	
	$query="SELECT A.SKU,A.Title,SUM( QTY ) AS output, SellingPrice, SUM( SellingPrice * Qty ),ItemId,B.Shipping,B.CostPrice, SUM( B.CostPrice * Qty ) FROM  `EbayTransactions` AS A LEFT JOIN EbayProductCost AS B ON A.Title = B.Title Where  SellingPrice > 0.00 AND CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY A.Title, SellingPrice ORDER BY `output` DESC";
	
	
	//echo $query."<br/>";
	$result= mysql_query($query);
    $rowsnum = mysql_num_rows($result);
    if($rowsnum==0)
    {
         $lines.= "<h3 style='color: #bf0000'>No results found</h3>";
        //return;
    }
    else
    {
        $lines.="<table border='1'><tr><th>Sno</th><th>SKU</th><th>Item</th><th>ItemId</th><th>Quantity Sold</th><th>Selling Price</th><th>Amount</th><th>Shipping</th><th>Ebay Fees</th><th>Cost Price</th><th>Profit</th><th>Profit%</th></tr>";
		$count=1;
        $total=0.0;
		$totalProfit=0.0;
		$totalEbayFees=0.0;
		$totalShipping=0.0;
		$totalQty=0;
		$totalCostPrice=0.0;
		
		for($j=0;$j<$rowsnum;$j++)
        {
            $row=mysql_fetch_row($result);
//            if($j==100)
//                break;
			$qty=$row[2];
			$sellingPrice=$row[3];
			$costPrice=$row[7];
			$shipping=$row[6];
			$ebayFee=floatval($row[3])*.20; //20%fees
			$profit=($sellingPrice-($costPrice+$shipping+$ebayFee))*$qty;
			
			$denom=($costPrice+$shipping+$ebayFee)*$qty;
			//echo $denom;
			if($costPrice<.1){
				$profitPerc=0;
				$profit=0;
			}				
			else
				$profitPerc=($profit/$denom)*100;
           // $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[2]."</td></tr>";
            $lines.="<tr><td>".$count++."</td><td>".$row[0]."</td><td>".$row[1]."</td><td>".$row[5]."</td><td>".$row[2]."</td><td>".$row[3]."</td><td>".$row[4]."</td><td>".$row[6]."</td><td>".$ebayFee."</td><td>".$row[7]."</td><td>".number_format($profit,2,'.',',')."</td><td>".number_format($profitPerc,2,'.',',')."%</td></tr>";
            $total+=floatval($row[4]);
			$totalProfit+=$profit;
			$totalEbayFees+=$ebayFee*$qty;
			$totalShipping+=$shipping*$qty;
			$totalQty+=$qty;
			$totalCostPrice+=$row[7]*$qty;
        }

        $lines.="<tr><td></td><td></td><td></td><td><b>Total:</b></td><td><b>".$totalQty."</b></td><td></td><td><b>".number_format($total, 2, '.', ',')."$</b></td><td><b>".number_format($totalShipping, 2, '.', ',')."$</b></td><td><b>".number_format($totalEbayFees, 2, '.', ',')."$</b></td><td><b>".number_format($totalCostPrice,2,'.',',')."$</b></td><td><b>".number_format($totalProfit,2,'.',',')."$</b></td><td></td></tr>";
        $lines.="</table>";
		if($total<$totalCostPrice)
			$lines.="<br/><b>".WRONG."</b>";
       
    }
    $lines.="</div><br/>";
	return $lines;
}


?>


