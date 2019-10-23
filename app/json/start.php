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
        "status" => "success", 
        "round" => $currentRound
    ];
} elseif ($currentStatus == "closed" && $currentRound == 2){
    $result = [
        "status" => "error", 
        "message" => ["round 2 ended"]
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

