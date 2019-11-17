<?php
require_once 'include/protect.php';
require_once 'include/common.php';
require_once 'include/navbar.php';

if(isset($_SESSION['userid'])){
    $userid = $_SESSION['userid'];
}

$studentDAO = new StudentDAO();
$student = $studentDAO->retrieve($userid);
$name = $student->getName();
$edollar = number_format($student->getEdollar(),2);

$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();

?>

<html>
    <head>


        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>

        <h1>BIOS BIDDING </h1>
        
        <div style='float:left; width:60%'>
            <h2>Welcome, <b><?=$name?></b>!</h2>
            <h3>Current Round: <?=$currentRound?>
            <?php 
            
            if($currentStatus == 'closed'){
                    echo "<span style = 'color:red'><b>(".strtoupper($currentStatus).")</b>";
                } else {
                    echo "<span style = 'color:green'><b>(".strtoupper($currentStatus).")</b>";
                }
            ?>
            </h3>

            <h4>Your E-Dollar Balance: <b><u>$<?=$edollar?></u></b></h4>

            <?php
                
                $courseDAO = new CourseDAO();
                $bidDAO = new BidDAO();
                $bids = $bidDAO->retrieveByUserid($userid);
                if ($currentStatus == "opened") {
                    $pending = array();
                    $success = array();
                    foreach ($bids as $bid) {
                        if ($bid->getR1Status() == "Pending" || $bid->getR2Status()) {
                            $pending[] = $bid;
                        } elseif ($bid->getR1Status() == "Success") {
                            $success[] = $bid;
                        }
                    }
                    
                    currentBidsTable($pending, $currentRound);
                    if ($currentRound == 2) {
                        enrolledSectionsTable($success);
                    }
                } else {
                    bidResultsTable($bids);
                }
            ?>
        </div>
        <div style='float:right; width:40%'>
            <?=printSuccessFloat()?>
            <?=printErrorsFloat()?>
        </div>
    </body>

</html>

