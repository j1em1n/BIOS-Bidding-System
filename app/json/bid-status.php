<?php

require_once '../include/common.php';
require_once '../include/token.php';

$errors = commonValidationsJSON(basename(__FILE__));
$success = array();
$result = array();
$studentArray = array();

if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    // Retrieve 'r' GET parameter
    $jsonObj = json_decode($_REQUEST['r']);
    $courseCode = $jsonObj->course;
    $sectionNum = $jsonObj->section;

    $errors = array();

    // Initialise DAOs
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $studentDAO = new StudentDAO();
    $roundDAO = new RoundDAO();

    // Validate course code
    if(!($courseDAO->retrieve($courseCode))) {
        $errors[] = "invalid course";
    } elseif (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
        // Check if section code is found in section (only for valid course code)
        $errors[] = "invalid section";
    }

    if(empty($errors)) {
        $roundNum = $roundDAO->retrieveRoundInfo()->getRoundNum();
        $roundStatus = $roundDAO->retrieveRoundInfo()->getStatus();
        $section = $sectionDAO->retrieve($courseCode, $sectionNum);
        $vacancies = $section->getVacancies();
        $minBid = $section->getMinBid();
    
        //round 1
        //check for status success
        //Minimum bid price: when #bid is less than the #vacancy,
        //report the lowest bid amount. Otherwise, set the price as the 
        //clearing price. When there is no bid made, the minimum bid price will be 10.0 dollars.
        if ($roundNum == 1 || ($roundNum == 2 & $roundStatus == "opened")) {
            $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionNum, $roundNum);
        } elseif ($roundNum == 2 & $roundStatus == "closed") {
            $sectionBids = $bidDAO->getSuccessfulBidsBySection($courseCode, $sectionNum);
        }
    
        $numOfBids = count($sectionBids);
    
        if ($roundNum == 1 && $roundStatus == "opened") {
            if ($numOfBids < $vacancies) {
                $minBid = end($sectionBids)->getAmount();
            } else {
                $minBid = $sectionBids[$vacancies-1]->getAmount();
            }
        } elseif ($roundStatus == "closed" && $numOfBids != 0) {
            $successful = array();
            foreach($sectionBids as $bid) {
                $bidStatus = ($roundNum == 1) ? $bid->getR1Status() : $bid->getR2status();
                if ($bidStatus == "Success") {
                    $successful[] = $bid;
                }
            }
            $minBid = end($successful)->getAmount();
        }
    
        $bidDump = array();
        foreach($sectionBids as $bid) {
            $userid = $bid->getUserid();
            $student = $studentDAO->retrieve($userid);
            $status = ($bid->getR1Status() != null ? $bid->getR1Status() : $bid->getR2Status() );
            $bidDump[] = [
                "userid" => $userid,
                "amount" => (float)($bid->getAmount()),
                "balance" => (float)($student->getEdollar()),
                "status" => strtolower($status)
            ];
        }

        $success = [
            "status" => "success",
            "vacancy" => (int)($vacancies),
            "min-bid-amount" => (float)($minBid),
            "students" => $bidDump
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