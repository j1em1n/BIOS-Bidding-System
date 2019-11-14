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
    
    $courseCode = $jsonObj->course;
    $sectionNum = $jsonObj->section;

    // initialize DAO for validations
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();

    // Check for valid course code
    if(!($courseDAO->retrieve($courseCode))) {
        $errors[] = "invalid course";
    } else {
        if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
            // Check for valid section (only if course code is valid)
            $errors[] = "invalid section";
        }
    }

    // If course and section exist, 
    if (empty($errors)){

        // initialize DAO for validations
        $roundDAO = new RoundDAO();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();
        $currentNum = $roundDAO->retrieveRoundInfo()->getRoundNum();

        $bidStatus = "";
        $row = 1;
        $bidAmount = 0;
        $bidArr = array();
        $bids = $bidDAO->getSectionBids($courseCode, $sectionNum, $currentNum);
        
        $bidsToDump = array();

        foreach ($bids as $bid) {
            $bidAmount = floatval($bid->getAmount());
            $bidStatus = ($currentNum == 1) ? $bid->getR1Status() : $bid->getR2Status();
            $bStatus = "";
            
            if ($currentStatus == "opened" || $bidStatus == "Pending") {
                $bStatus = "-";
            } elseif ($bidStatus == "Success") {
                $bStatus = "in";
            } elseif ($bidStatus == "Fail") {
                $bStatus = "out";
            }

            $bidsToDump[] = [
                "row" => $row,
                "userid" => $bid->getUserid(),
                "amount" => $bidAmount,
                "result" => $bStatus
            ];

            $row++;
        }

        $success = [
            "status" => "success",
            "bids" => $bidsToDump
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