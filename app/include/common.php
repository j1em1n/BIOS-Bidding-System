<?php

// this will autoload the class that we need in our code
spl_autoload_register(function($class) {
 
    // we are assuming that it is in the same directory as common.php
    // otherwise we have to do
    // $path = 'path/to/' . $class . ".php"    
    require_once "$class.php"; 
  
});

// session related stuff

session_start();

function printErrors() {
    echo "<table>";
           
    if(isset($_SESSION['errors'])){
        echo "<ul id='errors' style='color:red;'>";

        echo "<tr>
                <th>Note</th>
            </tr>";

        foreach ($_SESSION['errors'] as $value) {
            
            echo "<tr>
            <td>" . $value . "</td>
            </tr>";
        }
        
        echo "</ul>";   
        unset($_SESSION['errors']);
    }    
}

# check if an int input is an int and non-negative
function isNonNegativeInt($var) {
    if (is_numeric($var) && $var >= 0 && $var == round($var))
        return TRUE;
}

# check if a float input is is numeric and non-negative
function isNonNegativeFloat($var) {
    if (is_numeric($var) && $var >= 0)
        return TRUE;
}

# this is better than empty when use with array, empty($var) returns FALSE even when
# $var has only empty cells
function isEmpty($var) {
    if (isset($var) && is_array($var))
        foreach ($var as $key => $value) {
            if (empty($value)) {
               unset($var[$key]);
            }
        }

    if (empty($var))
        return TRUE;
}

# Function to check date-time format YYYY-MM-DD
function validateDate($date, $format)
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function printSuccess() {

    echo "<table>";
       
    if(isset($_SESSION['success'])){
        echo "<ul id='success' style='color:DarkGreen;'>";

        echo "<tr>
                <th>Note:</th>
            </tr>";

        foreach ($_SESSION['success'] as $value) {

            echo "<tr>
            <td>" . $value . "</td>
            </tr>";
        }
        echo "</ul>";
        unset($_SESSION['success']);
    }    
}

function numToDay($n, $format) {
    $days_full = [
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
        '6' => 'Saturday',
        '7' => 'Sunday'
    ];
    $days_short = [
        '1' => 'Mon',
        '2' => 'Tues',
        '3' => 'Wed',
        '4' => 'Thurs',
        '5' => 'Fri',
        '6' => 'Sat',
        '7' => 'Sun'
    ];
    if ($format == 'full') {
        return $days_full[$n];  
    } elseif ($format == 'short') {
        return $days_short[$n];
    }
}

function printSectionInfo($sections) {
    echo "<table>
    <tr>
        <th>Course</th>
        <th>Section</th>
        <th>Day</th>
        <th>Start</th>
        <th>End</th>
        <th>Instructor</th>
        <th>Venue</th>
        <th>Size</th>
        <th>Vacancies</th>
        <th>Minimum Bid</th>
        <th>Enter e$</th>
        <th></th>
    </tr>";
    foreach($sections as $section) {
        $code = $section->getCourse();
        $sectId = $section->getSection();
        $day = numToDay($section->getDay(), 'short');
        $start = $section->getStart();
        $end = $section->getEnd();
        $instructor = $section->getInstructor();
        $venue = $section->getVenue();
        $size = $section->getSize();
        $vacancies = $section->getVacancies();
        $minBid = $section->getMinBid();

        echo "<tr>
            <form action='process_placebid.php' method='POST'>
            <td>{$code}<input type='hidden' name='coursecode' value='{$code}'></td>
            <td>{$sectId}<input type='hidden' name='sectionnum' value='{$sectId}'></td>
            <td>{$day}</td>
            <td>{$start}</td>
            <td>{$end}</td>
            <td>{$instructor}</td>
            <td>{$venue}</td>
            <td>{$size}</td>
            <td>{$vacancies}</td>
            <td>{$minBid}</td>
            <td><input type='number' step='.01' name='edollar' style='width=50px'></td>
            <td><input type='submit' value='Place bid'></td>
            </form>
        </tr>";
    }
    echo "</table>";
}

