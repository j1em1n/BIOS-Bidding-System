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
        $sql = 'select userid, password, name, school, edollar from student where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $student = null;
        if($row = $stmt->fetch()) {
            $student = new Student($row['userid'],$row['password'], $row['name'],
                $row['school'], $row['edollar']);
        }
    
        $stmt = null;
        $conn = null;

        return $student;
    }
    
    public function removeAll() {
        $sql = 'DELETE FROM student';
        
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
        
        $userid = $student->getUserid();
        $password = $student->getPassword();
        $name = $student->getName();
        $school = $student->getSchool();
        $edollar = $student->getEdollar();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $edollar, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isAddOK;
    }

    public function updateEdollar($userid, $biddedAmount){

        $sql = 'UPDATE student SET edollar=:biddedAmount WHERE userid=:userid';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':biddedAmount', $biddedAmount, PDO::PARAM_STR);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }

    
}
?>