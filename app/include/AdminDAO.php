<?php

class AdminDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM admin ORDER BY userid';
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Admin($row['userid'], $row['password']);
        }

        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve($userid) {
        $sql = "SELECT * FROM admin WHERE userid=:userid";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $userid);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $admin = null;
        if($row = $stmt->fetch()) {
            $admin = new Admin($row['userid'], $row['password']);
        }

        $stmt = null;
        $conn = null;
        
        return $admin;
    }
    
    public function removeAll() {
        $sql = 'TRUNCATE TABLE admin';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }  
    
    public function add($admin) {
        $sql = 'INSERT INTO admin (userid, password) VALUES (:userid, :password)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $admin->getUserid(), PDO::PARAM_STR);
        $stmt->bindParam(':password', $admin->getPwd(), PDO::PARAM_STR);


        $isAddOK = $stmt->execute();
        
        $stmt = null;
        $conn = null;

        return $isAddOK;
    }
    
}
?>
