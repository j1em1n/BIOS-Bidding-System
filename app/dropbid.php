<?php
    require_once 'include/protect.php';
    require_once 'include/protect_roundclosed.php';
    require_once 'include/common.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>

    <body>
        <h1>Drop Bid</h1>
        <form action='process_dropbid.php' method='POST'>
            <table border='1'>
                <tr>
                    <th>Course Code</th>
                    <td><input name='coursecode' type='text'></td>
                </tr>
                <tr>
                    <th>Section Number</th>
                    <td><input name='sectionnum' type='text'></td>
                </tr>
               
                <tr>
                    <td colspan='2'><input type='submit' value='Drop Bid'></td>
                </tr>
            </table>
        </form>

        <p>
            <?=printSuccess()?>
            <?=printErrors()?>
        </p>
        <p>
            <a href='index.php'>Home</a>
        </p>
    </body>

</html>

