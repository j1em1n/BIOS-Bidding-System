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

    function getHashedPassword($username){
        $conn_manager = new ConnectionManager();
        $pdo = $conn_manager->getConnection();
        
        $sql = "select * from admin where userid = :userid";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":userid", $username);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $stmt->execute();
        if($row = $stmt->fetch()){
            
           $hashed_password = $row["password"];

        } else {
            $hashed_password = FALSE;
        }

        $stmt->closeCursor();
        $pdo = null;

        return $hashed_password;
    }

    public  function retrieve($userid) {
        $sql = "SELECT * FROM admin where userid=:userid";
        
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $userid);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result = new Admin($row['userid'], $row['password']);
        }

        $stmt = null;
        $conn = null;
        
        return $result;
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
