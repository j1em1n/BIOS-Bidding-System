<?php
# edit the file included below. the bootstrap logic is there
require_once 'include/bootstrap.php';
$errors = doBootstrap();
if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: bootstrap.php");
    exit();
} else {
    $_SESSION['success'] = "Bootstrap successful! Bidding Round 1 started.";
    header("Location: bootstrap.php");
    exit();
}