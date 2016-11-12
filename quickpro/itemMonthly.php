<?php
class ItemMonthly
{
    public $itemId;
    public $sku;
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
    public $total;



    public function __construct($sku,$itemId,$title,
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
                                $total){
        $this->itemId=$itemId;
        $this->title=$title;
        $this->sku=$sku;

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
        $this->total=$total;

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

    public function setTotal($val){
        $this->total=$val;
    }

    public function setTitle($val){
        $this->title=$val;
        //echo "sdsds";
    }

    public function setSku($val){
        $this->sku=$val;
    }

    public function display(){

        $line='';
        $line.='<b>'.$this->sku." ( ".$this->title. " )".'</b>';
         $line.='<table border="1"><tr><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th>
                      <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Total</th></tr>';
        $line.='<tr>';
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
        $line.='<td>'.$this->total."</td>";
        $line.='</tr>';
          $line.="</table>";
        echo $line."<br/>";

    }

}
