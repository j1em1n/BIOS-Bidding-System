<?php
require_once '../include/common.php';
require_once '../include/token.php';

$errors = commonValidationsJSON(basename(__FILE__));
$success = array();

if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    $roundDAO = new RoundDAO();
    $roundInfo = $roundDAO->retrieveRoundInfo();
    $currentRound = $roundInfo->getRoundNum();
    $currentStatus = $roundInfo->getStatus();

    if ($currentStatus == "opened") {
        $success = [
            "status" => "success",
            "round" => (int)($currentRound)
        ];
    } elseif($currentRound == 1) {
        $updateStatusOK = $roundDAO->updateRoundStatus("opened");
        $updateNumberOK = $roundDAO->updateRoundNumber(2);
        if ($updateStatusOK && $updateNumberOK) {
            deleteFailedBids();
            $success = [
                "status" => "success",
                "round" => 2
            ];
        } else {
            if (!$updateStatusOK) {
                $errors[] = "round status could not be updated";
            } if (!$updateNumberOK) {
                $errors[] = "round number could not be updated";
            }
        }
    } elseif($currentRound == 2 && $currentStatus == "closed") {
        $errors[] = "round 2 ended";
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

