<?php
require_once ('login.php');
require_once ('reportHeader.php');
require_once('helperFunctions.php');
#Title like '%&quot;%'";
$query="Select TransactionId,Title from EbayTransactions where TransactionId='1354177936005'";

$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);

for($i=0;$i<$rowsnum;$i++)
{
	
	$row=mysql_fetch_row($result);
	$transactionId=$row[0];
	$title=$row[1];
	
	
	//need to update the title
	$updateQuery="UPDATE `EbayTransactions` SET `Title`='".mysql_real_escape_string($title)."' WHERE TransactionId='".$transactionId."'";
	echo $updateQuery."<br/>";
	$updateResult=mysql_query($updateQuery);
	if(!$updateResult)
	{	
		
		echo mysql_error()."<br/>";
	}
	
	
}




?>

