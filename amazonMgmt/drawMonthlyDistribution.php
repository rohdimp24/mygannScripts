<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title></title>
</head>


<!--
Add details of the mysql database

We can fetch the data only from 120 days onwards. Earlier data shall be possible to manually enter by excel download or something like that

-->
<?php
if(!isset($_COOKIE['ID_my_site']))
{
	header("Location: http://mygann.com/EbayScripts/ProductLogin.php");
}
//require_once ('login.php');
include("Chart/class/pData.class.php");
include("Chart/class/pDraw.class.php");
include("Chart/class/pImage.class.php");
require_once ('reportHeader.php');
$db_hostname='98.130.0.118';
$db_database='dvirji_mygann';
$db_username='dvirji_mygann';
$db_password='Murtaza1';


set_time_limit(0);



//echo $currentYear."=>".$currentMonth;
$arrMonth=array();
$arrMonth['01']="January:31";
$arrMonth['02']="Feburary:28";
$arrMonth['03']="March:31";
$arrMonth['04']="April:30";
$arrMonth['05']="May:31";
$arrMonth['06']="June:30";
$arrMonth['07']="July:31";
$arrMonth['08']="August:31";
$arrMonth['09']="September:30";
$arrMonth['10']="October:31";
$arrMonth['11']="November:30";
$arrMonth['12']="December:31";



$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

mysql_select_db($db_database)
or die("Unable to select database: " . mysql_error());

//get the max date from the database
$queryMaxMinDate="SELECT max(SellingDate), min(SellingDate) FROM `AmazonTransactions`";
$resultMaxMinDate=mysql_query($queryMaxMinDate);
$rowsMaxMin = mysql_fetch_row($resultMaxMinDate);
$maxDate=$rowsMaxMin[0];
$minDate=$rowsMaxMin[1];
$maxYear=date('Y', strtotime($maxDate));
$minYear=date('Y',strtotime($minDate));
if($minYear<2015)
	$minYear=2015;
mysql_close($db_server);

?>
<br/>
<br/>
<br>
<h1 style="margin-left:20px;">Select the year for generating the Monthly Distribution Chart</h1>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="GET">
    <table style="margin-left:20px;">
	  <tr>
			<?php
				for($i=$minYear;$i<=$maxYear;$i++)
				{
				?>
					<td><input type="checkbox" name="saleYear[]" value="<?php echo $i ?>"
							   <?php
								if(isset($_GET['saleYear'])) {
									if (in_array($i, $_GET['saleYear'])) {
										echo ' checked="checked"" ';
									}
								}
							   ?>

							><?php echo $i ?></td>
				<?php
				}

				?>
			<td><input type="submit" value="Search" id="submit" name="submit" /></td>
	  </tr>

    </table>
</form>

<?php
//$mind=date_parse_from_format("Y-m-d", $minDate);
//$startIndex=$mind;
//$minMonth=$mind["month"];

//
//if($maxMonth<10)
//    $maxMonth="0".$maxMonth;
////echo $maxMonth;
////get the month name
//$maxMonthName=explode(":",$arrMonth[$maxMonth])[0];

//if($minMonth<10)
//    $minMonth="0".$minMonth;
//echo $minMonth;

//echo $arrMonth[$minMonth];

