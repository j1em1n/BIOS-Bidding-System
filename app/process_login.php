<?php
    require_once 'include/common.php';

    $userid = $_POST['userid'];
    $password = $_POST['password'];

    //Validation for admin
    if ($userid === 'admin') {

        $adminDAO = new AdminDAO();
        $admin = $adminDAO->retrieve($userid);
        
        //Get the hashedpassword and verify

        if ( password_verify($password,$admin->getPassword()) ) {
            $_SESSION['userid'] = $userid; 
            //bidding_admin = admin home page? can be change ltr on 
            header("Location: admin_index.php");
            exit();
        }

    } else {
        //Validation for student
        $studentDAO = new StudentDAO();
        $student = $studentDAO->retrieve($userid);

        if ( $student != null && $student->getPassword() == $password ) {
            $_SESSION['userid'] = $userid; 
            header("Location: index.php");
            exit();

        }
    }
        
    $_SESSION['errors'] = ['Invalid username / password'];
    header("Location: login.php");
    exit();
?>