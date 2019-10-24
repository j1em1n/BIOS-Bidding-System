<?php

require_once '../include/common.php';
require_once '../include/token.php';


// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('userid'), 
            isMissingOrEmpty ('password') ];
$errors = array_filter($errors);


if (isEmpty($errors)) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    # complete authenticate API

    # check if userid and password are right. generate a token and return it in proper json format
    
        $adminDAO = new AdminDAO();
        $admin = $adminDAO->retrieve($userid);
    
        if ($admin != null && $admin->authenticate($password) ) {
            # after you are sure that the $userid and $password are correct, you can do 
            # generate a secret token for the user based on their username

            $token = generate_token($username);
            $_SESSION['token'] = $token;

            $result = [
                "status" => "success", 
                "token" => $token
            ];

        # return error message if something went wrong 
        } else {
            $result = [
                "status" => "error",
                "message"=> "userid/password invalid"
            ];
        }
} else {
    $result = [
        "status" => "error",
        "messages" => array_values($errors)
    ];

}

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);


?>