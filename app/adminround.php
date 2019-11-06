<?php
require_once 'include/protect.php';
require_once 'include/common.php';
require_once 'include/navbar_admin.php';

//status_entered
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();

$bidDAO = new BidDAO();
$bids = $bidDAO->retrieveAll();

$sectionDAO = new SectionDAO();
$sections = $sectionDAO->retrieveAll();

//only avail those that bidded.
foreach($bids as $eachbid){
    $bidsArray[] = [$eachbid->getCode() => $eachbid->getSection()];
}

foreach ($sections as $section) {
    $vacancies = $section->getVacancies();
}

$display = $currentRound;
if ($currentStatus == "closed" && $currentRound == 1) {
    $display = 2;

} elseif ($currentStatus == "closed") {
    $display = 1;
}



?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="include/style.css">
</head>
    <body>
        <form action ="process_round.php" method = "POST">
           <br>
            <table border = '1'>
                <tr>
                    <th>Current Round</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><?=$currentRound?></td>
                    <?php
                    if($currentStatus == 'closed'){
                            $currentStatus = strtoupper($currentStatus);
                            echo "<td><span style = 'color:red'><b>$currentStatus</b></td>";
                        } else {
                            $currentStatus = strtoupper($currentStatus);
                            echo "<td><span style = 'color:green'><b>$currentStatus</b></td>";
                        }
                   ?> 
                    
                </tr>
                </table>
                <br>

                    <?php
                        if ($currentStatus == "opened") {
                            echo "<button name='submit' type='submit' value='closed'>Close Round $display</button>";
                        } else {
                            echo "<button name='submit' type='submit' value='opened'>Open Round $display</button>";
                        }
                        echo "<input type='hidden' name='number' value='$display'>";
                    ?>

        </form>

        <p>
            <?=printSuccess()?>
            <?=printErrors()?>
        </p>
    </body>
</html>


