<?php

class SectionDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM section ORDER BY course, section ';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size']);
        }
            
        return $result;
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
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

        return $isAddOK;
    }
}
?>