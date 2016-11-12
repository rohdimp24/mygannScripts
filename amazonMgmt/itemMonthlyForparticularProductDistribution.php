<?php
class ItemMonthlyForparticularProductDistribution
{
    public $itemId;
    public $title;
    public $jan;
    public $feb;
    public $mar;
    public $apr;
    public $may;
    public $jun;
    public $jul;
    public $aug;
    public $sep;
    public $oct;
    public $nov;
    public $dec;
    public $year;
    public $totalQty;
    //these have been added newly
    public $costPrice;
    public $shipping;
    public $sellingPrice;
    public $totalSale;
    public $profit;
    public $profitPercent;
    public $amazonFee;



    public function __construct($itemId,$title,
                                $jan,
                                $feb,
                                $mar,
                                $apr,
                                $may,
                                $jun,
                                $jul,
                                $aug,
                                $sep,
                                $oct,
                                $nov,
                                $dec,
                                $year,
                                $totalQty,
                                $costPrice,
                                $shipping,
                                $sellingPrice,
                                $totalSale,
                                $profit,
                                $profitPercent,
                                $amazonFee
                                ){
        $this->itemId=$itemId;
        $this->title=$title;
        $this->jan=$jan;
        $this->feb=$feb;
        $this->mar=$mar;
        $this->apr=$apr;
        $this->may=$may;
        $this->jun=$jun;
        $this->jul=$jul;
        $this->aug=$aug;
        $this->sep=$sep;
        $this->oct=$oct;
        $this->nov=$nov;
        $this->dec=$dec;
        $this->year=$year;
        $this->totalQty=$totalQty;
        $this->costPrice=$costPrice;
        $this->shipping=$shipping;
        $this->sellingPrice=$sellingPrice;
        $this->totalSale=$totalSale;
        $this->profit=$profit;
        $this->profitPercent=$profitPercent;
        $this->amazonFee=$amazonFee;

    }

    public function setJan($val){
        $this->jan=$val;
    }
    public function setFeb($val){
        $this->feb=$val;
    }
    public function setMar($val){
        $this->mar=$val;
    }
    public function setApr($val){
        $this->apr=$val;
    }
    public function setMay($val){
        $this->may=$val;
    }
    public function setJun($val){
        $this->jun=$val;
    }
    public function setJul($val){
        $this->jul=$val;
    }
    public function setAug($val){
        $this->aug=$val;
    }
    public function setSep($val){
        $this->sep=$val;
    }
    public function setOct($val){
        $this->oct=$val;
    }
    public function setNov($val){
        $this->nov=$val;
    }
    public function setDec($val){
        $this->dec=$val;
    }

   
    public function setTitle($val){
        $this->title=$val;
        //echo "sdsds";
    }

    public function setItemId($val){
        $this->itemId=$val;
    }

    public function setShipping($val){
        $this->shipping=$val;
    }

    public function setCostPrice($val){
        $this->shipping=$val;
    }
    public function setSellingPrice($val)
    {
        $this->sellingPrice=$val;
    }



    public function displayHeader(){
        $line='';
        $line.='<table border="1"><tr><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th>
                      <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>TotalQty</th>
                      <th>costPrice</th><th>shipping</th><th>sellingPrice</th><th>TotalSale</th><th>AmazonFee</th>
                      <th>profit</th><th>profit percent</th></tr>';
        return $line;              
    }

    public function displayFooter(){
        return "</table>";
    }

    public function display(){

        //$line='';
        //$line.='<b>'.$this->itemId.'</b>';
        /*$line.='<table border="1"><tr><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th>
                      <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>TotalQty</th>
                      <th>costPrice</th><th>shipping</th><th>sellingPrice</th><th>TotalSale</th><th>AmazonFee</th>
                      <th>profit</th><th>profit percent</th></tr>';
        */
        $line='<tr>';
        $line.='<td>'.$this->title."</td>";
        $line.='<td>'.$this->itemId."</td>";
        $line.='<td>'.$this->jan."</td>";
        $line.='<td>'.$this->feb."</td>";
        $line.='<td>'.$this->mar."</td>";
        $line.='<td>'.$this->apr."</td>";
        $line.='<td>'.$this->may."</td>";
        $line.='<td>'.$this->jun."</td>";
        $line.='<td>'.$this->jul."</td>";
        $line.='<td>'.$this->aug."</td>";
        $line.='<td>'.$this->sep."</td>";
        $line.='<td>'.$this->oct."</td>";
        $line.='<td>'.$this->nov."</td>";
        $line.='<td>'.$this->dec."</td>";
        $line.='<td>'.$this->totalQty."</td>";
        $line.='<td>'.number_format($this->costPrice,2,'.',',')."</td>";
        $line.='<td>'.number_format($this->shipping,2,'.',',')."</td>";
        $line.='<td>'.number_format($this->sellingPrice,2,'.',',')."</td>";
        $line.='<td>'.number_format($this->totalSale,2,'.',',')."</td>";
        $line.='<td>'.number_format($this->amazonFee,2,'.',',')."</td>";        
        $line.='<td>'.number_format($this->profit,2,'.',',')."</td>";
        $line.='<td>'.number_format($this->profitPercent, 2, '.', ',')."%</td>";
                
        $line.='</tr>';
        //$line.="</table>";
        return $line;

    }

}
