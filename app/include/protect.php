<?php
require_once 'token.php';
require_once 'common.php';

$username = '';
if  (isset($_SESSION['userid'])) {
	$username = $_SESSION['userid'];
} #checks if username is valid
else { #user not logged in
	$_SESSION['errors'][] = 'You are not logged in.';
	header("Location: login.php");
	exit;
}

# check if the username session variable has been set 
# send user back to the login page with the appropriate message if it was not

# add your code here 

?>