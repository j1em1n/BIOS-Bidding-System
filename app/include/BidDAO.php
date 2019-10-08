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
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;
    }

    public  function retrieveByUserid($userid) {
        $sql = 'SELECT * FROM bid WHERE userid=:userid';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;
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

    public function delete($bid){

        $sql = 'DELETE FROM bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';
        
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
    
    public function getEnrolledBids($bid){

        $sql = 'SELECT userid, amount, code, section FROM bid WHERE :status = "enrolled"';
        
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