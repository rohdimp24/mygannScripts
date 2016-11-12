<?php

require_once "LIB_parse.php";
//$fp=fopen("ebayspecificdetails.csv","r");

//$fw=fopen("updatedQty.txt","w");

/*while(!feof($fp))
{
    $tempStr=fgets($fp);
    list($productId,$ebayLink,$ebayId)=explode(";",$tempStr);
    $ebayLink=return_between($ebayLink,"\"http://www.ebay.com/itm/","/",EXCL);
    echo $ebayLink."<br/>";

    $pos=stripos($ebayLink,"PC");

    $start=0;
    if($pos==3)
        $start=0;
    else if($pos==4)
        $start=0;
    else if($pos==5)
        $start=0;
    else if($pos>5)
        $start=$pos-5;

    else
    {
        echo "no data";
        $writeString=$productId.";"."NA".PHP_EOL;
        fwrite($fw,$writeString);

    }

//    if($start==0)
//        echo "no data";
//    else
//    {
    $strpiece=substr($ebayLink,$start,6);
    //echo $pos."-->".$strpiece."<br/>";

    $arrElements=explode("-",$strpiece);
    print_r($arrElements);


    //check for digits
    for($j=0;$j<sizeof($arrElements);$j++)
    {
        if(preg_match('/^[1-9][0-9]*$/', $arrElements[$j])==true)
        {
            echo "the qty is=>".$arrElements[$j]."<br/>";
            $writeString=$productId.";".$arrElements[$j].PHP_EOL;
            fwrite($fw,$writeString);
            break;
        }

    }




    //TODO: get the list of the product ids which have only PC as the qty.
    // next for these items check what can be the qty
    //there are some for whcih ROLLS is the unit







//    echo $tempStr."<br/>";
}*/



require_once 'loginDetails.php';

//require_once'ProductDetails.php';
$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

$fw=fopen("updatedQty.txt","w");

$query="SELECT ebayspecificdetails.product_url,productNum FROM `jos_vm_product`,`ebayspecificdetails` where jos_vm_product.product_sku=ebayspecificdetails.productNum and product_s_desc like 'PC%'";
echo $query;
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);
echo $rowsnum;
for($i=0;$i<$rowsnum;$i++)
{
    $row=mysql_fetch_row($result);
    $productId=$row[1];

    $ebayLink=$row[0];
    //echo $ebayLink ."<br/>";
    $ebayLink=return_between($ebayLink,"http://www.ebay.com/itm/","pt=LH_DefaultDomain_0",EXCL);
    //echo $ebayLink."<br/>";

    $pos=stripos($ebayLink,"PC");

    $start=0;
    if($pos==3)
        $start=0;
    else if($pos==4)
        $start=0;
    else if($pos==5)
        $start=0;
    else if($pos>5)
        $start=$pos-5;

    else
    {
        //echo "no data";
        $writeString=$productId.";"."NA".PHP_EOL;
        //fwrite($fw,$writeString);

    }

//    if($start==0)
//        echo "no data";
//    else
//    {
    $strpiece=substr($ebayLink,$start,6);
    //echo $pos."-->".$strpiece."<br/>";

    $arrElements=explode("-",$strpiece);
    print_r($arrElements);


    //check for digits
    for($j=0;$j<sizeof($arrElements);$j++)
    {
        if(preg_match('/^[1-9][0-9]*$/', $arrElements[$j])==true)
        {
           // echo "the qty is=>".$arrElements[$j]."<br/>";
            $writeString=$productId.";".$arrElements[$j].PHP_EOL;
            fwrite($fw,$writeString);
            break;
        }

    }





}

fclose($fw);

$fp=fopen("updatedQty.txt","r");


while(!feof($fp))
{
    $tempStr=fgets($fp);
    list($productId,$qty)=explode(";",$tempStr);
    
    $query="Select product_s_desc,product_sku ,ebayspecificdetails.product_url FROM `jos_vm_product`,`ebayspecificdetails` where jos_vm_product.product_sku=ebayspecificdetails.productNum and product_sku='".$productId."'";
    $result=mysql_query($query);
    $row=mysql_fetch_row($result);
    echo "productId =>".$row[1]."product =>".$row[2]."original =>".$row[0]." while calculated =>".$qty."<br/>";

    if($qty==1)
        continue;
    if($qty>1 && $qty<5)
    {   
        $updateQtyDesc="SET (".$qty." PCS)";
        echo "New is ".$updateQtyDesc."<br/>";
    }
    else
    {
        $updateQtyDesc="LOT (".$qty." PCS)";
        echo "NEW is ".$updateQtyDesc."<br/>";
    }


     $updateQuery="UPDATE `jos_vm_product` SET product_s_desc='".$updateQtyDesc."' where product_sku='".$productId."'";
     $updateResult=mysql_query($updateQuery);
     if($updateResult)
        echo "success <br/>";
    else
        echo mysql_error();
    

}

fclose($fp);





























    ?>