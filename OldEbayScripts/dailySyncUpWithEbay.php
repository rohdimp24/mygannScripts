<?php

/********************************************************************************************************************
 * This script is used to fetch the number of products from the Ebay site. This will help in finding out
 * how many pagination calls need to be made
 ********************************************************************************************************************/

// Thsi script will find all the categories from the ebay

// Turn on all errors, warnings and notices for easier PHP debugging
//error_reporting(E_ALL);
# Include http library
include("LIB_http.php");
#include parse library
include("LIB_parse.php");
require_once("HelperAddCSVToDatabaseForAllEbayCategory.php");
require_once("HelperAddProductsFinalyToDB.php");

set_time_limit(0);

// Define global variables
$m_endpoint = "http://svcs.ebay.com/services/search/FindingService/v1?";  // shpping URL to call
$cellColor = "bgcolor=\"#dfefff\"";  // Light blue background used for selected items
$appid = 'rohit8cdf-21d7-48e8-965f-8582146cc49';
$responseEncoding = 'XML';  // Type of response we want back
$urlForCSV='';
$StoreName="Seashells-Plus-More";

//echo $StoreName;


function getTotalProductsFromStore()
{
    $action="http://stores.ebay.com/seashellsplusmore";
    $method="GET";                                    // GET method
    $ref = "";                                        // Referer variable
    $response="";
    $data_array="";
    $response = http($target=$action, $ref, $method, $data_array, EXCL_HEAD);
    //echo $response;
    // etract the content
    $after_removing_extra_html = return_between($response['FILE'], "Search Store", "</center>", EXCL);

    //get the images
    $product_total = return_between($after_removing_extra_html, "countClass", "</span>",EXCL);
    $total=substr($product_total,2);
    $intTotal= intval(str_replace(",","",$total));
    echo $intTotal;

    $numberOfPages=(int)($intTotal/100)+1;
    echo "pages".$numberOfPages;
    return $numberOfPages;

} // End of getMostWatchedItemsResults function

function fetchProductsPerPageFromEbayStore($numberOfPages)
{

    for($i=1;$i<=$numberOfPages;$i++)
    {
        //$fileName="ProductFilesXML//ProductList_Page_".$i.".xml";
        $fileName="ProductList_Todays_Page_".$i.".xml";
        $fp=fopen($fileName,'w');
        $resp=getXMLFromEBay($i);
        //echo $resp;
        echo"Writing the page#".$i."<br/>";
        fwrite($fp,$resp);
        fclose($fp);
    }

}


function getXMLFromEBay($pageNumber)
{

    global $m_endpoint;
    global $responseEncoding;
    global $appid;
    global $StoreName;
    /// add some global variables
    $apicalla  = "$m_endpoint";
    $apicalla .= "OPERATION-NAME=findItemsIneBayStores"; // name of the API we are interested in
    $apicalla .= "&SERVICE-VERSION=1.8.0";
    $apicalla .= "&SECURITY-APPNAME=$appid"; // the app id
    $apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
    $apicalla .= "&storeName=$StoreName";
    $apicalla .= "&paginationInput.pageNumber=$pageNumber"; // The page to view
    $apicalla .= "&paginationInput.entriesPerPage=100";  // name of the category
    $apicalla .= "&sortOrder=StartTimeNewest";  // name of the category
    $shopName= $StoreName;
    echo $apicalla;
    // finally send this call to the server and the response will be collected in the $resp
    //$resp = simplexml_load_file($apicalla);
    $resp=file_get_contents($apicalla);
    return $resp;
}

/**
 * now classify the products to the mygann categories..may be for now create a single file that contains all the data..
 * this function might not be required if the mygann categories will be same as that of ebay
 *
 **/
function classifyProductsFromEbayToMygannCategories_old()
{
    $fh=fopen("EbayMygannMapping.txt",'r');
    $outputFile= "FinalOutputProducts.csv";
    $fp=fopen($outputFile,'w');

    echo $outputFile;

    while(!feof($fh))
    {
        $tempStr=fgets($fh);
        $line=trim($tempStr);
        //echo $line;
        list($ebayCategory,$mygannCat,$mygannSubCat,$autoCorrect)=explode("#",$line);
        echo "NOW CREATING MAPPING FOR ".$ebayCategory."<br/>";
        ReadDataForClassification($ebayCategory,$fp);
    }

    fclose($fp);

}