function currentBidsTable($bids, $roundNum) {
    $courseDAO = new CourseDAO();
    echo "
        <h2>Your current bids</h2>
        <table border = '1'>
        <tr>
            <b>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Section</th>
            <th>Bid amount (e$)</th>
            <th>Result</th>
            </b>
        </tr>";
    foreach ($bids as $bid) {
        $code = $bid->getCode();
        $section = $bid->getSection();
        $amount = $bid->getAmount();
        $status = ($roundNum == 1) ? $bid->getR1Status() : $bid->getR2Status();

        echo "
        <tr>
            <form action='process_dropbid.php' method='POST'>
            <td>$code<input type='hidden' name='coursecode' value='$code'></td>
            <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
            <td>$section<input type='hidden' name='sectionnum' value='$section'></td>
            <td>{$bid->getAmount()}</td>
            <td style='background-color: ".highlightBid($status)."'>$status</td>
            <td><input type='submit' value='Drop bid'></td>
            </form>
        </tr>";
    }
    echo "</table>";
}

function bidResultsTable($bids) {
    $courseDAO = new CourseDAO();
    echo "
        <h2>Bidding Results</h2>
        <table border = '1'>
        <tr>
            <b>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Section</th>
            <th>Bid amount (e$)</th>
            <th>Result</th>
            <th></th>
            </b>
        </tr>";
    foreach ($bids as $bid) {
        $code = $bid->getCode();
        $section = $bid->getSection();
        $amount = $bid->getAmount();
        $status = ($bid->getR1Status()) ? $bid->getR1Status() : $bid->getR2Status();

        echo "
        <tr>
            <td>$code</td>
            <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
            <td>$section</td>
            <td>{$bid->getAmount()}</td>
            <td style='background-color: ".highlightBid($status)."'>$status</td>
        </tr>";
    }
    echo "</table>";    

}

function enrolledSectionsTable($bids) {
    $courseDAO = new CourseDAO();
    echo "
        <h2>Your enrolled sections</h2>
        <table>
        <tr>
            <b>
            <th>Course Code</th>
            <th>Course Name</th>
            <th>Section</th>
            <th>Bid amount (e$)</th>
            <th></th>
            </b>
        </tr>";
    foreach ($bids as $bid) {
        $code = $bid->getCode();
        $section = $bid->getSection();
        $amount = $bid->getAmount();

        echo "
        <tr>
            <form action='process_dropsection.php' method='POST'>
            <td>$code<input type='hidden' name='coursecode' value='$code'></td>
            <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
            <td>$section<input type='hidden' name='sectionnum' value='$section'></td>
            <td>{$bid->getAmount()}</td>
            <td><input type='submit' value='Drop section'></td>
            </form>
        </tr>";
    }
    echo "</table>";
}

function highlightBid($status) {
    if($status == 'Success'){
        $colour = 'LightGreen';
    } elseif ($status == 'Fail') {
        $colour = 'Salmon';
    } else {
        $colour = 'LemonChiffon';
    }
    return $colour;
}

function deleteFailedBids(){
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $roundNum = $roundDAO->retrieveRoundInfo()->getRoundNum();
    $failedBids = $bidDAO->getFailedBids();
    foreach($failedBids as $bid) {
        $bidDAO->delete($bid);
    }
}

function isMissingOrEmpty($user) {
    if (!isset($_REQUEST[$user])) {
        return "$user cannot be empty";
    }

    // client did send the value over
    $value = $_REQUEST[$user];
    if (empty($value)) {
        return "$user cannot be empty";
    }
}

function commonValidationsJSON($filename) {
    $mandatoryFields = [
		"authenticate.php" => ["password", "username"],
		"bootstrap.php" => ["token"],
		"dump.php" => ["token"],
		"start.php" => ["token"],
		"stop.php" => ["token"],
        "update-bid.php" => ["amount", "course", "section", "token", "userid"],
        "delete-bid.php" => ["course", "section", "token", "userid"],
        "drop-section.php" => ["course", "section", "token", "userid"],
        "user-dump.php" => ["token", "userid"],
        "bid-dump.php" => ["course", "section", "token"],
        "section-dump.php" => ["course", "section", "token"],
        "bid-status.php" => ["course", "section", "token"]
    ];
    $commonValidationErrors = array();
	
	if (array_key_exists($filename, $mandatoryFields)) {
        $fieldsToCheck = $mandatoryFields[$filename];
        $request = null;
        if (isset($_REQUEST['r'])) {
            $request = json_decode($_REQUEST['r']);
        }

		foreach ($fieldsToCheck as $field) {
            if ($field == "token" || $filename == "authenticate.php") {
                if (!isset($_REQUEST[$field])) {
                    $commonValidationErrors[] = "missing $field";
                } elseif (empty($_REQUEST[$field])) {
                    $commonValidationErrors[] = "blank $field";
                } elseif ($field == "token") {
                    $token = $_REQUEST["token"];
                    $isValid = verify_token($token);
                    if (!$isValid || $isValid != "admin") {
                        $commonValidationErrors[] = "invalid token";
                    }
                }
            } elseif ($request) {
                if (!isset($request->{$field})) {
                    $commonValidationErrors[] = "missing $field";
                } elseif (empty($request->{$field})) {
                    $commonValidationErrors[] = "blank $field";
                }
            } else {
                $commonValidationErrors[] = "missing $field";
            }
		}
	}
	
    return $commonValidationErrors;
}

