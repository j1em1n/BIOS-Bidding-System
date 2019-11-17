<!DOCTYPE html>
<?php
    require_once "include/common.php";
    //require_once "include/process_bids.php";

    $courseCode = $_POST['coursecode'];
    $sectionNum = $_POST['sectionnum'];
    $edollar = $_POST['edollar'];
    $userid = $_SESSION['userid'];

    // Check for empty fields
    if (isEmptyString($courseCode) || isEmptyString($sectionNum) || isEmptyString($edollar)) {
        if (isEmptyString($courseCode)) {
            $_SESSION['errors'][] = "Course code cannot be blank";
        } if (isEmptyString($sectionNum)) {
            $_SESSION['errors'][] = "Section number cannot be blank";
        } if (isEmptyString($edollar)) {
            $_SESSION['errors'][] = "E-dollar cannot be blank";
        }
        header("Location: placebid.php");
        exit();
    }

    // Initialise DAOs for validations
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $edollarArr = array();

    // Check for valid course code
    if(!($courseDAO->retrieve($courseCode))) {
        $_SESSION['errors'][] = "Invalid course code";
    } else {
        if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
            // Check for valid section (only if course code is valid)
            $_SESSION['errors'][] = "Invalid section";
        }
    }

    // Check if is edollar is valid
    if(!isValidEdollar($edollar)){
        $_SESSION['errors'][] = "E-dollar must be a positive number with up to 2 decimal places";
    } elseif ($edollar < 10.0) {
        // Check if bidding amount >= 10.0
        $_SESSION['errors'][] = "E-dollar must be greater than 10.00";
    }

    // If inputs do not pass field and data validations, redirect user back to placebid.php immediately
    if (isset($_SESSION['errors'])) {
        header("Location: placebid.php");
        exit();
    }

    // Initialise the rest of the DAOs and objects needed if data validations are passed
    $studentDAO = new StudentDAO();
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $currentRound = $roundDAO->retrieveRoundInfo()->getRoundNum();
    $student = $studentDAO->retrieve($userid);
    $course = $courseDAO->retrieve($courseCode);
    $section = $sectionDAO->retrieve($courseCode, $sectionNum);

    // Perform logic validations only if input data validations are passed

    // Check if bid is above min bid
    if ($currentRound == 2) {
        $minBid = $section->getMinBid();
        if ($edollar < $minBid) {
            $_SESSION['errors'][] = "Your bid is less than the minimum bid";
        }
    }

    // Check if student has enough e-dollars
    if ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
        $_SESSION['errors'][] = "You do not have enough e-dollars to make this bid";
    }

    // Check for class timetable clash
    $bid = classClash($userid, $section);
    if ($bid) {
        $_SESSION['errors'][] = "Class timing clashes with {$bid->getCode()} {$bid->getSection()} class";
    }

    // Check for exam timetable clash
    $bid = examClash($userid, $course);
    if ($bid) {
        $_SESSION['errors'][] = "Exam clashes with {$bid->getCode()} exam";
    }

    // Check if course has a prerequisite, and if the student has completed it
    if (!prereqCompleted($userid, $courseCode)) {
        $_SESSION['errors'][] = "You have not yet completed the prerequisite for this course";
    }

    // Check if student has already completed the course
    if ($courseCompletedDAO->retrieve($userid,$courseCode)) {
        $_SESSION['errors'][] = "You have already completed this course";
    }

    // Check if student already made 5 bids
    if (count($bidDAO->retrieveByUserid($userid)) == 5) {
        $_SESSION['errors'][] = "You have reached your maximum number of bids";
    }

    // Check if student has already bidded for / enrolled in this course
    $previousBid = $bidDAO->retrieve($userid, $courseCode);
    if ($previousBid && $previousBid->getR1Status() == "Pending") {
        $_SESSION['errors'][] = "You have already bidded for another section in this course, please drop your existing bid before placing a new bid";
    } elseif ($previousBid && $bid->getR1Status() == "Success") { // already enrolled
        $_SESSION['errors'][] = "You are already enrolled in this course, please drop section before placing a new bid";
    }

    // If round 1, check if course is offered by student's school
    if ($currentRound == 1 && !($student->getSchool() == $course->getSchool())) {
        $_SESSION['errors'][] = "You can only bid for courses offered by your school in Bidding Round 1";
    }

    // If round 2, check if there are vacancies in the section
    if ($currentRound == 2) {
        if (!($section->getVacancies() > 0)) {
            $_SESSION['errors'][] = "There are no vacancies left for this section";
        }
    }

    // Check if there are any errors and if yes, redirect to placebid.php
    // Else, display success message
    if(isset($_SESSION['errors'])){
        header("Location: placebid.php");
        exit();
    } else {
        // Create new bid object and add it to database
        $thisBid = ($currentRound == 1) ? new Bid($userid, $edollar, $courseCode, $sectionNum, "Pending", null) : new Bid($userid, $edollar, $courseCode, $sectionNum, null, "Pending");
        $bidDAO->add($thisBid);
        // Update student's e-dollar balance
        $updatedAmount = number_format($student->getEdollar() - $edollar,2);
        $studentDAO->updateEdollar($userid, round($updatedAmount,2));

        $_SESSION['success'][] = "Your bid for $courseCode {$course->getTitle()}, Section $sectionNum was placed successfully!<br>
        You have $$updatedAmount left in your balance.";

        // if the current round is round 2, process bids to get predicted results
        if ($currentRound == 2) {
            round2Processing(TRUE, FALSE);
        }

        header("Location: placebid.php");
        exit();
        
    }
    
?>