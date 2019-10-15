<?php
    require_once 'include/protect.php';
    require_once 'include/common.php';

    $coursecode = $_POST['coursecode'];
    $sectionnum = $_POST['sectionnum'];
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

        } else {
            if (!($sectionDAO->getSectionsByCourse($coursecode))) {
                // Check if section code is found in section.csv (only for valid course code)
                $_SESSION['errors'][] = "invalid section";

                header("Location: dropbid.php");
                exit();

            } else {

                $student = $studentDAO->retrieve($userid);
                $currentedollars = $student->edollar;
                $updatedamount = 0.0;
                // get all bids from this user 
                $listofbids = $bidDAO->retrieveByUserid($userid);
                //var_dump($listofbids);
                var_dump($currentedollars);

                //specifiy which bid want to drop based on userid and coursecode and sectionnum
                //foreach($listofbids as $eachbid){
                    if($listofbids->userid == $userid && $listofbids->code == $coursecode 
                        && $listofbids->section == $sectionnum){
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