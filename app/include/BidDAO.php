<?php

class BidDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM bid ORDER BY amount*1 DESC, userid ASC, section, code';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }
        $stmt = null;
        $conn = null;
            
        return $result;
    }

    public function getSectionBids($code, $section, $roundNum) {
        $sql = "";
        $column = ($roundNum == 1) ? "r1status" : "r2status";
        $sql = "SELECT * FROM bid WHERE code=:code AND section=:section AND $column IS NOT NULL ORDER BY amount*1 DESC, userid ASC";
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":code", $code, PDO::PARAM_STR);
        $stmt->bindParam(":section", $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();
        while($row = $stmt->fetch()) {
            $result[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }
        
        $stmt = null;
        $conn = null;

        return $result;
    }

    public function retrieve($userid, $code) {
        $sql = 'SELECT * FROM bid WHERE userid=:userid AND code=:code';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":code", $code, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $bid = null;        
        if($row = $stmt->fetch()) {
            $bid = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
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
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }
        $stmt = null;
        $conn = null;

        return $bids;
    }

    public function removeAll() {
        $sql = 'DELETE FROM bid';
        
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
        $r1status = $bid->getR1Status();
        $r2status = $bid->getR2Status();
        
        $sql = 'INSERT INTO bid (userid, amount, code, section, r1status, r2status) VALUES (:userid, :amount, :code, :section, :r1status, :r2status)';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':r1status', $r1status, PDO::PARAM_STR);
        $stmt->bindParam(':r2status', $r2status, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isAddOK;
    }

    public function delete($bid){
        $userid = $bid->getUserid();
        $code = $bid->getCode();
        $section = $bid->getSection();

        $sql = 'DELETE FROM bid WHERE userid = :userid AND code=:code AND section=:section';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isDeleteOK = $stmt->execute(); 

        $stmt = null;
        $conn = null;

        return $isDeleteOK;

    }
    
    public function getFailedBids(){
        $sql = "SELECT * FROM bid WHERE r1status='Fail' OR r2status='Fail'";
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $bids = array();

        while ($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }

        $stmt = null;
        $conn = null;

        return $bids;
    }

    public function getSuccessfulBids(){
        $sql = "SELECT * FROM bid WHERE r1status='Success' OR r2status='Success' ORDER BY userid ASC";
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $bids = array();

        while ($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }

        $stmt = null;
        $conn = null;

        return $bids;
    }

    public function getR1EnrolledBidsBySection($course, $section){

        $sql = 'SELECT * FROM bid WHERE code=:course AND section=:section AND r1status="Success" ORDER BY userid ASC';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();

        $bids = array();

        while ($row = $stmt->fetch()) {
            $bids[] = new Bid($row['userid'],$row['amount'], $row['code'], $row['section'], $row['r1status'], $row['r2status']);
        }

        $stmt = null;
        $conn = null;

        return $bids;
    }

    public function updateBid($userid, $biddedAmount, $section){

        $sql = 'UPDATE bid SET amount=:biddedAmount, section=:section WHERE userid=:userid';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':biddedAmount', $biddedAmount, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }

    public function updateBidStatus($bid, $roundNum, $newStatus) {
        $column = ($roundNum == 1) ? "r1status" : "r2status";
        $sql = "UPDATE bid SET $column=:newStatus WHERE userid=:userid AND code=:code";

        $userid = $bid->getUserid();
        $code = $bid->getCode();
        $section = $bid->getSection();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }

    public function updateR2Status($bid, $newStatus) {
        $sql = 'UPDATE bid SET r2status=:newStatus WHERE userid=:userid AND code=:code AND section=:section';

        $userid = $bid->getUserid();
        $code = $bid->getCode();
        $section = $bid->getSection();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':newStatus', $newStatus, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }
}
?>