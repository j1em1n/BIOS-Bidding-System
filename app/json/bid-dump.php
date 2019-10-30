<?php

require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('course'),
            isMissingOrEmpty ('section')];
$errors = array_filter($errors);

// if there is no blank/empty field
if (isEmpty($errors)) {
    $courseCode = $_POST['course'];
    $sectionNum = $_POST['section'];

    // initialize DAO for validations
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();

    // Check for valid course code
    if(!($courseDAO->retrieve($courseCode))) {
        $errors = ["invalid course"];
    } else {
        if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
            // Check for valid section (only if course code is valid)
            $errors = ["invalid section"];
        }
    }

    // If course and section exist, 
    if (isEmpty($errors)){

        // initialize DAO for validations
        $roundDAO = new RoundDAO();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();

        $bidStatus = "";
        $row = 1;
        $bidAmount = 0;
        $bidArr = array();
        $bids = $bidDAO->retrieveBidsBySection($courseCode, $sectionNum);
        
        foreach ($bids as $bid){
            $bidAmount = floatval($bid->getAmount());
            $bidAmount = round($bidAmount, 1);
            // create a new array and store userid as key and bid amount as value
            $bidArr["{$bid->getUserid()}"] = $bidAmount;
        }

        // sort the array according to the value in desc order - bid amount
        arsort($bidArr, SORT_NUMERIC);

        foreach ($bidArr as $key => $value){
            $bidAmount = floatval($bid->getAmount());
            $bidAmount = round($bidAmount, 1);
            foreach ($bids as $bid){
                if ($bid->getUserid() == $key && $bidAmount == $value){
                    if($currentStatus == "opened"){
                        $bidStatus = "-";
                    } else {
                        if ($bid->getStatus() == "successful"){
                            $bidStatus = "in";
                        } else {
                            $bidStatus = "out";
                        }        
                    }

                    $result = [
                        "status" => "success",
                        "row" => $row,
                        "userid" => $bid->getUserid(),
                        "amount" => $bidAmount,
                        "result" => $bidStatus
                    ];
            
                }
                $row++;
            }
        }
         
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