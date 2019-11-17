<?php
require_once 'include/protect.php';
require_once 'include/common.php';
require_once 'include/navbar_admin.php';

$roundDAO = new RoundDAO();
$roundInfo = $roundDAO->retrieveRoundInfo();
$currentRound = $roundInfo->getRoundNum();
$currentStatus = $roundInfo->getStatus();
$display = $currentRound;
if ($currentStatus == "closed" && $currentRound == 1) {
    $display = 2;
} elseif ($currentStatus == "closed") {
    $display = 1;
}

?>

<html>
    <head>

        <link rel="stylesheet" type="text/css" href="include/style.css"/>
    </head>
    <body>

    <h1>BIOS BIDDING (Administrator)</h1>
    <div style='float:left; width:60%'>
        <!-- <h3>Current Round: //$currentRound?>  -->

        <?php
        // if($currentStatus == 'closed'){
        //             echo "<span style = 'color:red'><b>(".strtoupper($currentStatus).")</b>";
        //         } else {
        //             echo "<span style = 'color:green'><b>(".strtoupper($currentStatus).")</b>";
        //         }

        ?>

        <h2 style='color:black'><b>Open / Close Round</b></h2>
        <form action ="process_round.php" method = "POST">
            <table border = '1'>
                <tr>
                    <th>Current Round</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td><?=$currentRound?></td>
                    <?php
                        if($currentStatus == 'closed'){
                            echo "<td><span style = 'color:red'><b>".strtoupper($currentStatus)."</b></td>";
                        } else {
                            echo "<td><span style = 'color:green'><b>".strtoupper($currentStatus)."</b></td>";
                        }
                   ?> 
                    
                </tr>
                </table>
                <br>

                    <?php
                        if ($currentStatus == "opened") {
                            echo "<button name='submit' type='submit' value='closed'>Close Round $display</button>";
                        } elseif ($currentRound == 1) {
                            echo "<button name='submit' type='submit' value='opened'>Open Round $display</button>";
                        }
                        echo "<input type='hidden' name='number' value='$display'>";
                    ?>
        </form>
        <br>

        <h2 style='color:black'><b>Bootstrap</b></h2>
        <form id='bootstrap-form' action="bootstrap_process.php" method="post" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Bootstrap file: </td>
                    <td><input id='bootstrap-file' type="file" name="bootstrap-file"></td>
                    <td><input type="submit" name="submit" value="Import"></td>
                </tr>
            </table>	
        </form>
        
        
    </div>

    <div style='float:right; width:40%'>
        <?=printSuccessFloat()?>
        <?=printErrorsFloat()?>
    </div>
    </body>
</html>

