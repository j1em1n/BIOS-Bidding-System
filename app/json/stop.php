<?php
require_once '../include/protect.php';
require_once '../include/common.php';

//status_entered
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();
$token = $_SESSION['token'];

# verify whether the token is valid
$checkToken = verify_token($token);
if($checkToken == FALSE){
    $result = [
        "status" => "error",
        "message"=> "Invalid token"
    ];    
} else {  
    // Ensure that if the current status is open, we should be able to close it
    if ($currentStatus == "opened"){
        $status_entered = "closed";
        $UpdateStatusOK = $roundDAO->updateRoundStatus($status_entered);
        $UpdateNumberOK = $roundDAO->updateRoundNumber($currentRound);

        // Check if we have updated the current status successfully
        if($UpdateStatusOK && $UpdateNumberOK){
           $result = [
                "status" => "success" 
            ];

            // When the round status is close successfully, modify the current status of the students bid

        } else {
            $result = [
                "status" => "error", 
                "message" => ["Round could not be stop"]
            ];
        }
    // Check that if the current status is close, an error message will be shown
    } elseif ($currentStatus == "closed"){
        $result = [
            "status" => "error", 
            "message" => ["round already ended"]
        ];
    } 
    
}
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);


?>

