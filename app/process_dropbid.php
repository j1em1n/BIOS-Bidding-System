<?php

    require_once 'include/protect.php';
    require_once 'include/protect_roundclosed.php';
    require_once 'include/process_bids.php';
    require_once 'include/common.php';


    $userid = $_SESSION['userid'];

    // User clicked on Drop Bid on index page
    // Retrieve everything (userid, amount, code, section, status)
    if(isset($_SESSION['code']) && isset($_SESSION['section']) && isset($_SESSION['amount']) ){      

        var_dump($_SESSION['code']);

        $code = $_SESSION['code'];
        $sectionnum = $_SESSION['section'];
        $biddedamount = $_SESSION['amount'];
    
        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();
        $roundDAO = new RoundDAO();

        $section = $sectionDAO->retrieve($code, $sectionnum);
        $student = $studentDAO->retrieve($userid);
        $currentedollars = $student->getEdollar();
        $updatedamount = 0.0;

        $selected_bid = $bidDAO->retrieve($userid, $code, $sectionnum);
            
        $updatedamount =  strval($currentedollars + $biddedamount);
        $studentDAO->updateEdollar($userid, $updatedamount);
        $isDeleteOK = $bidDAO->delete($selected_bid);

        if ($isDeleteOK) {
            $_SESSION['success'] = "Bid dropped successfully. You have e$$updatedamount left.";

            // if the current round is round 2, process bids to get predicted results
            if ($roundDAO->retrieveRoundInfo()->getRoundNum() == 2) {
                $results = getBiddingResults($section, 2, $bidDAO, $sectionDAO);
                $successful = $results[0];
                $unsuccessful = $results[1];
                foreach($successful as $bid) {
                    $bidDAO->updatePredicted($bid, "Success");
                }
                foreach($unsuccessful as $bid) {
                    $bidDAO->updatePredicted($bid, "Fail");
                }
            }
            
            header("Location: index.php");
            exit();
            
        } else {
            $_SESSION['errors'][] = "Error: unable to delete bid";
            header("Location: index.php");
            exit();
        }              
        
    }
?>