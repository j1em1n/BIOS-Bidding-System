<?php

class PrerequisiteDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM prerequisite ORDER BY course, prerequisite';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Prerequisite($row['course'], $row['prerequisite']);
        }
        
        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve($coursecode) {
        $sql = 'SELECT * FROM prerequisite where course = :coursecode';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":coursecode", $coursecode);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Prerequisite($row['course'], $row['prerequisite']);
        }
        
        $stmt = null;
        $conn = null;

    }
        
    public function removeAll() {
        $sql = 'TRUNCATE TABLE prerequisite';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
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

        $stmt = null;
        $conn = null;

        return $isAddOK;
    }
}
?>