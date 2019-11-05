<?php

require_once '../include/common.php';
require_once '../include/token.php';


$errors = commonValidationsJSON(basename(__FILE__));
$success = array();
$result = array();
$studentArray = array();

//var_dump area

//var-dump area






if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    // Retrieve 'r' GET parameter
    $jsonObj = json_decode($_REQUEST['r']);
    $course = $jsonObj->course;
    $section = $jsonObj->section;


    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $studentDAO = new StudentDAO();
    $roundDAO = new RoundDAO();
    
    $roundNum = $roundDAO->retrieveRoundInfo();

    $allInfo = $sectionDAO->retrieve($course, $section);

    $vacancy = $allInfo->getSize();
    $minBidAmt = $allInfo->getMinBid();
    $enrolledBids = $bidDAO->getBidsByCourseSection($course, $section);
    //round 1
    //check for status success
    //Minimum bid price: when #bid is less than the #vacancy,
    //report the lowest bid amount. Otherwise, set the price as the 
    //clearing price. When there is no bid made, the minimum bid price will be 10.0 dollars.
    foreach($enrolledBids as $eachBid){
        $userid = $eachBid->getUserid();
        $amount = $eachBid->getAmount();
    
        $studentInfo = $studentDAO->retrieve($eachBid->getUserid());
        $balance = $studentInfo->getEdollar();
    
        if($roundNum->getRoundNum() == '1'){
            $status = $eachBid->getR1Status();
    
        } else {
            $status = $eachBid->getR2status();
        }
    
        $studentArray[]= [
            "userid" => $userid,
            "amount" => $amount,
            "balance" => $balance,
            "status" => $status
    
        ];
       
    }
    
    $success = [
        "status" => "success",
        "vacancy" => "$vacancy",
        "min-bid-amount" => $minBidAmt
        //"student" => $studentArray
    ];

    foreach($studentArray as $eachStudentArray){
        $success['student'][] = $eachStudentArray;
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