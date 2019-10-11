<?php

class Prerequisite{
    //property declaration
    private $course;
    private $prerequisite;

    public function __construct($course,$prerequisite) {
        $this->course = $course;
        $this->prerequisite = $prerequisite;
        
    }

    public function getCourse(){
        return $this->course;
    }

    public function getPrerequisite(){
        return $this->prerequisite;
    }
}
?>