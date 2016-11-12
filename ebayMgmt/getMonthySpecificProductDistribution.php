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
require_once ('login.php');
require_once ('reportHeader.php');
require_once ('itemMonthlyForparticularProductDistribution.php');
?>

<form action= "<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" >
<div>
<?php
echo "<br/><br/><hr/><b>Enter the Product Id for the monthly distribution</b>";
echo "<input type='text' id='txtItemId' name='txtItemId' />";
echo "<input type='submit' class='btn-success btn-small' style='margin-bottom: 10px;' 
        name='productSubmit' value='Get Data'>";

?>
    </div>
</form>

<?php

if(isset($_POST["txtItemId"])&&isset($_POST["productSubmit"])){

    

    function addMonthlyDataToObject($obj,$monthId,$qty)
    {
        $totalQty=0;
        if($monthId==1){
            $obj->setJan($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==2){
            $obj->setFeb($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==3){
            $obj->setMar($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==4){
            $obj->setApr($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==5){
            $obj->setMay($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==6){
            $obj->setJun($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==7){
            $obj->setJul($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==8){
            $obj->setAug($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==9){
            $obj->setSep($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==10){
            $obj->setOct($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==11){
            $obj->setNov($qty);
            $totalQty+=intval($qty);
        }
        if($monthId==12){
            $obj->setDec($qty);
            $totalQty+=intval($qty);
        }
        return $totalQty;
    }

    if(strlen($_POST["txtItemId"])<1)
    {
        echo "<h3>You have not entered any value</h3>";
        return;
    }

    $itemId=$_POST["txtItemId"].'%';


    $queryProducts="SELECT Title,SKU,Month(CreationDate),YEAR(CreationDate),sum(Qty),
    SellingPrice FROM `EbayTransactions` WHERE `SKU` like '".$itemId."' 
     group by YEAR(CreationDate), MONTH(CreationDate), SellingPrice order by Year(CreationDate) Desc,SKU, SellingPrice";

    $resultProducts=mysql_query($queryProducts);
    $rowsnumProducts=mysql_num_rows($resultProducts);
    if($rowsnumProducts==0)
    {
        echo "<h3>Product not found</h3>";
        return;
    }


    $str="<table border='1'>";

    $oldId="0";
    $arrYear=array();
    $currentYear=20;
    $arrData=array();

    
    for($i=0;$i<$rowsnumProducts;$i++)
    {
        //$obj=new ItemMonthly($itemId,'',0,0,0,0,0,0,0,0,0,0,0,0,$year,0);
        
        $rowDist=mysql_fetch_row($resultProducts);
        #print_r($rowDist);
        #echo "<br/>";
        $str.="<tr>";
        $str.="<td>".$rowDist[0]."</td>";
        $str.="<td>".$rowDist[1]."</td>";
        $str.="<td>".$rowDist[2]."</td>";
        $str.="<td>".$rowDist[3]."</td>";
        $str.="<td>".$rowDist[4]."</td>";
        $str.="<td>".$rowDist[5]."</td>";
        
        $titleFetched=$rowDist[0];
        $yearFetched=$rowDist[3];
        $itemFetched=$rowDist[1];
        $monthFetched=$rowDist[2];
        $qty=$rowDist[4];
        $sellingPrice=$rowDist[5];
        $totalSale=0;
        $internalKey=$itemFetched."_".$yearFetched."_".$sellingPrice;

        $str.="</tr>";
        /*if($currentYear!=$yearFetched)
        {
            echo "newyear".$yearFetched."<br/>";
            $currentYear=$yearFetched;
            array_push($arrYear, $currentYear);
            //$oldId=0;
                    
        }*/

        if($oldId!=$internalKey)
        {
            //echo "new".$itemFetched.",".$internalKey."<br/>";
            //get the costprice, shipping of this item ..actually this should be done just once
            //and not repeated for every year. but here for the simplicity of the logic we are doing it 
            // in this manner

            $queryExpenses="SELECT `SKU`, `CostPrice`, `Shipping` FROM `EbayProductCost` 
            where SKU='".$itemFetched."' and Title='".$titleFetched."'";    
            $resultExpenses=mysql_query($queryExpenses);
            $rowsnumExpenses=mysql_num_rows($resultExpenses);
            if($rowsnumExpenses==0)
            {
                $costPrice=0;
                $shipping=0;
            }
            else
            {
                $rowExpense=mysql_fetch_row($resultExpenses);
                $costPrice=$rowExpense[1];
                $shipping=$rowExpense[2];
            }

            //echo "<br/>";
            $oldId=$internalKey;
            if(isset($obj))
            {
                $obj->ebayFee=floatval($obj->totalSale*0.20);
				if($obj->costPrice<0.1)
				{
					$obj->profit=0;
					$obj->profitPercent=0;
				}
				else
				{
					$obj->profit=$obj->totalSale-($obj->totalQty*($obj->costPrice+$obj->shipping)+$obj->ebayFee);
					$denom=$obj->totalQty*($obj->costPrice+$obj->shipping)+$obj->ebayFee;
					$obj->profitPercent=($obj->profit/$denom)*100;
				}
                
                array_push($arrData,$obj);    
            }

 
            $obj=new ItemMonthlyForparticularProductDistribution($itemFetched,$titleFetched,0,0,0,0,0,0,0,0,0,0,0,0,$yearFetched,0,
                $costPrice,$shipping,$sellingPrice,$totalSale,0,0,0);
            #$total=0;
            $obj->totalQty+=addMonthlyDataToObject($obj,$monthFetched,$qty);
            $obj->totalSale+=floatval($qty*$sellingPrice);
            
       }
       else
       {
                         
            $obj->totalQty+=addMonthlyDataToObject($obj,$monthFetched,$qty);
            $obj->totalSale+=floatval($qty*$sellingPrice);
           
       }

       #to add the last obj
       if($i==($rowsnumProducts-1))
       {
            if(isset($obj))
            {
                $obj->ebayFee=floatval($obj->totalSale*0.20);
				if($obj->costPrice<0.1)
				{
					$obj->profit=0;
					$obj->profitPercent=0;
				}
				else
				{
				    $obj->profit=$obj->totalSale-($obj->totalQty*($obj->costPrice+$obj->shipping)+$obj->ebayFee);
					$denom=$obj->totalQty*($obj->costPrice+$obj->shipping)+$obj->ebayFee;
					$obj->profitPercent=($obj->profit/$denom)*100;
				}
                array_push($arrData,$obj);    
            }
       }
      
    }



    $str.="</table>";
    //echo $str;

    //print_r($arrData);

    #now printing the distribution
    /*for($j=0;$j<sizeof($arrYear);$j++)
    {
        $currYear=$arrYear[$j];

    }*/

    $currentYear=0;
    $displayString='';
	
	$totalCP=0.0;
    $totalSP=0.0;
    $totalShipping=0.0;
    $totalEbayFee=0.0;
    $totalProfit=0.0;


    echo "<h2>The Monthly distribution for the item ".$_POST['txtItemId']."</h2>";
    for($i=0;$i<sizeof($arrData);$i++)
    {
        $obj=$arrData[$i];
        if($currentYear!=$obj->year)
        {
            $currentYear=$obj->year;
            //echo "<b>".$currentYear."</b><br/>";
            //$displayString.="</table>";
            if($i>0)
			{
				
				$totalDenom=($totalCP+$totalShipping+$totalEbayFee);
				if($totalDenom<0.1)
					$totalProfitPercent=0;
				else
					$totalProfitPercent=($totalProfit/$totalDenom)*100;
				$displayString.='<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td><b>Total</td>
				<td>'.number_format($totalCP,2,'.',',').'</td>
				<td>'.number_format($totalShipping,2,'.',',').'</td>
				<td></td><td>'.number_format($totalSP,2,'.',',').'</td>
                <td>'.number_format($totalEbayFee,2,'.',',').'</td>
				<td>'.number_format($totalProfit,2,'.',',').'</td>
				<td>'.number_format($totalProfitPercent,2,'.',',').'%</td>
				</tr>';

				$totalCP=0.0;
				$totalSP=0.0;
				$totalShipping=0.0;
				$totalEbayFee=0.0;
				$totalProfit=0.0;
	
				$displayString.='</table><br/>';
            }
			$displayString.="<b> For Year ".$currentYear."</b><br/>";
            $displayString.='<table border="1"><tr><th>Title</th><th>ItemId</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th>
                      <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>TotalQty</th>
                      <th>costPrice</th><th>shipping</th><th>sellingPrice</th><th>TotalSale</th><th>EbayFee</th>
                      <th>profit</th><th>profit percent</th></tr>';
            
        }

        $displayString.=$obj->display();
		$totalCP+=$obj->costPrice*$obj->totalQty;
        $totalSP+=$obj->totalSale;
        $totalProfit+=$obj->profit;
        $totalShipping+=$obj->shipping*$obj->totalQty;
        $totalEbayFee+=$obj->ebayFee;

    }
	$totalDenom=($totalCP+$totalShipping+$totalEbayFee);
	if($totalDenom<0.1)
		$totalProfitPercent=0;
	else
		$totalProfitPercent=($totalProfit/$totalDenom)*100;
				
	$displayString.='<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td><b>Total</b></td>
				<td>'.number_format($totalCP,2,'.',',').'</td>
				<td>'.number_format($totalShipping,2,'.',',').'</td>
				<td></td><td>'.number_format($totalSP,2,'.',',').'</td>
                <td>'.number_format($totalEbayFee,2,'.',',').'</td>
				<td>'.number_format($totalProfit,2,'.',',').'</td>
				<td>'.number_format($totalProfitPercent,2,'.',',').'%</td>
				</tr>';
                
    echo $displayString;
    
}





?>
