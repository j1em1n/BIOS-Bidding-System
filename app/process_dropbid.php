<?php

    require_once 'include/common.php';

    $coursecode = $_POST['coursecode'];
    $sectionnum = $_POST['sectionnum'];
    $userid = $_SESSION['userid'];


    if (!empty($coursecode) && !empty($sectionnum)){

        $courseDAO = new CourseDAO();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();

        // Check if course exists in database
        if(!($courseDAO->retrieve($coursecode))) {
            $_SESSION['errors'][] = "invalid course code";
        } else {
            if (!($sectionDAO->retrieve($coursecode, $sectionnum))) {
                // Check if section code is found in section.csv (only for valid course code)
                $_SESSION['errors'][] = "invalid section";
            } 
        }
        header("Location: dropbid.php");
        exit();
            
        
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