<?php
require_once 'include/common.php';
require_once 'include/navbar.php';

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>

    <body>
        <h1>Welcome to Bios!</h1>
        <form method='POST' action='process_login.php'>
            <table align = "left">
                <tr>
                    <td>Username</td>
                    <td>
                        <input name='userid' placeholder ='User ID' />
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password' placeholder = 'Password'/>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Login' type='submit' />
                    </td>
                </tr>
            </table>             
        </form>

        <p>
            <?=printErrors();?>
        </p>
        
    </body>
</html>