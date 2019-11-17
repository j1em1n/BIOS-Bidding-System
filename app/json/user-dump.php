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
    $userid = $jsonObj->userid;

    //initialize DAO for validations
    $studentDAO = new StudentDAO();

    if ($studentDAO->retrieve($userid)){
        $student = $studentDAO->retrieve($userid);
        $edollar = floatval($student->getEdollar());
        $success = [
            "status" => "success",
            "userid" => $student->getUserid(),
            "password" => $student->getPassword(),
            "name" => $student->getName(),
            "school" => $student->getSchool(),
            "edollar" => $edollar
        ];
    } else {
        $errors[] = "invalid userid"; 
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