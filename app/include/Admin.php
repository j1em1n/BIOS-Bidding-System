<?php

class Admin{
    //property declaration
    private $userid;
    private $hashedpassword;

    public function __construct($userid, $hashedpassword){
        $this->userid = $userid;
        $this->hashedpassword = $hashedpassword;
    }

    public function getUserid(){
        return $this->userid;
    }

    public function getPassword(){
        return $this->hashedpassword;
    }

}
?>