function classifyProductsFromEbayToMygannCategories($numProductPages)
{
    //instead of saying FinalOutputProduct
    $outputFile= "classifiedProducts.csv";
    $fp=fopen($outputFile,'w');

    for($i=0;$i<$numProductPages;$i++)
    {
        //$filename="ProductFilesXML//ProductList_Page_".$i.".xml";
        $filename="ProductList_Todays_Page_".$i.".xml";
        $resp=simplexml_load_file($filename);
        // print_r($resp->searchResult);

        // Set return value for the function to null
        $retna = '';
        // Verify whether call was successful
        $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";


        // For each item node, build a table cell and append it to $retna
        foreach($resp->searchResult->item as $item)
        {

            $productSku=split_string($item->title, '#', AFTER, EXCL);
            $productName=split_string($item->title, '#', BEFORE, EXCL);
            //dont consider the product if the # is not there which means we will have a 0 length name
            if(strlen($productName)<1)
                continue;

            //  $count++;
            $retna.= "<tr> \n";
            $retna .= "<td> \n";
            //$retna .= "<img src=\"$item->galleryURL\"> \n";
            $retna .= "<p><a href=\"" . $item->viewItemURL . "\">" . $item->title . "</a></p>\n";
            $retna .= 'ProductName: <b>'.$item->title."</b><br>\n";

            $retna .= 'ProductSKU: <b>' . split_string($item->title, '#', AFTER, EXCL) . "</b><br> \n";
            $retna .= 'ProductName: <b>' . split_string($item->title, '#', BEFORE, EXCL) . "</b><br> \n";

            $retna .= 'ItemID: <b>' . $item->itemId . "</b><br> \n";
            $retna .= 'Current price: <b>$' .$item->sellingStatus->currentPrice . "</b><br> \n";
            $retna .= 'Date of Listing: <b>' .$item->listingInfo->startTime . "</b><br> \n";
            $retna .= 'Ebay Category: <b>' .$item->primaryCategory->categoryName . "</b><br> \n";
            $retna .= 'Ebay CategoryId: <b>' .$item->primaryCategory->categoryId . "</b><br> \n";
            $mygannCombinedCategory=getNewSubCategory($item->title);
            list($mygannCategory,$mygannSubCategory,$mygannCategoryId)=explode(":",$mygannCombinedCategory);
            $retna .= 'combined: <b>' .$mygannCombinedCategory . "</b><br> \n";
            $retna .= 'Mygann Catgeory: <b>' .$mygannCategory. "</b><br> \n";
            $retna .= 'Mygann Sub Catgeory: <b>' .$mygannSubCategory. "</b><br> \n";
            $retna .= 'Mygann CategoryId: <b>' .$mygannCategoryId. "</b><br> \n";



            //$retna.='Mygann Category:<b>'.$mygannCategory."</b><br/>\n";
            $temparray= array();
            $strRemovecommaTitle=str_replace(",","+",$item->title);
            array_push($temparray,$strRemovecommaTitle);
            //productNumber
            array_push($temparray,split_string($strRemovecommaTitle, '#', AFTER, EXCL));
            //productTitle
            array_push($temparray,split_string($strRemovecommaTitle, '#', BEFORE, EXCL));
            //productLink
            array_push($temparray,$item->viewItemURL);
            //price
            array_push($temparray,$item->sellingStatus->currentPrice);
            //ebay category
            array_push($temparray,str_replace(",","+",$item->primaryCategory->categoryName));
            //mygann category
            array_push($temparray,str_replace(",","+",$mygannCategory));
            //subcategory
            //$subCategory=$mygannSubCategory;
            array_push($temparray,str_replace(",","+",$mygannSubCategory));

            //categoryId
            array_push($temparray,$mygannCategoryId);

            //thumbnailImage
            array_push($temparray,$item->galleryURL);
            //combinedShipping
            array_push($temparray,$combinedShipping);
            //ShippingRate
            array_push($temparray,$shippingRate);
            //ebayItemCode
            array_push($temparray,$item->itemId);


            fwrite($fp,convertArrayToCSVstring($temparray));
            $retna .= "</td> \n";
            $retna .= "</tr> \n";


        }
        $retna .= "</table> \n<!-- finish table in getMostWatchedItemsResults --> \n";

        echo $retna;

    }

    fclose($fp);
}



