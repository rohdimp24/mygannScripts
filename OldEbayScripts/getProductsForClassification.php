<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 2/14/15
 * Time: 7:22 PM
 */


include("LIB_http.php");
#include parse library
include("LIB_parse.php");

include("loginDetails.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

set_time_limit(0);
$fp=fopen("productClassification.txt",'w');

for($j=1;$j<97;$j++)
{

    $query="SELECT  `product_sku`,`product_name`,`category_id` FROM  `jos_vm_product_category_xref` , `jos_vm_product`
    WHERE jos_vm_product_category_xref.`product_id` = jos_vm_product.`product_id` and `category_id`='".$j."'";
    echo $query."<br/>";
    $result=mysql_query($query);
    $rowsnum=mysql_num_rows($result);
    if($rowsnum>20)
        $rowsnum=20;
    for($i=0;$i<$rowsnum;$i++)
    {
        $row=mysql_fetch_row($result);
        $string=$row[0]."=>".$row[1]."=>".$row[2]."\n";

        if($row[2]=='97')
            continue;
        echo $string."<br/>";
        fwrite($fp,$string);

    }
}
fclose($fp);
