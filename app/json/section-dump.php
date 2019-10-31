<?php

require_once '../include/common.php';
require_once '../include/token.php';

$errors = commonValidationsJSON(basename(__FILE__));
$success = array();

if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    // Retrieve 'r' GET parameter
    $jsonObj = json_decode($_REQUEST['r']);
    
    $coursecode = $jsonObj->course;
    $section = $jsonObj->section;

    $errors = array();

    //initialize DAO
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $courseDAO = new CourseDAO();

    // Check if course code is found in course
    if(!($courseDAO->retrieve($coursecode))) {
        $errors[] = "invalid course";
    } elseif (!($sectionDAO->retrieve($coursecode, $section))) {
        // Check if section code is found in section (only for valid course code)
        $errors[] = "invalid section";
    }

    if(empty($errors)){ 
        // Success if field validity checks are passed
        $students = array();
        //get enrolled students here
        $successfulBids = $bidDAO->getSuccessfulBids();
    
        foreach($successfulBids as $eachbid){
            $students[] = [
                "userid" => $eachbid->getUserid(),
                "amount" => floatval($eachbid->getAmount())
            ];
        }
        $success = [
            "status" => "success",
            "students" => $students
        ];
    }
    if (empty($errors) && !empty($success)) {
        $result = $success;
    } else {
        sort($errors);
        $result = jsonErrors($errors);
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION+JSON_PRETTY_PRINT);

?>