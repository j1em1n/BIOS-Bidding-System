<?php

// this will autoload the class that we need in our code
spl_autoload_register(function($class) {
 
    // we are assuming that it is in the same directory as common.php
    // otherwise we have to do
    // $path = 'path/to/' . $class . ".php"    
    require_once "$class.php"; 
  
});

// session related stuff

session_start();

function printErrors() {
    if(isset($_SESSION['errors'])){
        echo "<ul id='errors' style='color:red;'>";
        
        foreach ($_SESSION['errors'] as $value) {
            echo "<li>" . $value . "</li>";
        }
        
        echo "</ul>";   
        unset($_SESSION['errors']);
    }    
}

function isMissingOrEmpty($user) {
    if (!isset($_REQUEST[$user])) {
        return "$user cannot be empty";
    }

    // client did send the value over
    $value = $_REQUEST[$user];
    if (empty($value)) {
        return "$user cannot be empty";
    }
}

# check if an int input is an int and non-negative
function isNonNegativeInt($var) {
    if (is_numeric($var) && $var >= 0 && $var == round($var))
        return TRUE;
}

# check if a float input is is numeric and non-negative
function isNonNegativeFloat($var) {
    if (is_numeric($var) && $var >= 0)
        return TRUE;
}

# this is better than empty when use with array, empty($var) returns FALSE even when
# $var has only empty cells
function isEmpty($var) {
    if (isset($var) && is_array($var))
        foreach ($var as $key => $value) {
            if (empty($value)) {
               unset($var[$key]);
            }
        }

    if (empty($var))
        return TRUE;
}

# Function to check date-time format YYYY-MM-DD
function validateDate($date, $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function padZerosTime($time) {
    if (strlen($time) == 4) {
        $time = '0'.$time;
    }
    return $time;
}

function printSuccess() {
    if(isset($_SESSION['success'])){
        echo "<h2 id='success' style='color:DarkGreen;'>
        {$_SESSION['success']}
        </h2>";   
        unset($_SESSION['success']);
    }    
}