if(isset($_GET["submit"]))
{

	function getTotalSaleForMonth($startDate,$endDate)
{

    // $startDate='2012-01-02';
    //$endDate='2012-29-02';
    //echo "hi i am in ".$month;
    //$lines="<div style='margin-left:10px'>";
    // $lines="<H2>Displaying top results for ".$month ."</H2><br/>";
    $total = 0.0;
    $lines = '';
    //$query="SELECT ItemId,Title,SUM(QTY) AS output FROM EbayTransactions Where CreationDate <='".$endDate."'and CreationDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
    //$query = "SELECT ItemId,ProductName,SUM(Qty) as output, SellingPrice, SUM(SellingPrice*Qty) as subtotal from quickpro
    //        where SellingDate>='" . $startDate . "' and SellingDate <= '" . $endDate . "' GROUP by ItemId order by output DESC";
   
   $query="SELECT ItemId,Title,SUM(Qty) AS output,SellingPrice,SUM(SellingPrice*Qty) as subtotal,Shipping,CostPrice,SUM(CostPrice*Qty) FROM AmazonTransactions Where SellingDate <='".$endDate."'and SellingDate >='".$startDate."' GROUP BY ItemId ORDER BY `output` DESC";
   
   // echo $query;
    $result = mysql_query($query);
    //print_r($result);
    //echo "<br/>";

    if ($result) {
        while ($row = mysql_fetch_array($result)) {
            // do something with the $row
            $total+=floatval($row['subtotal']);
        }
    } else {
        echo mysql_error();
    }
	//echo "total".$total;
    return $total;

}

function getImage(&$arrAllYear,&$years)
{
	//echo "from the function";
	//print_r($arrAllYear);
    /* Create and populate the pData object */
    $MyData = new pData();
	$count=0;
	foreach($years as $year){
	
		$MyData->addPoints($arrAllYear[$count++], $year);
	}
	
    //$MyData->addPoints(array(-4, VOID, VOID, 12, 8, 3), "Probe 1");
    //$MyData->addPoints(array(3, 12, 15, 8, 5, -5), "Probe 2");
    //$MyData->addPoints(array(2, 7, 5, 18, 19, 22), "Probe 3");
    //$MyData->setSerieTicks("Probe 2", 4);
    //$MyData->setSerieWeight("Probe 3", 2);
    $MyData->setAxisName(0, "Sales");
    $MyData->addPoints(array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"), "Labels");
    $MyData->setSerieDescription("Labels", "Months");
    $MyData->setAbscissa("Labels");


    /* Create the pChart object */
    $myPicture = new pImage(1400, 800, $MyData);

    /* Turn of Antialiasing */
    $myPicture->Antialias = FALSE;

    /* Add a border to the picture */
    $myPicture->drawRectangle(10, 10, 1299, 759, array("R" => 0, "G" => 0, "B" => 0));

    /* Write the chart title */
    $myPicture->setFontProperties(array("FontName" => "Chart/fonts/Forgotte.ttf", "FontSize" => 20));
    $myPicture->drawText(150, 35, "Monthly Sales Distribution", array("FontSize" => 20, "Align" => TEXT_ALIGN_BOTTOMMIDDLE));

    /* Set the default font */
    $myPicture->setFontProperties(array("FontName" => "Chart/fonts/pf_arma_five.ttf", "FontSize" => 10));

    /* Define the chart area */
    $myPicture->setGraphArea(60, 40, 1250, 750);

    /* Draw the scale */
    $scaleSettings = array("XMargin" => 10, "YMargin" => 10, "Floating" => TRUE, "GridR" => 200, "GridG" => 200, "GridB" => 200, "DrawSubTicks" => TRUE, "CycleBackground" => TRUE);
    $myPicture->drawScale($scaleSettings);

    /* Turn on Antialiasing */
    $myPicture->Antialias = TRUE;

    /* Draw the line chart */
    $myPicture->drawLineChart();
    $myPicture->drawPlotChart(array("DisplayValues" => TRUE, "PlotBorder" => TRUE, "BorderSize" => 2, "Surrounding" => -60, "BorderAlpha" => 80));
    /* Write the chart legend */
    $myPicture->drawLegend(540, 20, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));

    /* Render the picture (choose the best way) */
// $myPicture->autoOutput("simple.png");
    $fileName = "simple.png";
    /* Render the picture (choose the best way) */
    $myPicture->render($fileName);
    return $fileName;
}


//the main body
    $arrAllYear=array();
    echo "<span><h1  style='padding-left:20px;margin-top:30px;'>Monthly sales data</h1></span><br/>";
    $years=$_GET["saleYear"];
	
    foreach($years as $year){
        $db_server = mysql_connect($db_hostname, $db_username, $db_password);
        if (!$db_server) die("Unable to connect to MYSQL: " . mysql_error());

        mysql_select_db($db_database)
        or die("Unable to select database: " . mysql_error());


        //echo $year;
        $arrPerYear=array();
        for($i=1;$i<=12;$i++)
        {
            //skip the earlier months as we dont have the data before april 2015

            if($i<10)
            {
                $i="0".$i;

            }
            //echo $arrMonth[$i]."<br/>";
            $parts=explode(":",$arrMonth[$i]);
            $startDate=$year."-".$i."-01";
            $endDate=$year."-".$i."-".$parts[1];

            //echo $startDate.";".$endDate."<br/>";
            //$monthName=$parts[0]." ".$k;
            //echo displayTop100Data($startDate,$endDate,$monthName);
            $monthlyTotal=getTotalSaleForMonth($startDate,$endDate);
            //echo $monthlyTotal."<br/>";
            array_push($arrPerYear,$monthlyTotal);
        }
		//print_r($arrPerYear);
        mysql_close($db_server);
        array_push($arrAllYear,$arrPerYear);
		

    }

	//print_r($arrAllYear);
	//exit();
	$imageName=getImage($arrAllYear,$years);

	//echo $imageName;
	
	?>
	
<div>
    <div class="container">
        <div class="row" style="left-margin:-85px">
            <div class="span12" style="left-margin:-85px">
                <img src='<?php echo $imageName ?>' >
            </div>
            <div class="span12" >
              <table border='1'>
				
				<tr><th>Month</th>
				<?php 
				
				foreach($years as $year)
				{
				?>
					<th><?php echo $year ?></th>
				<?php
				}
					
				?>
				</tr>
				<?php
				
				 for($i=1;$i<=12;$i++)
				 {
					if($i<10)
					{
						$i="0".$i;
					}
					//echo $arrMonth[$i]."<br/>";
					$parts=explode(":",$arrMonth[$i]);
				 ?>
				  
				 <tr>
					<td><?php echo $parts[0] ?></td>
					<?php
					for($k=0;$k<sizeof($years);$k++)
					{
					?>
						<td><?php echo $arrAllYear[$k][$i-1]?></td>
					<?php
					}
					?>
					
				 </tr>
				 
				 <?php
				
				 }
					 
				?>
			  
			  
			  
			  </table>
			  
			  
			  
			  
			  
            </div>

        </div>

    </div>



<div>
	
<?php	
	
}

?>




