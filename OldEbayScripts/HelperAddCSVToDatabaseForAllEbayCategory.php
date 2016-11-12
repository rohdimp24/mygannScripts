
<?php

include("loginDetails.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

echo "hh";

function checkFunction()
{
    return "function called";
}

function GetTimeStamp()
{
    $accessDate=date("Y-m-d");
    $timezone='Asia/Calcutta';
    date_default_timezone_set($timezone);
    $tz = date_default_timezone_get();
    $accessTime=date("H:i:s");
    $timeStamp=$accessDate;
    return $timeStamp;
}



function getProductsFromStore()
{
    //instead of saying FinalOutputProduct
    $inputFile= "classifiedProducts.csv";
    $fp=fopen($inputFile,'r');
    $count=0;
    //$arrCategory=array();
    $retna='';
    $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";

    while(!feof($fp))
    {
        $tempStr=fgets($fp);
        $line=trim($tempStr);
        if(strlen($line)==0)
            continue;
        list($ebayProductName,$productNum,$productName,$productLink,$price,$ebayCategory,$mygannCategory,$subCategory,$mygannCategoryId,$thumbNail,$combinedShipping,$shippingRate,$ebayItemCode)=explode('^',$line);

        //check if the product already exists in the database
        $isDuplicate=isSameEbayItemPresent($ebayItemCode);
        if($isDuplicate){
            echo $ebayProductName." is an old product. the link is".$productLink."  and ebaycode:".$ebayItemCode."<br/>";
            continue;
        }

        //in case the ebay id is different but exact same ebay name product exixts
        $isSimilarProductExists=isSameEbayLabelItemPresent($ebayProductName);
        if($isSimilarProductExists){
            echo $ebayProductName." is an similar and old product. the link is".$productLink."  and ebaycode:".$ebayItemCode."<br/>";
            continue;
        }



        $status=isProductPresent($productNum);
        if($status)
        {
            $productNum=getNewProductNum($productNum);
        }
        else
        {
            //insert the suffix only if the last character is not numeric
            $lastChar=$productNum[strlen($productNum)-1];
            if(!is_numeric($lastChar))
            {
                $suffix=$lastChar;
                $prodId=substr($productNum,0,strlen($productNum)-1);
                //check if the entry is already present
                $querySuffix="Select * from `csv_product_suffix` where `product_id`='".$prodId."'";
                $resultSuffix=mysql_query($querySuffix);
                $rowsnumSuffix=mysql_num_rows($resultSuffix);
                if($rowsnumSuffix==0)
                {
                    $insertSuffix="INSERT INTO `csv_product_suffix` (`product_id`,`suffix`) VALUES ('".$prodId."','".$suffix."')";
                    echo "initial".$insertSuffix."<br/>";
                    $resultSuffix=mysql_query($insertSuffix);
                }
                else
                {
                    $updateQuery="Update `csv_product_suffix` SET `suffix`='".$suffix."' where `product_id`='".$productNum."'";
                    echo $updateQuery;
                    mysql_query($updateQuery);
                }
            }
            else
            {
                $querySuffix="Select * from `csv_product_suffix` where `product_id`='".$productNum."'";
                $resultSuffix=mysql_query($querySuffix);
                $rowsnumSuffix=mysql_num_rows($resultSuffix);
                if($rowsnumSuffix==0)
                {
                    $insertSuffix="INSERT INTO `csv_product_suffix` (`product_id`) VALUES ('".$productNum."')";
                    echo "initial".$insertSuffix."<br/>";
                    $resultSuffix=mysql_query($insertSuffix);
                }
                else
                {
                    $updateQuery="Update `csv_product_suffix` SET `suffix`='' where `product_id`='".$productNum."'";
                    echo $updateQuery;
                    mysql_query($updateQuery);
                }
            }

        }
        $retna.= "<tr> \n";
        $retna .= "<td> \n";
        $retna .= "<img src=\"$thumbNail\"> \n";
        $retna .= "<p><a href=\"" . $productLink . "\">" . $ebayProductName . "</a></p>\n";
        $retna .= 'ProductSKU: <b>' . $productNum . "</b><br> \n";
        $retna .= 'EbayItemCode: <b>' . $ebayItemCode . "</b><br> \n";
        $retna .= 'ProductName: <b>' . $productName . "</b><br> \n";
        $retna .= 'Current price: <b>$' .$price. "</b><br> \n";
        $retna .= 'Ebay Category: <b>' .$ebayCategory. "</b><br> \n";
        $retna .= 'Mygann Catgeory: <b>' .$mygannCategory. "</b><br> \n";
        $retna .= 'Mygann Sub Catgeory: <b>' .$subCategory. "</b><br> \n";
        //$retna .= 'Condition: <b>' .$item->condition->conditionDisplayName . "</b><br> \n";
        //$retna .= 'Payment Method: <b>' .$item->paymentMethod . "</b><br> \n";

        $retna .= "</td> \n";

        $retna .= "</tr> \n";


        $insertQuery="INSERT INTO csv_product (`product_id`,`product_name`,`product_ebay_name`,`product_url`,`product_price`,`product_ebay_category`,`product_mygann_category`,`product_subcategory`,`product_categoryId`,`product_thumb_image`,`ebayItemCode`)
        VALUES ('".$productNum."','".htmlspecialchars($productName)."','".htmlspecialchars($ebayProductName)."','".$productLink."','".$price."','".htmlspecialchars($ebayCategory)."','".htmlspecialchars($mygannCategory)."','".htmlspecialchars($subCategory)."','".$mygannCategoryId."','".$thumbNail."','".$ebayItemCode."')";

        //echo $count."..".$insertQuery."<br/>";
        $count++;

        $result=mysql_query($insertQuery);
        if(!$result)
        {
            echo "cannot insert due to". mysql_error(). "<br/>";

        }
        else
        {
            echo "insert successful". $productNum." (".$ebayItemCode.")<br/>";
            //entering the suffix
            //enterSuffix($productNum);

        }

        $addedOnDate=GetTimeStamp();

        //also add the item as a new item
        $insertStatus="INSERT INTO `ebaySyncUp` (`ebayItemCode`,`status`,`addedOnDate`) VALUES ('".$ebayItemCode."','1','".$addedOnDate."')";
        $resultStatus=mysql_query($insertStatus);
        if(!$resultStatus)
        {
            echo "cannot insert due to". mysql_error(). "<br/>";

        }

    }
    $retna .= "</table>";
    fclose($fp);


    //echo $retna;



} // End of getMostWatchedItemsResults function

//function to check if the product is already present in the database
function isProductPresent($productNum)
{
    $query="select * from csv_product where `product_id`='".$productNum."'";
    echo "testing for ".$query;
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    if($rowsnum>0)
        return true;
    else
        return false;

}

function isSameEbayItemPresent($ebayItemCode)
{
    $query="select * from csv_product where `ebayItemCode`='".$ebayItemCode."'";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    if($rowsnum>0)
        return true;
    else
        return false;

}

function isSameEbayLabelItemPresent($ebayProductName)
{
    $query="select * from csv_product where `product_ebay_name`='".$ebayProductName."'";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    if($rowsnum>0)
        return true;
    else
        return false;
    
}


//function to
function AppendAlphabet($productNum)
{

    $alpha=substr($productNum,strlen($productNum)-1,1);
    if(is_numeric($alpha))
        return 'A';
    else
    {

        //$replacedProductNum=substr_replace($productNum,++$alpha,-1);
        //return $replacedProductNum;
        return ++$alpha;
    }
    //echo "alpha".$alpha;


}

function getNewProductNum($productNum)
{
    //find the suffix from the product num
    echo $productNum;
    $suffixFound=false;

    //$suffix=AppendAlphabet($productNum);
    //echo "suffix".$suffix;
    $tempProduct=$productNum;
    $lastCharNotNumeric=false;
    $lastChar=$tempProduct[strlen($tempProduct)-1];
    if(!is_numeric($lastChar))
    {
        //$suffix=$lastChar;
        $prodId=substr($tempProduct,0,strlen($tempProduct)-1);
        $lastCharNotNumeric=true;
    }
    else
    {
        $prodId=$tempProduct;
        $lastCharNotNumeric=false;
    }
    $querySuffix="Select * from `csv_product_suffix` where `product_id`='".$prodId."'";
    echo $querySuffix."<br/>";
    $resultSuffix=mysql_query($querySuffix);
    $row=mysql_fetch_row($resultSuffix);
    print_r($row);
    $alpha=trim($row[1]);
    echo "alpha".$alpha;
    if(strlen($alpha)==0)
    {
        echo "hi A";
        $alpha="A";
        $suffix=$alpha;
    }
    else
    {
        echo "hi alpha";
        $suffix=++$alpha;


    }

    if($lastCharNotNumeric)
        $tempProduct[strlen($tempProduct)-1]=$suffix;
    else
        $tempProduct[strlen($tempProduct)]=$suffix;
    while(!$suffixFound)
    {


        if(isProductPresent($tempProduct))
        {
            $suffixFound=false;
            $suffix=++$alpha;
            $tempProduct[strlen($tempProduct)-1]=$suffix;

            echo "Trying Again with".$tempProduct."<br/>";
        }
        else
        {
            $suffixFound=true;
            break;
        }

    }

    $updateQuery="Update `csv_product_suffix` SET 	`suffix`='".$suffix."' where `product_id`='".$prodId."'";
    echo $updateQuery;
    mysql_query($updateQuery);
    //return substr_replace($productNum,$suffix,-1);
    if($lastCharNotNumeric)
        $productNum[strlen($productNum)-1]=$suffix;
    else
        $productNum[strlen($productNum)]=$suffix;
    return $productNum;

}



//just for testing
function identifyLatestProducts()
{
    $inputFile= "FinalOutputProducts.csv";
    $fp=fopen($inputFile,'r');

    //truncate the table initially

    while(!feof($fp))
    {
        $tempStr=fgets($fp);
        $line=trim($tempStr);
        if(strlen($line)==0)
            continue;
        list($ebayProductName,$productNum,$productName,$productLink,$price,$ebayCategory,$mygannCategory,$subCategory,$thumbNail,$combinedShipping,$shippingRate,$ebayItemCode)=explode('^',$line);

        if(isSameEbayItemPresent($ebayItemCode))
        {
            $insertStatus="INSERT INTO `ebaySyncUp` (`ebayItemCode`,`status`) VALUES ('".$ebayItemCode."','1')";
            $resultStatus=mysql_query($insertStatus);
            if(!$resultStatus)
            {
                echo "cannot insert due to". mysql_error(). "<br/>";

            }
        }


    }

    fclose($fp);

 }

//just for testing
function deleteProductsToSyncUp()
{
    $inputFile= "FinalOutputProducts.csv";
    $fp=fopen($inputFile,'r');

    //truncate the table initially

    while(!feof($fp))
    {
        $tempStr=fgets($fp);
        $line=trim($tempStr);
        if(strlen($line)==0)
            continue;
        list($ebayProductName,$productNum,$productName,$productLink,$price,$ebayCategory,$mygannCategory,$subCategory,$thumbNail,$combinedShipping,$shippingRate,$ebayItemCode)=explode('^',$line);

        $productIdQuery="Select product_id from csv_product where ebayItemCode='".$ebayItemCode."'";
        $resultProductIdQuery=mysql_query($productIdQuery);
        $rowsnumProduct=mysql_num_rows($resultProductIdQuery);
        if($rowsnumProduct==0)
            continue;
        else
        {
            $rowsnumProduct=mysql_fetch_row($resultProductIdQuery);
            $productId=$rowsnumProduct[0];
        }


        $queryDeleteSuffix="Delete from csv_product_suffix where product_id='".$productId."'";
        $resultDeleteSuffix=mysql_query($queryDeleteSuffix);

        $queryDeleteProduct="Delete from csv_product where product_id='".$productId."'";
        $resultDeleteProduct=mysql_query($queryDeleteProduct);


    }

    fclose($fp);

}


//truncate the insert table entries
function truncateSyncUpTable()
{
   $query="TRUNCATE ebaySyncUp";
   $result=mysql_query($query);

}



function createProductInfoReadyForDatabase()
{
    $addedOnDate=GetTimeStamp();
    //get all the new products
    $query="SELECT * FROM ebaySyncUp,csv_product WHERE ebaySyncUp.ebayItemCode=csv_product.ebayItemCode and ebaySyncUp.addedOnDate='".$addedOnDate."'";
    //and  ebaySyncUp.ebayItemCode='171661758167'";
    echo $query;
    $result=mysql_query($query);
    $rowsNum=mysql_num_rows($result);
    $outputFileName="productReadyNewToday.csv";
    echo "generating the file".$outputFileName;
    $fp = fopen($outputFileName, 'w');
    $retna='';
    for($i=0;$i<$rowsNum;$i++)
    {

        $row=$productRow=mysql_fetch_assoc($result);
       // if($i<4700)
         //   continue;

        $itemURL=$row[0];
        $productNum=$productRow['product_id'];
        $productName=str_replace("&quot;","\"",$productRow['product_name']);
        $productLink=$productRow['product_url'];
        $price=$productRow['product_price'];
        $ebayItemCode=$productRow['ebayItemCode'];
        $thumbNail=$productRow['product_thumb_image'];
        $combinedShipping=$productRow['combined_shopping'];
        $shippingRate=$productRow['shipping_rate'];
        $category=$productRow['product_mygann_category'];
        $subcategory=$productRow['product_subcategory'];
        $categoryId=$productRow['product_categoryId'];

        //this is the place that will perform the screen scrapping to fetch the data
        $prodDesc= getProductDescription($productLink);
        $size=$prodDesc["SIZE"];
        $qty=$prodDesc["QTY"];
        $images=$prodDesc["IMG"];
        //in case the images are not there then you need to skip this product
        if(strlen($images)<1)
        {
            echo "no image found";
            continue;
        }
//        echo "<br/>";
//        echo "The size obtained from getProduct";
//        print_r($prodDesc);
//        echo $size;
        $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";
        $retna.= "<tr> \n";
        $retna .= "<td> \n";
        $retna .= "<img src=\"$thumbNail\"> \n";
        $retna .= "<p><a href=\"" . $productLink . "\">".$productName.  "</a></p>\n";
        $retna .= 'ProductSKU: <b>' . $productNum . "</b><br> \n";
        $retna .= 'ProductName: <b>' . $productName . "</b><br> \n";
        $retna .= 'Current price: <b>$' .$price. "</b><br> \n";
        $retna .= 'Ebay ItemCode: <b>' .$ebayItemCode. "</b><br> \n";
        $retna .= 'category: <b>' .$category. "</b><br> \n";
        $retna .= 'sub-category: <b>' .$subcategory. "</b><br> \n";
        $retna .= 'categoryId: <b>' .$categoryId. "</b><br> \n";


        if($size==NULL)
        {
           // echo "sdklsdljls";
            $retna .= "size: <b>COULD NOT</b><br> \n";
            $size="NA";
        }
        else
        {
            $retna .= 'size: <b>' .$size. "</b><br> \n";
        }
        $retna .= 'qty: <b>' .$qty. "</b><br> \n";

        $retna .= "</td> \n";

        $retna .= "</tr> \n";

        $temparray= array();
            //productNumber
        array_push($temparray,$productNum);
        //productTitle
        array_push($temparray,$productName);
        //productLink
        array_push($temparray,$productLink);
        //ebay item code
        array_push($temparray,$ebayItemCode);
        //price
        array_push($temparray,$price);
        //category
        array_push($temparray,$category);
        //subcategory
        array_push($temparray,$subcategory);
        //categoryId
        array_push($temparray,$categoryId);
        //thumbnail
        array_push($temparray,$thumbNail);
        //qty(fromProductDesc)
        array_push($temparray,$qty);
        //size(fromProductDesc)
        array_push($temparray,$size);
        //imageList(FromProductList)
        array_push($temparray,$images);
        //combinedShipping
        array_push($temparray,$combinedShipping);
        //ShippingRate
        array_push($temparray,$shippingRate);

        fwrite($fp,convertArrayToCSVstring($temparray));
    }
        $retna .= "</table>";
    echo $retna;
    fclose($fp);

}


function getProductDescription($itemURL)
{
    $action=$itemURL;
    $method="GET";                                    // GET method
    $ref = "";                                        // Referer variable
    $response="";
    $data_array="";
    $response = http($target=$action, $ref, $method, $data_array, EXCL_HEAD);
    // etract the content
    $after_removing_extra_html = return_between($response['FILE'], "Search Store", "</center>", EXCL);

    //get the images
    $images_array = parse_array($after_removing_extra_html, "<img", ">");
    //create a image string out of array seperated by ^
    $imageStringTemp= implode ("@" ,$images_array );
    $imageStringPrefix=str_replace("<img src=\"","", $imageStringTemp);
    $imageStringExtraSpace=str_replace("\" />","", $imageStringPrefix);
    $imageString=str_replace("\">","", $imageStringExtraSpace);

    // remove the images
    $images_removed=remove($after_removing_extra_html, "<img", ">");

    //After removing the breakpoints
    $breakpoints_removed=remove($images_removed, "<br", ">");
    // remove the html tags
    $details_array= parse_array($breakpoints_removed,"<([A-Z][A-Z0-9]*)>",">");
    //print_r($details_array); //need to find the size thing fromt this array
    $indexSize=FindIndexforSize($details_array);
   // echo "indexSize".$indexSize;
    /*if($indexSize>0)
        echo "the value is".$details_array[$indexSize];
    else
        echo "NA";
    */
    //Get the Size from the line
    $sizeString=strip_tags($details_array[$indexSize]);
    //echo $sizeString;
    $sizeSplit=split(':',$sizeString);
    $sizeVal=$sizeSplit[1];
    // in case the "SIZE:" is not the string found
    if(strlen($sizeVal)==0)
        $sizeVal=$sizeSplit[0];
    //Get the Price line this will be the last line in the lines we have got
    $lenOfDetails=count($details_array);
    $priceString=strip_tags($details_array[$lenOfDetails-1]);
    //echo $priceString;
    // Try to get the qty by splitting the price line and then constructing the quantity from the portions of the array
    $priceSplit=split(' ',strtoupper($priceString));
    $count=count($priceSplit);
    $startKey=array_search('PRICE:',$priceSplit);
    //echo "aa".$startKey;
    $qty="";
    //in case the PRICE: line is missing
    if($startKey>-1)
    {
        for($i=$startKey+2;$i<$count;$i++)
            $qty .= ' '.trim($priceSplit[$i]);
    }

    /*for($i=$startKey+2;$i<$count;$i++)
        $qty .= ' '.trim($priceSplit[$i]);
    */
    if(strlen($qty)<2)
        $qty="PC";

    $description["SIZE"]=$sizeVal;
    $description["QTY"]=$qty;
    $description["IMG"]=$imageString;

    print_r($description);
    return $description;
}


// check the index of the size string
function FindIndexforSize($details_array)
{
    for($i=0;$i<sizeof($details_array);$i++)
    {
        //echo $details_array[$i];
        $pos = stripos($details_array[$i], "size:");
        if($pos!==false)
            return $i;
        $pos = stripos($details_array[$i],"size is");
        if($pos!==false)
            return $i;

    }
    return -1;

}


  ?>