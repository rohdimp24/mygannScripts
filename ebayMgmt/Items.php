<?php
class Item
{
    public $itemId;
    public $title;
    public $sku;
    public $lastSellingDate;
    public $daysUnSold;
    public $monthsUnsold;
    public $neverSold;


   public function __construct($itemId,$title,$sku,$lastSellingDate,$daysUnsold,$monthsUnsold,$neverSold){
       $this->itemId=$itemId;
       $this->title=$title;
       $this->sku=$sku;
       $this->lastSellingDate=$lastSellingDate;
       $this->daysUnSold=$daysUnsold;
       $this->monthsUnsold=$monthsUnsold;
       $this->neverSold=$neverSold;

      }
//
//    public function getItemId(){
//        return $this->itemId;
//    }
//
//    public function getTitle(){
//        return $this->title;
//    }
//
//    public function getQty(){
//        return $this->qty;
//    }
//
//    public function setQty($val){
//        $this->qty=$val;
//    }


}

?>