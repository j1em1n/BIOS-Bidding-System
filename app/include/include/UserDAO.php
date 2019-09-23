<?php

class UserDAO {
    
    public  function retrieve($userid) {
        $sql = 'select userid, password, name, school, edollar from student where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User($row['userid'],$row['password'], $row['name'],
                $row['school'], $row['edollar']);
        }
    }

    public  function retrieveAll() {
        $sql = 'select * from user';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();


        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new User($row['userid'], $row['gender'],$row['password'], $row['name']);
        }
        return $result;
    }

   
}

