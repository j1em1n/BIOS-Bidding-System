<?php

require_once 'include/common.php';
require_once 'include/protect.php';

$dao = new PokemonDAO();
$results = $dao->retrieveAll();
    
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>BIOS BIDDING</h1>
        <h2>Welcome <?$user->name?>
        <p>
            <a href='logout.php'>Logout</a>
        </p>

        <table>
            <tr>
                <th></th>
                <th>E_Balance: $<?$user->edollar ?></th>
            </tr>
        
        </table>

        
        <p>
        <a id="add" href="PlanBid.php">Plan & Bid</a>
    </body>
</html>

