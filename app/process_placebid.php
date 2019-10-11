<?php

    $courseCode = $_POST['coursecode'];
    $sectionNum = $_POST['sectionnum'];
    $edollar = $_POST['edollar'];
    $userid = $_SESSION['userid'];

    // Check for empty fields
    if (empty($courseCode) || empty($sectionNum) || empty($edollar)) {
        if (empty($courseCode)) {
            $_SESSION['errors'][] = "Course code cannot be blank";
        } if (empty($sectionNum)) {
            $_SESSION['errors'][] = "Section number cannot be blank";
        } if (empty($edollar)) {
            $_SESSION['errors'][] = "E-dollar cannot be blank";
        }
        header("Location: placebid.php");
        exit();
    }

    // Initialise DAOs for validations
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();

    // Check for valid course code
    if(!($courseDAO->retrieve($courseCode))) {
        $_SESSION['errors'][] = "Invalid course code";
    } else {
        if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
            // Check for valid section (only if course code is valid)
            $_SESSION['errors'][] = "Invalid section";
        }
    }

    // Check for valid e-dollar

    if (!(is_numeric($edollar) || is_float($edollar))) {
        $_SESSION['errors'][] = "Please enter a valid number for E-dollar";
    } else {
        $checkedEdollar = strval($edollar);
        $edollarArr = explode(".", $checkedEdollar);
        if(strlen($edollarArr[1]) > 2){
            $_SESSION['errors'][] = "E-dollar can only have up to 2 decimal places";
        }
    }

    // If inputs do not pass field and data validations, redirect user back to placebid.php immediately
    if (count($_SESSION['errors'])) {
        header("Location: placebid.php");
    }

    // Initialise the rest of the DAOs and objects needed if data validations are passed
    $prerequisiteDAO = new PrerequisiteDAO();
    $studentDAO = new StudentDAO();
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $currentRound = $roundDAO->retrieve()->getRound();
    $student = $studentDAO->retrieve($userid);
    $studentBids = $bidDAO->retrieveByUserid($userid);
    $course = $courseDAO->retrieve($courseCode);
    $section = $sectionDAO->retrieve($courseCode, $sectionNum);

    // Perform logic validations only if input data validations are passed

    // Check if bid is above min bid
    if (($currentRound == 1 && $edollar < 10.0) || ($currentRound == 2 && $edollar < $minBid)) {
        $_SESSION['errors'][] = "Your bid is less than the minimum bid";
    }

    // Check if student has enough e-dollars
    if ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
        $_SESSION['errors'][] = "You do not have enough e-dollars to make this bid";
    }

    // Check for class timetable clash

    // Check for exam timetable clash

    // Check if course has a prerequisite, and if the student has completed it
    if ($prerequisiteDAO->retrieve($courseCode)) {
        $prerequisite = $prerequisiteDAO->retrieve($courseCode);
        if(!($courseCompletedDAO->retrieveByUserIdAndCode($userid, $prerequisite->getPrerequisite()))){
            $_SESSION['errors'][] = "You have not yet completed the prerequisite for this course";
        }
    }

    // Check if student has already completed the course

    // Check if student is already enrolled in the course
    
    // Check if student already made 5 bids
    if (count($studentBids) == 5) {
        $_SESSION['errors'][] = "You already reached your maximum number of bids";
    }

    // Check if student has already bidded for this course
    $alreadyBidded = FALSE;
    foreach($studentBids as $b) {
        if ($b->getCode() == $courseCode) {
            $alreadyBidded == TRUE;
        }
    }
    if ($alreadyBidded) {
        $_SESSION['errors'][] = "You have already bidded for another section in this course";
    }

    // If round 1, check if course is offered by student's school
    if ($currentRound == 1 && !($student->getSchool() == $course->getSchool())) {
        $_SESSION['errors'][] = "You can only bid for courses offered by your school in Bidding Round 1";
    }

    // If round 2, check if there are vacancies in the section
?>