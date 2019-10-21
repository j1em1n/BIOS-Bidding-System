<?php
require_once 'include/protect.php';
require_once 'include/common.php';
    
if(isset($_SESSION['userid'])){
    $userid = $_SESSION['userid'];
    ///echo $userid;
}

$studentDAO = new StudentDAO();
$student = $studentDAO->retrieve($userid);
$name = $student->getName();
$edollar = $student->getEdollar();

$bidDAO = new BidDAO();
$bids = $bidDAO->retrieveByUserid($userid);

$courseDAO = new CourseDAO();

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

        <h1>BIOS BIDDING</h1>
        <h2>Welcome <?=$name?>

        
        <p>
            <a href='logout.php'>Logout</a>
        </p>
        
        
        <table>
            <tr>
                <b>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Section</th>
                <th>Bid amount (e$)</th>
                <th>Result</th>
                </b>
            </tr>

        <?php
            foreach ($bids as $bid){

                $code = $bid->getCode();
                $section = $bid->getSection();
                $amount = $bid->getAmount();
                $status = $bid->getStatus();

                $form = "";
                $drop = "";

                // Check if round 1 = 'drop bid'
                if($currentRound == '1' && $currentStatus == 'opened'){
                    $_SESSION['code'] = $code;
                    $_SESSION['section'] = $section;
                    $_SESSION['amount'] = $amount;

                    $form = "process_dropbid.php";
                    $drop = 'Drop Bid';

                    echo 
                    "<form method = 'POST' action = '$form'>";

                  

                } else if($currentRound == '1' && $currentStatus == 'closed'){
                    // Show results

                } else { // CurrentRound == '2' && currentStatus == 'opened'
                    $_SESSION['code'] = $code;
                    $_SESSION['section'] = $section;
                    $_SESSION['amount'] = $amount;

                    $form = "process_dropsection.php";
                    $drop = 'Drop Section';


                    echo 
                    "<form method = 'POST' action = '$form'>";

                }

                echo "
                <tr>
                    <td>{$bid->getCode()}</td>
                    <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                    <td>{$bid->getSection()}</td>
                    <td>{$bid->getAmount()}</td>
                    <td>{$bid->getStatus()}</td>
                    <td><input type = 'submit' value = '$drop'></td>
                </tr>";

                echo '</form>';

            }
        ?>

        </table>



        <table>
            <tr>
                <th>Your E-Dollar Balance: $<?=$edollar?></th>
            </tr>
        
        </table>
        
        <p>
            <a id="add" href="placebid.php">Plan & Bid</a><br>
            <a id="dropsection" href="dropsection.php">Drop Section</a>
        </p>
        <p><?=printErrors()?>

        <?php
        if(isset($_SESSION['success'])){
            $success = $_SESSION['success'];
            echo "$success";
        }
        ?>

    </body>

</html>

