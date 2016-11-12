<?php
if(isset($_COOKIE['ID_my_site']))
{
?>
<?php

/****
* This is the script to add the categories to the dababase
* *************/

	/**
	*database access
	**/
	require_once 'loginDetails.php';
	
	$db_server = mysql_connect($db_hostname, $db_username, $db_password);
	if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

	mysql_select_db($db_database)
	or die("Unable to select database: " . mysql_error());
	
	// get the list order
	function findListOrder()
	{
		$query="SELECT  `list_order` FROM  `jos_vm_category` ORDER BY  `list_order` DESC";
		$result=mysql_query($query);
		$rowsnum=mysql_num_rows($result);
		if($rowsnum==0)
			return 1;
		else
		{
			$row=mysql_fetch_row($result);
			
			return $row[0]+1;
		}
			
	}

	function findLastCategoryId()
	{
		$query="SELECT `category_id` FROM  `jos_vm_category` ORDER BY  `category_id` DESC";
		$result=mysql_query($query);
		$rowsnum=mysql_num_rows($result);
		if($rowsnum==0)
			return 1;
		else
		{
			$row=mysql_fetch_row($result);
			
			return $row[0]+1;
		}

	}

	
	
	/**
	* categoryName is the name of the category you wnat to enter
	* parentId is the id of the parent category
	* parentcaetgoryname we will put in the category_description column. Put it NA if no parent
	**/
	function addCatgeory($categoryName,$parentId,$parentCategoryName)
	{
		$newMainCatId=findLastCategoryId();
		$listOrder=findListOrder();
		
		//echo "need to get the new catid".$newMainCatId."<br/>";
		//echo "the listorder is ".$listOrder."<br/>";
		//insert the main category cdate=1347534670, mdate=1347536933,browse_3
		$insertQuery="INSERT INTO `jos_vm_category`(`category_id`,`vendor_id`, `category_name`,`category_description`,`category_publish`, `cdate`, `mdate`, `category_browsepage`, `products_per_row`, `category_flypage`, `list_order`) VALUES ('".$newMainCatId."','1','".$categoryName."','".$parentCategoryName."','Y','1347534670','1347536933','browse_3','3','flypage.tpl','".$listOrder."')";
		echo $insertQuery."<br/>";
		$resultInsert=mysql_query($insertQuery);
		if(!$resultInsert)
			die("could not enter the category");
		
		
		//add the reference to the category refere
		$insertXrefQuery="INSERT INTO `jos_vm_category_xref`(`category_parent_id`, `category_child_id`, `category_list`) VALUES ('".$parentId."','".$newMainCatId."','".$listOrder."')";
		//echo $insertXrefQuery."<br/>";
		
		$resultXrefInsert=mysql_query($insertXrefQuery);
		if(!$resultXrefInsert)
			die("could not enter the reference");
		
		return $newMainCatId;

	}


	//read from List of categories file

	$fr = fopen('CategoriesNew.txt', 'r');
	while(!feof($fr))
	{
		$ADD_MAIN_CAT=0;
		$ADD_SUB_CAT=0;
		
		$tempStr=fgets($fr);
		$line=trim($tempStr);
		echo $line."<br/>";
		//get the name of the main category and subcat
		list($mainCategory,$subCategory)=explode(":",$line);
		if($subCategory=="NULL")
		{
			$ADD_SUB_CAT=0;
			echo "sdhjdsdks";
		}
		else
		{
			$ADD_SUB_CAT=1;
		}

		//check if the main category is already there in the database
		$query="select * from `jos_vm_category` where `category_name`='".$mainCategory."'";
		echo $query."<br/>";
		$result=mysql_query($query);
		$rowsnum=mysql_num_rows($result);
		echo "roesnum".$rowsnum."<br/>";
		if($rowsnum>0)
		{
			$row=mysql_fetch_array($result);
			$ADD_MAIN_CAT=0;
			$mainCatId=$row['category_id'];
			echo "found an existing id".$mainCatId."<br/>";
			
		}	
		else
		{
			$ADD_MAIN_CAT=1;
			
		}
		//check if the sub category is already there in the database...this will be to find the existing subcategory of the main category and 
		//then checkigng if the subcategory is already present

		if($ADD_MAIN_CAT==0 && $ADD_SUB_CAT==1 )
		{
			echo "Only subcatgeory needs to be added <br/>";
			$query="Select * from jos_vm_category where category_id in (Select category_child_id from jos_vm_category_xref,`jos_vm_category` where category_parent_id=category_id and category_id='".$mainCatId."') and category_name='".$subCategory."'";
			
			$result=mysql_query($query);
			$rowsnum=mysql_num_rows($result);
			if($rowsnum==0)
			{
				//add the subcategory here
				addCatgeory($subCategory,$mainCatId,$mainCategory);

			}
			else
			{
				echo "Duplicate entry";
			}
		}

		else if($ADD_MAIN_CAT==1 && $ADD_SUB_CAT==1 )
		{
			echo "both category and subcategory addedd <br/>";
			//we should manually add teh category id
			$parentId=addCatgeory($mainCategory,0,"NA");

			
			//now insert the subcategory using the main catgeory id
			echo addCatgeory($subCategory,$parentId,$mainCategory);



		}
		else if($ADD_MAIN_CAT==1 && $ADD_SUB_CAT==0 )
		{
			echo "just the parent<br/>";
			//jutst add the parent category
			addCatgeory($mainCategory,0,"NA");
		}
	

			//select * from jos_vm_category where category_id in (Select category_child_id from jos_vm_category_xref where category_parent_id in (SELECT category_id FROM `jos_vm_category` where `category_id`='10'))
			
			//Select * from jos_vm_category where category_id in (Select category_child_id from jos_vm_category_xref,`jos_vm_category` where category_parent_id=category_id and category_id='10')
		
		
		}


?>
<?php
}
else
{
	header("Location: ProductLogin.php");
}
?>





