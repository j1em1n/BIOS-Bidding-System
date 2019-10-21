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

            <div style='float: right;'><a href='logout.php'>Logout</a></div>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>

        <h1>BIOS BIDDING </h1>
        
        <h2>Welcome <?=$name?>


        
        
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
                if($currentStatus == 'opened' && $status == 'pending'){ // round 1 and 2 can drop bid
                    $_SESSION['code'] = $code;
                    $_SESSION['section'] = $section;
                    $_SESSION['amount'] = $amount;

                    $form = "process_dropbid.php";
                    $drop = 'Drop Bid';

                    echo 
                    "<form method = 'POST' action = '$form'>";

                  

                } else if($currentRound == '1' && $currentStatus == 'closed'){
                    // Show results
                    if($status == 'successful'){
                        $color = 'green';
                    } else {
                        $color = 'orange';
                    }

                    echo "
                    <tr>
                        <td>{$bid->getCode()}</td>
                        <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                        <td>{$bid->getSection()}</td>
                        <td>{$bid->getAmount()}</td>
                        <td style='background-color: $color'>{$bid->getStatus()}</td>
                    </tr>";
                    

                } else if($currentRound == '2' && $currentStatus == 'opened' && $status == 'successful'){
                    $_SESSION['code'] = $code;
                    $_SESSION['section'] = $section;
                    $_SESSION['amount'] = $amount;

                    $form = "process_dropsection.php";
                    $drop = 'Drop Section';


                    echo 
                    "<form method = 'POST' action = '$form'>";

                }

                if($currentStatus == 'opened' && ($status == 'pending' || $status == 'successful')){


                    echo "
                    <tr>
                        <td>{$bid->getCode()}</td>
                        <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                        <td>{$bid->getSection()}</td>
                        <td>{$bid->getAmount()}</td>";

                    if($status == 'successful'){
                        $color = 'green';
                    } else {
                        $color = 'orange';
                    }
                    echo "    
                        <td style='background-color: $color'>{$bid->getStatus()}</td>";
                        if($currentStatus == 'opened' && ($status == 'pending' || $status == 'successful')){
                            echo "<td><input type = 'submit' value = '$drop'></td>";
                        } 
                        
                    echo '</tr></form>';
                }

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

