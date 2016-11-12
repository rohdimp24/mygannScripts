<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/16/15
 * Time: 5:52 AM
 */
require_once ('loginDetails.php');
$fr=fopen("report3.txt","r");
//$lineArr=array();
while(!feof($fr))
{
    $line=fgets($fr);
    $lineArr=explode('item-is-marketplace	quantity',$line);
    //print_r($lineArr);
    //echo $line."rohit";
    $lineArr=explode('y	',trim($lineArr[1]));
    //print_r($lineArr);
}

fclose($fr);
//exit();

$length=count($lineArr);
$products=$length/11;

//we will remove the lines that dont have more than 5 parts
$filteredLinesArr=array();
//echo "<table border='1'>";
//echo "<tr><th></th>"
for($i=0;$i<$length;$i++){
//    echo $lineArr[$i]."<br/>";

    $parts=explode('	',$lineArr[$i]);
    if(count($parts)<5)
    {
        //echo "removing this line";
        //echo $lineArr[$i]."<br/>";
        continue;
    }
    else
        array_push($filteredLinesArr,$lineArr[$i]);
    //print_r($parts);
    //echo "<br/><hr/>";

}

//print_r($filteredLinesArr);
//
////now we need to get the sales date which is the first date that you have
$lengthFit=count($filteredLinesArr);

$finalOutput=array();

echo "<table border='1'>";
echo "<tr><th>Key</th><th>Item</th><th>ItemId</th><th>AmazonId</th><th>Quantity</th><th>Selling Price</th><th>Shipping</th><th>Date</th></tr>";
$count=1;
for($i=0;$i<$lengthFit;$i++){

  // echo $filteredLinesArr[$i]."<br/>";
  /* $pos=stripos($filteredLinesArr[$i],':');
    //echo $pos;
   $dateStartIndex=intval($pos)-13;
   $dateEndIndex=intval($pos)+10;

   $saledate=substr($filteredLinesArr[$i],$dateStartIndex,10);


    //also have the datetime which can act as a primary key along with the itemId
    $dateTimeKey=substr($filteredLinesArr[$i],$dateStartIndex,19);
    //echo "dat".$dateTimeKey;
*/

    if($i==16199)
       continue;
   else
   {
        $posForQty=stripos($filteredLinesArr[$i+1],' ');

        $qty=substr($filteredLinesArr[$i+1],0,$posForQty);

       if(strlen($qty)>2)
       {
           //echo $filteredLinesArr[$i+1]."<br/>";
        continue;
       }

   }

    //get the sku
    //$skuPart1=explode(':',$filteredLinesArr[$i]);
    //$skuPart2=explode('2015-',$skuPart1[0]);
    $skuPart2=explode('2015-',$filteredLinesArr[$i]);
    //print_r($skuPart2);
    $replacedString=str_replace('	','^',$skuPart2[0]);
    $replacedString=str_replace(' ','^',$replacedString);
    $skuPart3=explode('^',$replacedString);
    $ll=sizeof($skuPart3);

    $itemId=$skuPart3[$ll-4];
    $amazonId=$skuPart3[$ll-5];
    $sellingPrice=$skuPart3[$ll-3];
    //skip the selling price 0 stuff
    if($sellingPrice=="0")
        continue;
    $shippingCharges=$skuPart3[$ll-2];
    //echo $amazonId."=>".$itemId."<br/>";
    //if the amazonid is small that means ther is a problem in the way it has been parsed
    if(strlen($amazonId)<4){

        echo "some error in AmazonID".$filteredLinesArr[$i]."<br/>";
   }


    //to get the date
    $replacedStringForDate=str_replace('	','^',$skuPart2[1]);
    $replacedStringForDate=str_replace(' ','^',$replacedStringForDate);
    $explodeForDate=explode('^',$replacedStringForDate);
    $saleDate="2015-".$explodeForDate[0];

    $primaryKey=$itemId."_".$saleDate."_".$explodeForDate[1];





    //echo $skuPart3[$ll-4]."=>".$skuPart3[$ll-5]."<br/>";

//    $ll=sizeof($skuPart2);
//    $partString=substr($skuPart2[0],strlen($skuPart2[0])-20);
//    $partString = preg_replace('/\s+/', '#', $partString);
//    $skuPart3=explode('#',$partString);
//    if(count($skuPart3)<4)
//        continue;
//    //$itemId=$skuPart3[1];
    //echo $itemId."=>".$partString."<br/>";
   // $primaryKey=$itemId."_".$dateTimeKey;



    //get the name of the product
    //$parts=explode('	',$filteredLinesArr[$i]);
    $parts=explode($amazonId,$filteredLinesArr[$i]);

    //remove the qty from the start of the string.
    //skip the first string
    if($i>0)
    {
     //echo "sdjshkd";
        $stringPos=strpos($parts[0],' ');
        //echo $stringPos;
        $title=substr($parts[0],$stringPos);
    }
    else
    {
        $title=$parts[0];
    }


    if(strlen($title)==0)
        //echo $filteredLinesArr[$i];
        continue;

    //few title containing 00:00
    //but this will become too specific.


    if($qty=="0")
        //echo $filteredLinesArr[$i]."<br/>";
        continue;


 echo "<tr><td>".$primaryKey."</td><td>".$title."</td><td>".$itemId."</td><td>".$amazonId."</td><td>".$qty."</td><td>".$sellingPrice.
     "</td><td>".$shippingCharges."</td><td>".$saleDate."</td></tr>";



    //add to the database

    $query="INSERT INTO `AmazonTransactions`(`Id`, `Title`, `ItemId`, `AmazonId`, `SellingDate`, `Qty`, `SellingPrice`, `Shipping`) VALUES
            ('".$primaryKey."','".mysql_real_escape_string($title)."','".$itemId."','".$amazonId."','".$saleDate."',
            '".$qty."','".$sellingPrice."','".$shippingCharges."')";

    $result=mysql_query($query);
    if(!$result)
    {
        echo mysql_error()."<br/>";
        echo $query."<br/>";
        //return;
    }
    else
    {
       // echo "inserted into Amazon Transactions"."<br/>";
    }

}
/**
 * TO Do:
 * Move this code to the code that is fecthing in the reports in the first place
 * Update the dashboard so that the reporting is one of the section
 * We cannot group together the reports since there is no common key
 *
 *
 */


echo "</table>";
