<?php
require_once('login.php');
require_once ('reportHeader.php');

if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
?>

<!DOCTYPE html>
<html>
<body>

<form action="" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload File" name="submit">
</form>

</body>
</html>

<?php


/**
 * XLS parsing uses php-excel-reader from http://code.google.com/p/php-excel-reader/
 */
	//header('Content-Type: text/plain');

if(isset($_FILES)&& isset($_POST['submit']))
{
	
	$fh = fopen("dup.txt", "w");
	//print_r($_FILES);
	$file_tmp=$_FILES['fileToUpload']['tmp_name'];
	$file_name = $_FILES['fileToUpload']['name'];
	move_uploaded_file($file_tmp,"uploads/".$file_name);
	//$Filepath="Oct 24_31.xlsx";//$_FILES["fileToUpload"]["tmp_name"];
	$Filepath="uploads/".$file_name;;
	
	
	$fp = fopen($Filepath, 'r');
	$catArray=array();
	$count=0;
	$currentProductNum='';
	$currentProductName='';
	while(($line = fgets($fp))!=null)
	{
		$count++;
		//echo $line."<br/>";
		$row=explode(",",$line);
		//print_r($row);
		

		//$array = split(',', $theData);
			
		//echo $array[0];
		//echo "<br/>";
	
		
		//echo $key.': ';
		if ($row)
		{
			//echo "size".sizeof($row)."   ";
			//if(isset($row[2]) && !isset($row[15]))
			$checkProductPos=strpos($row[2],"Total");
			if($checkProductPos===FALSE)
			{
				$len=strlen($row[2]);
				if($len>0)
				{
					echo "product found".$row[2]."<br/>";
					$pos=strpos($row[2],"(");

					$productNum=substr($row[2],0,$pos-1);
					$productNum=str_ireplace("\"","",$productNum);
					$productName=trim(substr($row[2],$pos+1,$len-$pos-3));
					$productName=str_ireplace("\"\"","\"",$productName);
					echo $productName."<br/>";
					$currentProductNum=$productNum;
					$currentProductName=$productName;
										
				}
				else //this is the row where the details of the product are
				{
					if($row[5]!=null && $row[5]=="Invoice" ){
						$customerName=$row[13];
						//echo $customerName;
						$sellingPrice=$row[17];
						$qty=$row[15];
						$total=$row[19];
						$num=$row[9];
						$id=$currentProductNum."_".$num;
						$checkingProductName=str_replace("\"\"","\"",$row[11]);
						//echo $row[11].",".$checkingProductName."<br/>";
						
						$tempDate=$row[7];
						//$tempDate=str_replace('/','-',$tempDate);
						$sellingDate=date('Y-m-d', strtotime($tempDate));
						//$sellingDate=$tempDate;
						//echo $sellingDate;
						$queryInsertDetails="INSERT INTO `quickpro`(`Id`, `ItemId`, `ProductName`, `SellingDate`, `Num`, `CustomerName`, `Qty`,
									`SellingPrice`, `Total`, `testProductName`)
								  VALUES ('".$id."','".$currentProductNum."','".mysql_real_escape_string($currentProductName)."','".$sellingDate."',
								  '".$num."','".mysql_real_escape_string($customerName)."','".$qty."','".$sellingPrice."','".$total."','".mysql_real_escape_string($checkingProductName)."')";
						
						echo $queryInsertDetails."<br/>";
						$resultInsertDetails=mysql_query($queryInsertDetails);
						 if(!$resultInsertDetails) {
							echo "errror found" . mysql_error();

							fprintf($fh,$queryInsertDetails);
							fprintf($fh,"\n");

						}
						
					}
				}
						
			//need to insert in the data base or update for the existsing product
				
			}
			//end when tyou meet this line
			$pos2=strpos($row[1],"Total Inventory");
			if($pos2!==FALSE)
				break;
	
		}
			
	
	}
	fclose($fp);
	fclose($fh);
}
?>
