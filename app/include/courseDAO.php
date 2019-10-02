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

    public  function retrieve() {
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

    public function removeAll() {
        $sql = 'TRUNCATE TABLE course';
        
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
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $course->getCourse(), PDO::PARAM_STR);
        $stmt->bindParam(':school', $course->getSchool(), PDO::PARAM_STR);
        $stmt->bindParam(':title', $course->getTitle(), PDO::PARAM_STR);
        $stmt->bindParam(':description', $course->getDescription(), PDO::PARAM_STR);
        $stmt->bindParam(':exam_date', $course->getExamDate(), PDO::PARAM_STR);
        $stmt->bindParam(':exam_start', $course->getExamStart(), PDO::PARAM_STR);
        $stmt->bindParam(':exam_end', $course->getExamEnd(), PDO::PARAM_STR);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        $stmt = null;
        $conn = null;
        
        return $isAddOK;
    }

    
}
?>