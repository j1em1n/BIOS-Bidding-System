<?php
require_once 'include/common.php';

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>
        <h1>Login</h1>
        <form method='POST' action='login.php'>
            <table>
                <tr>
                    <td>Select your role:
                        <select name='role'>
                            <option value='Student' selected>Student</option>
                            <option value='Admin'>Admin</option>
                        </select>
                    </td>
                </tr>
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
            <?=$error?>
        </p>
        
    </body>
</html>