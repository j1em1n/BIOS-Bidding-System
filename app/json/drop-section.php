<?php

    require_once '../include/protect.php';
    require_once '../include/common.php';

    // isMissingOrEmpty(...) is in common.php
    $errors = [ isMissingOrEmpty ('userid'), 
                isMissingOrEmpty ('course'),
                isMissingOrEmpty ('section') ];
    $errors = array_filter($errors);

    // if there is no blank/empty field
    if (isEmpty($errors)) {
        $userid = $_POST['userid'];
        $courseCode = $_POST['course'];
        $sectionNum = $_POST['section'];
    
        // Initialise DAOs and objects needed for validations
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();
        $roundDAO = new RoundDAO();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();

        // Check for valid course code
        if (!($courseDAO->retrieve($courseCode))) {
            $errors = ["invalid course"];
        } else {
            if (!($sectionDAO->retrieve($courseCode, $sectionNum))) {
                // Check for valid section (only if course code is valid)
                $errors = ["invalid section"];
            }
        }

        // Check if userid is valid
        if (!($studentDAO->retrieve($userid))){
            $errors = ["invalid userid"];
        }

        // Check for any active round
        if ($currentStatus == "closed"){
            $errors = ["round ended"];
        }

        // If there is active bidding round, (course, userid and section are valid) and round is currently active
        if (isEmpty($errors)){
            // now check against USER's BID INFO
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $updatedamount = 0.0;
            
            $getBid = $bidDAO->retrieve($userid, $courseCode, $sectionNum);
            if ($getBid->getStatus() == "successful"){
                //update student entry in bid table
                $isDeleteOK = $bidDAO->delete($getBid);

                if ($isDeleteOK) {
                    $bidamound = $getBid->getAmount();
                    $updatedamount = $currentedollars + $bidamount;
                    //update edollars
                    $studentDAO->updateEdollar($userid, $updatedamount);

                    $result = [
                        "status" => "success" 
                    ];
                } else {
                    $errors = ["no such section"];
                }
            }
        
        }
        
    }

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