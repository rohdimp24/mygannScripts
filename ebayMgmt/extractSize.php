<?php
require_once ('login.php');
set_time_limit(0);

$query="SELECT Distinct(`Title`) FROM `EbayTransactions` where SKU!='' and CreationDate>'2015-12-26'";
//$query="SELECT Distinct(Title) FROM `EbayTransactions` Where Size is NULL";
$result=mysql_query($query);
$rowsnum=mysql_num_rows($result);
for($i=0;$i<$rowsnum;$i++)
{
	
	$row=mysql_fetch_row($result);
	$title=htmlspecialchars_decode($row[0]);
	echo $title;
	/**** find inches ***/	
	$posFirstQuote=strpos($title,"\"");
	if($posFirstQuote=='')
		$posFirstQuote="NA";
	
	echo "----".$posFirstQuote;
	
	$posHash=strrpos($title,'#');
	echo "....".$posHash;
	//if($posFirstQuote!=="NA")
	//{
	//	echo "hi";
	//}
	if($posFirstQuote!=="NA")
	{
		$stringLength=$posHash-$posFirstQuote;
		$firstCut=substr($title,$posFirstQuote-7,$stringLength+7);
		preg_match('/\d/', $firstCut, $m, PREG_OFFSET_CAPTURE);
		if(sizeof($m))
		{
			//print_r($m);
			//echo "######".$firstCut;
			$offset=$m[0][1];
			$output=substr($firstCut,$offset);
			echo "####".$output;
			updateDb($title,$output);
		}
		else
			echo "iiiiii".$firstCut;
		
	}
	else{
		//echo "<br/>";
			
		
		
		/*** find weight in pounds **/
		
		$posFirstPound=strripos($title,"POUND");
		if($posFirstPound=='')
			$posFirstPound="NA";
		
		echo "----".$posFirstPound;
		if($posFirstPound!=="NA")
		{
			//$stringLength=$posHash-$posFirstQuote;
			$firstCut=substr($title,$posFirstPound-7,7);
			preg_match('/\d/', $firstCut, $m, PREG_OFFSET_CAPTURE);
			if(sizeof($m))
			{
				//print_r($m);
				//echo "######".$firstCut;
				$offset=$m[0][1];
				$output=substr($firstCut,$offset)."POUND";
				echo "####".$output;
				updateDb($title,$output);
			}
			else
				echo "pppppp".$firstCut;
			
			//echo "#####".$firstCut;
		}
		else //check for MM
		{
			$posFirstMM=strripos($title,"MM");
			
			if($posFirstMM=='')
				$posFirstMM="NA";
			echo "----".$posFirstMM;
			
			if($posFirstMM!=="NA")
			{
				//$stringLength=$posHash-$posFirstQuote;
				$firstCut=substr($title,$posFirstMM-10,10);
				preg_match('/\d/', $firstCut, $m, PREG_OFFSET_CAPTURE);
				if(sizeof($m))
				{
					//print_r($m);
					//echo "######".$firstCut;
					$offset=$m[0][1];
					$output=substr($firstCut,$offset)."mm";
					echo "####".$output;
					updateDb($title,$output);
				}
				else
					echo "mmmmmmm".$firstCut;
				
				//echo "#####".$firstCut;
			}
			
			
			
		}
			
	}
		
	echo "<br/>";
			
}


function updateDb($title,$size)
{
	$query="UPDATE `EbayTransactions` SET `Size`='".$size."' WHERE `Title`='".$title."'";
	$result = mysql_query($query);
	if(!$result)
	{
		echo "Update failed".mysql_error();		
	}
}




?>