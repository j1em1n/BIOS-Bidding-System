<?php

class SectionDAO {

    public  function retrieveAll() {
        $sql = 'SELECT * FROM section ORDER BY course, section ';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = array();

        while($row = $stmt->fetch()) {
            $result[] = new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['min_bid'], $row['vacancies']);
        }

        $stmt = null;
        $conn = null;

        return $result;
    }

    public  function retrieve($coursecode, $section) {
        $sql = 'SELECT * FROM section WHERE course=:coursecode AND section=:section';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':coursecode', $coursecode, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();

        $section = null;
        if($row = $stmt->fetch()) {
            $section = new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['min_bid'], $row['vacancies']);
        }
       
        $stmt = null;
        $conn = null;

        return $section;
    }

    public function removeAll() {
        $sql = 'DELETE FROM section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();

        $stmt = null;
        $conn = null;
    }    

    public function add($section) {
        $sql = 'INSERT INTO section (course, section, day, start, end, instructor, venue, size, min_bid, vacancies) VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size, :minbid, :vacancies)';
        
        $course = $section->getCourse();
        $sectionId = $section->getSection();
        $day = $section->getDay();
        $start = $section->getStart();
        $end = $section->getEnd();
        $instructor = $section->getInstructor();
        $venue = $section->getVenue();
        $size = $section->getSize();
        $minbid = $section->getMinBid();
        $vacancies = $section->getVacancies();

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $sectionId, PDO::PARAM_STR);
        $stmt->bindParam(':day', $day, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $instructor, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
        $stmt->bindParam(':size', $size, PDO::PARAM_INT);
        $stmt->bindParam(':minbid', $minbid, PDO::PARAM_STR);
        $stmt->bindParam(':vacancies', $vacancies, PDO::PARAM_INT);

        $isAddOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isAddOK;
    }

    public function updateVacancies($course, $section, $vacancies) {
        $sql = 'UPDATE section SET vacancies=:vacancies WHERE course=:course AND section=:section';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':vacancies', $vacancies, PDO::PARAM_INT);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isUpdateOK;
    }

    public function updateMinBid($course, $section, $minbid) {
        $sql = 'UPDATE section SET min_bid=:minbid WHERE course=:course AND section=:section';

        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':minbid', $minbid, PDO::PARAM_INT);

        $isUpdateOK = $stmt->execute();

        $stmt = null;
        $conn = null;

        return $isUpdateOK;
    }

    public function getSectionsByCourse($course){

        $sql = 'SELECT section FROM section WHERE course=:course';
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $sections = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sections[] = $row['section'];
        }
       
        $stmt = null;
        $conn = null;

        return $sections;
    }

    public function searchByCourse($searchstr) {
        $sql = "SELECT * FROM section WHERE LOWER(course) LIKE :searchstr";
            
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $searchstr = strtolower($searchstr);
        $searchstr = "%$searchstr%";

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':searchstr', $searchstr, PDO::PARAM_STR);
        $stmt->execute();

        $sections = array();

        while($row = $stmt->fetch()) {
            $sections[] = new Section($row['course'], $row['section'], $row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['min_bid'], $row['vacancies']);
        }
       
        $stmt = null;
        $conn = null;

        return $sections;
    }
}
?>