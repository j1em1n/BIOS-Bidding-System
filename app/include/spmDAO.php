<?php

class spmDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM is212_spm_data';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new student($row['userid'], $row['name'],
                $row['school'], $row['edollar']); //password?
        }
            
                 
        return $result;
    }
    
    public  function retrieveCourse($course) {
        $sql = 'SELECT course, school, title, description, exam_date, exam_start, exam_end
          FROM course WHERE `course` = :course ORDER BY `course`';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new course($row['course'], $row['school'], $row['title,'], $row['description'],
                $row['exam_date'], $row['exam_start'], $row['exam_end']);
        }
            
                 
        return $result;
    }

    public function retrieveSection(){
        $sql = 'SELECT course, section, day, start, end, instructor, venue, size
         type FROM section';
        
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Section($row['course'], $row['section'],
            $row['day'], $row['start'],
            $row['end'], $row['instructor'],
            $row['venue'], $row['size']
        
        );
        }
        
        return $result;
    }
  
    public  function retrieveSectionByCourse($course) {
        $sql = 'SELECT course, section, day, start, end, instructor, venue, size
         type FROM section WHERE course=:course';
        
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Section($row['course'], $row['section'],
            $row['day'], $row['start'],
            $row['end'], $row['instructor'],
            $row['venue'], $row['size']
        
        );
        }
        
        return $result;
    }
  
  
}