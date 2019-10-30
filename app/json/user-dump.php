<?php

require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('userid')];
$errors = array_filter($errors);

// if there is no blank/empty field
if (isEmpty($errors)) {
    $userid = $_POST['userid'];

    //initialize DAO for validations
    $studentDAO = new StudentDAO();

    if ($studentDAO->retrieve($userid)){
        $student = $studentDAO->retrieve($userid);
        $edollar = floatval($student->getEdollar());
        $edollar = round($edollar, 1);
        $result = [
            "status" => "success",
            "userid" => $student->getUserid(),
            "password" => $student->getPassword(),
            "name" => $student->getName(),
            "school" => $student->getSchool(),
            "edollar" => $edollar
        ];
    } else {
        $errors = ["invalid userid"]; 
    }
}

if(!(isEmpty($errors))){
    $final_errors = array_multisort($errors);
    $result = [
        "status" => "error",
        "messages" => array_values($final_errors)
    ];      
 }


header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>