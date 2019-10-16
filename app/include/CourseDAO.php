<?php

class CourseDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM course ORDER BY course';
         
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        
        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve($courseid) {
        $sql = "SELECT * FROM course WHERE course=:courseid";
         
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':courseid', $courseid, PDO::PARAM_STR);
        $stmt->execute();

        $course = null;
        if($row = $stmt->fetch()) {
            $course = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        
        $stmt = null;
        $conn = null;
        
        return $course;
    }

    public function removeAll() {
        $sql = 'DELETE FROM course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }    

    public function add($course) {
        $sql = 'INSERT INTO course (course, school, title, description, exam_date, exam_start, exam_end) VALUES (:course, :school, :title, :description, :exam_date, :exam_start, :exam_end)';
        
        $coursecode = $course->getCourse();
        $school = $course->getSchool();
        $title = $course->getTitle();
        $description = $course->getDescription();
        $date = $course->getExamDate();
        $start = $course->getExamStart();
        $end = $course->getExamEnd();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $coursecode, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':exam_date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':exam_start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':exam_end', $end, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isAddOK;
    }

    public function getCoursesBySchool($school){

        $courses = [];
        $sql = 'SELECT * FROM Course WHERE school=:school';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->execute();

        while($row = $stmt->fetch()) {
            $courses[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
        
        $stmt = null;
        $conn = null;

        return $courses;
    }

    
}
?>