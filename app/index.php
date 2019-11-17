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
$edollar = $student->getEdollar();

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

        <h3>Your E-Dollar Balance: <b><u>$<?=$edollar?></u></b></h3>

        <p>
            <?=printErrors()?>
            <?=printSuccess()?>
        </p>
            <hr>
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
        
        <!-- <p>
            <div class = "button" :hover><a href="placebid.php" style = "text-decoration: none;">Plan & Bid</a><br></div>
        </p> -->

    </body>

</html>

