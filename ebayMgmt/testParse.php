<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/27/15
 * Time: 8:46 PM
 */

/*$str="lurelady1 Final price: $15.00, 2 of 10 items @ $7.50 (Store)";
#$str="carley6166 Final price: $5.00 (Store)";

$strParts=explode("Final price:",$str);
print_r($strParts);

if(strlen($strParts[1])<20)
{
    $finalParts=explode("(Store)",$strParts[1]);
    $sellingPrice=trim($finalParts[0]);
    $sellingPrice=substr($sellingPrice,1);
    echo $sellingPrice;
}
else
{
    //$finalParts=
    $tempFinalParts=explode("@",$strParts[1]);
    $finalParts=explode("(Store)",$tempFinalParts[1]);
    $sellingPrice=trim($finalParts[0]);
    $sellingPrice=substr($sellingPrice,1);
    echo $sellingPrice;
    //print_r($finalParts);

}*/

require_once('login.php');

$query="SELECT Title,ItemId FROM EbayTransactions Where SKU=''";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);
for($i=0;$i<$rowsnum;$i++){
    $row=mysql_fetch_row($result);
    $itemId=$row[1];
    #print_r($row);
    echo $row[0];
    $itemSKU='';
    $pos=strrpos($row[0],'#');
    if($pos>0){
        //echo substr($row[0],$pos+1)."<br/>";
        $itemSKU=substr($row[0],$pos+1);
    }

    else
    {
        echo $row[0]."=>";
        $pos2=strrpos(trim($row[0]),' ');
        if($pos2>0)
        {
            $output= substr($row[0],$pos2+1);
            //$output='6 CUPS - ASSORT POLISHED WATER BUFFALO HORN MEDIEVAL DRINKING CUP MUG 6"';
            $pos3=strpos($output,'&quot;');

            #echo "pos=>".$pos2;
            if($pos3>0)
            {
                //echo "jhjhjhj";
                echo $output."=>Cannont<br/>";

            }
            else
            {
                echo $output."<br/>";
                //basically you need to check if the string contains any digits or is it totally a string.
                if (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $output))
                {
                    echo 'Does not Contains at least one letter and one number'."<br/>";
                }
                else
                {
                    $itemSKU=$output;
                }
//                if($x)
//                    echo "TRUE";
//                else
//                    echo "FALSE";
//                echo "<br/>";
            }


        }
        else
            echo "CANNOT"."<br/>";
    }

    //update code to the mysql
    echo $itemSKU."<br/>";
    $updateQuery="UPDATE `EbayTransactions` SET `SKU`='".$itemSKU."'WHERE ItemId='".$itemId."'";
    $updateResult=mysql_query($updateQuery);
    if(!$updateResult)
        echo mysql_error();


}