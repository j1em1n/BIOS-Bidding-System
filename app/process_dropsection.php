<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

   
    $userid = $_SESSION['userid'];

    // check if round is active
    $roundDAO = new RoundDAO();
    $roundinfo = $roundDAO->retrieveRoundInfo();
    $roundnum = $roundinfo->getRoundNum();
    $roundstatus = $roundinfo->getStatus();
    var_dump($roundstatus);


    //if round is active:
        if(isset($_SESSION['code']) && isset($_SESSION['section']) ){      

    

            $courseDAO = new CourseDAO();
            $sectionDAO = new SectionDAO();
            $bidDAO = new BidDAO();
            $studentDAO = new StudentDAO();

            $code= $_SESSION['code'];
            $sectionnum = $_SESSION['section'];
        
                
            // now check against USER's BID INFO
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $updatedamount = 0.0;
            // get all bids from this user 
            $listofbids = $bidDAO->getBidsBySectionStatus($code, $sectionnum, 'successful');
            
            //assuming that user has more than 1 enrolled course
            foreach($listofbids as $eachbid){
                $bidamount = $eachbid->getAmount();
                $updatedamount = $currentedollars + $bidamount;

                //update edollars
                $studentDAO->updateEdollar($userid, $updatedamount);

                //update student entry in bid table
                $isDeleteOK = $bidDAO->delete($eachbid);

                if ($isDeleteOK) {
                    $_SESSION['success'] = "Bid section dropped successfully. You have e$$updatedamount left.";
                    header("Location: index.php");

                    // if the current round is round 2, process bids to get predicted results
                    if ($currentRound == 2) {
                        require_once 'include/round2_bid_processing.php';
                    }

                    exit();
                    
                } else {
                    $_SESSION['errors'][] = "Error: unable to delete section";
                    header("Location: index.php");
                    exit();
                }
            }       
     
            

    
    }
?>