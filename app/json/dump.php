<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    $errors = commonValidationsJSON(basename(__FILE__));
    $success = array();
    $result = array();

    //DAO
    $courseDAO = new CourseDAO();
    $allCourses = $courseDAO->retrieveAll();
    
    $studentDAO = new StudentDAO();
    $allStudents = $studentDAO->retrieveAll();

    $prerequisiteDAO = new PrerequisiteDAO();
    $allPrerequisites = $prerequisiteDAO->retrieveAll();

    $sectionDAO = new SectionDAO();
    $allSections = $sectionDAO->retrieveAll();

    $coursecompletedDAO = new CourseCompletedDAO();
    $allCourseCompleted = $coursecompletedDAO->retrieveAll();

    $bidDAO = new BidDAO();
    $allBids = $bidDAO->retrieveAll();
    
    //dump course
    foreach($allCourses as $eachCourse){
        $course_info[] = [ 
            'course' => $eachCourse->getCourse(),
            'school' => $eachCourse->getSchool(),
            'title' => $eachCourse->getTitle(),
            'description' => $eachCourse->getDescription(),
            'exam date' => $eachCourse->getExamDate(),
            'exam start' => $eachCourse->getExamStart(),
            'exam end' => $eachCourse->getExamEnd()
        ];
    }

    //dump student
    foreach($allStudents as $eachStudent){
        $student_info[] = [
            'userid' => $eachStudent->getUserId(),
            'password' => $eachStudent->getPassword(),
            'name' => $eachStudent->getName(),
            'school' => $eachStudent->getSchool(),
            'edollar' => $eachStudent->getEdollar()
        ];
    }

    //dump section
    foreach($allSections as $eachSection){
        $section_info[] = [
            'course'=>$eachSection->getCourse(),
            'section'=>$eachSection->getSection(),
            'day'=>$eachSection->getDay(),
            'start'=>$eachSection->getStart(),
            'end'=>$eachSection->getEnd(),
            'instructor'=>$eachSection->getInstructor(),
            'venue'=>$eachSection->getVenue(),
            'size'=>$eachSection->getSize()
        ];
    }

    //dump pre-req
    foreach($allPrerequisites as $eachPrerequisite){
        $prerequisite_info[] = [
            'course' => $eachPrerequisite->getCourse(),
            'prerequisite' => $eachPrerequisite->getPrerequisite()
        ];
    }

    //dump course_completed
    foreach($allCourseCompleted as $eachCourseCompleted){
        $courseCompleted_info[] = [
            'userid'=> $eachCourseCompleted->getUserid(),
            'course'=> $eachCourseCompleted->getCode()
        ];
    }

    //dump bids
    foreach($allBids as $eachBid){
        $bids_info[] = [
            'userid'=> $eachBid->getUserid(),
            'amount'=> $eachBid->getAmount(),
            'course'=> $eachBid->getCode(),
            'section'=> $eachBid->getSection()
        ];
    }

    //dump section-student
    foreach($allSections as $eachSection){
        foreach($allBids as $eachBid){
            if($eachBid->getStatus() == 'Success'){
                if($eachBid->getCode() == $eachSection->getCourse()){
                    $sectionstudent_info[] = [
                        'userid' => $eachBid->getUserid(),
                        'amount' => $eachBid->getAmount(),
                        'course' => $eachSection->getCourse(),
                        'section' => $eachSection->getSection()
                    ];
                }
            }
        }
    }


    asort($course_info);
    asort($section_info);
    asort($student_info);
    asort($prerequisite_info);
    asort($courseCompleted_info);
    asort($bids_info);
    asort($sectionstudent_info);
    
    if(!empty($course_info) && !empty($student_info) && !empty($prerequisite_info) && !empty($section_info)
            && !empty($courseCompleted_info)){
        $result["status"] = "success";
        $result['course'] = $course_info;
        $result['section'] = $section_info;
        $result['student'] = $student_info;
        $result['prerequisite'] = $prerequisite_info;
        $result['completed-course'] = $courseCompleted_info;
        $result['bid'] = $bids_info;
        $result['section-student'] = $sectionstudent_info;
    } 

    if(isEmpty($errors)){
        $result["status"] = "error";    
    }
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);

?>