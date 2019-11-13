<?php

    require_once '../include/common.php';
    require_once '../include/token.php';

    $errors = commonValidationsJSON(basename(__FILE__));
    $success = array();
    $result = array();

    if (!empty($errors)) {
            $result = jsonErrors($errors);
    } else {
        //DAO
        $courseDAO = new CourseDAO();
        $allCourses = $courseDAO->retrieveAll();
        $courseDump = array();
        
        $studentDAO = new StudentDAO();
        $allStudents = $studentDAO->retrieveAll();
        $studentDump = array();

        $prerequisiteDAO = new PrerequisiteDAO();
        $allPrerequisites = $prerequisiteDAO->retrieveAll();
        $prerequisiteDump = array();

        $sectionDAO = new SectionDAO();
        $allSections = $sectionDAO->retrieveAll();
        $sectionDump = array();

        $coursecompletedDAO = new CourseCompletedDAO();
        $allCourseCompleted = $coursecompletedDAO->retrieveAll();
        $courseCompletedDump = array();

        $bidDAO = new BidDAO();
        $allBids = $bidDAO->retrieveAll();
        $bidDump = array();
        $sectionStudentDump = array();

        $roundDAO = new RoundDAO();
        $currentStatus = $roundDAO->retrieveRoundInfo()->getStatus();
        $currentNum = $roundDAO->retrieveRoundInfo()->getRoundNum();
        
        //dump course
        //sort courses by course code a) alphabetically and b) numerically
        usort($allCourses, function ($a, $b) {
            return strnatcmp($a->getCourse(), $b->getCourse());
        });

        foreach($allCourses as $eachCourse){
            $courseDump[] = [ 
                'course' => $eachCourse->getCourse(),
                'school' => $eachCourse->getSchool(),
                'title' => $eachCourse->getTitle(),
                'description' => $eachCourse->getDescription(),
                'exam date' => $eachCourse->getExamDate(),
                'exam start' => str_replace(":","",$eachCourse->getExamStart()),
                'exam end' => str_replace(":","",$eachCourse->getExamEnd())
            ];
        }
        

        //dump student

        foreach($allStudents as $eachStudent){
            $studentDump[] = [
                'userid' => $eachStudent->getUserId(),
                'password' => $eachStudent->getPassword(),
                'name' => $eachStudent->getName(),
                'school' => $eachStudent->getSchool(),
                'edollar' => (float)($eachStudent->getEdollar())
            ];
        }

        //dump section

        $sectionsByCourse = array();
        foreach($allCourses as $course) {
            $courseCode = $course->getCourse();
            $sections = $sectionDAO->getSectionsByCourse($courseCode);
            if ($sections) {
                usort($sections, function ($a, $b) {
                    return strnatcmp($a->getCourse(), $b->getCourse());
                });
                $sectionsByCourse[$courseCode] = array();

                foreach($sections as $eachSection) {
                    $sectionsByCourse[$courseCode][] = [
                        'course' => $eachSection->getCourse(),
                        'section' => $eachSection->getSection(),
                        'day' => numToDay($eachSection->getDay(),"full"),
                        'start' => str_replace(":","",$eachSection->getStart()),
                        'end' => str_replace(":","",$eachSection->getEnd()),
                        'instructor' => $eachSection->getInstructor(),
                        'venue' => $eachSection->getVenue(),
                        'size' => (int)($eachSection->getSize())
                    ];
                }
            }
        }

        array_multisort(array_keys($sectionsByCourse), SORT_NATURAL, $sectionsByCourse);
        foreach($sectionsByCourse as $courseCode => $sections) {
            foreach($sections as $section_info) {
                $sectionDump[] = $section_info;
            }
        }

        //dump pre-req
        $prereqsByCourse = array();

        foreach($allCourses as $course) {
            $courseCode = $course->getCourse();
            $prerequisites = $prerequisiteDAO->retrieveByCourse($courseCode);
            if ($prerequisites) {
                usort($prerequisites, function ($a, $b) {
                    return strnatcmp($a->getCourse(), $b->getCourse());
                });
                if (!array_key_exists($courseCode, $prereqsByCourse)) {
                    $prereqsByCourse[$courseCode] = array();
                }
                foreach($prerequisites as $eachPrereq) {
                    $prereqsByCourse[$courseCode][] = [
                        'course' => $eachPrereq->getCourse(),
                        'prerequisite' => $eachPrereq->getPrerequisite()
                    ];
                }
            }
        }

        array_multisort(array_keys($prereqsByCourse), SORT_NATURAL, $prereqsByCourse);
        foreach($prereqsByCourse as $courseCode => $prereqs) {
            foreach($prereqs as $prereq_info) {
                $prereqDump[] = $prereq_info;
            }
        }

        //dump course_completed
        $completedByCourse = array();

        foreach($allCourseCompleted as $completed) {
            $courseCode = $completed->getCode();
            if(!array_key_exists($courseCode, $completedByCourse)){
                $completedByCourse[$courseCode] = array();
            }
            $completedByCourse[$courseCode][] = [
                "userid" => $completed->getUserid(),
                "course" => $courseCode
            ];
        }

        array_multisort(array_keys($completedByCourse), SORT_NATURAL, $completedByCourse);
        foreach($completedByCourse as $courseCode => $completed) {
            foreach($completed as $courseCompleted_info) {
                $courseCompletedDump[] = $courseCompleted_info;
            }
        }

        //dump bids
        $allBidsByCodeSection = array();
        foreach($allSections as $section) {
            $sectionid = $section->getSection();
            $courseCode = $section->getCourse();
            $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionid, $currentNum);
            if($sectionBids) {
                if(!array_key_exists($courseCode, $allBidsByCodeSection)){
                    $allBidsByCodeSection[$courseCode] = array();
                } if(!array_key_exists($sectionid, $allBidsByCodeSection[$courseCode])){
                    $allBidsByCodeSection[$courseCode][$sectionid] = array();
                }
                foreach($sectionBids as $bid) {
                    $allBidsByCodeSection[$courseCode][$sectionid][] = $bid;
                }
                //var_dump($allBidsByCodeSection[$courseCode][$sectionid]);
                //$allBidsByCodeSection[$courseCode][$sectionid] = sortBids($allBidsByCodeSection[$courseCode][$sectionid]);
            }
        }

        array_multisort(array_keys($allBidsByCodeSection), SORT_NATURAL, $allBidsByCodeSection);
        foreach($allBidsByCodeSection as $coursecode => $sectionnum) {
            $sections = $sectionnum;
            array_multisort(array_keys($sections), SORT_NATURAL, $sections);
            foreach($sections as $sectionbids) {
                foreach($sectionbids as $bid) {
                    $bidDump[] = [
                        "userid" => $bid->getUserid(),
                        "amount" => (float)($bid->getAmount()),
                        "course" => $bid->getCode(),
                        "section" => $bid->getSection()
                    ];
                }
            }
        }

        //dump section-student

        $successfulBids = $bidDAO->getSuccessfulBids();
        $successfulByCourse = array();
        foreach ($successfulBids as $bid) {
            $courseCode = $bid->getCode();
            if(!array_key_exists($courseCode, $successfulByCourse)){
                $successfulByCourse[$courseCode] = array();
            }
            $successfulByCourse[$courseCode][] = [
                "userid" => $bid->getUserid(),
                "course" => $courseCode,
                "section" => $bid->getSection(),
                "amount" => (float)($bid->getAmount())
            ];
        }
        array_multisort(array_keys($successfulByCourse), SORT_NATURAL, $successfulByCourse);
        foreach($successfulByCourse as $courseCode => $successes) {
            foreach($successes as $bid) {
                $sectionStudentDump[] = $bid;
            }
        }
        
        $result = [
            "status" => "success",
            "course" => $courseDump,
            "section" => $sectionDump,
            "student" => $studentDump,
            "prerequisite" => $prerequisiteDump,
            "bid" => $bidDump,
            "completed-course" => $courseCompletedDump,
            "section-student" => $sectionStudentDump
        ];
    }

    // if(isEmpty($errors)){
    //     $result["status"] = "error";    
    // }
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION+JSON_PRETTY_PRINT);

?>