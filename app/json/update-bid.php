<?php

    require_once '../include/common.php';
    require_once '../include/token.php';


    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ('userid'), 
                isMissingOrEmpty ('amount'),
                isMissingOrEmpty ('course'),
                isMissingOrEmpty ('section') ];
    $errors = array_filter($errors);

    // if there is no blank/empty field
    if (isEmpty($errors)) {
        $userid = $_POST['userid'];
        $edollar = $_POST['amount'];
        $course = $_POST['course'];
        $section = $_POST['section'];

        # complete authenticate API
        
        // Initialise DAOs for validations
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $edollarArr = array();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();

        // Check if e-dollar is numeric
        if (!(isNonNegativeInt($edollar) || isNonNegativeFloat($edollar))) {
            $errors = ["invalid amount"];
        }

        // Check if e-dollar has <= 2 dp
        if (isNonNegativeFloat($edollar)) {
            $checkedEdollar = strval($edollar);
            $edollarArr = explode(".", $checkedEdollar);
            if(count($edollarArr) > 1){
                if(strlen($edollarArr[1]) > 2){
                    $errors = ["invalid amount"];
                }
            }
        }

        // Check for valid course code
        if(!($courseDAO->retrieve($courseCode))) {
            $errors = ["invalid course code"];
        } else {
            if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
                // Check for valid section (only if course code is valid)
                $errors = ["invalid section"];
            }
        }

        // Check for valid userid
        $verifyStud = $studentDAO->retrieve($userid);
        if($verifyStud == null){
            $errors = ["invalid userid"];
        }

        $errors = array_filter($errors);

        // If there is no validation error 
        if(isEmpty($errors)){
            // Initialise the rest of the DAOs and objects needed if data validations are passed
            $prerequisiteDAO = new PrerequisiteDAO();
            $courseCompletedDAO = new CourseCompletedDAO();
            $studentDAO = new StudentDAO();
            $bidDAO = new BidDAO();
            $roundDAO = new RoundDAO();
            $currentRound = $roundDAO->retrieveRoundInfo()->getRoundNum();
            $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();
            $student = $studentDAO->retrieve($userid);
            $studentBids = $bidDAO->retrieveByUserid($userid);
            $course = $courseDAO->retrieve($courseCode);
            $section = $sectionDAO->retrieve($courseCode, $sectionNum);

            // Perform logic validations only if input data validations are passed

            // Check if bid is above min bid
            $minBid = $section->getMinBid();
            if ($edollar < $minBid) {
                $errors = ["bid too low"];
            }

            // Check if student has enough e-dollars
            if ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
                $errors = ["insufficient e$"];
            }

            // Check for class timetable clash
            // Iterate through each of the student's current bids
            foreach ($studentBids as $bid) {
                // Retrieve the section corresponding to the bid
                $bidSection = $sectionDAO->retrieve($bid->getCode(), $bid->getSection());
                // Check if classes are on the same day and if yes, check for timing clashes
                if (($bidSection->getDay() == $section->getDay()) && ($bidSection->getStart() == $section->getStart())) {
                    $errors = ["class timetable clash"];
                }
            }

            // Check for exam timetable clash
            // Iterate through each of the student's current bids
            foreach ($studentBids as $bid) {
                // Retrieve the course corresponding to the bid
                $bidCourse = $courseDAO->retrieve($bid->getCode());
                // Check if exams are on the same date and if yes, check for timing clashes
                if (($bidCourse->getExamDate() == $course->getExamDate()) && ($bidCourse->getExamStart() == $course->getExamStart())) {
                    $errors = ["exam timetable clash"];
                }
            }

            // Check if course has a prerequisite, and if the student has completed it
            if ($prerequisiteDAO->retrieve($courseCode)) {
                $prerequisite = $prerequisiteDAO->retrieve($courseCode);
                if(!($courseCompletedDAO->retrieve($userid, $prerequisite->getPrerequisite()))){
                    $errors = ["incomplete prerequisites"];
                }
            }

            // Check if there is any active round
            if($currentStatus == "closed"){
                $errors = ["round ended"];
            }

            // Check if student has already completed the course
            if ($courseCompletedDAO->retrieve($userid,$courseCode)) {
                $errors = ["course completed"];
            }

            // Check if student already made 5 bids
            if (count($studentBids) == 5) {
                $errors = ["section limit reached"];
            }

            // Check if student has already bidded for / enrolled in this course
            foreach($studentBids as $bid) {
                if ($bid->getCode() == $courseCode && $bid->getStatus() == "Pending") { // already bidded
                    $_SESSION['errors'][] = "You have already bidded for another section in this course";
                } elseif ($bid->getCode() == $courseCode && $bid->getStatus() == "Success") { // already enrolled
                    $errors = ["course enrolled"];
                }
            }

            // If round 1, check if course is offered by student's school
            if ($currentRound == 1 && !($student->getSchool() == $course->getSchool())) {
                $errors = ["not own school course"];
            }

            // If round 2, check if there are vacancies in the section
            if ($currentRound == 2) {
                if (count($bidDAO->getBidsBySectionStatus($courseCode, $sectionNum, "Success")) == $section->getSize()) {
                    $errors = ["no vacancy"];
                }
            }
            // Check if there's any logic error
            if(isEmpty($errors)){

                // Check if student has any existing bid
                $bid = $bidDAO->retrieve($userid, $course, $section);

                if ($bid == null){
                    $newBid = new Bid($userid, $edollar, $course, $section, "Pending");
                    $addResult = $bidDAO->add($newBid);
                    if ($addResult){
                        $result = [
                            "status" => "success" 
                        ];
                    }
                } elseif ($bid->getStatus() == "Pending"){
                    // Update the bid if there's an existing bid and the status is Pending
                    $updateResult = $bidDAO->updateBid($userid, $course, $section);
                    if ($updateResult){
                        $result = [
                            "status" => "success" 
                        ];
                    }   
                } elseif ($bid->getStatus() == "Successful"){
                    // If student already bidded for this course and the bid status is successful. 
                    $errors = ["Student already had successful bid for this course"];
                }


                
            }
        }
    }
    //         } else {
    //             $result = [
    //                 "status" => "error",
    //                 "messages" => array_values($errors)
    //             ];    
    //         }
    //     # return error message if there's validation errors    
    //     } else {
    //         $result = [
    //             "status" => "error",
    //             "messages" => array_values($errors)
    //         ];
    //     }
    // // If there's blank field in any of the field, return error message
    // } else {
    //     $result = [
    //         "status" => "error",
    //         "messages" => array_values($errors)
    //     ];

    // }

    if(!(isEmpty($errors))){
        $final_errors = array_multisort($errors);
        $result = [
            "status" => "error",
            "messages" => array_values($final_errors)
        ];    
    }


    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);


?>


