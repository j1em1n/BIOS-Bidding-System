<?php
    require_once 'include/common.php';
?>
<!DOCTYPE html>
<html>
    <head>
    </head>

    <body>
        <h1>Bid for a Section</h1>
        <form action='process_placebid.php' method='POST'>
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
                    <th>E-dollars</th>
                    <td><input name='edollar' type='text' maxlength='5'></td>
                </tr>
                <tr>
                    <td colspan='2'><input type='submit' value='Place Bid'></td>
                </tr>
            </table>
        </form>

        <p>
            <?=printErrors()?>
        </p>
    </body>

</html>