function getNewSubCategory($productName)
{
    //adage stone

    if(stripos($productName,"Agate stone")!==false)
    {
        if(stripos($productName,"arrowhead")!==false)
        {
            return "AGATE STONE:Arrowhead:2";
        }
        else if(stripos($productName,"plate")!==false)
        {
            return "AGATE STONE:Stone Plates:7";
        }
        else if(stripos($productName,"tumbled")!==false)
        {
            return "AGATE STONE:Tumbled Stones:8";
        }
    }
    else if(stripos($productName,"Egg")!==false)
    {
        if(stripos($productName,"stand")!==false)
        {
            return "AGATE STONE:Egg Stands:3";
        }
        else
        {
            return "AGATE STONE:Eggs:4";
        }
    }
    else if(stripos($productName,"rock")!==false)
    {
        if(stripos($productName,"chips")!==false)
        {
            return "AGATE STONE:Stone Chips:5";
        }
        else if(stripos($productName,"pebbles")!==false)
        {
            return "AGATE STONE:Stone Pebbles:6";
        }
    }

    //this has to be before the bead other wise it will get categrorised as bead
    else if(((stripos($productName,"cord")!==false)&&(stripos($productName,"beading")!==false))||
        ((stripos($productName,"beading")!==false)&&(stripos($productName,"wire")!==false)))
    {
        return "JEWELERY MAKING:Beading Wire/Cord:58";
    }

    //Inlay Material
    else if((stripos($productName,"nut")!==false)&&(stripos($productName,"bone")!==false))
    {
        return "INLAY MATERIALS:Bone saddle/Nut:54";
    }

    else if((stripos($productName,"nut")!==false)&&(stripos($productName,"horn")!==false))
    {
        return "INLAY MATERIALS:Horn saddle/Nut:55";
    }

    else if((stripos($productName,"saddle")!==false)&&(stripos($productName,"horn")!==false))
    {
        return "INLAY MATERIALS:Horn saddle/Nut:55";
    }

    else if((stripos($productName,"saddle")!==false)&&(stripos($productName,"bone")!==false))
    {
        return "INLAY MATERIALS:Bone saddle/Nut:54";
    }




    //beads
    //make sure that the beads are not pendant

    else if(((stripos($productName,"bead")!==false)|| (stripos($productName,"beading bead")!==false))&&
        (stripos($productName,"pendant")==false))
    {
        if((stripos($productName,"disc")!==false)||(stripos($productName,"coin")!==false))
        {
            return "INLAY MATERIALS:Abalone Dots & Disc:53";
        }
        if(stripos($productName,"abalone")!==false)
        {
            return "BEADS:Abalone Beads:10";
        }
        else if(stripos($productName,"bone")!==false)
        {
            return "BEADS:Bone Beads:11";
        }
        else if(stripos($productName,"Gem Stone")!==false)
        {
            return "BEADS:Gemstone Beads:12";
        }
        else if(stripos($productName,"glass")!==false)
        {
            return "BEADS:Glass Beads:13";
        }
        else if(stripos($productName,"horn")!==false)
        {
            return "BEADS:Horn Beads:14";
        }
        else if((stripos($productName,"silver plate")!==false)||(stripos($productName,"gold plate")!==false)||(stripos($productName,"metal")!==false))
        {
            return "BEADS:Metal Beads:15";
        }
        else if((stripos($productName,"zinc alloy")!==false)&&(stripos($productName,"charm")!==false))
        {
            return "BEADS:Pewter Charms:17";
        }
        else if(stripos($productName,"resin")!==false)
        {
            return "BEADS:Resin Beads:18";
        }
        else if((stripos($productName,"shell")!==false)&& (stripos($productName,"charm")!==false))
        {
            return "BEADS:Shell Charms:20";
        }
        else if(stripos($productName,"shell")!==false)
        {
            return "BEADS:Shell Beads:19";
        }
        else if(stripos($productName,"wood")!==false)
        {
            return "BEADS:Wood Beads:21";
        }

        else
        {
            return "BEADS:Others:16";
        }

    }

    //bracelets
    else if(stripos($productName,"bracelet")!==false)
    {
        return "BRACELETS:None:23";
    }

    //buffalo horn
    else if(stripos($productName,"buffalo")!==false)
    {
        if(stripos($productName,"Bowl")!==false)
        {
            return "BUFFALO HORNS:Bowl:25";
        }
        else if(stripos($productName,"Dagger")!==false)
        {
            return "BUFFALO HORNS:Daggers:26";
        }
        else if(stripos($productName,"Cup")!==false)
        {
            return "BUFFALO HORNS:Drinking Cup:27";
        }
        else if(stripos($productName,"Plate")!==false)
        {
            return "BUFFALO HORNS:Plate:29";
        }
        else if(stripos($productName,"Polished")!==false)
        {
            return "BUFFALO HORNS:Polished:30";
        }
        else if(stripos($productName,"Slices")!==false)
        {
            return "BUFFALO HORNS:Slices/Disc:31";
        }
        else if((stripos($productName,"Rough")!==false)||(stripos($productName,"unfinished")!==false))
        {
            return "BUFFALO HORNS:Unfinished/Ro​ugh:32";
        }
        else if(stripos($productName,"roll")!==false)
        {
            return "KNIFE CRAFT:Horn Roll:65";
        }

        else
        {
            return "BUFFALO HORNS:Others:28";
        }

    }


    //buttons
    else if(stripos($productName,"button")!==false)
    {
        if(stripos($productName,"Abalone")!==false)
        {
            return "BUTTONS:Abalone Buttons:34";
        }
        else if(stripos($productName,"Bone")!==false)
        {
            return "BUTTONS:Bone Buttons:35";
        }
        else if(stripos($productName,"Horn")!==false)
        {
            return "BUTTONS:Horn Buttons:36";
        }
        else if(stripos($productName,"Mother of pearl")!==false)
        {
            return "BUTTONS:Mother of Pearl Buttons:37";
        }
        else if(stripos($productName,"Resin")!==false)
        {
            return "BUTTONS:Resin Buttons:38";
        }
        else if(stripos($productName,"Shell")!==false)
        {
            return "BUTTONS:Shell Buttons:39";
        }
        else if(stripos($productName,"Wood")!==false)
        {
            return "BUTTONS:Wood Buttons:40";
        }

    }

    else if(stripos($productName, "Wood carved tripod")!==false)
    {
        return "CALIFORNIA SAGE BUNDLES:Abalone Kits:42";
    }

    else if(stripos($productName, "palo santo")!==false)
    {
        return "CALIFORNIA SAGE BUNDLES:Palo Santo Woods:43";
    }

    else if(stripos($productName, "white sage")!==false)
    {
        return "CALIFORNIA SAGE BUNDLES:White Sages:44";
    }

    else if(stripos($productName, "jute twine")!==false)
    {
        return "CRAFTS:Jute Twine:46";
    }

    else if(stripos($productName, "Earring")!==false)
    {
        return "EARRINGS:None:47";
    }

    else if(stripos($productName, "bottle")!==false)
    {
        return "GLASS BOTTLES:None:48";
    }

    else if(stripos($productName, "float")!==false)
    {
        return "GLASS FLOATS:None:49";
    }

    else if(stripos($productName, "hair stick")!==false)
    {
        return "HAIR ACCESSORIES:None:50";
    }
    else if(stripos($productName, "bell")!==false)
    {
        return "HANDMADE METAL BELLS:None:51";
    }



    else if((stripos($productName,"blank")!==false) &&
        ((stripos($productName,"shell")!==false)||(stripos($productName,"inlay")!==false)) )
    {
        return "INLAY MATERIALS:Shell Blanks:56";
    }

    //jwelery making

    else if(stripos($productName, "brass metal cap")!==false)
    {
        return "JEWELERY MAKING:Brass Caps:59";
    }

    else if(stripos($productName, "chain")!==false)
    {
        return "JEWELERY MAKING:Cords/Chains:60";
    }

    else if(stripos($productName, "plastic storage box")!==false)
    {
        return "JEWELERY MAKING:Plastic Storage Boxes:62";
    }


    else if((stripos($productName,"purse")!==false)||(stripos($productName,"bag")!==false))
    {
        return "PURSES/BAGS:None:80";
    }


    //now you can test for box
    else if(stripos($productName, "box")!==false)
    {
        return "BOXES:None:22";
    }

    //knife craft
    else if((stripos($productName, "blade")!==false) && (stripos($productName, "plate")!==false) )
    {
        return "KNIFE CRAFT:Horn Plate:64";
    }

    else if((stripos($productName,"knife")!==false)&&(stripos($productName,"holder")!==false))
    {
        return "KNIFE CRAFT:Knife Holders:66";
    }

    //Necklace
    else if(stripos($productName, "necklace")!==false)
    {
        if(stripos($productName,"Abalone")!==false)
        {
            return "NECKLACES:Abalone:69";
        }
        else if(stripos($productName,"Shell")!==false)
        {
            return "NECKLACES:Shell:71";
        }
        else
        {
            return "NECKLACES:Others:70";
        }
    }


    //pendants
    else if(stripos($productName, "pendant")!==false)
    {
        if(stripos($productName,"Abalone")!==false)
        {
            return "PENDANTS:Abalone Pendants:73";
        }
        else if(stripos($productName,"Bone")!==false)
        {
            return "PENDANTS:Bone Pendants:74";
        }
        else if((stripos($productName,"gem")!==false)||(stripos($productName,"silver")!==false)||(stripos($productName,"gold")!==false))
        {
            return "PENDANTS:Gemstone Pendants:75";
        }
        else if(stripos($productName,"Horn")!==false)
        {
            return "PENDANTS:Horn Pendants:76";
        }
        else if(stripos($productName,"Shell")!==false)
        {
            return "PENDANTS:Shell Pendants:78";
        }
        else if(stripos($productName,"Tibet")!==false)
        {
            return "PENDANTS:Tibet Pendants:79";
        }
        else
        {
            return "PENDANTS:Others:77";
        }
    }


    else if(stripos($productName, "ring")!==false)
    {
        return "RINGS:None:81";
    }
    else if(stripos($productName, "shark")!==false)
    {
        return "SHARK TEETH AND JAWS:None:96";
    }

    //sea shells
    else if(stripos($productName, "sea shell")!==false)
    {
        if(stripos($productName,"Abalone")!==false)
        {
            return "SEA SHELLS:Abalone Shells:83";
        }
        else if(stripos($productName,"cowrie")!==false)
        {
            return "SEA SHELLS:Cowries:84";
        }
        else if(stripos($productName,"craft")!==false)
        {
            return "SEA SHELLS:Craft Shells:85";
        }
        else if(stripos($productName,"night")!==false)
        {
            return "SEA SHELLS:Nightlight/Lamp:89";
        }
        else if(stripos($productName,"dollar")!==false)
        {
            return "SEA SHELLS:Sand Dollars:90";
        }
        else if(stripos($productName,"Starfish")!==false)
        {
            return "SEA SHELLS:Starfish:94";
        }
        else if(stripos($productName,"wind chime")!==false)
        {
            return "SEA SHELLS:Windchimes/Hangers:95";
        }
        else if(stripos($productName,"urchin")!==false)
        {
            return "SEA SHELLS:Sea Urchins,Corals,Barnacles:91";
        }
        else if ((stripos($productName,'- 1"')!==false)||(stripos($productName,' 1"')!==false)||(stripos($productName,' 1 1/2"')!==false)||
            (stripos($productName,' 2"')!==false)||
            (stripos($productName,' 2 1/2"')!==false)||(stripos($productName,'- 3"')!==false))
        {
            return "SEA SHELLS:Small Shells:93";
        }
        else if ((stripos($productName,' 3 1/2"')!==false)||(stripos($productName,'- 3 1/2"')!==false)||
            (stripos($productName,'- 4"')!==false)||(stripos($productName,' 4"')!==false)||(stripos($productName,' 4 1/2"')!==false)||
            (stripos($productName,' 5"')!==false)||(stripos($productName,' 5 1/2"')!==false)||(stripos($productName,'- 6"')!==false))
        {
            return "SEA SHELLS:Medium Shells:88";
        }
        else if ((stripos($productName,' 6 1/2"')!==false)||(stripos($productName,' 7"')!==false)||
            (stripos($productName,' 7 1/2"')!==false)||(stripos($productName,' 8"')!==false)||(stripos($productName,' 8 1/2"')!==false)||
            (stripos($productName,' 9"')!==false)||(stripos($productName,' 9 1/2"')!==false)||(stripos($productName,' 10"')!==false)||
            (stripos($productName,' giant')!==false)||(stripos($productName,' big')!==false)
        )
        {
            return "SEA SHELLS:Large Shells:87";
        }
        else
        {
            return "SEA SHELLS:Shell Novelties:92";
        }
    }

    else if(stripos($productName, "Dentalium Aprinum Shell")!==false)
    {
        return "SEA SHELLS:Dentalium Aprinum Shells:86";
    }

    else if(stripos($productName,"coral")!==false)
    {
        return "SEA SHELLS:Sea Urchins,Corals,Barnacles:91";
    }
    else
    {
        return "MISCELLANEOUS:None:97";
    }


}










