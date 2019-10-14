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
	if ($_FILES["bootstrap-file"]["size"] <= 0)
		$errors[] = "input files not found";

	else {
		
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
				$errors[] = "input files not found";
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
				
				$studentDAO = new StudentDAO();
                $studentDAO->removeAll();

                $courseDAO = new CourseDAO();
                $courseDAO->removeAll();

                $sectionDAO = new SectionDAO();
                $sectionDAO->removeAll();
                
                $prerequisiteDAO = new PrerequisiteDAO();
                $prerequisiteDAO->removeAll();
                
                $courseCompletedDAO = new CourseCompletedDAO();
                $courseCompletedDAO->removeAll();

                $bidDAO = new BidDAO();
                $bidDAO->removeAll();

				
				// STUDENT 

				// Skip table headings
				$data = fgetcsv($student); 

				//An indexed array to store all the existing userid in, to check for duplicate userid
				$checkDupUserId[] = $userId;
				while ( ($data = fgetcsv($student) ) !== false){
					$countStud = 1;
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
							$rowErrors[] = "student.csv - row $countStud - blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "student.csv - row $countStud - blank password";
						}
						if(empty($data[2])){
							$rowErrors[] = "student.csv - row $countStud - blank name";
						}
						if(empty($data[3])){
							$rowErrors[] = "student.csv - row $countStud -  blank school";
						}
						if(empty($data[4])){
							$rowErrors[] = "student.csv - row $countStud - blank e-dollar";
						}
					} else {
						//Checking if the userid field is > 128 characters
						if (strlen($userId) > 128) {
							$rowErrors[] = "student.csv - row $countStud - invalid userid";
						}
						
						// Checking for duplicate user IDs
						if(in_array($userId, $checkDupUserId)){
							$rowErrors[] = "student.csv - row $countStud - duplicate userid";
						} else {
							$checkDupUserId[] = $userId;
						}
						
						//Checking if edollar is a non-negative numeric value
						if(!(isNonNegativeInt($edollar) || isNonNegativeFloat($edollar))){
							$rowErrors[] = "student.csv - row $countStud - invalid e-dollar";
						} else {
							//If edollar is a float, check if it has more than 2 decimal places
							if (is_float($edollar)) {
								$checkedollar = strval($edollar);
								$edollarArr = explode(".", $checkedollar);
								if(strlen($edollarArr[1]) > 2){
									$rowErrors[] = "student.csv - row $countStud - invalid e-dollar";
								}
							} 
						}
						//Checking if the password field has > 128 characters
						if(strlen($pwd) > 128){
							$rowErrors[] = "student.csv - row $countStud - invalid password";
						}
						//Checking if the name field has > 100 characters
						if(strlen($name) > 100){
							$rowErrors[] = "student.csv - row $countStud - invalid name";
						}
					} 
					// Check if row has any errors and if no, create Student object and add to database
					if(!empty($rowErrors)){
						$errors = array_merge($errors, $rowErrors);
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

				while ( ($data = fgetcsv($course) ) !== false){
					$countCourse = 1;
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
							$rowErrors[] = "course.csv - row $countCourse - blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "course.csv - row $countCourse - blank school";
						}
						if(empty($data[2])){
							$rowErrors[] = "course.csv - row $countCourse - blank title";
						}
						if(empty($data[3])){
							$rowErrors[] = "course.csv - row $countCourse - blank description";
						}
						if(empty($data[4])){
							$rowErrors[] = "course.csv - row $countCourse - blank exam date";
						}
						if(empty($data[5])){
							$rowErrors[] = "course.csv - row $countCourse - blank exam start";
						}
						if(empty($data[6])){
							$rowErrors[] = "course.csv - row $countCourse - blank exam end";
						}
					} else {
						//Checking if the title field is > 100 characters
						if(strlen($title) > 100){
							$rowErrors[] = "course.csv - row $countCourse - invalid title";
						} 
						//Checking if the description field has > 1000 characters
						if(strlen($description) > 1000){
							$rowErrors[] = "course.csv - row $countCourse - invalid description";
						}
						//Checking if the date field is in ymd format
						if(validateDate($exam_date, "Ymd") == FALSE){
							$rowErrors[] = "course.csv - row $countCourse - invalid exam date";
						}
						
						if (!(validateDate($exam_start) && validateDate($exam_end))) {
							//Checking if exam_start is in H:mm format
							if(!validateDate($exam_start, "H:mm")){
								$rowErrors[] = "course.csv - row $countCourse - invalid exam start";
							}
							//Checking if exam_end is in H:mm format
							if(!validateDate($exam_end, "H:mm")){
								$rowErrors[] = "course.csv - row $countCourse - invalid exam end";
							}
						} else {
							//Check if exam_end is later than exam_start (only if both times are valid)
							$exam_start_datetime = DateTime::createFromFormat("H:mm", $exam_start);
							$exam_end_datetime = DateTime::createFromFormat("H:mm", $exam_end);
							if($exam_end_datetime < $exam_start_datetime) {
								$rowErrors[] = "course.csv - row $countCourse - invalid exam end";
							}
						}
					}
					//Check if row has any errors and if no, create Course object and add to database
					if(!empty($rowErrors)){
						$errors = array_merge($errors, $rowErrors);
					} else {
						$courseObj = new Course($getCourse, $school, $title, $description, $exam_date, $exam_start, $exam_end);
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
        		while ( ($data = fgetcsv($section) ) !== false){
					$countSect = 1;
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
							$rowErrors[] = "section.csv - row $countSect - blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "section.csv - row $countSect - blank section";
						}
						if(empty($data[2])){
							$rowErrors[] = "section.csv - row $countSect - blank day";
						}
						if(empty($data[3])){
							$rowErrors[] = "section.csv - row $countSect - blank start time";
						}
						if(empty($data[4])){
							$rowErrors[] = "section.csv - row $countSect - blank end time";
						}
						if(empty($data[5])){
							$rowErrors[] = "section.csv - row $countSect - blank instructor";
						}
						if(empty($data[6])){
							$rowErrors[] = "section.csv - row $countSect - blank venue";
						}
						if(empty($data[7])){
							$rowErrors[] = "section.csv - row $countSect - blank size";
						}
					} else {
						//check if course is in course.csv
						if(!($courseDAO->retrieve($coursecode))) {
							$rowErrors[] = "section.csv - row $countSect - invalid course";
						} else {
							//intval returns 0 if the parameter cannot be converted to int successfully.
							$sectionNum = intval(substr($sectionid, 1));
							//check if the first character should be an S followed by a positive numeric number (1-99). Check only if course is valid.
							if($sectionid[0] !== 'S' || !($sectionNum >= 1 && $sectionNum <= 99)){
								$rowErrors[] = "section.csv - row $countSect - invalid section";
							}
						}

						// Check if the day field is a number between 1(inclusive) and 7 (inclusive). 1 - Monday, 2 - Tuesday, ... , 7 - Sunday.
						if($day < 1 && $day > 7){
							$rowErrors[] = "section.csv - row $countSect - invalid day";
						} 
						//Checking if start is in H:mm format

						if (!(validateDate($start) && validateDate($end))) {
							//Checking if start is in H:mm format
							if(!validateDate($start, "H:mm")){
								$rowErrors[] = "section.csv - row $countSect - invalid start";
							}
							//Checking if end is in H:mm format
							if(!validateDate($end, "H:mm")){
								$rowErrors[] = "section.csv - row $countSect - invalid end";
							}
						} else {
							//Check if exam_end is later than exam_start (only if both times are valid)
							$start_datetime = DateTime::createFromFormat("H:mm", $start);
							$end_datetime = DateTime::createFromFormat("H:mm", $end);
							if($end_datetime < $start_datetime) {
								$rowErrors[] = "section.csv - row $countSect - invalid end";
							}
						}

						//Checking if the instructor field > 100 characters using strlen()
						if(strlen($instructor) > 100){
							$rowErrors[] = "section.csv - row $countSect - invalid instructor";
						}
						//Checking if the venue field has > 100 characters using strlen()
						if(strlen($venue) > 100){
							$rowErrors[] = "section.csv - row $countSect - invalid venue";
						}
						// Check if the size field is a positive numeric number.
						if(!isNonNegativeInt($size)) {
							$rowErrors[] = "section.csv - row $countSect - invalid size";
						}
				} 
				if(!empty($rowErrors)){
					$errors = array_merge($errors, $rowErrors);
				} else {
					$sectionObj = new Section($coursecode, $sectionid, $day, $start, $end, $instructor, $venue, $size);
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
				while ( ($data = fgetcsv($prerequisite) ) !== false){ 
					$countPrereq = 1;
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$coursecode = trim($data[0]);
					$prerequisiteid = trim($data[1]);
				
					//Check for any empty fields
					if(empty($coursecode) || empty($prerequisiteid)) {
						if(empty($data[0])){
							$rowErrors[] = "prerequisite.csv - row $countPrereq - blank course";
						} 
						if(empty($data[1])){
							$rowErrors[] = "prerequisite.csv - row $countPrereq - blank prerequisite";
						}
					} else {
						// Check if course code is found in the course.csv
						// Check if prerequisite course code is found in the course.csv

						if(!($courseDAO->retrieve($coursecode))) {
							$rowErrors[] = "prerequisite.csv - row $countPrereq - invalid course";
						}
						if(!($courseDAO->retrieve($prerequisiteid))) {
							$rowErrors[] = "prerequisite.csv - row $countPrereq - invalid prerequisite";
						}
					}
					if(!empty($rowErrors)){
						$errors = array_merge($errors, $rowErrors);
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
				while ( ($data = fgetcsv($course_completed) ) !== false){
					$countCourseCompleted = 1;
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$code = trim($data[1]);
				
					//Check for any empty fields
					if (empty($userid) || empty($code)) {
						if(empty($data[0])){
							$rowErrors[] = "course_completed.csv - row $countCourseCompleted - blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "course_completed.csv - row $countCourseCompleted - blank code";
						}
					} else {
						// Check if userid is found in the student.csv
						// Check if course code is found in the course.csv

						if(!($studentDAO->retrieve($userid))) {
							$rowErrors[] = "course_completed.csv - row $countCourseCompleted - invalid userid";
						}
						if(!($courseDAO->retrieve($code))) {
							$rowErrors[] = "course_completed.csv - row $countCourseCompleted - invalid code";
						}
						// Check if the completed course has a prerequisite
						if($prerequisiteDAO->retrieve($code)) {
							$prereqcourse = $prerequisiteDAO->retrieve($code);
							// Check if the student has completed the prerequisite (row with userid and prerequisite code exists)
							if(!($courseCompletedDAO->retrieve($userid, $prereqcourse->getPrerequisite()))){
								$rowErrors[] = "course_completed.csv - row $countCourseCompleted - invalid course completed";
							}
						}
					}
					if(!empty($rowErrors)){
						$errors = array_merge($errors, $rowErrors);
					} else {
						$courseCompletedObj = new Prerequisite($userid, $code);
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
				while ( ($data = fgetcsv($bid) ) !== false){
					$countBid = 1;
					$rowErrors = array();
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$amount = trim($data[1]);
					$code = trim($data[2]);
					$sectionid = trim($data[3]);
				
					//Check for any empty fields
					if (empty($userid) || empty($amount) || empty($code) || empty($section)) {
						if(empty($data[0])){
							$rowErrors[] = "bid.csv - row $countBid - blank userid";
						} 
						if(empty($data[1])){
							$rowErrors[] = "bid.csv - row $countBid - blank amount";
						}
						if(empty($data[2])){
							$rowErrors[] = "bid.csv - row $countBid - blank code";
						}
						if(empty($data[3])){
							$rowErrors[] = "bid.csv - row $countBid - blank section";
						}
					} else {
						// Data validations

						// Check if userid is found in the student.csv
						if(!($studentDAO->retrieve($userid))) {
							$rowErrors[] = "bid.csv - row $countBid - invalid userid";
						}

						// Check if bidding amount is a numeric value
						if(!(isNonNegativeInt($edollar) || isNonNegativeFloat($edollar))){
							$rowErrors[] = "student.csv - row $countStud - invalid e-dollar";
						} elseif (is_float($edollar)) {
							//If edollar is a float, check if it has more than 2 decimal places
							$checkedollar = strval($edollar);
							$edollarArr = explode(".", $checkedollar);
							if(strlen($edollarArr[1]) > 2){
								$rowErrors[] = "student.csv - row $countStud - invalid e-dollar";
							}
						} elseif ($edollar < 10.0) {
							// Check if bidding amount >= 10.0
							$rowErrors[] = "student.csv - row $countStud - invalid e-dollar";
						}

						// Check if course code is found in the course.csv
						if(!($courseDAO->retrieve($code))) {
							$rowErrors[] = "bid.csv - row $countBid - invalid code";
						} elseif (!($sectionDAO->retrieve($sectionid))) {
							// Check if section code is found in section.csv (only for valid course code)
							$rowErrors[] = "bid.csv - row $countBid - invalid section";
						}

						// Logic validations, only if data validations are passed
						if (!isset($rowErrors)) {
							$bidStud = $studentDAO->retrieve($userid);
							$bidCourse = $courseDAO->retrieve($code);
							$bidSection = $sectionDAO->retrieve($sectionid);
							$studentBids = $bidDAO->retrieveByUserid($userid);
							// Check if student has already bidded for this course and update bid if yes
							$alreadyBidded = FALSE;
							foreach($studentBids as $b) {
								if ($b->getCode() == $code) {
									$alreadyBidded = TRUE;
								}
							}
							if ($alreadyBidded) {
								/* if the student's bid for this course exists, we can assume that:
									1. There is no exam timetable clash
									2. The course is offered by the student's school
									3. The student has completed the prerequisites
									4. The student has not completed the course
									5. Since the bid is being updated, the student has <= 5 bids, so there is no need to check for the section limit
								*/
								
								// Check if student has enough e-dollars
								if ($amount > $bidStud->getEdollar()) { // Check if student has enough e-dollars
									$rowErrors[] = "bid.csv - row $countBid - not enough e-dollar";
								}

								// Check for class timetable clash
								// Iterate through each of the student's current bids
								foreach ($studentBids as $b) {
									// Retrieve the section corresponding to the bid
									$bSection = $sectionDAO->retrieve($b->getCode(), $b->getSection());
									// Check if classes are on the same day and if yes, check for timing clashes
									if (($bSection->getDay() == $bidSection->getDay()) && ($bSection->getStart() == $bidSection->getStart())) {
										$rowErrors[] = "bid.csv - row $countBid - class timetable clash";
									}
								}
							} else {
								// Check if course is offered by student's school
								if (!($bidStud->getSchool() == $bidCourse->getSchool())) {
									$rowErrors[] = "bid.csv - row $countBid - not own school course";
								}

								// Check if student has enough e-dollars
								if ($amount > $bidStud->getEdollar()) { // Check if student has enough e-dollars
									$rowErrors[] = "bid.csv - row $countBid - not enough e-dollar";
								}

								// Check for class timetable clash
								// Iterate through each of the student's current bids
								foreach ($studentBids as $b) {
									// Retrieve the section corresponding to the bid
									$bSection = $sectionDAO->retrieve($b->getCode(), $b->getSection());
									// Check if classes are on the same day and if yes, check for timing clashes
									if (($bSection->getDay() == $bidSection->getDay()) && ($bSection->getStart() == $bidSection->getStart())) {
										$rowErrors[] = "bid.csv - row $countBid - class timetable clash";
									}
								}

								// Check for exam timetable clash
								// Iterate through each of the student's current bids
								foreach ($studentBids as $b) {
									// Retrieve the course corresponding to the bid
									$bCourse = $courseDAO->retrieve($b->getCode());
									// Check if exams are on the same date and if yes, check for timing clashes
									if (($bCourse->getExamDate() == $bidCourse->getExamDate()) && ($bCourse->getExamStart() == $bidCourse->getExamStart())) {
										$rowErrors[] = "bid.csv - row $countBid - exam timetable clash";
									}
								}

								// Check if course has a prerequisite, and if the student has completed it
								if ($prerequisiteDAO->retrieve($code)) {
									$prerequisiteid = $prerequisiteDAO->retrieve($code)->getPrerequisite();
									if(!($courseCompletedDAO->retrieve($userid, $prerequisiteid))){
										$rowErrors[] = "bid.csv - row $countBid - incomplete prerequisites";
									}
								}

								// Check if student has already completed the course
								if ($courseCompletedDAO->retrieve($userid,$code)) {
									$rowErrors[] = "bid.csv - row $countBid - course completed";
								}

								// Check if student already has 5 bids
								if (count($studentBids) == 5) {
									$rowErrors[] = "bid.csv - row $countBid - section limit reached";
								}
							}
						}
					}
					if(!empty($rowErrors)){
						$errors = array_merge($errors, $rowErrors);
					} else {
						$bidObj = new Bid($userid, $amount, $code, $section, "Pending");
						$bidDAO->add($bidObj);
						$bid_processed++; #line added successfully  
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
	$updateRoundStat = $roundDAO->updateRoundStatus("open");
	if(!($updateRoundNum && $updateRoundStat)) {
		$errors[] = "error: could not start Round 1";
	}

	return $errors;

}

# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

// if (!isEmpty($errors))
// {	
// 	$sortclass = new Sort();
// 	$errors = $sortclass->sort_it($errors,"bootstrap");
// 	$result = [ 
// 		"status" => "error",
// 		"messages" => $errors
// 	];
// }

// else
// {	
// 	$result = [ 
// 		"status" => "success",
// 		"num-record-loaded" => [
// 			"pokemon.csv" => $pokemon_processed,
// 			"pokemon_type.csv" => $pokemon_type_processed,
// 			"User.csv" => $User_processed
// 		]
// 	];
// }
// header('Content-Type: application/json');
// echo json_encode($result, JSON_PRETTY_PRINT);
?>