<?php
require_once 'include/protect.php';
require_once 'include/common.php';

$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();

?>

<html>
    <head>

        <link rel="stylesheet" type="text/css" href="include/style.css"/>
    </head>
    <body>

    <h1>BIOS BIDDING (Administrator)</h1>
	<h3>Current Round: <?=$currentRound?> <b>(<?=strtoupper($currentStatus)?>)<b></h3>
    
    <input class = "button2" type = "button" value = "Bootstrap" onclick = "window.location.href='bootstrap.php'"/><br>
    <br>
    <input class = "button2" type = "button" value = "Open/Close Bidding Round" onclick = "window.location.href='adminround.php'"/><br>
    <br>
    <input class = "button1" type = "button" value = "Logout" onclick = "window.location.href='logout.php'"><br>

        <!-- <p><a href='bootstrap.php'>Bootstrap</a></p>
        <p><a href='adminround.php'>Open / Close Bidding Round</a></p>
        <p><a href='logout.php'>Logout</a></p> -->
    </body>
</html>

