<?php

class StudentDAO {

public  function retrieveAll() {
    $sql = 'SELECT * FROM student ORDER BY userid';
    
        
    $connMgr = new ConnectionManager();      
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();

    $result = array();

    while($row = $stmt->fetch()) {
        $result[] = new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
    }
        
    return $result;
}

?>