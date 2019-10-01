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
        
    public function removeAll() {
        $sql = 'TRUNCATE TABLE prerequisite';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    

    public function add($prerequisite) {
        $sql = 'INSERT INTO prerequisite (course, prerequisite) VALUES (:course, :prerequisite)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $prerequisite->getCourse(), PDO::PARAM_STR);
        $stmt->bindParam(':prerequisite', $prerequisite->getPrerequisite(), PDO::PARAM_STR);
       
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }
}
?>