<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

    // Protect against user attempting to access page via url
    if(!(isset($_REQUEST['coursecode']) && isset($_REQUEST['sectionnum']))) {
        header("Location: dropbid.php");
        exit();
    }

    $coursecode = strtoupper($_POST['coursecode']);
    $sectionnum = strtoupper($_POST['sectionnum']);
    $userid = $_SESSION['userid'];


    //if everything is added in successfully
    
    if (!empty($coursecode) && !empty($sectionnum)){

        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $studentDAO = new StudentDAO();

        // Check if course exists in database
        if(!($courseDAO->retrieve($coursecode))) {
            $_SESSION['errors'][] = "invalid course code";
            header("Location: dropbid.php");
            exit();
        } elseif (!($sectionDAO->getSectionsByCourse($coursecode))) {
            // Check if section code is found in section.csv (only for valid course code)
            $_SESSION['errors'][] = "invalid section";
            header("Location: dropbid.php");
            exit();
        } else {
<<<<<<< HEAD
            if (!($sectionDAO->getSectionsByCourse($coursecode))) {
                // Check if section code is found in section.csv (only for valid course code)
                $_SESSION['errors'][] = "invalid section";

                header("Location: dropbid.php");
                exit();

            } else {

                $student = $studentDAO->retrieve($userid);
                $currentedollars = $student->getEdollar();
                $updatedamount = 0.0;
                // get all bids from this user 
                $listofbids = $bidDAO->retrieveByUserid($userid);
                //var_dump($listofbids);
                var_dump($currentedollars);

                //specifiy which bid want to drop based on userid and coursecode and sectionnum
                foreach($listofbids as $eachbid){
                    if($eachbid->userid == $userid && $eachbid->code == $coursecode 
                        && $eachbid->section == $sectionnum && $eachbid->getStatus() == 'pending'){
                        // update edollars by subtraction
                        $biddedamount = $listofbids->amount;                        
                        $updatedamount =  strval($currentedollars - $biddedamount);

                        var_dump($updatedamount);
                        var_dump($userid);

                        // update in database
                        $studentDAO->updateEdollar($userid, $updatedamount) ;
                        
                        header("Location: dropbid.php");
                        exit();
                    }

                

               
            } 
=======
            $student = $studentDAO->retrieve($userid);
            $currentedollars = $student->getEdollar();
            $updatedamount = 0.0;

            if ($bidDAO->retrieve($userid, $coursecode, $sectionnum)) {
                $bid = $bidDAO->retrieve($userid, $coursecode, $sectionnum);
                if ($bid->getStatus() == "Success") {
                    $_SESSION['errors'][] = "You are currently enrolled in this course. To drop this course, please proceed to the Drop Section page instead.";
                    header("Location: dropbid.php");
                    exit();
                }
                $biddedamount = $bid->getAmount();                        
                $updatedamount =  strval($currentedollars + $biddedamount);
                $studentDAO->updateEdollar($userid, $updatedamount);
                $isDeleteOK = $bidDAO->delete($bid);
                if ($isDeleteOK) {
                    $_SESSION['success'] = "Bid dropped successfully. You have e$$updatedamount left.";
                    header("Location: dropbid.php");
                    exit();
                } else {
                    $_SESSION['errors'][] = "Error: unable to delete bid";
                    header("Location: dropbid.php");
                    exit();
                }
            }              
            $_SESSION['errors'][] = "You do not have any bids for this section";
            header("Location: dropbid.php");
            exit();
>>>>>>> 6244b465f531eb94835b7b3de2f65f510c32c0c2
        }
    } else {
        if(empty($coursecode)) {
            $_SESSION['errors'][] = "blank course code";
        }
        if(empty($sectionnum)) {
            $_SESSION['errors'][] = "blank section number";
        }
        header("Location: dropbid.php");
        exit();
    }
?>