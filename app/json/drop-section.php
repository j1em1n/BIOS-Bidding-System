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
            $errors[] = "round not active";
        }

        // If there is active bidding round, (course, userid and section are valid) and round is currently active
        if (isEmpty($errors)){
            // now check against USER's BID INFO
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $updatedamount = 0.0;
            
            $getBid = $bidDAO->retrieve($userid, $courseCode);
            if ($getBid->getR1Status() == "Success"){
                //update student entry in bid table
                $isDeleteOK = $bidDAO->delete($getBid);

                if ($isDeleteOK) {
                    $bidamount = $getBid->getAmount();
                    $updatedamount = $currentedollars + $bidamount;
                    //update edollars
                    $studentDAO->updateEdollar($userid, $updatedamount);

                    // if the current round is round 2, process bids to get predicted results
                    if ($roundDAO->retrieveRoundInfo()->getRoundNum() == 2) {
                        round2Processing(FALSE, TRUE);
                    }

                    $success = [
                        "status" => "success" 
                    ];
                } else {
                    $errors[] = "no such section";
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