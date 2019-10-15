<?php
    require_once 'common.php';

    $roundDAO = new RoundDAO();
    $round = retrieveRoundInfo();
    $roundStatus = $round->getStatus();

    if ($roundStatus == "closed") {
        $_SESSION['errors'][] = "This feature is not available now as the bidding round is not active.";
        header("Location: index.php");
        exit();
    }
?>