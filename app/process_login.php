<?php
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