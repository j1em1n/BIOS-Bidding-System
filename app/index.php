<?php
require_once 'include/protect.php';
require_once 'include/common.php';
    
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

            <div style='float: right;'><a href='logout.php'>Logout</a></div>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>

        <h1>BIOS BIDDING </h1>
        
        <h2>Welcome <?=$name?></h2>
        <h3>Current Round: <?=$currentRound?> (<?=strtoupper($currentStatus)?>)</h3>

        <p>
            <?=printErrors()?>
            <?=printSuccess()?>
        </p>

        <table>
            <tr>
                <th>Your E-Dollar Balance: $<?=$edollar?></th>
            </tr>
        
        </table>

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
                enrolledSectionsTable($success);
            } else {
                bidResultsTable($bids);
            }
        ?>
        
        <p>
            <a id="add" href="placebid.php">Plan & Bid</a><br>
        </p>

    </body>

</html>

