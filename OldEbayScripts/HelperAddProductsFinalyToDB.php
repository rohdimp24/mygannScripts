<?php
include("loginDetails.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());


function fillData($fileName)
{
        //1. read the file
    $fr=fopen($fileName,'r');
    $retna='';
    $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";
    $showDebug=true;
    while(!feof($fr))
    {

        $tempStr=fgets($fr);
        $line=trim($tempStr);
        if(strlen($line)==0)
            continue;
        //2.for each line you have to get the csv split yup
        list($productNum,$productName,$productLink,$ebayItemCode,$price,$category,$subcategory,$mygannCatId
            ,$thumbNail,$qty,$size,$images,$combinedShipping,$shippingRate)=explode("^",$line);

        echo $line ."<br/>";

        $fullImageArray=explode("@",$images);


        $retna.= "<tr> \n";
        $retna .= "<td> \n";
       // $retna .= "<img src=\"$thumbNail\"> \n";
        $retna .= "<p><a href=\"" . $productLink . "\">".$productName.  "</a></p>\n";
        $retna .= 'ProductSKU: <b>' . $productNum . "</b><br> \n";
        $retna .= 'ProductName: <b>' . $productName . "</b><br> \n";
        $retna .= 'Current price: <b>$' .$price. "</b><br> \n";
        $retna .= 'Ebay ItemCode: <b>' .$ebayItemCode. "</b><br> \n";
        $retna .= 'category: <b>' .$category. "</b><br> \n";
        $retna .= 'sub-category: <b>' .$subcategory. "</b><br> \n";
        $retna .= 'categoryId: <b>' .$mygannCatId. "</b><br> \n";

        $retna .= 'shipping rate: <b>' .$shippingRate. "</b><br> \n";
        $retna .= 'combined shipping: <b>' .$combinedShipping. "</b><br> \n";
        $retna .= 'full Image: <b>' .$fullImageArray[0]. "</b><br> \n";
        //$retna .= "<img src=\"$fullImageArray[0]\"> \n";
        //echo "<h2>".$fullImageArray[0]."</h2>";
        //$fc = @fopen($fullImageArray[0], "r");
        if (@GetImageSize($fullImageArray[0]))
        {
            echo "image found";
        }
        else
        {
            echo "<h2>image not found..need to skip the product(".$ebayItemCode.") with image ".$fullImageArray[0]."</h2>";
            continue;
        }
       

        if($size==0)
        {
            $retna .= "size: <b>COULD NOT</b><br> \n";
            $size="";
        }
        else

            $retna .= 'size: <b>' .$size. "</b><br> \n";
        $retna .= 'qty: <b>' .$qty. "</b><br> \n";

        $retna .= "</td> \n";

        $retna .= "</tr> \n";

        $message='';
        //3. Check some entries like the price should be numeric etc
        if(empty($productNum)||empty($productName)||empty($category)||empty($price))
        {
            $message= $message. "The necessary fields are empty \n";
            echo "ERROR:You have not filled the necessary fields \n";;
        }
        else if(!is_numeric($price))
        {
            $message=$message."The price value is not numeric\n";
            echo "ERROR:You have not supplied the numeric value for price\n";
        }
        else if(!is_numeric($shippingRate))
        {
            $message=$message."The Shipping value is not numeric\n";
            echo "ERROR:You have not supplied the numeric value for Shipping Cost \n";
        }

        //4. The fullimage if not given is the image that is the part of the description. Assuming this for now
        $fullImage=$fullImageArray[0];

        //5. Before inserting check if the product ID exixts
        // Check to see if the product ID exists
        if($showDebug)
            echo "inserting for".$productNum."having ebay id".$ebayItemCode."<br/>";
        $query="SELECT product_id FROM jos_vm_product WHERE product_sku = '".$productNum."'";
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        if(strlen($row[0])>0)
        {
            $isProductNumExists=true;
            if($showDebug)
                $message= $message. "Product already available";
            echo "ERROR: Product#".$productNum." already available <br>";
            // it should redirect to the new entry form..I think just make the below else cover the entire logic
        }
        else // this is a new product
        {
            $isProductNumExists=false;

            if($showDebug)
                $message= $message. "Product status=".$isProductNumExists."\n";

            // Check to see if the vendor ID exists
            $query="SELECT IF (COUNT(vendor_id) = 0, 1, vendor_id) AS vendor_id FROM jos_vm_product WHERE product_sku = '".$productNum."'";
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            if(strlen($row[0])==0)
            {
                $isVendorIdExists=false;
                if($showDebug)
                    $message= $message. "Vendor Id not available \n";
                // it should redirect to the new entry form
            }
            else
            {
                $isVendorIdExists=true;
            }
            if($showDebug)
                $message= $message. "Vendor status=".$isVendorIdExists."\n";

            //Get default shopper group
            $query="SELECT shopper_group_id FROM jos_vm_shopper_group WHERE `default`='1' and vendor_id='1'";
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            $defaultShopperGrp=$row[0];
            if($showDebug)
                $message= $message. "Default Shopper Group=".$defaultShopperGrp."\n";


            // Get default manufacturer
            $query="SELECT manufacturer_id FROM jos_vm_manufacturer LIMIT 1";
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            $defaultManuFacturer=$row[0];
            if($showDebug)
                $message= $message. "Default Manufacturer =".$defaultManuFacturer."\n";


            //6a insert into jos_vm_product you need to enter some default values for the vendor
            $query="INSERT INTO `jos_vm_product` ( `vendor_id`,`product_sku`,`product_s_desc`,`product_desc`,
            `product_thumb_image`,`product_full_image`,`product_publish`,`product_weight`,`product_weight_uom`,
            `product_availability`,`cdate`,`mdate`,`product_name`,`product_tax_id`,`product_unit`,`custom_attribute` ) VALUES
            ( '1','".$productNum."','".$qty."','".$size."','".$fullImage."','".$fullImage."','Y',
            '0','pounds','48h.gif','1276745014','1276745014','".htmlspecialchars($productName)."','3','".$qty."','' )";
            echo $query."<br/>";
            $result = mysql_query($query);
            //echo "result of insert".$result;
            if(!$result)
            {
                echo mysql_error();
                return;
            }
            else
            {
            if($showDebug)
                $message= $message. "inserted into product table"."\n";
            }

            //also insert the extra details like the product link,ebayitemcode in a seperte tabke..other ebay specific
            //things should also go in this
            $query="INSERT INTO `ebayspecificdetails` ( `productNum`,`product_url`,`ebayItemCode` ) VALUES ('".$productNum."','".$productLink."','".$ebayItemCode."')";
            echo $query."<br/>";
            $result = mysql_query($query);
            //echo "result of insert".$result;
            if(!$result)
            {
                echo mysql_error();
                return;
            }
            else
            {
                if($showDebug)
                    $message= $message. "inserted into ebay specific product table"."\n";
            }

			 //6b get the product id .. this is the latest entry in the jos_vm_product
            $query="SELECT max( product_id )FROM jos_vm_product" ;
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            $productId=$row[0];
            if($showDebug)
                $message= $message. "The product id = ".$productId."\n";



			//add the extra images if any to the jos_vm_product_files
			if(sizeof($fullImageArray>1))
			{
				for($jj=1;$jj<sizeof($fullImageArray);$jj++)
				{

					 $query="INSERT INTO `jos_vm_product_files` (`file_product_id`,`file_name`,`file_title`,`file_description`,`file_extension`,`file_mimetype`,`file_url`,`file_published`,`file_is_image`,`file_image_thumb_height`,`file_image_thumb_width`,`file_image_height`,`file_image_width`  ) VALUES ('". $productId."','".$fullImageArray[$jj]."','".htmlspecialchars($productName)."','','jpg','image/jpeg','".$fullImageArray[$jj]."','1','1','200','200','500','500')";
					 echo $query."<br/>";
					 $result = mysql_query($query);
					//echo "result of insert".$result;
					if(!$result)
					{
						echo mysql_error();
						return;
					}
					else
					{
						if($showDebug)
							$message= $message. "inserted into extra images specific product table"."\n";
					}
				}
	
			}

           
            //6c insert Price query in jos_vm_product_price
             $query="INSERT INTO `jos_vm_product_price` ( `product_id`,`product_price`,`product_currency`,`mdate`,
             `shopper_group_id` ) VALUES ( '".$productId."','".$price."','USD','1276745014','".$defaultShopperGrp."' )";
            echo $query."<br/>";
            $result = mysql_query($query);
            if(!$result)
            {
                echo mysql_error();
            }
            else
            {
            if($showDebug)
                $message= $message. "Inserted the price \n";
            }
            //6d insert the combined shipping nformation in ShippingPreferences
            $query="INSERT INTO `ShippingPreferences` ( `ProductNum`,`IsCombinedShipping`)
            VALUES ( '".$productNum."','0')";
            echo $query."<br/>";
            $result = mysql_query($query);
            if(!$result)
            {
                echo mysql_error();
            }
            else
            {
            if($showDebug)
                $message= $message. "inserted into Shipping Preferences table"."\n";
            }
            //6e. insert the shipping rate information in jos_vm_product_qty_shipping
            $query="INSERT INTO `jos_vm_product_qty_shipping` ( `product_sku`,`base_amount`,`quantity`,`additional_charge`)
             VALUES ( '".$productNum."','".$shippingRate."','1','0')";
            echo $query."<br/>";
            $result = mysql_query($query);
            if(!$result)
            {
                echo mysql_error();
            }
            else
            {

            if($showDebug)
                $message= $message. "inserted into product qty shipping table"."\n";
            }
            //6f. Enter some default value in the jos_vm_product_mf_xref
            //Check if product manufacturer link exists
            $query="SELECT COUNT(product_id) AS total FROM jos_vm_product_mf_xref WHERE product_id ='".$productId."'";
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            if($row[0]==0)
            {
                $isManufacLinkExists=false;
                if($showDebug)
                    $message= $message. "Manufactired not available \n";
                // it should redirect to the new entry form
            }
            else
            {
                $isManufacLinkExists=true;
            }

            if($showDebug)
                $message= $message. "Manufacturer Status=".$isManufacLinkExists."\n";

            if(!$isManufacLinkExists)
            {
                // Manufacturer product link
                $query="INSERT INTO `jos_vm_product_mf_xref` ( `product_id`,`manufacturer_id` ) VALUES ( '".$productId."','1' )";
                $result = mysql_query($query);
                if($showDebug)
                    $message= $message. "Inserted the Manufacturer product link \n";
            }

			
			//get the category id for the product to be added first
		
			//$catId=getCategoryId($category,$subcategory);
            $catId=$mygannCatId;
			   //6i insert the refe in `jos_vm_product_category_xref`

			 $query="INSERT INTO `jos_vm_product_category_xref` ( `category_id`,`product_id` ) VALUES ( '".$catId."','".$productId."' )";
			echo $query;
            $result=mysql_query($query);

			  if(!$result)
            {
                echo mysql_error();
            }
            else
			{
				if($showDebug)
					$message= $message. "Inserted the  product category link\n";
			}
		

            echo "Product Added Successfully\n ";


        }

        //break;

    }
    $retna .= "</table>";

    echo $retna;
     //6h. Get the category id for the product category (I think we need to get the subcategory id)
        //6i insert the refe in `jos_vm_product_category_xref`


}



function getCategoryId($category,$subcategory)
{
	if(strtolower($subcategory)=="none")
		$query="SELECT `category_id` FROM  `jos_vm_category` WHERE  `category_name` =  '".$category."'";
	else
		$query="SELECT `category_id` FROM  `jos_vm_category` WHERE  `category_name` =  '".$subcategory."' and category_description='".$category."'";
	$result=mysql_query($query);
	$rowsnum=mysql_num_rows($result);
	$row=mysql_fetch_row($result);
	return $row[0];

}


?>









