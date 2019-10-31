<?php

class Bid{
    //property declaration
    private $userid;
    private $amount;
    private $code;
    private $section;
    private $r1status;
    private $r2status;

    public function __construct($userid, $amount, $code, $section, $r1status, $r2status) {
        $this->userid = $userid;
        $this->amount = $amount;
        $this->code = $code;
        $this->section = $section;
        $this->r1status = $r1status;
        $this->r2status = $r2status;
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

    public function getR1Status(){
        return $this->r1status;
    }
    
    public function getR2Status(){
        return $this->r2status;
    }
}
?>