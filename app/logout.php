<?php
require_once 'include/protect.php';
require_once 'include/common.php';

// unset($_SESSION['userid']);
// unset($_SESSION['success']);
session_destroy();

header("Location: login.php");