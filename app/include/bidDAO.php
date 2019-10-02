<?php

class BidDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM bid ORDER BY userid, code, section';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;
            
        return $result;
    }

    public  function retrieve() {
        $sql = 'SELECT * FROM bid ORDER BY userid, code, section';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;
            
        return $result;
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE bid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }

    public function add($bid) {
        $sql = 'INSERT INTO bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $bid->getUserid(), PDO::PARAM_STR);
        $stmt->bindParam(':amount', $bid->getAmount(), PDO::PARAM_STR);
        $stmt->bindParam(':code', $bid->getCode(), PDO::PARAM_STR);
        $stmt->bindParam(':section', $bid->getSection(), PDO::PARAM_STR);

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