<?php
require_once '../include/protect.php';
require_once '../include/common.php';

//status_entered
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();

if ($currentStatus == "opened"){
    $result = [
        "status" => "success" 
    ];
} elseif ($currentStatus == "closed"){
    $result = [
        "status" => "error", 
        "message" => ["round already ended"]
    ];
} else {
    $result = [
        "status" => "error", 
        "message" => ["Check your code!!"]
    ];
}
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);


?>