/*function ReadDataForClassification($MatchingCatName,$fp)
{

    $fh=fopen("EbayMygannMapping.txt",'r');
    while(!feof($fh))
    {
        $tempStr=fgets($fh);
        $line=trim($tempStr);
        //echo $line;
        list($ebayCategory,$mygannCat,$mygannSubCat,$autoCorrect)=explode("#",$line);
        if(strcmp($ebayCategory,$MatchingCatName)==0)
        {
            break;
        }
    }
    //you have got the mapping
    echo $mygannCat,$mygannSubCat,$autoCorrect;
    $stringMatchingCatName=str_replace(",","_",$MatchingCatName);
    $stringMatchingCatName=str_replace("&","_",$stringMatchingCatName);
    $stringMatchingCatName=str_replace("\\","_",$stringMatchingCatName);
    $stringMatchingCatName=str_replace("/","_",$stringMatchingCatName);


    $count=0;
    for($i=1;$i<4;$i++)
    {
        $filename="ProductList_Page_".$i.".xml";
        $resp=simplexml_load_file($filename);
        //Check to see if the response was loaded, else print an error
        if ($resp)
        {
            // Set return value for the function to null
            $retna = '';
        // Verify whether call was successful
            if ($resp->ack == "Success")
            {
                $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"> \n";
                // For each item node, build a table cell and append it to $retna
                foreach($resp->searchResult->item as $item)
                {
                    $catName=$item->primaryCategory->categoryName;
                    if(strcmp($catName,$MatchingCatName)==0)
                    {
                        $count++;
                        $retna.= "<tr> \n";
                        $retna .= "<td> \n";
                        $retna .= "<img src=\"$item->galleryURL\"> \n";
                        $retna .= "<p><a href=\"" . $item->viewItemURL . "\">" . $item->title . "</a></p>\n";
                        $retna .= 'ProductSKU: <b>' . split_string($item->title, '#', AFTER, EXCL) . "</b><br> \n";
                        $retna .= 'ProductName: <b>' . split_string($item->title, '#', BEFORE, EXCL) . "</b><br> \n";
                        $retna .= 'ItemID: <b>' . $item->itemId . "</b><br> \n";
                        $retna .= 'Current price: <b>$' .$item->sellingStatus->currentPrice . "</b><br> \n";
                        $retna .= 'Date of Listing: <b>' .$item->listingInfo->startTime . "</b><br> \n";
                        $retna .= 'Ebay Category: <b>' .$item->primaryCategory->categoryName . "</b><br> \n";
                        $retna .= 'Ebay CategoryId: <b>' .$item->primaryCategory->categoryId . "</b><br> \n";

                        $catName=$item->primaryCategory->categoryName;
                        $temparray= array();
                        $strRemovecommaTitle=str_replace(",","+",$item->title);
                        array_push($temparray,$strRemovecommaTitle);
                        //productNumber
                        array_push($temparray,split_string($strRemovecommaTitle, '#', AFTER, EXCL));
                        //productTitle
                        array_push($temparray,split_string($strRemovecommaTitle, '#', BEFORE, EXCL));
                        //productLink
                        array_push($temparray,$item->viewItemURL);
                        //price
                        array_push($temparray,$item->sellingStatus->currentPrice);
                        //ebay category
                        array_push($temparray,str_replace(",","+",$item->primaryCategory->categoryName));
                        //mygann category
                        array_push($temparray,str_replace(",","+",$mygannCat));
                        if($autoCorrect)
                            $subCategory=getNewSubCategory(trim(split_string($strRemovecommaTitle, '#', BEFORE, EXCL)),$mygannCat);
                        else
                            $subCategory=$mygannSubCat;
                        array_push($temparray,str_replace(",","+",$subCategory));
                        $retna .= 'Mygann Catgeory: <b>' .$mygannCat. "</b><br> \n";
                        $retna .= 'Mygann Sub Catgeory: <b>' .$subCategory. "</b><br> \n";

                        //thumbnailImage
                        array_push($temparray,$item->galleryURL);
                        //combinedShipping
                        array_push($temparray,$combinedShipping);
                        //ShippingRate
                        array_push($temparray,$shippingRate);
                        //ebayItemCode
                        array_push($temparray,$item->itemId);

                        fwrite($fp,convertArrayToCSVstring($temparray));
                        $retna .= "</td> \n";
                        $retna .= "</tr> \n";
                    }
                }
                $retna .= "</table> \n<!-- finish table in getMostWatchedItemsResults --> \n";
            }
            else
            {
                // If there were errors, print an error
                $retna = "The response contains errors<br>";

            }  // if errors

        }
        else
        {
            // If there was no response, print an error
            $retna = "Dang! Must not have got the getMostWatchedItems response!<br>";
       //     $retna .= "Call used was: $apicalla";
        }  // End if response exists

        // Return the function's value
        echo $retna;
    }
}*/


