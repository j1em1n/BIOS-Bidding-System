<?php

class RoundDAO {
    public function updateRoundStatus($status_entered){
        $sql = 'UPDATE round SET status=:status_entered';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':status_entered', $status_entered, PDO::PARAM_STR);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }

    public function updateRoundNumber($number_entered){
        $sql = 'UPDATE round SET round_num=:number_entered';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':number_entered', $number_entered, PDO::PARAM_INT);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;
        
        return $isUpdateOK;
    }

    public function retrieveRoundInfo(){
        $sql = 'SELECT * FROM round';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        if($row = $stmt->fetch()) {
            $round = new Round($row['round_num'], $row['status']);
        }

        $stmt = null;
        $conn = null;

        return $round;
    }
}