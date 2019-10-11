<?php

class Bid{
    //property declaration
    private $userid;
    private $amount;
    private $code;
    private $section;
    private $status;

    public function __construct($userid, $amount, $code, $section, $status) {
        $this->userid = $userid;
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
        $this->status = $status;
        
    }

    public function getUserid(){
        return $this->userid;
    }

    public function getAmount(){
        return $this->amount;
    }

    public function getCode(){
        return $this->code;
    }

    public function getSection(){
        return $this->section;
    }

    public function getStatus(){
        return $this->status;
    }

}
?>