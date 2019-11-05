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
    $course = $jsonObj->course;
    $section = $jsonObj->section;


    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $studentDAO = new StudentDAO();

    //round 1
    //check for status success
    //Minimum bid price: when #bid is less than the #vacancy,
    //report the lowest bid amount. Otherwise, set the price as the 
    //clearing price. When there is no bid made, the minimum bid price will be 10.0 dollars.

    $successfulBids = $bidDAO->getSuccessfulBids();
    $allInfo = $sectionDAO->retrieve($course, $section);

    $bidsInfo = $bidDAO->retrieveAll();
    $bidsInfo2 = array();

    foreach($bidsInfo as $eachBid){
        $userId = $eachBid->getUserid();

        //info from student table
        $studentInfo = $studentDAO->retrieve($userId);
        $eBalance = $studentInfo->getEdollar();

        $balanceArray = [
            $eachBid->getUserid() => $eBalance
        ];

    }

    // $studentArray = [
    //     "userid"=>$bidsInfo->getUserid(),
    //     "amount"=>$bidsInfo->getAmount(),
    //     "balance"=> "here"


    // ];

    // $success = [
    //     "status"=>"success",
    //     "vacancy"=>$allInfo->getSize(),
    //     "min-bid-amount"=>$allInfo->getMinBid(),
    //     "students"=>"students array HERE"
    // ];


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