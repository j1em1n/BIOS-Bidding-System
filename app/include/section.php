<?php

class Section {
    // property declaration
    public $course;    
    public $section;
    public $day;
    public $start;
    public $end;
    public $venue;
    public $size;



	
    public function __construct($course='', $section='', $day = '', $start = '', $end = '',
                $venue='', $size = '') {
        $this->course = $course;
        $this->section = $section;
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->venue = $venue;
        $this->size = $size;



    }
}

?>