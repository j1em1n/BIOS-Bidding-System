<?php
require_once 'include/common.php';

$error = '';

if ( isset($_GET['error']) ) {
    $error = $_GET['error'];
} elseif ( isset($_POST['userid']) && isset($_POST['password']) ) {
    $username = $_POST['userid'];
    $password = $_POST['password'];

//Validation for student
if($_POST['role'] == "Student"){
    $studentDAO = new StudentDAO();
    $stud = $studentDAO->retrieve($username);

    if ( $stud != null && $stud->authenticate($password) ) {
        $_SESSION['userid'] = $username; 
        header("Location: index.php");
        return;

    } else {
        $error = 'Incorrect username or password!';
    }
} else{
    //Validation for admin
    $adminDAO = new AdminDAO();
    $admin = $adminDAO->retrieve($username);
    
    //Get the hashedpassword from database
    $hashed = $adminDAO->getHashedPassword($username);

    //Verify whether the password given is correct using hashedpassword
    $status = password_verify($password,$hashed);

    if ( $admin != null && $status ) {
        $_SESSION['userid'] = $username; 
        //bidding_admin = admin home page? can be change ltr on 
        header("Location: bidding_admin.php");
        return;

    } else {
        $error = 'Incorrect username or password!';
    }
}
    


}
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