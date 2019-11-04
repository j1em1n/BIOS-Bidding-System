<?php
require_once 'include/common.php';

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>Welcome to Bios!</h1>
        <h2>Login page<h2>
        <h3>Sign in with your userID and password</h3>
        <form method='POST' action='process_login.php'>
            <table align = "center">
                <tr>
                    <td>Username</td>
                    <td>
                        <input name='userid' />
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password' />
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