function displayProductsClassified()
{
   echo "sdkshkdhskdk";

  // echo $rr;
}



/**
 * The product ids for the new products have been sorted out
 */
function findProductIdsForNewProducts()
{
    //truncateSyncUpTable();
    getProductsFromStore();
    echo "done";
}

/**
 * Function to get teh description of the products
 */
function getDescriptionOfProducts()
{
    createProductInfoReadyForDatabase();
    echo "done";
}


/** function to add the details to the database
 **/
function addProductsToDatabase()
{

    /*$dir="finalReadyProducts";
    if (is_dir($dir)){
        if ($dh = opendir($dir)){
            while (($file = readdir($dh)) !== false){
                $info = pathinfo($file);
                if($info["extension"]=="csv")
                {
                    echo "<h1>filename:" . $file . "<br></h1>";
                    fillData($dir."//".$file);

                }
            }
            closedir($dh);
        }
    }*/
    fillData("productReadyNewToday.csv");
}


//helper function to convert to csv file
function convertArrayToCSVstring($temparray)
{
    $csvstring='';

    foreach ($temparray as $value)
    {
        if ($csvstring <> '')
        {
            $csvstring .= '^';
        }
        if (is_numeric($value))
        {
            $csvstring .= $value;
        }
        else
        {
            $csvstring .= "$value";
        }

    }
    $csvstring .= '^'."\n";
    return $csvstring;

}


