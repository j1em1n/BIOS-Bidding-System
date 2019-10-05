<?php

class Admin{
    //property declaration
    public $userid;
    public $hashed_password;

    public function __construct($userid, $hashed_password){
        $this->userid = $userid;
        $this->hashedpassword = $hashed_password;
    }

    public function getUserid(){
        return $this->userid;
    }

    public function getPwd(){
        return $this->hashed_password;
    }

}
?>