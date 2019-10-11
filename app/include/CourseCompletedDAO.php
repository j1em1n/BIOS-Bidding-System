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

    public  function retrieveByUserIdAndCode($userid, $coursecode) {
        $sql = 'SELECT * FROM couse_completed where userid = :userid, course = :coursecode';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":userid", $userid);
        $stmt->bindParam(":coursecode", $coursecode);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new CourseCompleted($row['userid'], $row['code']);
        }
        
        $stmt = null;
        $conn = null;

        return $result;
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE course_completed';
        
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
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $courseCompleted->getUserid(), PDO::PARAM_STR);
        $stmt->bindParam(':code', $courseCompleted->getCode(), PDO::PARAM_STR);

        $isAddOK = $stmt->execute();
        return $isAddOK;
    }
}
?>