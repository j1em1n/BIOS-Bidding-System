<?php

require_once 'include/common.php';
require_once 'include/protect.php';
require_once './include/studentDAO.php';

$dao = new StudentDAO();
$user = $dao->retrieveAll();
//var_dump($user);
    
if(isset($_SESSION['userid'])){
    $userid = $_SESSION['userid'];
    ///echo $userid;
}

$biddao = new BidDAO();
$allbid = $biddao->retrieveAll();

$coursedao = new CourseDAO();
$allcourse = $coursedao->retrieveAll();
//var_dump($allcourse);

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>BIOS BIDDING</h1>
        <h2>Welcome <?=$user[0]->name?>
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
                </b>
            </tr>

        <?php
            foreach ($allbid as $eachbid){
                foreach($eachbid as $eachuserbid){
                    if($eachuserbid == $userid){
                        echo "
                        <tr>
                            <td>$eachbid->code</td>";

                            foreach($allcourse as $eachcourse) {
                                foreach($eachcourse as $eachcoursecode){
                       
                                    if($eachcoursecode == $eachbid->code){
                                        var_dump($eachcoursecode);
                                        echo "<td>$eachcourse->title</td>";
                                    }
                                }
                            }
                            echo "
                            <td>$eachbid->section</td>
                            <td>$eachbid->amount</td>
                            
                        </tr>";
                    }
                }
            }
        ?>

        </table>

        <table>
            <tr>"
                <th>E_Balance: $<?=$user[0]->edollar ?></th>
            </tr>

            <tr> 
                <th>Amount left for bidding: $</th>

        
        </table>

        
        <p>
        <a id="add" href="PlanBid.php">Plan & Bid</a>
    </body>




</html>

