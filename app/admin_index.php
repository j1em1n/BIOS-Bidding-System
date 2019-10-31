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

            <div style='float: right;'><a href='logout.php'>Logout</a></div>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>BIOS BIDDING (Administrator)</h1>
	<h2>Welcome, Admin</h2>
	<h3>Current Round: <?=$currentRound?> (<?=strtoupper($currentStatus)?>)</h3>

        <p><a href='bootstrap.php'>Bootstrap</a></p>
        <p><a href='adminround.php'>Open / Close Bidding Round</a></p>
        <p><a href='logout.php'>Logout</a></p>
    </body>
</html>

