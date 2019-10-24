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

function printSuccess() {
    if(isset($_SESSION['success'])){
        echo "<h2 id='success' style='color:DarkGreen;'>
        {$_SESSION['success']}
        </h2>";   
        unset($_SESSION['success']);
    }    
}

function numToDay($n, $format) {
    $days_full = [
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
        '6' => 'Saturday',
        '7' => 'Sunday'
    ];
    $days_short = [
        '1' => 'Mon',
        '2' => 'Tues',
        '3' => 'Wed',
        '4' => 'Thurs',
        '5' => 'Fri',
        '6' => 'Sat',
        '7' => 'Sun'
    ];
    if ($format == 'full') {
        return $days_full[$n];  
    } elseif ($format == 'short') {
        return $days_short[$n];
    }
}

function printSectionInfo($sections) {
    echo "<table>
    <tr>
        <th>Course</th>
        <th>Section</th>
        <th>Day</th>
        <th>Start</th>
        <th>End</th>
        <th>Instructor</th>
        <th>Venue</th>
        <th>Size</th>
        <th>Vacancies</th>
        <th>Minimum Bid</th>
        <th>Enter e$</th>
        <th></th>
    </tr>";
    foreach($sections as $section) {
        $code = $section->getCourse();
        $sectId = $section->getSection();
        $day = numToDay($section->getDay(), 'short');
        $start = $section->getStart();
        $end = $section->getEnd();
        $instructor = $section->getInstructor();
        $venue = $section->getVenue();
        $size = $section->getSize();
        $vacancies = $section->getVacancies();
        $minBid = $section->getMinBid();

        echo "<tr>
            <form action='process_placebid.php' method='POST'>
            <td>{$code}<input type='hidden' name='coursecode' value='{$code}'></td>
            <td>{$sectId}<input type='hidden' name='sectionnum' value='{$sectId}'></td>
            <td>{$day}</td>
            <td>{$start}</td>
            <td>{$end}</td>
            <td>{$instructor}</td>
            <td>{$venue}</td>
            <td>{$size}</td>
            <td>{$vacancies}</td>
            <td>{$minBid}</td>
            <td><input type='number' step='.01' name='edollar' style='width=50px'></td>
            <td><input type='submit' value='Place bid'></td>
            </form>
        </tr>";
    }
    echo "</table>";
}