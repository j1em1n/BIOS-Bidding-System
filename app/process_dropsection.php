<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

    // Protect against user attempting to access page via url
    if(!(isset($_REQUEST['coursecode']) && isset($_REQUEST['sectionnum']))) {
        header("Location: index.php");
        exit();
    }
   
    $userid = $_SESSION['userid'];
    $coursecode = strtoupper($_POST['coursecode']);
    $sectionnum = strtoupper($_POST['sectionnum']);

    if (!empty($coursecode) && !empty($sectionnum)){
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();
        $roundDAO = new RoundDAO();
        // Check if course exists in database
        if(!($courseDAO->retrieve($coursecode))) {
            $_SESSION['errors'][] = "invalid course code";
            header("Location: index.php");
            exit();
        } elseif (!($sectionDAO->retrieve($coursecode, $sectionnum))) {
            // Check if section code is found in section.csv (only for valid course code)
            $_SESSION['errors'][] = "invalid section";
            header("Location: index.php");
            exit();
        } else {
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $updatedamount = 0.0;
            $selected_bid = $bidDAO->retrieve($userid, $coursecode);
            if ($selected_bid) {
                if ($selected_bid->getR1Status() != "Success") {
                    $_SESSION['errors'][] = "You not currently enrolled in this course.";
                    header("Location: index.php");
                    exit();
                }
                $biddedamount = $selected_bid->getAmount();
                $updatedamount =  strval($currentedollars + $biddedamount);
                $studentDAO->updateEdollar($userid, $updatedamount);
                $isDeleteOK = $bidDAO->delete($selected_bid);
                if ($isDeleteOK) {
                    $_SESSION['success'][] = "Section dropped successfully. You have e$$updatedamount left.";
                    // if the current round is round 2, process bids to get predicted results
                    if ($roundDAO->retrieveRoundInfo()->getRoundNum() == 2) {
                        processBids();
                    }
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['errors'][] = "Error: unable to delete section";
                    header("Location: index.php");
                    exit();
                }
            } else {
                $_SESSION['errors'][] = "You are not enrolled in this section";
                header("Location: index.php");
                exit();
            }
        }
    } else {
        if(empty($coursecode)) {
            $_SESSION['errors'][] = "blank course code";
        }
        if(empty($sectionnum)) {
            $_SESSION['errors'][] = "blank section number";
        }
        header("Location: index.php");
        exit();
    }

?>