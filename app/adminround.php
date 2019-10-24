<?php
require_once 'include/protect.php';
require_once 'include/common.php';

//status_entered
$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();

$bidDAO = new BidDAO();
$allBids = $bidDAO->retrieveAllOrderByAmount();
$allBidsTotal = count($allBids);

$sectionDAO = new SectionDAO();
$sections = $sectionDAO->retrieveAll();

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
            <table>
                <tr>
                    <th>Current Round</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><?=$currentRound?></td>
                    <td><?=$currentStatus?></td>
                </tr>
                <tr><td>
                    <?php
                        if ($currentStatus == "opened") {
                            echo "<button name='submit' type='submit' value='closed'>Close Round $display</button>";
                        } else {
                            echo "<button name='submit' type='submit' value='opened'>Open Round $display</button>";
                        }
                        echo "<input type='hidden' name='number' value='$display'>";
                    ?>
                </td></tr>
            </table>
        </form>
        <p><a href="admin_index.php">Home</a></p>

        <p>
            <?=printSuccess()?>
            <?=printErrors()?>
        </p>
    </body>
</html>

<?php

if ($currentStatus == "closed" && $currentRound == 1) {

    echo "Vacancies: $vacancies 
        
        <br><br>
    
        Total number of bids: $allBidsTotal <br>";

    echo "<table>
            <tr>
                <td>Ranking</td>
                <td>Bid Price</td>
                <td>State</td>
            </tr>";

    $rank = 1;
    foreach($allBids as $eachbid){
            echo "<tr>
                    <td>$rank</td>
                    <td>{$eachbid->getAmount()}</td>
                    <td>{$eachbid->getStatus()}</td>
                </tr>";
            
                $rank += 1;
        
    }

    echo "</table>";


}
?>

</html>