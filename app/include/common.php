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
    if(isset($_SESSION['errors'])){
        echo "<table border='1'>";  
        echo "<tr>
                <th><img src = 'include/error_icon.png' width = '30' height = '30'>&nbsp;&nbsp;&nbsp;&nbsp;Error</th>";
        foreach ($_SESSION['errors'] as $value) {
            echo "<tr>
            <td style = 'color:red;'>" . $value . "</td>
            </tr>";
        }
        echo "</table>";   
        unset($_SESSION['errors']);
    }    
}

function printErrorsFloat() {    
    if(isset($_SESSION['errors'])){
        echo "<table border='1' style='width:80%; float:left;'>";   
        echo "<tr>
                <th><img src = 'include/error_icon.png' width = '30' height = '30'>&nbsp;&nbsp;&nbsp;&nbsp;Error</th>";
        foreach ($_SESSION['errors'] as $value) {
            echo "<tr>
            <td style = 'color:red;'>" . $value . "</td>
            </tr>";
        }
        echo "</table>";   
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
    if(isset($_SESSION['success'])){
        echo "<table border='1'>";
        echo "<tr>
                <th><img src = 'include/success_icon.jpg' width = '30' height = '30'>&nbsp;&nbsp;&nbsp;&nbsp;Success</th>
            </tr>";
        foreach ($_SESSION['success'] as $value) {
            echo "<tr>
            <td style='color:DarkGreen;'>" . $value . "</td>
            </tr>";
        }
        echo "</table>";
        unset($_SESSION['success']);
    }    
}

function printSuccessFloat() {
    if(isset($_SESSION['success'])){
        echo "<table border='1' style='width:80%; float:left;'>";
        echo "<tr>
                <th><img src = 'include/success_icon.jpg' width = '30' height = '30'>&nbsp;&nbsp;&nbsp;&nbsp;Success</th>
            </tr>";
        foreach ($_SESSION['success'] as $value) {
            echo "<tr>
            <td style='color:DarkGreen;'>" . $value . "</td>
            </tr>";
        }
        echo "</table>";
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

function printSectionInfo($sections, $userid) {
    $courseDAO = new CourseDAO();
    $studentDAO = new StudentDAO();
    $roundDAO = new RoundDAO();
    $roundNum = $roundDAO->retrieveRoundInfo()->getRoundNum();
    $student = $studentDAO->retrieve($userid);
    $school = $student->getSchool();


    echo "<div class='scroll' style='width:70%'>
    <table border='1' style = 'width:100%; font-size:80%'>
    <tr>
        <th>School</th>
        <th>Course</th>
        <th>Section</th>
        <th>Day</th>
        <th>Start</th>
        <th>End</th>
        <th>Instructor</th>
        <th>Venue</th>
        <th>Size</th>";
    if ($roundNum == 2) {
        echo "<th>Vacancies</th>
        <th>Minimum Bid</th>";
    }
    echo "<th>Enter e$</th>
        <th></th>
    </tr>";
    foreach($sections as $section) {
        $code = $section->getCourse();
        $school = $courseDAO->retrieve($code)->getSchool();
        $sectId = $section->getSection();
        $day = numToDay($section->getDay(), 'short');
        $start = $section->getStart();
        $end = $section->getEnd();
        $instructor = $section->getInstructor();
        $venue = $section->getVenue();
        $size = $section->getSize();
        $vacancies = $section->getVacancies();
        $minBid = number_format($section->getMinBid(),2);

        echo "
            <tr>
            <form action='process_placebid.php' method='POST'>
            <td>{$school}</td>
            <td>{$code}<input type='hidden' name='coursecode' value='{$code}'></td>
            <td>{$sectId}<input type='hidden' name='sectionnum' value='{$sectId}'></td>
            <td>{$day}</td>
            <td>{$start}</td>
            <td>{$end}</td>
            <td>{$instructor}</td>
            <td>{$venue}</td>
            <td>{$size}</td>";
        if ($roundNum == 2) {
            echo "<td>{$vacancies}</td>
            <td>{$minBid}</td>";
        }
        echo "<td><input type='number' step='.01' name='edollar' style='width=auto'></td>
            <td><input type='submit' value='Place bid'></td>
            </form>
        </tr>";
    }

    echo "
    </table></div>";
}

//for placebid reminder
function currentBidsTableInPlaceBid($userid) {
    $bidDAO = new BidDAO();
    $courseDAO = new CourseDAO();
    $pending = array();
    $enrolled = array();
    $bids = $bidDAO->retrieveByUserid($userid);
    
    foreach ($bids as $bid) {
        if ($bid->getR1Status() == "Success") {
            $enrolled[] = $bid;
        } elseif ($bid->getR1Status() == "Pending" || $bid->getR2Status()) {
            $pending[] = $bid;
        }
    }
    echo "<table border='1' style = 'width:25%; float: right; font-size:80%'>
    <th colspan = '4' bgcolor='#B7C8B7' ><b>Your current bids<b></th>";

    if (!empty($pending)) {
        printSimplifiedTable($pending);
    } else {
        echo "<tr><td colspan='4'>You have no active bids</td></tr>";
    }
    echo "<th colspan = '4' bgcolor='#B7C8B7' ><b>Your enrolled sections<b></th>";
    if(!empty($enrolled)) {
        printSimplifiedTable($enrolled);
    } else {
        echo "<tr><td colspan='4'>You are not enrolled in any sections</td></tr>";
    }
    echo " <tr>
                <td colspan = '1' style = 'color: red; background-color: white'><b>Note:</b></td>
                <td colspan = '3' style = 'color: red; background-color: white'>Dropping of bid(s)/section(s) only to be done in <b>Home Page</b></td>
            </tr>";

    echo "</table>";
}

function printSimplifiedTable($bids) {
    $courseDAO = new CourseDAO();
    echo "
    <tr>
        <b>
        <th>Course Code</th>
        <th>Course Name</th>
        <th>Section</th>
        <th>Bid amount (e$)</th>
        </b>
    </tr>"; 
    foreach ($bids as $bid) {
        $amount = number_format($bid->getAmount(),2);
        echo "
        <tr>
            <td>{$bid->getCode()}</td>
            <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
            <td>{$bid->getSection()}</td>
            <td>$amount</td>
        </tr>";
    }
}

function currentBidsTable($bids, $roundNum) {
    echo "<h2><b>Your current bids:</b></h2>";
    if (!empty($bids)) {
        $courseDAO = new CourseDAO();
        echo "
            <table border='1'>
            <tr>
                <b>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Section</th>
                <th>Bid amount (e$)</th>
                <th>Result</th>
                <th>Drop</th>
                </b>
            </tr>";
        foreach ($bids as $bid) {
            $code = $bid->getCode();
            $section = $bid->getSection();
            $amount = number_format($bid->getAmount(),2);
            $status = ($roundNum == 1) ? $bid->getR1Status() : $bid->getR2Status();

            echo "
            <tr>
                <form action='process_dropbid.php' method='POST'>
                <td>$code<input type='hidden' name='coursecode' value='$code'></td>
                <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                <td>$section<input type='hidden' name='sectionnum' value='$section'></td>
                <td>$amount</td>
                <td style='background-color: ".highlightBid($status)."'>$status</td>
                <td><input type='submit' value='Drop bid'></td>
                </form>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<table border='1'><tr><td><h3>You have no active bids.</h3></td></tr></table>";
    }
    
}

function bidResultsTable($bids) {
    echo "<h2><b>Bidding Results</b></h2>";
    if(!empty($bids)) {
        $courseDAO = new CourseDAO();
        echo "<table border='1'>
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
            $amount = number_format($bid->getAmount(),2);
            $status = ($bid->getR1Status()) ? $bid->getR1Status() : $bid->getR2Status();

            echo "
            <tr>
                <td>$code</td>
                <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                <td>$section</td>
                <td>$amount</td>
                <td style='background-color: ".highlightBid($status)."'>$status</td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<table border='1'><tr><td><h3>You did not place any bids.</h3></td></tr></table>";
    }
}

function enrolledSectionsTable($bids) {
    $courseDAO = new CourseDAO();
    echo "<h2><b>Your enrolled sections</b></h2>";
    if(!empty($bids)) {
        echo "<table border='1'>
            <tr>
                <b>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Section</th>
                <th>Bid amount (e$)</th>
                <th>Drop</th>
                </b>
            </tr>";
        foreach ($bids as $bid) {
            $code = $bid->getCode();
            $section = $bid->getSection();
            $amount = number_format($bid->getAmount(),2);

            echo "
            <tr>
                <form action='process_dropsection.php' method='POST'>
                <td>$code<input type='hidden' name='coursecode' value='$code'></td>
                <td>{$courseDAO->retrieve($bid->getCode())->getTitle()}</td>
                <td>$section<input type='hidden' name='sectionnum' value='$section'></td>
                <td>$amount</td>
                <td><input type='submit' value='Drop section'></td>
                </form>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<table border='1'><tr><td><h3>You are not enrolled in any courses.</h3></td></tr></table>";
    }
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

function isEmptyString($myStr) {
    if (isset($myStr) && $myStr === "") {
        return TRUE;
    } else {
        return FALSE;
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
                } elseif (isEmptyString($request->{$field})) {
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
        if (($bCourse->getExamDate() == $bidCourse->getExamDate()) && (($bStart < $examEnd) && ($bEnd > $examStart))) {
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
    } elseif (!isNonNegativeInt($edollar)) { //If edollar is a float, check if it has more than 2 decimal places
        $checkedollar = strval($edollar);
        $edollarArr = explode(".", $checkedollar);
        if(strlen($edollarArr[1]) > 2){
            return FALSE;
        }
    }
    return TRUE;
}

function sortBids($bidArray) {
    $amountArray = array();
    foreach($bidArray as $bid) {
        $amountArray[] = (float)($bid->getAmount());
    }
    array_multisort($amountArray, SORT_DESC, $bidArray);
    return $bidArray;
}

function round1Clearing() {
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $studentDAO = new StudentDAO();
    $sections = $sectionDAO->retrieveAll();

    foreach ($sections as $section) {
        $vacancies = $section->getVacancies();
        $courseCode = $section->getCourse();
        $sectionNum = $section->getSection();
        $minBid = $section->getMinBid();

        // System sorts the 'pending' bids from the highest to the lowest
        $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionNum, 1);
        $sectionBids = sortBids($sectionBids);

        // arrays to store (predicted) successful and unsucessful bids
        $successfulBids = [];
        $unsuccessfulBids = [];

        if (!empty($sectionBids)) {
            if (count($sectionBids) >= $vacancies) {
                $clearing = $sectionBids[$vacancies-1]->getAmount();
                $currentPrice = $sectionBids[0]->getAmount();
    
                while ($currentPrice >= $clearing) {
                    $thisBid = $sectionBids[0];
                    $successfulBids[] = $thisBid;
                    array_shift($sectionBids);
                    if (empty($sectionBids)) {
                        break;
                    } else {
                        $currentPrice = $sectionBids[0]->getAmount();
                    }
                }
                $unsuccessfulBids = $sectionBids;
                
                $clearingCount = 0;
                foreach ($successfulBids as $success) {
                    if ($success->getAmount() == $clearing) {
                        $clearingCount++;
                    }
                }
                if ($clearingCount > 1) {
                    while (!empty($successfulBids) && $successfulBids[count($successfulBids)-1]->getAmount() == $clearing) {
                        $unsuccessfulBids[] = array_pop($successfulBids);
                    }
                }
            } else {
                // otherwise, accept all bids
                $successfulBids = $sectionBids;
            }
        }

        foreach($successfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, 1, "Success");
            $vacancies--;
        }

        foreach($unsuccessfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, 1, "Fail");
    
            // if bid is unsuccessful, refund student the full amount
            $refund = $bid->getAmount();
            $studentName = $bid->getUserid();
            $student = $studentDAO->retrieve($studentName);
            $newBalance = $student->getEdollar() + $refund;
            $studentDAO->updateEdollar($studentName, round($newBalance,2));
        }

        // update vacancies for round 2
        $sectionDAO->updateVacancies($courseCode, $sectionNum, $vacancies);
        
    }
}

function round2Processing($updateMinBid, $updateVacancies) {
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $sections = $sectionDAO->retrieveAll();

    foreach ($sections as $section) {
        $vacancies = $section->getVacancies();
        $courseCode = $section->getCourse();
        $sectionNum = $section->getSection();
        $minBid = $section->getMinBid();

        // After every bid, the system sorts the 'pending' bids from the highest to the lowest
        $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionNum, 2);
        $sectionBids = sortBids($sectionBids);

        // arrays to store (predicted) successful and unsucessful bids
        $successfulBids = [];
        $unsuccessfulBids = [];

        if(!empty($sectionBids)) {
            if (count($sectionBids) >= $vacancies) {

                $clearing = $sectionBids[$vacancies-1]->getAmount();
                $currentPrice = $sectionBids[0]->getAmount();
    
                while ($currentPrice >= $clearing) {
                    $thisBid = $sectionBids[0];
                    $successfulBids[] = $thisBid;
                    array_shift($sectionBids);
                    if(empty($sectionBids)) {
                        break;
                    } else {
                        $currentPrice = $sectionBids[0]->getAmount();
                    }
                }
                $unsuccessfulBids = $sectionBids;
    
                if (count($successfulBids) > $vacancies) {
                    while ($successfulBids[count($successfulBids)-1]->getAmount() == $clearing) {
                        $unsuccessfulBids[] = array_pop($successfulBids);
                    }
                }
            } else {
                // otherwise, accept all bids
                $successfulBids = $sectionBids;
            }
        }

        foreach($successfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, 2, "Success");
        }

        foreach($unsuccessfulBids as $bid) {
            $bidDAO->updateBidStatus($bid, 2, "Fail");
        }

        // update the minimum bid
        if ($updateMinBid && !empty($successfulBids) && count($successfulBids) >= $vacancies) {
            $newMinBid = end($successfulBids)->getAmount() + 1;
            if ($minBid < $newMinBid) {
                $sectionDAO->updateMinBid($courseCode, $sectionNum, $newMinBid);
            }
        }

        // update vacancies
        if ($updateVacancies) {
            $enrolled = $bidDAO->getEnrolledBidsBySectionRound($courseCode, $sectionNum, 1);
            $vacancies = $section->getSize() - count($enrolled);
            $sectionDAO->updateVacancies($courseCode, $sectionNum, $vacancies);
        }
    }
}

function round2Clearing() {
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $studentDAO = new StudentDAO();
    $sections = $sectionDAO->retrieveAll();

    foreach ($sections as $section) {
        $vacancies = $section->getVacancies();
        $courseCode = $section->getCourse();
        $sectionNum = $section->getSection();
        $minBid = $section->getMinBid();
        $countSuccess = 0;

        // get all bids - final results will be the same as predicted results, so no need to update results
        $sectionBids = $bidDAO->getSectionBids($courseCode, $sectionNum, 2);

        foreach ($sectionBids as $bid) {
            $status = $bid->getR2Status();
            
            // get failed bids and refund student full amount
            if ($status == "Fail") {
                // if bid is unsuccessful, refund student the full amount
                $refund = $bid->getAmount();
                $studentName = $bid->getUserid();
                $student = $studentDAO->retrieve($studentName);
                $newBalance = $student->getEdollar() + $refund;
                $studentDAO->updateEdollar($studentName, round($newBalance,2));
            } else {
                $countSuccess++;
                $vacancies--;
            }
    
            // update vacancies for good measure?
            $sectionDAO->updateVacancies($courseCode, $sectionNum, $vacancies);
    
        }
    }
}

?>