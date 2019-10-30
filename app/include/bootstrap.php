<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$student_processed = 0;
	$course_processed = 0;
	$section_processed = 0;
	$prerequisite_processed = 0;
	$course_completed_processed = 0;
	$bid_processed = 0;

	# check file size
	if ($_FILES["bootstrap-file"]["size"] <= 0){
		$message = "input files not found";
		echo "<script type='text/javascript'>alert('$message');</script>";
	} else {
		
		$zip = new ZipArchive;
		$res = $zip->open($zip_file);

		if ($res === TRUE) {
			$zip->extractTo($temp_dir);
			$zip->close();
		
			$student_path = "$temp_dir/student.csv";
			$section_path = "$temp_dir/section.csv";
			$course_path = "$temp_dir/course.csv";
			$course_completed_path = "$temp_dir/course_completed.csv";
			$prerequisite_path = "$temp_dir/prerequisite.csv";
			$bid_path = "$temp_dir/bid.csv";

			$student = @fopen($student_path, "r");
			$section = @fopen($section_path, "r");
			$course = @fopen($course_path, "r");
			$course_completed = @fopen($course_completed_path, "r");
			$prerequisite = @fopen($prerequisite_path, "r");
			$bid = @fopen($bid_path, "r");

			if (empty($student) || empty($section) || empty($course) || empty($course_completed) || empty($prerequisite) || empty($bid) ){
				$message = "input files not found";
				echo "<script type='text/javascript'>alert('$message');</script>";
				if (!empty($student)){
					fclose($student);
					@unlink($student_path);
				} 
				
				if (!empty($section)) {
					fclose($section);
					@unlink($section_path);
				}
				
				if (!empty($course)) {
					fclose($course);
					@unlink($course_path);
				}
				
				if (!empty($course_completed)) {
					fclose($course_completed);
					@unlink($course_completed);
				}
				
				if (!empty($prerequisite)) {
					fclose($prerequisite);
					@unlink($prerequisite_path);
				}

				if (!empty($bid)) {
					fclose($bid);
					@unlink($bid_path);
				}
			}
			else {
				$connMgr = new ConnectionManager();
				$conn = $connMgr->getConnection(); 

				# start processing
				# truncate current SQL tables

                $prerequisiteDAO = new PrerequisiteDAO();
                $prerequisiteDAO->removeAll();
                
                $courseCompletedDAO = new CourseCompletedDAO();
                $courseCompletedDAO->removeAll();

                $bidDAO = new BidDAO();
				$bidDAO->removeAll();

				$sectionDAO = new SectionDAO();
                $sectionDAO->removeAll();
				
				$studentDAO = new StudentDAO();
                $studentDAO->removeAll();

                $courseDAO = new CourseDAO();
                $courseDAO->removeAll();
				
				// STUDENT 

				// Skip table headings
				$data = fgetcsv($student); 
				$countStud = 1;
				$errors["student.csv"] = array();

				//An indexed array to store all the existing userid in, to check for duplicate userid
				$checkDupUserId = array();
				while ( ($data = fgetcsv($student) ) !== false){
					
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userId = trim($data[0]);
					$pwd = trim($data[1]);
					$name = trim($data[2]);
					$school = trim($data[3]);
					$edollar = trim($data[4]);
					//Check for any empty field 
					if (empty($userId) || empty($pwd) || empty($name) || empty($school) || empty($edollar)) {
						//pass the line in the file 
						//Print out all the errors for user
						if(empty($data[0])){
							$rowErrors[] = "blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank password";
						}
						if(empty($data[2])){
							$rowErrors[] = "blank name";
						}
						if(empty($data[3])){
							$rowErrors[] = " blank school";
						}
						if(empty($data[4])){
							$rowErrors[] = "blank e-dollar";
						}
					} else {
						//Checking if the userid field is > 128 characters
						if (strlen($userId) > 128) {
							$rowErrors[] = "invalid userid";
						}
						
						// Checking for duplicate user IDs
						if(in_array($userId, $checkDupUserId)){
							$rowErrors[] = "duplicate userid";
						} else {
							$checkDupUserId[] = $userId;
						}
						
						// Check if is edollar is valid
						if(!isValidEdollar($edollar)){
							$rowErrors[] = "invalid e-dollar";
						}
						//Checking if the password field has > 128 characters
						if(strlen($pwd) > 128){
							$rowErrors[] = "invalid password";
						}
						//Checking if the name field has > 100 characters
						if(strlen($name) > 100){
							$rowErrors[] = "invalid name";
						}
					} 
					// Check if row has any errors and if no, create Student object and add to database
					if(!empty($rowErrors)){
						$errors["student.csv"][] = [
							"line" => $countStud,
							"message" => $rowErrors
						];
					} else {
						$studentObj = new Student($userId, $pwd, $name, $school, $edollar);
						$studentDAO->add($studentObj);
						$student_processed++; //Line added successfully	
					}
					$countStud++;
				}

				fclose($student); // close the file handle
				unlink($student_path); // delete the temp file

				// COURSE 

				// Skip table headings
				$data = fgetcsv($course);
				$countCourse = 1;
				$errors["course.csv"] = array();

				while ( ($data = fgetcsv($course) ) !== false){
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string
					$coursecode = trim($data[0]);
					$school = trim($data[1]);
					$title = trim($data[2]);
					$description = trim($data[3]);
					$exam_date = trim($data[4]);
					$exam_start = trim($data[5]);
					$exam_end = trim($data[6]);

					//Check for any empty field 
					if (empty($coursecode) || empty($school) || empty($title) || empty($description) || empty($exam_date) || empty($exam_start) || empty($exam_end)) {
						if(empty($data[0])){
							$rowErrors[] = "blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank school";
						}
						if(empty($data[2])){
							$rowErrors[] = "blank title";
						}
						if(empty($data[3])){
							$rowErrors[] = "blank description";
						}
						if(empty($data[4])){
							$rowErrors[] = "blank exam date";
						}
						if(empty($data[5])){
							$rowErrors[] = "blank exam start";
						}
						if(empty($data[6])){
							$rowErrors[] = "blank exam end";
						}
					} else {
						//Checking if the title field is > 100 characters
						if(strlen($title) > 100){
							$rowErrors[] = "invalid title";
						} 
						//Checking if the description field has > 1000 characters
						if(strlen($description) > 1000){
							$rowErrors[] = "invalid description";
						}
						//Checking if the date field is in ymd format
						if(validateDate($exam_date, "Ymd") == FALSE){
							$rowErrors[] = "invalid exam date";
						}
						
						if (!(validateDate($exam_start, "G:i") && validateDate($exam_end, "G:i"))) {
							//Checking if exam_start is in H:mm format
							if(!validateDate($exam_start, "G:i")){
								$rowErrors[] = "invalid exam start";
							}
							//Checking if exam_end is in H:mm format
							if(!validateDate($exam_end, "G:i")){
								$rowErrors[] = "invalid exam end";
							}
						} else {
							//Check if exam_end is later than exam_start (only if both times are valid)
							$exam_start_datetime = DateTime::createFromFormat("G:i", $exam_start);
							$exam_end_datetime = DateTime::createFromFormat("G:i", $exam_end);
							if($exam_end_datetime < $exam_start_datetime) {
								$rowErrors[] = "invalid exam end";
							}
						}
					}
					//Check if row has any errors and if no, create Course object and add to database
					if(!empty($rowErrors)){
						$errors["course.csv"][] = [
							"line" => $countCourse,
							"message" => $rowErrors
						];
					} else {
						$courseObj = new Course($coursecode, $school, $title, $description, $exam_date, $exam_start, $exam_end);
						$courseDAO->add($courseObj);
						$course_processed++; #line added successfully	
					}
					$countCourse++;
				}

				fclose($course); // close the file handle
				unlink($course_path); // delete the temp file

				// SECTION 

				// Skip table headings
				$data = fgetcsv($section);
				$countSect = 1;
				$errors["section.csv"] = array();

        		while ( ($data = fgetcsv($section) ) !== false){
					
					$rowErrors = array();
          			//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$coursecode = trim($data[0]);
					$sectionid = trim($data[1]);
					$day = trim($data[2]);
					$start = trim($data[3]);
					$end = trim($data[4]);
					$instructor = trim($data[5]);
					$venue = trim($data[6]);
					$size = trim($data[7]);

					//Check for any empty fields
					if (empty($coursecode) || empty($sectionid) || empty($day) || empty($start) || empty($end) || empty($instructor) || empty($venue)|| empty($size)) {
						if(empty($data[0])){
							$rowErrors[] = "blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank section";
						}
						if(empty($data[2])){
							$rowErrors[] = "blank day";
						}
						if(empty($data[3])){
							$rowErrors[] = "blank start time";
						}
						if(empty($data[4])){
							$rowErrors[] = "blank end time";
						}
						if(empty($data[5])){
							$rowErrors[] = "blank instructor";
						}
						if(empty($data[6])){
							$rowErrors[] = "blank venue";
						}
						if(empty($data[7])){
							$rowErrors[] = "blank size";
						}
					} else {
						//check if course is in course.csv
						if(!($courseDAO->retrieve($coursecode))) {
							$rowErrors[] = "invalid course";
						} else {
							//intval returns 0 if the parameter cannot be converted to int successfully.
							$sectionNum = intval(substr($sectionid, 1));
							//check if the first character should be an S followed by a positive numeric number (1-99). Check only if course is valid.
							if($sectionid[0] !== 'S' || !($sectionNum >= 1 && $sectionNum <= 99)){
								$rowErrors[] = "invalid section";
							}
						}

						// Check if the day field is a number between 1(inclusive) and 7 (inclusive). 1 - Monday, 2 - Tuesday, ... , 7 - Sunday.
						if($day < 1 || $day > 7){
							$rowErrors[] = "invalid day";
						} 

						//Checking if start and end are in H:mm format
						if (!(validateDate($start, "G:i") && validateDate($end, "G:i"))) {
							if(!validateDate($start, "G:i")){
								$rowErrors[] = "invalid start";
							} if(!validateDate($end, "G:i")){
								$rowErrors[] = "invalid end";
							}
						} else {
							//Check if exam_end is later than exam_start (only if both times are valid)
							$start_datetime = DateTime::createFromFormat("G:i", $start);
							$end_datetime = DateTime::createFromFormat("G:i", $end);
							if($end_datetime < $start_datetime) {
								$rowErrors[] = "invalid end";
							}
						}

						//Checking if the instructor field > 100 characters using strlen()
						if(strlen($instructor) > 100){
							$rowErrors[] = "invalid instructor";
						}
						//Checking if the venue field has > 100 characters using strlen()
						if(strlen($venue) > 100){
							$rowErrors[] = "invalid venue";
						}
						// Check if the size field is a positive numeric number.
						if(!isNonNegativeInt($size)) {
							$rowErrors[] = "invalid size";
						}
				} 
				if(!empty($rowErrors)){
					$errors["section.csv"][] = [
						"line" => $countSect,
						"message" => $rowErrors
					];
				} else {
					$sectionObj = new Section($coursecode, $sectionid, $day, $start, $end, $instructor, $venue, $size, '10.0', $size);
					$sectionDAO->add($sectionObj);
					$section_processed++; #line added successfully  
				}
				$countSect++;
			}

				fclose($section); // close the file handle
				unlink($section_path); // delete the temp file
				
				// PRE-REQUISITE 

				//Skip table headings
				$data = fgetcsv($prerequisite);
				$countPrereq = 1;
				$errors["prerequisite.csv"] = array();

				while ( ($data = fgetcsv($prerequisite) ) !== false){ 
					
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$coursecode = trim($data[0]);
					$prerequisiteid = trim($data[1]);
				
					//Check for any empty fields
					if(empty($coursecode) || empty($prerequisiteid)) {
						if(empty($data[0])){
							$rowErrors[] = "blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank prerequisite";
						}
					} else {
						// Check if course code is found in the course.csv
						// Check if prerequisite course code is found in the course.csv

						if(!($courseDAO->retrieve($coursecode))) {
							$rowErrors[] = "invalid course";
						}
						if(!($courseDAO->retrieve($prerequisiteid))) {
							$rowErrors[] = "invalid prerequisite";
						}
					}
					if(!empty($rowErrors)){
						$errors["prerequisite.csv"][] = [
							"line" => $countPrereq,
							"message" => $rowErrors
						];
					} else {
						$prerequisiteObj = new Prerequisite($coursecode, $prerequisiteid);
						$prerequisiteDAO->add($prerequisiteObj);
						$prerequisite_processed++; #line added successfully  
					}
					$countPrereq++;
				}

				fclose($prerequisite); // close the file handle
				unlink($prerequisite_path); // delete the temp file

				// COURSE COMPLETED

				// Skip table headings
				$data = fgetcsv($course_completed);
				$countCourseCompleted = 1;
				$errors["course_completed.csv"] = array();
				
				while ( ($data = fgetcsv($course_completed) ) !== false){
					
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$code = trim($data[1]);
				
					//Check for any empty fields
					if (empty($userid) || empty($code)) {
						if(empty($data[0])){
							$rowErrors[] = "blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank code";
						}
					} else {
						// Check if userid is found in the student.csv
						// Check if course code is found in the course.csv

						if(!($studentDAO->retrieve($userid))) {
							$rowErrors[] = "invalid userid";
						}
						if(!($courseDAO->retrieve($code))) {
							$rowErrors[] = "invalid code";
						}
						// Check if the completed course has a prerequisite and if the student has completed the prereq
						if (!prereqCompleted($userid, $code)) {
							$rowErrors[] = "invalid course completed";
						}
					}
					if(!empty($rowErrors)){
						$errors["course_completed.csv"][] = [
							"line" => $countCourseCompleted,
							"message" => $rowErrors
						];
					} else {
						$courseCompletedObj = new CourseCompleted($userid, $code);
						$courseCompletedDAO->add($courseCompletedObj);
						$course_completed_processed++; #line added successfully  
					}
					$countCourseCompleted++;
				}

				fclose($course_completed); // close the file handle
				unlink($course_completed_path); // delete the temp file

				// BID

				// Skip table headings
				$data = fgetcsv($bid);
				$countBid = 1;
				$errors["bid.csv"] = array();

				while ( ($data = fgetcsv($bid) ) !== false){
					
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$amount = trim($data[1]);
					$code = trim($data[2]);
					$sectionid = trim($data[3]);
				
					//Check for any empty fields
					if (empty($userid) || empty($amount) || empty($code) || empty($section)) {
						if(empty($data[0])){
							$rowErrors[] = "blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "blank amount";
						}
						if(empty($data[2])){
							$rowErrors[] = "blank code";
						}
						if(empty($data[3])){
							$rowErrors[] = "blank section";
						}
					} else {
						// Data validations

						// Check if userid is found in the student.csv
						if(!($studentDAO->retrieve($userid))) {
							$rowErrors[] = "invalid userid";
						}

						// Check if bidding amount is a numeric value
						if(!isValidEdollar($amount)){
							$rowErrors[] = "invalid e-dollar";
						} elseif ($amount < 10.0) {
							// Check if bidding amount >= 10.0
							$rowErrors[] = "invalid e-dollar";
						}

						// Check if course code is found in the course.csv
						if(!($courseDAO->retrieve($code))) {
							$rowErrors[] = "invalid code";
						} elseif (!($sectionDAO->retrieve($code, $sectionid))) {
							// Check if section code is found in section.csv (only for valid course code)
							$rowErrors[] = "invalid section";
						}

						// Logic validations, only if data validations are passed
						if (empty($rowErrors)) {
							$bidStud = $studentDAO->retrieve($userid);
							$bidCourse = $courseDAO->retrieve($code);
							$bidSection = $sectionDAO->retrieve($code, $sectionid);
							
							// Check if student has already bidded for this course and update bid if yes
							$alreadyBiddedCourse = FALSE;
							$sameSection = FALSE;
							$previousBid = $bidDAO->retrieve($userid, $code);

							if ($previousBid) {
								$alreadyBiddedCourse = TRUE;
								if ($previousBid->getSection() == $sectionid) {
									$sameSection = TRUE;
								}
							}

							if ($alreadyBiddedCourse) {
								/* if the student's bid for this course exists, we can assume that:
									1. There is no exam timetable clash
									2. The course is offered by the student's school
									3. The student has completed the prerequisites
									4. The student has not completed the course
									5. Since the bid is being updated, the student has <= 5 bids, so there is no need to check for the section limit
								*/
								
								// Check if student has enough e-dollars
								if ($amount > $bidStud->getEdollar()) { // Check if student has enough e-dollars
									$rowErrors[] = "not enough e-dollar";
								}

								// Check for class timetable clash only if student is bidding for another section
								if (!$sameSection) {
									if (classClash($userid, $bidSection)) {
										$rowErrors[] = "class timetable clash";
									}
								}
								
							} else {
								// Check if course is offered by student's school
								if (!($bidStud->getSchool() == $bidCourse->getSchool())) {
									$rowErrors[] = "not own school course";
								}

								// Check if student has enough e-dollars
								if ($amount > $bidStud->getEdollar()) { // Check if student has enough e-dollars
									$rowErrors[] = "not enough e-dollar";
								}

								// Check for class timetable clash
								if (classClash($userid, $bidSection)) {
									$rowErrors[] = "class timetable clash";
								}
				
								// Check for exam timetable clash
								if (examClash($userid, $bidCourse)) {
									$rowErrors[] = "exam timetable clash";
								}
								// Check if student has completed the prerequisites
								if (!prereqCompleted($userid, $code)) {
									$rowErrors[] = "incomplete prerequisites";
								}

								// Check if student has already completed the course
								if ($courseCompletedDAO->retrieve($userid,$code)) {
									$rowErrors[] = "course completed";
								}

								// Check if student already has 5 bids
								if (count($bidDAO->retrieveByUserid($userid)) == 5) {
									$rowErrors[] = "section limit reached";
								}
							}
						}
					}

					if(!empty($rowErrors)){
						$errors["bid.csv"][] = [
							"line" => $countBid,
							"message" => $rowErrors
						];
					} else {
						// if (isset($alreadyBiddedCourse) && $alreadyBiddedCourse) {
						if ($alreadyBiddedCourse) {
							// Store the previously bidded amount
							$previousAmount = $previousBid->getAmount();
							// Update the student's previous bid
							$bidDAO->updateBid($userid, $amount, $sectionid);
							// Refund amount for previous bid and charge edollars for current bid
							$thisStud = $studentDAO->retrieve($userid);
							$balance = $thisStud->getEdollar() + $previousAmount - $amount;
							$studentDAO->updateEdollar($userid, $balance);
						
						} else {
							$bidObj = new Bid($userid, $amount, $code, $sectionid, "Pending");
							$bidDAO->add($bidObj);
							
							// deduct amount from student's balance
							$thisStud = $studentDAO->retrieve($userid);
							$balance = $thisStud->getEdollar() - $amount;
							$studentDAO->updateEdollar($userid, $balance);

							$bid_processed++; #line added successfully  
						}
					}
					$countBid++;
				}

				fclose($bid); // close the file handle
				unlink($bid_path); // delete the temp file
			}
		}
	}

	// Start round 1 automatically
	$roundDAO = new RoundDAO();
	$updateRoundNum = $roundDAO->updateRoundNumber(1);
	$updateRoundStat = $roundDAO->updateRoundStatus("opened");

	$lines_processed = [
		"student.csv" => $student_processed,
		"course.csv" => $course_processed,
		"section.csv" => $section_processed,
		"prerequisite.csv" => $prerequisite_processed,
		"course_completed.csv" => $course_completed_processed,
		"bid.csv" => $bid_processed
	];

	return [$lines_processed, $errors];

}

