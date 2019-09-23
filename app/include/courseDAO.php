<?php

class CourseDAO {

public  function retrieveAll() {
    $sql = 'SELECT * FROM course ORDER BY course';
    
        
    $connMgr = new ConnectionManager();      
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();

    $result = array();

    while($row = $stmt->fetch()) {
        $result[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam_date'], $row['exam_start'], $row['exam_end']);
    }
        
    return $result;
}

?>