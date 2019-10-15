<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

    $coursecode = $_POST['coursecode'];
    $sectionnum = $_POST['sectionnum'];
    $userid = $_SESSION['userid'];

    // check if round is active
    $roundDAO = new RoundDAO();
    $roundinfo = $roundDAO->retrieveRoundInfo();
    $roundnum = $roundinfo->getRoundNum();
    $roundstatus = $roundinfo->getStatus();
    var_dump($roundstatus);


    //if round is active:
    if($roundstatus == 'opened' ){ // rmb to include round num before push

        // check if user has entered all fields
        if (!empty($coursecode) && !empty($sectionnum)){

            $courseDAO = new CourseDAO();
            $sectionDAO = new SectionDAO();
            $bidDAO = new BidDAO();
            $studentDAO = new StudentDAO();

            // Check if course exists in database
            if(!($courseDAO->retrieve($coursecode))) {
                $_SESSION['errors'][] = "invalid course code";

                header("Location: dropsection.php");
                exit();
            
            
            } else { //course exists
                if (!($sectionDAO->getSectionsByCourse($coursecode))) {
                    // Check if section code is found in section.csv (only for valid course code)
                    $_SESSION['errors'][] = "invalid section";

                    header("Location: dropsection.php");
                    exit();

                } else { // course and section exist
                
                    // now check against USER's BID INFO
                    $student = $studentDAO->retrieve($userid);
                    $currentedollars = $student->getEdollar();
                    $updatedamount = 0.0;
                    // get all bids from this user 
                    $listofbids = $bidDAO->getBidsBySectionStatus($coursecode, $sectionnum, 'enrolled');
                    
                    //assuming that user has more than 1 enrolled course
                    foreach($listofbids as $eachbid){
                        $bidamount = $eachbid->getAmount();
                        $updatedamount = $currentedollars + $bidamount;

                        //update edollars
                        $studentDAO->updateEdollar($userid, $updatedamount);

                        //update student entry in bid table
                        $bidDAO->delete($eachbid);

                        echo "<head></head>
                        <body>
                        <h2>Drop section for Course $coursecode, Section $sectionnum was dropped successfully!</h2>
                        <a href=\"dropbid.php\">Drop another bid | </a>
                        <a href=\"index.php\">Home</a>
                        </body>";
                        
                    }

          
                        
                }
            } 

        } else {
            if(empty($coursecode)) {
                $_SESSION['errors'][] = "blank course code";
            }
            if(empty($sectionnum)) {
                $_SESSION['errors'][] = "blank section number";
            }

            header("Location: dropsection.php");
            exit();
        }
    } else {
            $_SESSION['errors'][] = "There is currently no active round";

            header("Location: dropsection.php");
            exit();
        
    }
?>