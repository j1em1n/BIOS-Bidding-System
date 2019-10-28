<?php

require_once '../include/common.php';
require_once '../include/token.php';

$retrieveInfo = $_REQUEST['r'];
$retrieveInfo = json_decode($retrieveInfo);

$coursecode = $retrieveInfo->course;
$section = $retrieveInfo->section;

$errors = array();
$result = array(); 
$studentsID = array();
$studentsAmount = array();
$studentsInfo = array();
$studentsInfo_2 = array();

//initialize DAO
$sectionDAO = new SectionDAO();
$bidDAO = new bidDAO();

if(empty($coursecode)){
    $errors[] = "Course is missing";
} 

if(empty($section)){
    $errors[] = "Section is missing";
}

if(!empty($coursecode) && !empty($section)){
    if(!$sectionDAO->searchByCourse($coursecode)){
        $errors[] = "invalid course";

    } else { //correct course, invalid section
        if(!$sectionDAO->searchByCourse($section)){
            $errors[] = "invalid section";
        }
    }
}


if(isEmpty($errors)){ 
    
    //course and section NOT empt

    if($sectionDAO->retrieve($coursecode, $section)){
        $result = [
            "status" => "success" 
        ];
    
        //display enrolled students here
        $successfulBids = $bidDAO->getBidsByStatus('Success');
    
        foreach($successfulBids as $eachbid){
            $studentsInfo["UserID"] = $eachbid->GetUserid();
            $studentsInfo["Amount"] = $eachbid->GetAmount();
            array_push($studentsInfo_2, $studentsInfo);
        }

        foreach($studentsInfo_2 as $eachinfo){
            $result["students"][] = $eachinfo;

        }
    }
}
    



if(!(isEmpty($errors))){

    $result = [
        "status" => "error",
        "messages" => array_values($errors)
    ];    
 }


header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>