function jsonErrors($errors) {
    $result = [
        "status" => "error",
        "message" => array_values($errors)
    ];
    return $result;
}

function classClash($userid, $bidSection, $exception = null) {
    $bidDAO = new BidDAO();
    $sectionDAO = new SectionDAO();
    $studentBids = $bidDAO->retrieveByUserid($userid);
    $start = DateTime::createFromFormat("G:i", $bidSection->getStart());
    $end = DateTime::createFromFormat("G:i", $bidSection->getEnd());
    foreach ($studentBids as $b) {
        // Retrieve the section corresponding to the bid
        $bSection = $sectionDAO->retrieve($b->getCode(), $b->getSection());
        $bStart = DateTime::createFromFormat("G:i", $bSection->getStart());
        $bEnd = DateTime::createFromFormat("G:i",$bSection->getEnd());
        // Check if classes are on the same day and if yes, check for timing clashes
        if (($bSection->getDay() == $bidSection->getDay()) && (($bStart < $end) && ($bEnd > $start))) {
            if (!($b == $exception)) {
                return $b;
            }
        }
    }
    return FALSE;
}

function examClash($userid, $bidCourse, $exception = null) {
    $bidDAO = new BidDAO();
    $courseDAO = new CourseDAO();
    $studentBids = $bidDAO->retrieveByUserid($userid);
    $examStart = DateTime::createFromFormat("G:i", $bidCourse->getExamStart());
    $examEnd = DateTime::createFromFormat("G:i", $bidCourse->getExamEnd());
    foreach ($studentBids as $b) {
        // Retrieve the course corresponding to the bid
        $bCourse = $courseDAO->retrieve($b->getCode());
        $bStart = DateTime::createFromFormat("G:i", $bCourse->getExamStart());
        $bEnd = DateTime::createFromFormat("G:i", $bCourse->getExamEnd());
        // Check if exams are on the same date and if yes, check for timing clashes
        if (($bCourse->getExamDate() == $bidCourse->getExamDate()) && (($bStart <= $examEnd) && ($bEnd >= $examStart))) {
            if (!($b == $exception)) {
                return $b;
            }
        }
    }
    return FALSE;
}

function prereqCompleted($userid, $coursecode) {
    $prerequisiteDAO = new PrerequisiteDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $prerequisite = $prerequisiteDAO->retrieveByCourse($coursecode);
    if ($prerequisite) {
        foreach($prerequisite as $p) {
            if(!($courseCompletedDAO->retrieve($userid, $p->getPrerequisite()))){
                return FALSE;
            }
        }
    }
    return TRUE;
}

function isValidEdollar($edollar) {
    if(!isNonNegativeFloat($edollar)){
        return FALSE;
    } else {
        //If edollar is a float, check if it has more than 2 decimal places
        if (!isNonNegativeInt($edollar)) {
            $checkedollar = strval($edollar);
            $edollarArr = explode(".", $checkedollar);
            if(strlen($edollarArr[1]) > 2){
                return FALSE;
            }
        } 
    }
    return TRUE;
}

