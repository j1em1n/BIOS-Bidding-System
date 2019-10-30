<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    $jsonObj = json_decode($_REQUEST['r']);

    $errors = array();

    // if there is no blank/empty field

    if (isEmpty($errors)) {
        $userid = $jsonObj->userid;
        $edollar = $jsonObj->amount;
        $courseCode = $jsonObj->course;
        $sectionNum = $jsonObj->section;

        # complete authenticate API
        
        // Initialise DAOs for validations
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $edollarArr = array();
        $studentDAO = new StudentDAO();

        // Check if e-dollar is numeric
        if(!isNonNegativeFloat($edollar)){
            $errors[] = "invalid amount";
        } else {
            //If edollar is a float, check if it has more than 2 decimal places
            if (!isNonNegativeInt($edollar)) {
                $checkedollar = strval($edollar);
                $edollarArr = explode(".", $checkedollar);
                if(strlen($edollarArr[1]) > 2){
                    $errors[] = "invalid amount";
                }
            } 
        }

        // Check for valid course code
        if(!($courseDAO->retrieve($courseCode))) {
            $errors[] = "invalid course code";
        } else {
            if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
                // Check for valid section (only if course code is valid)
                $errors[] = "invalid section";
            }
        }

        // Check for valid userid
        if(!($studentDAO->retrieve($userid))){
            $errors[] = "invalid userid";
        }

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
                $errors[] = "bid too low";
            }

            // Check if student has enough e-dollars
            if ($edollar > $student->getEdollar()) { // Check if student has enough e-dollars
                $errors[] = "insufficient e$";
            }

            // Check if student has already bidded for this course and update bid if yes
            $alreadyBiddedCourse = FALSE;
            $alreadyEnrolledCourse = FALSE;
            $sameSection = FALSE;
            $previousBid = null;

            foreach($studentBids as $b) {
                if ($b->getCode() == $code) {
                    $alreadyBiddedCourse = TRUE;
                    $previousBid = $b;
                    if ($b->getStatus() == "Success") {
                        $alreadyEnrolledCourse = TRUE;
                    }
                    if ($b->getSection() == $sectionid) {
                        $sameSection = TRUE;
                    }
                }
            }
            
            if ($alreadyEnrolledCourse) {
                $errors[] = "course enrolled";
            } elseif($alreadyBiddedCourse) {
                /* if the student's bid for this course exists, we can assume that:
                    1. There is no exam timetable clash
                    2. The course is offered by the student's school (for round 1 only)
                    3. The student has completed the prerequisites
                    4. The student has not completed the course
                    5. Since the bid is being updated, the student has <= 5 bids, so there is no need to check for the section limit
                */

                // Check for class timetable clash only if student is bidding for another section
                if (!$sameSection) {
                    // Iterate through each of the student's current bids
                    foreach ($studentBids as $b) {
                        // Retrieve the section corresponding to the bid
                        $bSection = $sectionDAO->retrieve($b->getCode(), $b->getSection());
                        // Check if classes are on the same day and if yes, check for timing clashes
                        if (($bSection->getDay() == $bidSection->getDay()) && ($bSection->getStart() == $bidSection->getStart())) {
                            $errors[] = "class timetable clash";
                        }
                    }
                    // If round 2, check if there are vacancies in the section
                    if ($currentRound == 2 && $section->getVacancies() <= 0) {
                        $errors[] = "no vacancy";
                    }
                }
            } else {
                // Check for class timetable clash
                // Iterate through each of the student's current bids
                foreach ($studentBids as $bid) {
                    // Retrieve the section corresponding to the bid
                    $bidSection = $sectionDAO->retrieve($bid->getCode(), $bid->getSection());
                    // Check if classes are on the same day and if yes, check for timing clashes
                    if (($bidSection->getDay() == $section->getDay()) && ($bidSection->getStart() == $section->getStart())) {
                        $errors[] = "class timetable clash";
                    }
                }

                // Check for exam timetable clash
                // Iterate through each of the student's current bids
                foreach ($studentBids as $bid) {
                    // Retrieve the course corresponding to the bid
                    $bidCourse = $courseDAO->retrieve($bid->getCode());
                    // Check if exams are on the same date and if yes, check for timing clashes
                    if (($bidCourse->getExamDate() == $course->getExamDate()) && ($bidCourse->getExamStart() == $course->getExamStart())) {
                        $errors[] = "exam timetable clash";
                    }
                }

                // Check if course has a prerequisite, and if the student has completed it
                if ($prerequisiteDAO->retrieve($courseCode)) {
                    $prerequisite = $prerequisiteDAO->retrieve($courseCode);
                    if(!($courseCompletedDAO->retrieve($userid, $prerequisite->getPrerequisite()))){
                        $errors[] = "incomplete prerequisites";
                    }
                }

                // Check if student has already completed the course
                if ($courseCompletedDAO->retrieve($userid,$courseCode)) {
                    $errors[] = "course completed";
                }

                // Check if student already made 5 bids
                if (count($studentBids) == 5) {
                    $errors[] = "section limit reached";
                }

                // If round 1, check if course is offered by student's school
                if ($currentRound == 1 && !($student->getSchool() == $course->getSchool())) {
                    $errors[] = "not own school course";
                }

                // If round 2, check if there are vacancies in the section
                if ($currentRound == 2 && $section->getVacancies() <= 0) {
                    $errors[] = "no vacancy";
                }
            }

            // Check if there is any active round
            if($currentStatus == "closed"){
                $errors[] = "round ended";
            }

            // Check if there's any logic error
            if(isEmpty($errors)){

                // Check if student has any existing bid
                $bid = $bidDAO->retrieve($userid, $courseCode, $sectionNum);

                if ($bid == null){
                    $newBid = new Bid($userid, $edollar, $courseCode, $sectionNum, "Pending");
                    $addResult = $bidDAO->add($newBid);
                    if ($addResult){
                        $result = [
                            "status" => "success" 
                        ];
                    }
                } elseif ($bid->getStatus() == "Pending"){
                    // Update the bid if there's an existing bid and the status is Pending
                    $updateResult = $bidDAO->updateBid($userid, $courseCode, $sectionNum);
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


