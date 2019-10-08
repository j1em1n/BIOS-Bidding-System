<?php

    $coursecode = $_POST['coursecode'];
    $sectionnum = $_POST['sectionnum'];
    $edollar = $_POST['edollar'];
    $userid = $_SESSION['userid'];

    if (!(empty($coursecode) || empty($sectionnnum) || empty($edollar))){

        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $prerequisiteDAO = new PrerequisiteDAO();
        $studentDAO = new StudentDAO();
        $bidDAO = new BidDAO();
        $roundDAO = new RoundDAO();
        $roundObj = $roundDAO->retrieve();
        $currentRound = $roundObj->getRound();
        $student = $studentDAO->retrieve($userid);

        // Check if edollar is a numeric value
        if (!((is_numeric($edollar) || is_float($edollar)))){
            $_SESSION['errors'][] = "invalid edollar";
        } else {
            // Check that edollar has at most 2dp
            $checkededollar = strval($edollar);
            $edollarArr = explode(".", $checkededollar);
            if(strlen($edollarArr[1]) > 2){
                $_SESSION['errors'][] = "edollar should have maximum 2 decimal places";
            } elseif ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
                $_SESSION['errors'][] = "you do not have enough e-dollars for this bid";
            } elseif ( $edollar < 10.0) { // Check if edollar is greater than minimum value
                $_SESSION['errors'][] = "your bid is less than the minimum bid";
            }
        }


        // Check if course exists in database
        if(!($courseDAO->retrieve($coursecode))) {
            $_SESSION['errors'][] = "invalid course code";
        } else {
            if (!($sectionDAO->retrieve($coursecode, $sectionnum))) {
                // Check if section code is found in section.csv (only for valid course code)
                $_SESSION['errors'][] = "invalid section";
            } else {
                $studentBids = $bidDAO->retrieveByUserid($userid);
                if(count($studentBids) == 5) {
                    $_SESSION['errors'][] = "you already reached the maximum number of bids";
                } else {
                    $alreadyBidded = FALSE;
                    foreach($studentBids as $b) {
                        if ($b->getCode() == $coursecode) {
                            $alreadyBidded == TRUE;
                        }
                    }
                    if ($alreadyBidded) {
                        $_SESSION['errors'][] = "you have already bidded for another section for this course";
                    }
                }
            }
        }
        
        
        
        
        //Check whether current round is 1 or 2
        elseif ($currentRound == 1) {
            // if round == 1, student can only bid for courses from their school
            $courseObj = $courseDAO->retrieve($coursecode);
            if ($student->getSchool() != $courseObj->getSchool()){
                $_SESSION['errors'][] = "please choose a course from your own school";
            } elseif ($prerequisiteDAO->retrieve($coursecode)) { // Check if the course has a prerequisite
                $prereqcourse = $prerequisiteDAO->retrieve($coursecode);
                // Check if the student has completed the prerequisite (row with userid and prerequisite code exists)
                if(!($courseCompletedDAO->retrieveByUserIdAndCode($userid, $prereqcourse->getPrerequisite()))){
                    $_SESSION['errors'][] = "you have not completed the prerequisite course";
                }
            }
        } elseif ($prerequisiteDAO->retrieve($coursecode)) { // Check if the course has a prerequisite
            $prereqcourse = $prerequisiteDAO->retrieve($coursecode);
            // Check if the student has completed the prerequisite (row with userid and prerequisite code exists)
            if(!($courseCompletedDAO->retrieveByUserIdAndCode($userid, $prereqcourse->getPrerequisite()))){
                $_SESSION['errors'][] = "you have not completed the prerequisite course";
            }
        }
        
        if()
        

    } else {
        if(empty($coursecode)) {
            $_SESSION['errors'][] = "blank course code";
        }
        if(empty($sectionnum)) {
            $_SESSION['errors'][] = "blank section number";
        }
        if(empty($edollar)) {
            $_SESSION['errors'][] = "blank e-dollar";
        }
        header("Location: placebid.php");
        exit();
    }
?>