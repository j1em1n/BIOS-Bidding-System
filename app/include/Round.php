<?php

class Round{
    //property declaration
    private $round_num;
    private $status;
 

    public function __construct($round_num, $status){
        $this->round_num = $round_num;
        $this->status = $status;
    }

    public function getRoundNum(){
        return $this->round_num;
    }

    public function getStatus(){
        return $this->status;
    }

}
?>