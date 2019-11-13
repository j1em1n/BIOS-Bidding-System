<?php
require_once 'include/common.php';

if(isset($_POST['submit']) && isset($_POST['number'])){

    $status_entered = $_POST['submit'];
    $newNumber = $_POST['number'];

    $roundDAO = new RoundDAO();
    $roundInfo = $roundDAO->retrieveRoundInfo();
    $currentRound = $roundInfo->getRoundNum();

    $updateStatusOK = $roundDAO->updateRoundStatus($status_entered);
    $updateNumberOK = $roundDAO->updateRoundNumber($newNumber);

    if($updateStatusOK && $updateNumberOK){
        $_SESSION['success'][] = "Round successfully $status_entered";
    } else {
        $_SESSION['errors'][] = "Round could not be $status_entered";
    }

    if ($status_entered == 'closed') {
        if ($newNumber == 1) { round1Clearing(); } else { round2Clearing(); }
    } else {
        deleteFailedBids();
    }
    
    header("Location: adminround.php");
    exit();
}
?>