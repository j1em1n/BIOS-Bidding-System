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

    public  function retrieve($userid) {
        $sql = 'select userid, password, name, school, edollar from student where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Student($row['userid'],$row['password'], $row['name'],
                $row['school'], $row['edollar']);
        }
    }
    public function removeAll() {
        $sql = 'TRUNCATE TABLE student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
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

        return $isAddOK;
    }
}
?>