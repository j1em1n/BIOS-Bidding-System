<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    $errors = commonValidationsJSON(basename(__FILE__));
    $success = array();

    // if there is no blank/empty field
    if (!empty($errors)) {
        $result = jsonErrors($errors);
    } else {
        // Retrieve 'r' GET parameter
        $jsonObj = json_decode($_REQUEST['r']);

        $roundDAO = new RoundDAO();
        $currentRound = $roundDAO->retrieveRoundInfo()->getRoundNum();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();

        // Check if there is any active round
        if($currentStatus == "closed"){
            $errors[] = "round ended";
        } else {
            $userid = $jsonObj->userid;
            $edollar = $jsonObj->amount;
            $courseCode = $jsonObj->course;
            $sectionNum = $jsonObj->section;

            // Initialise DAOs for validations
            $courseDAO = new CourseDAO();
            $sectionDAO = new SectionDAO();
            $edollarArr = array();
            $studentDAO = new StudentDAO();

            // Check if e-dollar is numeric
            if(!isValidEdollar($edollar)){
                $errors[] = "invalid amount";
            } elseif ($edollar < 10.0) {
                // Check if bidding amount >= 10.0
                $errors[] = "invalid amount";
            }


            // Check for valid course code
            if(!($courseDAO->retrieve($courseCode))) {
                $errors[] = "invalid course";
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

            // If there are no field validity errors, do logic validations
            if(isEmpty($errors)){
                // Initialise the rest of the DAOs and objects needed if data validations are passed
                $bidDAO = new BidDAO();
                $courseCompletedDAO = new CourseCompletedDAO();
                $student = $studentDAO->retrieve($userid);
                $course = $courseDAO->retrieve($courseCode);
                $section = $sectionDAO->retrieve($courseCode, $sectionNum);

                // Check if bid is above min bid
                $minBid = $section->getMinBid();
                if ($currentRound == 2 && $edollar < $minBid) {
                    $errors[] = "bid too low";
                }

                // Check if student has already bidded for this course and update bid if yes
                $alreadyBiddedCourse = FALSE;
                $alreadyEnrolledCourse = FALSE;
                $sameSection = FALSE;
                $previousBid = $bidDAO->retrieve($userid, $courseCode);

                if ($previousBid) {
                    $alreadyBiddedCourse = TRUE;
                    if ($previousBid->getSection() == $sectionNum) {
                        $sameSection = TRUE;
                    } if($previousBid->getR1Status() == "Success") {
                        $alreadyEnrolledCourse = TRUE;
                    }
                }
                
                
                if ($alreadyEnrolledCourse) {
                    $errors[] = "course enrolled";
                    $errors[] = "class timetable clash";
                    $errors[] = "exam timetable clash";
                } elseif($alreadyBiddedCourse) {
                    /* if the student has an existing bid for this course, we can assume that:
                        1. There is no exam timetable clash
                        2. The course is offered by the student's school (for round 1 only)
                        3. The student has completed the prerequisites
                        4. The student has not completed the course
                        5. Since the bid is being updated, the student has <= 5 bids, so there is no need to check for the section limit
                    */

                    // Check for class timetable clash only if student is bidding for another section
                    if (!$sameSection) {
                        // Check for class timing clashes
                        if (classClash($userid, $section, $previousBid)) {
                            $errors[] = "class timetable clash";
                        }
                        // If round 2, check if there are vacancies in the section
                        if ($currentRound == 2 && $section->getVacancies() <= 0) {
                            $errors[] = "no vacancy";
                        }
                    }
                    // Check if student has enough e-dollars if they are refunded previous amount
                    $previousAmount = $previousBid->getAmount();
                    if ($edollar > ($student->getEdollar() + $previousAmount)) {
                        $rowErrors[] = "insufficient e$";
                    }
                } else {
                    // Check if student has enough e-dollars
                    if ($edollar > $student->getEdollar()) {
                        $errors[] = "insufficient e$";
                    }

                    // Check for class timetable clash
                    if (classClash($userid, $section)) {
                        $errors[] = "class timetable clash";
                    }

                    // Check for exam timetable clash
                    if (examClash($userid, $course, $previousBid)) {
                        $errors[] = "exam timetable clash";
                    }

                    // Check if course has a prerequisite, and if the student has completed it
                    if (!prereqCompleted($userid,$courseCode)) {
                        $errors[] = "incomplete prerequisites";
                    }

                    // Check if student has already completed the course
                    if ($courseCompletedDAO->retrieve($userid,$courseCode)) {
                        $errors[] = "course completed";
                    }

                    // Check if student already made 5 bids
                    if (count($bidDAO->retrieveByUserid($userid)) == 5) {
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

                if(empty($errors)){
                    $thisStud = $studentDAO->retrieve($userid);
                    if ($alreadyBiddedCourse) {
                        // Store the previously bidded amount
                        $previousAmount = $previousBid->getAmount();
                        // Update the student's previous bid
                        $bidDAO->updateBid($userid, $edollar, $courseCode, $sectionNum);
                        // Refund amount for previous bid and charge edollars for current bid
                        $balance = $thisStud->getEdollar() + $previousAmount - $edollar;
                        $studentDAO->updateEdollar($userid, round($balance,2));
                    } else {
                        $newBid = ($currentRound == 1) ? new Bid($userid, $edollar, $courseCode, $sectionNum, "Pending", null) : new Bid($userid, $edollar, $courseCode, $sectionNum, null, "Pending");
                        $bidDAO->add($newBid);
                        // Deduct amount from student's balance
                        $balance = $thisStud->getEdollar() - $edollar;
                        $studentDAO->updateEdollar($userid, round($balance,2));
                    }

                    // if the current round is round 2, process bids to get predicted results
                    if ($currentRound == 2) {
                        round2Processing(TRUE, FALSE);
                    }

                    $success = [
                        "status" => "success"
                    ];
                }
            }
        }
        if (empty($errors) && !empty($success)) {
            $result = $success;
        } else {
            sort($errors);
            $result = jsonErrors($errors);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>


