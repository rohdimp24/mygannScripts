<!DOCTYPE html>
<html>


<?php

require_once ('login.php');

require_once ('helperFunctions.php');

set_time_limit(0);
?>
<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 7/13/15
 * Time: 10:27 PM
 */


//read the csv file
$fs=fopen("products_for_sp_update.txt","r");
$details="";
while(($strfp = fgets($fs))!=null){
  $details=explode("=>",$strfp);
  echo "<b>".$details[0]."---".$details[1]."</b><br/>";
  #$checkSql="Select Title,SellingPrice,SKU,CreationDate from ebaytransactions where title='".trim($details[0])."' and #creationDate>'2016-01-01' order by creationDate Desc";
  #$resultSql=mysql_query($checkSql);
  #$rowsnum=mysql_num_rows($resultSql);
  #for($i=0;$i<$rowsnum;$i++)
  #{
  #  $row=mysql_fetch_row($resultSql);
  #  //if($row[1]>0)
  #       echo $row[0]."=>".$row[1]."=>".$row[2]."=>".$row[3]."<br/>";
  #}

  $updateSql="UPDATE `EbayTransactions` SET `SellingPrice`='".$details[1]."' WHERE Title='".$details[0]."' and SellingPrice='0.0' and CreationDate>'2016-01-01'";
  $resultUpdateSql=mysql_query($updateSql);
  if(!$resultUpdateSql)
	echo $updateSql."---->".mysql_error()."<br/>";
  echo $updateSql."<br/>";
  
  echo "=======================================================<br/>";

}






/*

$fs=fopen("zero_sp_products.txt","r");
$details="";
while(($strfp = fgets($fs))!=null){
  $details=explode("=>",$strfp);
  echo "<b>".$details[0]."---".$details[1]."</b><br/>";
  $checkSql="Select Title,SellingPrice,SKU,CreationDate from ebaytransactions where title='".trim($details[0])."' and CreationDate>'2016-01-01' order by CreationDate Desc";
  $resultSql=mysql_query($checkSql);
  $rowsnum=mysql_num_rows($resultSql);
  for($i=0;$i<$rowsnum;$i++)
  {
    $row=mysql_fetch_row($resultSql);
    echo $row[0]."=>".$row[1]."=>".$row[2]."=>".$row[3]."<br/>";
  }

  echo "=======================================================<br/>";

}

*/








//check that the sp is always zero

//insert the sp to the new value as given by ali




?>