//classifier
/*function getNewSubCategory($productName,$mygannCat)
{
    //if((strcmp($mygannCat,"Beads")==0)||(strcmp($ebayCategory,"Beads")==0))
    if(strcasecmp($mygannCat,"Beads")==0)
    {
        if(stripos($productName,"BONE")!==false)
        {
            //echo "Category:Bone Beads <br/>";
            return "Bone Beads";
        }
        else if(stripos($productName,"HORN")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Horn Beads";
        }
        else if(stripos($productName,"GLASS")!==false)
        {
            //echo "Category:Glass Beads <br/>";
            return "Glass Beads";
        }
        else if(stripos($productName,"WOOD")!==false)
        {
            //echo "Category:Wood Beads <br/>";
            return "Wood Beads";
        }
        else if(stripos($productName,"ABALONE")!==false)
        {
            //echo "Category:Wood Beads <br/>";
            return "Abalone Beads";
        }
        else if(stripos($productName,"LOOK")!==false)
        {
            return "Shell Beads";
        }

        else if(stripos($productName,"SHELL")!==false)
        {
            if(stripos($productName,"CHARM")!==false)
            {
                //echo "Category:Shell Charm <br/>";
                return "Shell Charm";
            }
            else
            {
                //echo "Category:Shell Bead <br/>";
                return "Shell Beads";
            }
        }
        else
            //echo "CANNOT CLASSIFY<br/>";
            return "CANNOT CLASSIFY";
    }
    if(strcasecmp($mygannCat,"Buttons")==0)
    {
        if(stripos($productName,"Wood")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Wood Buttons";
        }
        else if(stripos($productName,"Abalone")!==false)
        {
            //echo "Category:Bone Beads <br/>";
            return "Abalone Buttons";
        }
        else if(stripos($productName,"Mother of Pearl")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Mother of Pearl Buttons";
        }

        else if(stripos($productName,"Horn")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Horn Buttons";
        }
        else if(stripos($productName,"Shell")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Shell Buttons";
        }
        else
        {
            return "CANNOT CLASSIFY";
        }

    }
    if(strcasecmp($mygannCat,"Pendants")==0)
    {
        if(stripos($productName,"Abalone")!==false)
        {
            //echo "Category:Bone Beads <br/>";
            return "Abalone Pendants";
        }

        else if(stripos($productName,"Wood")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Wood Pendants";
        }
        else if(stripos($productName,"Horn")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Horn Pendants";
        }
        else if(stripos($productName,"Bone")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Bone Pendants";
        }
        else if(stripos($productName,"Shell")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Shell Pendants";
        }
        else if(stripos($productName,"Bead")!==false)
        {
            //echo "Category:Horn Beads <br/>";
            return "Bead Pendants";
        }
        else
        {
            return "CANNOT CLASSIFY";
        }

    }




}

*/




