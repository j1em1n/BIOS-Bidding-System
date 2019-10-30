<?php
require_once 'include/common.php';
require_once 'include/process_bids.php';

if(isset($_POST['submit']) && isset($_POST['number'])){

    $status_entered = $_POST['submit'];
    $newNumber = $_POST['number'];

    $roundDAO = new RoundDAO();
    $roundInfo = $roundDAO->retrieveRoundInfo();
    $currentRound = $roundInfo->getRoundNum();

    $UpdateStatusOK = $roundDAO->updateRoundStatus($status_entered);
    $UpdateNumberOK = $roundDAO->updateRoundNumber($newNumber);

    if($UpdateStatusOK && $UpdateNumberOK){
        $_SESSION['success'] = "Round successfully $status_entered";
    } else {
        $_SESSION['errors'] = "Round could not be $status_entered";
    }

    if ($status_entered == 'closed') {
        processBids();
    } else {
        deleteFailedBids();
    }
    
    header("Location: adminround.php");
    exit();
}
?>