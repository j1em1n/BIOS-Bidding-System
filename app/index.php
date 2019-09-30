<?php

require_once 'include/common.php';
require_once 'include/protect.php';
require_once './include/studentDAO.php';

$dao = new StudentDAO();
$user = $dao->retrieveAll();
    
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
                <th></th>
                <th>E_Balance: $<?=$user[0]->edollar ?></th>
            </tr>
        
        </table>

        
        <p>
        <a id="add" href="PlanBid.php">Plan & Bid</a>
    </body>
</html>

