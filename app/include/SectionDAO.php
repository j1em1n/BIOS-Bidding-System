<?php

class SectionDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM section ORDER BY course, section ';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size']);
        }

        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve() {
        $sql = 'SELECT * FROM section ORDER BY course, section ';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size']);
        }
       
        $stmt = null;
        $conn = null;

    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }    

    public function add($section) {
        $sql = 'INSERT INTO section (course, section, day, start, end, instructor, venue, size) VALUES (:course, :section, :day, :start, :end, instructor, venue, size)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $section->getCourse(), PDO::PARAM_STR);
        $stmt->bindParam(':section', $section->getSection(), PDO::PARAM_STR);
        $stmt->bindParam(':day', $section->getDay(), PDO::PARAM_INT);
        $stmt->bindParam(':start', $section->getStart(), PDO::PARAM_STR);
        $stmt->bindParam(':end', $section->getEnd(), PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $section->getInstructor(), PDO::PARAM_STR);
        $stmt->bindParam(':venue', $section->getVenue(), PDO::PARAM_STR);
        $stmt->bindParam(':size', $section->getSize(), PDO::PARAM_INT);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        $stmt = null;
        $conn = null;

        return $isAddOK;
    }

    public function getSectionsByCourse($course){

        $sql = 'SELECT section FROM section WHERE course=:course';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $sections[] = $row['section'];
        }
       
        $stmt = null;
        $conn = null;
    }
}
?>