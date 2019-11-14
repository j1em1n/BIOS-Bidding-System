<?php
require_once '../include/common.php';
require_once '../include/token.php';
//require_once '../include/process_bids.php';

$errors = commonValidationsJSON(basename(__FILE__));
$success = array();

if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    $roundDAO = new RoundDAO();
    $roundInfo = $roundDAO->retrieveRoundInfo();
    $currentRound = $roundInfo->getRoundNum();
    $currentStatus = $roundInfo->getStatus();

    // If the current status is open, we should be able to close it
    if ($currentStatus == "opened"){
        $updateStatusOK = $roundDAO->updateRoundStatus("closed");
        try {
            if ($currentRound == 1) { round1Clearing(); } else { round2Clearing(); }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        // Check if we have updated the current status successfully
        if($updateStatusOK){
           $success = [
                "status" => "success" 
            ];

            // When the round status is closed successfully, modify the current status of the students bid

        } else {
            $errors[] = "round status could not be updated";
        }
    // Check that if the current status is close, an error message will be shown
    } elseif ($currentStatus == "closed"){
        $errors[] = "round already ended";
    } 
    if (empty($errors) && !empty($success)) {
        $result = $success;
    } else {
        sort($errors);
        $result = jsonErrors($errors);
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>

