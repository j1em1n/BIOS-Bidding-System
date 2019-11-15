<?php
require_once 'include/protect.php';
require_once 'include/common.php';
require_once 'include/navbar_admin.php';

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
    <h3>Current Round: <?=$currentRound?> 

    <?php
    if($currentStatus == 'closed'){
                echo "<span style = 'color:red'><b>(".strtoupper($currentStatus).")</b>";
            } else {
                echo "<span style = 'color:green'><b>(".strtoupper($currentStatus).")</b>";
            }

    ?><br><br>
    
    <input class = "button2" type = "button" value = "Bootstrap" onclick = "window.location.href='bootstrap.php'"/><br>
    <br>
    <input class = "button2" type = "button" value = "Open/Close Bidding Round" onclick = "window.location.href='adminround.php'"/><br>
    <br>

        <!-- <p><a href='bootstrap.php'>Bootstrap</a></p>
        <p><a href='adminround.php'>Open / Close Bidding Round</a></p>
        <p><a href='logout.php'>Logout</a></p> -->
    </body>
</html>

