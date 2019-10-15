<?php

class CourseCompletedDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM course_completed ORDER BY userid, code';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new CourseCompleted($row['userid'], $row['code']);
        }

        $stmt = null;
        $conn = null;
            
        return $result;
    }

    public  function retrieve($userid, $coursecode) {
        $sql = 'SELECT * FROM course_completed WHERE userid = :userid AND code = :coursecode';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":coursecode", $coursecode, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = null;
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new CourseCompleted($row['userid'], $row['code']);
        }
        
        $stmt = null;
        $conn = null;

        return $result;
    }

    public function removeAll() {
        $sql = 'DELETE FROM course_completed';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }    

    public function add($courseCompleted) {
        $sql = 'INSERT INTO course_completed (userid, code) VALUES (:userid, :code)';
        
        $userid = $courseCompleted->getUserid();
        $code = $courseCompleted->getCode();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();
        return $isAddOK;
    }
}
?>