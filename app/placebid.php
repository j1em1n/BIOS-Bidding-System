<?php
    require_once 'include/protect.php';
    require_once 'include/protect_roundclosed.php';
    require_once 'include/common.php';
    require_once 'include/navbar.php';

    $roundDAO = new RoundDAO();
    $roundInfo = $roundDAO->retrieveRoundInfo();
    $currentRound = $roundInfo->getRoundNum();
    $roundStatus = $roundInfo->getStatus();
    $userid = $_SESSION['userid'];
?>
<!DOCTYPE html>
<html>
    <head>
    <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>

    <body>
        <h1>Bid for a Section</h1>
        <p>
            <?=printErrors()?>
            <?=printSuccess()?>
        </p>
        <form action='placebid.php' method='POST'>
            <table>
                <tr>
                    <th>Course Code</th>
                    <td><input name='coursecode' type='text'></td>
                    <td><input type='submit' value='Search Courses'></td>
                    <td><button onClick="window.location.reload();">View All</button></td>
                </tr>
            </table>
           <br><br>

        </form>

        <?php
            $sectionDAO = new SectionDAO();

            if (!isset($_POST['coursecode'])) {
                $sectionList = $sectionDAO->retrieveAll();
                printSectionInfo($sectionList, $userid);                    
            } else {
                $search = $_POST['coursecode'];
                $sectionList = $sectionDAO->searchByCourse($search);
                if (count($sectionList)) {
                    printSectionInfo($sectionList, $userid);
                } else {
                    echo "<h3>Sorry! No results found for '$search'.</h3>";
                }
            }
            
            $roundDAO = new RoundDAO();
            $roundInfo = $roundDAO->retrieveRoundInfo();
            $currentRound = $roundInfo->getRoundNum();
            $currentStatus = $roundInfo->getStatus();

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
                
                currentBidsTableInPlaceBid($pending, $currentRound);
              
            } else {
                currentBidsTableInPlaceBid($pending, $currentRound);
            }
            echo "</div>";
        ?>
        </div>

         


    </body>

</html>