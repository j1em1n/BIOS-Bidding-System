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
                echo "
                <tr>
                    <td>{$bid->getCode()}</td>
                    <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                    <td>{$bid->getSection()}</td>
                    <td>{$bid->getAmount()}</td>
                    <td>{$bid->getStatus()}</td>
                </tr>";
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
        <a id="add" href="dropbid.php">Drop Bid</a>

    </body>

</html>

