<?php

class CoursecompletedDAO {

public  function retrieveAll() {
    $sql = 'SELECT * FROM course_completed ORDER BY userid, code';
        
    $connMgr = new ConnectionManager();      
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();

    $result = array();

    while($row = $stmt->fetch()) {
        $result[] = new Coursecompleted($row['userid'], $row['code']);
    }
        
    return $result;
}

?>