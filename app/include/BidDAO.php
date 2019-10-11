<?php

class BidDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM bid ORDER BY userid, code, section';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;
            
        return $result;
    }

    public  function retrieve($userid, $code, $section) {
        $sql = 'SELECT * FROM bid WHERE userid=:userid AND code=:code AND section=:section';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $bid = null;        
        if($row = $stmt->fetch()) {
            $bid = new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;

        return $bid;
    }

    public  function retrieveByUserid($userid) {
        $sql = 'SELECT * FROM bid WHERE userid=:userid';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $bids = array();
        
        while($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }
        $stmt = null;
        $conn = null;

        return $bids;
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
        $userid = $bid->getUserid();
        $amount = $bid->getAmount();
        $code = $bid->getCode();
        $section = $bid->getSection();
        
        $sql = 'INSERT INTO bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isAddOK;
    }

    public function delete($bid){
        $userid = $bid->getUserid();
        $amount = $bid->getAmount();
        $code = $bid->getCode();
        $section = $bid->getSection();

        $sql = 'DELETE FROM bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isDeleteOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isDeleteOK;

    }
    
    public function getBidsByStatus($status){

        $sql = 'SELECT * FROM bid WHERE status=:status';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        $bids = array();

        while ($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }

        $stmt = null;
        $conn = null;

        return $bids;
    }

    public function getBidsBySectionStatus($course, $section, $status){

        $sql = 'SELECT * FROM bid WHERE code=:course AND section=:section AND status=:status';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();

        $bids = array();

        while ($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section']);
        }

        $stmt = null;
        $conn = null;

        return $bids;
    }
}
?>