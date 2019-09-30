<?php

require_once '../include/common.php';
require_once '../include/token.php';


// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('username'), 
            isMissingOrEmpty ('password') ];
$errors = array_filter($errors);


if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "messages" => array_values( $errors)
        ];
}
else{
    $username = $_POST['username'];
    $password = $_POST['password'];

# complete authenticate API

    # check if username and password are right. generate a token and return it in proper json format
    
        $dao = new UserDAO();
        $user = $dao->retrieve($username);
    
        if ( $user != null && $user->authenticate($password) ) {
        # after you are sure that the $username and $password are correct, you can do 
        # generate a secret token for the user based on their username

        $token = generate_token($username);

        # return the token to the user via JSON    
        
        $result = [
            "status" => "success", 
            "token" => $token
        ];
  
        # return error message if something went wrong 


    } else {
        $result = [
            "status" => "error",
            "message"=> "username/password invalud"
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}

 
?>