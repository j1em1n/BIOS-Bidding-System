<?php

class RoundDAO {
    public function updateRoundStatus($status_entered, $round_num){
        $sql = 'UPDATE round SET status=:status_entered WHERE round_num=:round_num';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':status_entered', $status_entered, PDO::PARAM_STR);
        $stmt->bindParam(':round_num', $round_num, PDO::PARAM_INT);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        $stmt = null;
        $conn = null;
        
        return $isAddOK;
    }

}