<?php

class User {
    // property declaration
    public $userid;
    public $password;
    public $name;
    public $school;
    public $edollar;
  
    public function __construct($userid='', $password='',  $name = '', $school = '', $edollar = '') {
        $this->username = $userid;
        $this->password = $password;
        $this->name = $name;
        $this->school = $school;
        $this->edollar = $edollar;
    
    }
    public function authenticate($enteredPwd) {
        return $this->password == $enteredPwd;
    }
}

?>