function getBiddingResults($section, $roundNum, $bidDAO, $sectionDAO) {

    $courseCode = $section->getCourse();
    $sectionNum = $section->getSection();
    $minBid = $section->getMinBid();

    // After every bid, the system sorts the 'pending' bids from the highest to the lowest
    $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionNum, $roundNum);

    // arrays to store (predicted) successful and unsucessful bids
    $successfulBids = [];
    $unsuccessfulBids = [];

    $vacancies = $section->getVacancies();
    // all bids can be accommodated if:
    // Round 1 - no. of pending bids < vacancies
    // Round 2 - no. of pending bids <= vacancies

    if (!empty($sectionBids)) {
        if (($roundNum == 1 && count($sectionBids) < $vacancies) || ($roundNum == 2 && count($sectionBids) <= $vacancies)) {
            $successfulBids = $sectionBids;

            // for round 2, if the number of bids equals the number of vacancies, min bid must be updated
            // 'price never goes down', so only update minbid if the lowest bid is higher than the current min bid
            if (count($sectionBids) == $vacancies && $minBid < $sectionBids[$vacancies-1]->getAmount()) {
                $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
                $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
            }
        } else {
            // amount bidded by the nth student, where n = no. of vacancies. This is the clearing price.
            $clearing = $sectionBids[$vacancies-1]->getAmount();
            
            // Round 1: get the (n-1)th bid (first successful bid above the nth bid)
            // if the nth and (n-1)th bids are tied, all bids at clearing price are unsuccessful
            if($roundNum == 1) {
                $above = $sectionBids[$vacancies-2]->getAmount();
            }
            
            // Round 2: get the (n+1)th bid (first unsuccessful bid below the nth bid)
            // if the nth and (n+1)th bids are tied, all bids at clearing price are unsuccessful
            if($roundNum == 2) {
                $below = $sectionBids[$vacancies]->getAmount();
            }
            
            if (($roundNum == 1 && $above == $clearing) || ($roundNum == 2 && $below == $clearing)) {
                foreach ($sectionBids as $bid) {
                    if ($bid->getAmount() > $clearing) {
                        $successfulBids[] = $bid;
                    } else {
                        $unsuccessfulBids[] = $bid;
                    }
                }
            } else {
                // otherwise, bids up to the nth bid can be accommodated and all bids below the clearing price are unsuccessful.
                $successfulBids = array_slice($sectionBids, 0, $vacancies);
                $unsuccessfulBids = array_merge($unsuccessfulBids, array_slice($sectionBids, $vacancies));
            }

            // update the minimum bid for round 2
            $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
            // $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
            if (count($sectionBids) >= $vacancies && $minBid < $sectionBids[$vacancies-1]->getAmount()) {
                $newMinBid = $sectionBids[$vacancies-1]->getAmount() + 1;
                $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
            }
        }
    }
    return [$successfulBids, $unsuccessfulBids];
}

function processBids() {
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $roundDAO = new RoundDAO();
    $round = $roundDAO->retrieveRoundInfo();
    $roundNum = $round->getRoundNum();
    $roundStatus = $round->getStatus();

    $sections = $sectionDAO->retrieveAll();
    $studentDAO = new StudentDAO();
    foreach ($sections as $section) {
        $vacancies = $section->getVacancies();
        $results = getBiddingResults($section, $roundNum, $bidDAO, $sectionDAO);
        $coursecode = $section->getCourse();
        $sectionId = $section->getSection();
        $successfulBids = $results[0];
        $unsuccessfulBids = $results[1];

        if ($roundStatus == "closed") {
            foreach($successfulBids as $bid) {
                $bidDAO->updateBidStatus($bid, $roundNum, "Success");
                $vacancies--;
            }
            
            // update number of vacancies for this section
            $sectionDAO->updateVacancies($coursecode, $sectionId, $vacancies);
        
            foreach($unsuccessfulBids as $bid) {
                $bidDAO->updateBidStatus($bid, $roundNum, "Fail");
        
                // if bid is unsuccessful, refund student the full amount
                $refund = $bid->getAmount();
                $student = $studentDAO->retrieve($bid->getUserid());
                $newBalance = $student->getEdollar() + $refund;
                $studentDAO->updateEdollar($student->getUserid(), $newBalance);
            }
        } else {
            foreach($successfulBids as $bid) {
                $bidDAO->updateBidStatus($bid, $roundNum, "Success");
            } foreach($unsuccessfulBids as $bid) {
                $bidDAO->updateBidStatus($bid, $roundNum, "Fail");
            }
        }
    }
}

function generatePredictedResults($section, $currentRound, $bidDAO, $sectionDAO) {
    $results = getBiddingResults($section, $currentRound, $bidDAO, $sectionDAO);
    $successful = $results[0];
    $unsuccessful = $results[1];
    foreach($successful as $bid) {
        $bidDAO->updatePredicted($bid, "Success");
    }
    foreach($unsuccessful as $bid) {
        $bidDAO->updatePredicted($bid, "Fail");
    }
}
?>