echo "<h3>Step1: find the number of pages</h3>";
//$numberOfPages=getTotalProductsFromStore();
echo "<h3>Step2: Find the products from the ebay store that are sorted by the date</h3>";
$numberOfPages=2;
fetchProductsPerPageFromEbayStore($numberOfPages);
echo "<h3>Step3: Classify the products as per the mygann categories</h3>";
classifyProductsFromEbayToMygannCategories($numberOfPages);
echo "<h3>Step4:Find out the productIds for the new products</h3>";
findProductIdsForNewProducts();
echo "<h3>Step5:Get the description details from Ebay </h3>";
createProductInfoReadyForDatabase();
echo "<h3>Step6:Add the products to the database </h3>";
addProductsToDatabase();

//displayProductsClassified();
//getProductDescription("http://www.ebay.com/itm/3-PCS-PEARL-GREEN-AND-SARMATICUS-TURBO-SHELL-HERMIT-CRAB-2-1-2-3-7061-64-70-/171652974165?pt=LH_DefaultDomain_0");
//getProductDescription("http://www.ebay.com/itm/2-PCS-NATURAL-GRAYISH-SMOOTH-WATER-BUFFALO-HORN-DRINKING-CUP-6-7-8019-/151560232529?pt=LH_DefaultDomain_0");
//getProductDescription("http://www.ebay.com/itm/DARK-HONEY-SMOOTH-WATER-BUFFALO-HORN-DRINKING-CUP-8-T-2422O-/161569450269?pt=LH_DefaultDomain_0")
//getProductDescription("http://www.ebay.com/itm/3-PCS-NATURAL-GRAYISH-SMOOTH-WATER-BUFFALO-HORN-DRINKING-CUP-6-7-8019-/151574368391?pt=LH_DefaultDomain_0");

?>