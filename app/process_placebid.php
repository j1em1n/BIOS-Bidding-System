<!DOCTYPE html>
<?php
    require_once "include/common.php";
    require_once "include/process_bids.php";

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

    // Check if e-dollar is numeric
    if (!(isNonNegativeInt($edollar) || isNonNegativeFloat($edollar))) {
        $_SESSION['errors'][] = "Please enter a valid number for E-dollar";
    }

    // Check if e-dollar has <= 2 dp
    if (isNonNegativeFloat($edollar)) {
        $checkedEdollar = strval($edollar);
        $edollarArr = explode(".", $checkedEdollar);
        if(count($edollarArr) > 1){
            if(strlen($edollarArr[1]) > 2){
                $_SESSION['errors'][] = "E-dollar can only have up to 2 decimal places";
            }
        }
    }

    // If inputs do not pass field and data validations, redirect user back to placebid.php immediately
    if (isset($_SESSION['errors'])) {
        header("Location: placebid.php");
        exit();
    }

    // Initialise the rest of the DAOs and objects needed if data validations are passed
    $prerequisiteDAO = new PrerequisiteDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $studentDAO = new StudentDAO();
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $currentRound = $roundDAO->retrieveRoundInfo()->getRoundNum();
    $student = $studentDAO->retrieve($userid);
    $studentBids = $bidDAO->retrieveByUserid($userid);
    $course = $courseDAO->retrieve($courseCode);
    $section = $sectionDAO->retrieve($courseCode, $sectionNum);

    // Perform logic validations only if input data validations are passed

    // Check if bid is above min bid
    $minBid = $section->getMinBid();
    if ($edollar < $minBid) {
        $_SESSION['errors'][] = "Your bid is less than the minimum bid";
    }

    // Check if student has enough e-dollars
    if ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
        $_SESSION['errors'][] = "You do not have enough e-dollars to make this bid";
    }

    // Check for class timetable clash
    // Iterate through each of the student's current bids
    foreach ($studentBids as $bid) {
        // Retrieve the section corresponding to the bid
        $bidSection = $sectionDAO->retrieve($bid->getCode(), $bid->getSection());
        // Check if classes are on the same day and if yes, check for timing clashes
        if (($bidSection->getDay() == $section->getDay()) && ($bidSection->getStart() == $section->getStart())) {
            $_SESSION['errors'][] = "Class timing clashes with {$bid->getCode()} {$bid->getSection()} class";
        }
    }

    // Check for exam timetable clash
    // Iterate through each of the student's current bids
    foreach ($studentBids as $bid) {
        // Retrieve the course corresponding to the bid
        $bidCourse = $courseDAO->retrieve($bid->getCode());
        // Check if exams are on the same date and if yes, check for timing clashes
        if (($bidCourse->getExamDate() == $course->getExamDate()) && ($bidCourse->getExamStart() == $course->getExamStart())) {
            $_SESSION['errors'][] = "Exam clashes with {$bid->getCode()} exam";
        }
    }

    // Check if course has a prerequisite, and if the student has completed it
    if ($prerequisiteDAO->retrieve($courseCode)) {
        $prerequisite = $prerequisiteDAO->retrieve($courseCode);
        if(!($courseCompletedDAO->retrieve($userid, $prerequisite->getPrerequisite()))){
            $_SESSION['errors'][] = "You have not yet completed the prerequisite for this course";
        }
    }

    // Check if student has already completed the course
    if ($courseCompletedDAO->retrieve($userid,$courseCode)) {
        $_SESSION['errors'][] = "You have already completed this course";
    }

    // Check if student already made 5 bids
    if (count($studentBids) == 5) {
        $_SESSION['errors'][] = "You have reached your maximum number of bids";
    }

    // Check if student has already bidded for / enrolled in this course
    foreach($studentBids as $bid) {
        if ($bid->getCode() == $courseCode && $bid->getStatus() == "Pending") { // already bidded
            $_SESSION['errors'][] = "You have already bidded for another section in this course";
        } elseif ($bid->getCode() == $courseCode && $bid->getStatus() == "Success") { // already enrolled
            $_SESSION['errors'][] = "You are already enrolled in this course";
        }
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
        $thisBid = new Bid($userid, $edollar, $courseCode, $sectionNum, "Pending");
        $bidDAO->add($thisBid);
        // Update student's e-dollar balance
        $updatedAmount = $student->getEdollar() - $edollar;
        $studentDAO->updateEdollar($userid, $updatedAmount);

        $_SESSION['success'] = "Your bid for $courseCode {$course->getTitle()}, Section $sectionNum was placed successfully!<br>
        You have $$updatedAmount left in your balance.";

        // if the current round is round 2, process bids to get predicted results
        if ($currentRound == 2) {
            $results = getBiddingResults($section, $currentRound, $bidDAO, $sectionDAO);
            $successful = $results[0];
            $unsuccessful = $results[1];
            foreach($successful as $bid) {
                $bidDAO->updatePredicted($bid, "Success");
            }
            foreach($unsuccessful as $bid) {
                $bidDAO->updatePredicted($bid, "Fail");
            }
        }

        header("Location: placebid.php");
        exit();
        
    }
    
?>