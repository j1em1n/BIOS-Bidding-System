<?php
    require_once '../include/common.php';
    require_once '../include/token.php';

    $errors = commonValidationsJSON(basename(__FILE__));
    $success = array();

    if (!empty($errors)) {
        $result = jsonErrors($errors);
    } else {
        // Retrieve 'r' GET parameter
        $jsonObj = json_decode($_REQUEST['r']);
    
        $userid = $jsonObj->userid;
        $courseCode = $jsonObj->course;
        $sectionNum = $jsonObj->section;
    
        // Initialise DAOs and objects needed for validations
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();
        $roundDAO = new RoundDAO();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();
        $currentNum = $roundDAO->retrieveRoundInfo()->getRoundNum();

        // Check for valid course code
        if (!($courseDAO->retrieve($courseCode))) {
            $errors[] = "invalid course";
        } else {
            if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
                // Check for valid section (only if course code is valid)
                $errors[] = "invalid section";
            }
        }

        // Check if userid is valid
        if (!($studentDAO->retrieve($userid))){
            $errors[] = "invalid userid";
        }

        // Check for any active round
        if ($currentStatus == "closed"){
            $errors[] = "round ended";
        }

        // Check if bid exists only if course, userid and section are valid and round is currently active
        if (empty($errors)) {
            $bid = $bidDAO->retrieve($userid, $courseCode);
            if (!($bid)){
                $errors[] = "no such bid";
            } else {
                $r1Status = $bid->getR1Status();
                if ($r1Status && $r1Status != "Pending"){
                    $errors[] = "no such bid";
                }
            }
        }

        if (empty($errors)){
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $selected_bid = $bidDAO->retrieve($userid, $courseCode);
            $biddedamount = $selected_bid->getAmount();
            $updatedamount =  $currentedollars + $biddedamount;
            $isDeleteOK = $bidDAO->delete($selected_bid);

            if ($isDeleteOK) {
                $studentDAO->updateEdollar($userid, round($updatedamount,2));

                // if the current round is round 2, process bids to get predicted results
                if ($roundDAO->retrieveRoundInfo()->getRoundNum() == 2) {
                    round2Processing(FALSE, FALSE);
                }
                
                $success = [
                    "status" => "success" 
                ];
            } else {
                $errors[] = "error: could not delete bid";
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