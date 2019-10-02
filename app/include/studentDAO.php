<?php

class StudentDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM student ORDER BY userid';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }

        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve($userid) {
        $sql = 'SELECT * FROM student ORDER BY userid';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Student($row['userid'], $row['password'], $row['name'], $row['school'], $row['edollar']);
        }

        $stmt = null;
        $conn = null;

        return $result;
    }
    
    public function removeAll() {
        $sql = 'TRUNCATE TABLE student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
        
        $stmt = null;
        $conn = null;
    }  
    
    public function add($student) {
        $sql = 'INSERT INTO student (userid, password, name, school, edollar) VALUES (:userid, :password, :name, :school, :edollar)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $student->getUserid(), PDO::PARAM_STR);
        $stmt->bindParam(':password', $student->getPwd(), PDO::PARAM_STR);
        $stmt->bindParam(':name', $student->getName(), PDO::PARAM_STR);
        $stmt->bindParam(':school', $student->getSchool(), PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $student->getEdollar(), PDO::PARAM_STR);

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