function bootstrapJSON() {
	$response = doBootstrap();
	$lines_processed = $response[0];
	$errors = $response[1];
	ksort($lines_processed);

	if (isEmpty($errors)){
		$result = [
			"status" => "success",
			"num-record-loaded" => $lines_processed
		];
	} else {
		$result = [
			"status" => "error",
			"num-record-loaded" => $lines_processed
		];
		$result["error"] = array();

		ksort($errors);
		foreach($errors as $filename => $fileErrors) {
			foreach($fileErrors as $rowErrors) {
				$message = $rowErrors["message"];

				if (strpos($message[0], "blank") === FALSE) {
					sort($message);
				}
				
				$result["error"][] = [
					"file" => $filename,
					"line" => $rowErrors["line"],
					"message" => $message
				];
			}
		}
	}
	return $result;
}

function bootstrapUI() {
	$response = doBootstrap();
	$errors = $response[1];

	if (!isEmpty($errors)){
		$_SESSION['errors'] = array();
		foreach($errors as $filename => $fileErrors) {
			foreach($fileErrors as $rowErrors) {
				foreach($rowErrors['message'] as $message) {
					$_SESSION['errors'][] = "$filename - {$rowErrors['line']} - $message";
				}
			}
		}
	} else {
		$_SESSION['success'] = "Bootstrap successful! Bidding Round 1 started.";
	}
	header("Location: bootstrap.php");
	exit();
}
?>