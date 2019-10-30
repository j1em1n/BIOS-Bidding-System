<?php

require_once '../include/common.php';
require_once '../include/token.php';

$errors = commonValidationsJSON(basename(__FILE__));
$success = array();

if (!empty($errors)) {
    $result = jsonErrors($errors);
} else {
    $username = $_POST['username'];
    $password = $_POST['password'];

    # complete authenticate API

    # check if username and password are right. generate a token and return it in proper json format

    if ($username === "admin") {
    
        $adminDAO = new AdminDAO();
        $admin = $adminDAO->retrieve($username);
    
        if (password_verify($password,$admin->getPassword())) {
            # after you are sure that the $username and $password are correct, you can do 
            # generate a secret token for the user based on their username

            $token = generate_token($username);
            $_SESSION['token'] = $token;

            $success = [
                "status" => "success", 
                "token" => $token
            ];

        # return error message if something went wrong 
        } else {
            $errors[] = "invalid password";
        }
    } else {
        $errors[] = "invalid username";
    }
    if (empty($errors) && !empty($success)) {
        $result = $success;
    } else {
        sort($errors);
        $result = jsonErrors($errors);
    }
} 

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);

?>