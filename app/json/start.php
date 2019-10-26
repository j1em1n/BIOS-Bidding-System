<?php
require_once '../include/protect.php';
require_once '../include/common.php';

//status_entered
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();
//$token = $_SESSION['token'];

// # verify whether the token is valid
// $checkToken = verify_token($token);
// if($checkToken == FALSE){
//     $result = [
//         "status" => "error",
//         "message"=> "Invalid token"
//     ];    
// } else {
    // If the current status is opened, success message should be shown
    if ($currentStatus == "opened"){
        $result = [
            "status" => "success", 
            "round" => $currentRound
        ];
    // If it is Round 2 and the current status is closed, error message will be shown
    } elseif ($currentStatus == "closed" && $currentRound == 2){
        $result = [
            "status" => "error", 
            "message" => ["round 2 ended"]
        ];

    // If the current status is closed and current round is 0 / 1, we should be able to open the round successfully
    } elseif ($currentStatus == "closed" && ($currentRound == 0 || $currentRound == 1)){
        $newNumber = strval(intval($currentRound) + 1);
        $status_entered = "opened";
        $UpdateStatusOK = $roundDAO->updateRoundStatus($status_entered);
        $UpdateNumberOK = $roundDAO->updateRoundNumber($newNumber);
    
        // Verify whether we have updated the current status and round in the database
        if($UpdateStatusOK && $UpdateNumberOK){
            $result = [
                "status" => "success", 
                "round" => $newNumber
            ];
        } else {
            $result = [
                "status" => "error", 
                "message" => ["Round could not be started."]
            ];
        }
    } 
    
// }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);


?>

