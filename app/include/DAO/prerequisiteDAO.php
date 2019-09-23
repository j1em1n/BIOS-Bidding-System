<?php

class PrerequisiteDAO {

public  function retrieveAll() {
    $sql = 'SELECT * FROM prerequisite ORDER BY course, prerequisite';
        
    $connMgr = new ConnectionManager();      
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();

    $result = array();

    while($row = $stmt->fetch()) {
        $result[] = new Prerequisite($row['course'], $row['prerequisite']);
    }
        
    return $result;
}

?>