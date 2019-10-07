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
		$_SESSION['errors'][] = "input files not found";

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
				$_SESSION['errors'][] = "input files not found";
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

				# then read each csv file line by line (remember to skip the header)
				# $data = fgetcsv($file) gets you the next line of the CSV file which will be stored 
				# in the array $data
				# $data[0] is the first element in the csv row, $data[1] is the 2nd, ....
				
				// STUDENT 

				#skip header
				$data = fgetcsv($student); #will get array in data (2 fields cause csv files only have 2 columns)
				#give a file to read  
				while ( ($data = fgetcsv($student) ) !== false){ #double == to check for boolean also. 
					$countStud = 1;
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userId = trim($data[0]);
					$pwd = trim($data[1]);
					$name = trim($data[2]);
					$school = trim($data[3]);
					$edollar = trim($data[4]);
					//Check for any empty field 
					if(!(empty($userId) || empty($pwd) || empty($name) || empty($school) || empty($edollar))){
						//An indexed array to store all the exisiting userid in, to check for duplicate userid
						$checkDupUserId[] = $userId;
						//Checking if the userid field is > 128 characters using strlen()
						if(strlen($userId) <= 128){
							//Checking for any duplicate userId
							if(in_array($userId, $checkDupUserId)){
								$_SESSION['errors'][] = "student.csv - row $countStud - duplicate userid";
							}
						} else {
							$_SESSION['errors'][] = "student.csv - row $countStud - invalid userid";
						}
						//Checking if edollar is a numeric value and >= 0.0
						if((is_numeric($edollar) || is_float($edollar)) && $edollar >= 0.0){
							//Check whether the double has more than 2 decimal place 
							$checkedollar = strval($edollar);
							$edollarArr = explode(".", $checkedollar);
							if(strlen($edollarArr[1]) > 2){
								$_SESSION['errors'][] = "student.csv - row $countStud - invalid e-dollar";
							} 
						} else {
							$_SESSION['errors'][] = "student.csv - row $countStud - invalid e-dollar";
						}
						//Checking if the password field has > 128 characters using strlen()
						if(strlen($pwd) > 128){
							$_SESSION['errors'][] = "student.csv - row $countStud - invalid password";
						}
						//Checking if the name field has > 100 characters using strlen()
						if(strlen($name) > 100){
							$_SESSION['errors'][] = "student.csv - row $countStud - invalid name";
						}
						if(count($_SESSION['errors']) == 0){
							$studentObj = new Student($userId, $pwd, $name, $school, $edollar);
							$studentDAO->add($studentObj);
							$student_processed++; #line added successfully	
						} else {
							//Print out all the errors for user
							//print error for blank fields? CHECK!
							printErrors();
						}
					} else {
						//pass the line in the file 
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'][] = "student.csv - row $countStud - blank userid";
						} 
						if(empty($data[1])){
							$_SESSION['errors'][] = "student.csv - row $countStud - blank password";
						}
						if(empty($data[2])){
							$_SESSION['errors'][] = "student.csv - row $countStud - blank name";
						}
						if(empty($data[3])){
							$_SESSION['errors'][] = "student.csv - row $countStud -  blank school";
						}
						if(empty($data[4])){
							$_SESSION['errors'][] = "student.csv - row $countStud - blank e-dollar";
						}
						printErrors();
					}
					$countStud++;
				}

				fclose($student); // close the file handle
				unlink($student_path); // delete the temp file

				// COURSE 

				#skip header
				$data = fgetcsv($course); #will get array in data (2 fields cause csv files only have 2 columns)
				#give a file to read  
				while ( ($data = fgetcsv($course) ) !== false){ #double == to check for boolean also. 
					$countCourse = 1;
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$getCourse = trim($data[0]);
					$school = trim($data[1]);
					$title = trim($data[2]);
					$description = trim($data[3]);
					$exam_date = trim($data[4]);
					$exam_start = trim($data[5]);
					$exam_end = trim($data[6]);
					//Check for any empty field 
					if(!(empty($getCourse) || empty($school) || empty($title) || empty($description) || empty($exam_date) || empty($exam_start) || empty($exam_end))){
						//Checking if the title field is > 100 characters using strlen()
						if(strlen($title) > 100){
							$_SESSION['errors'][] = "course.csv - row $countCourse - invalid title";
						} 
						//Checking if the description field has > 1000 characters using strlen()
						if(strlen($description) > 1000){
							$_SESSION['errors'][] = "course.csv - row $countCourse - invalid description";
						}
						//Checking if the date field is in ymd format
						if((validateDate($exam_date, "Ymd") == FALSE){
							$_SESSION['errors'][] = "course.csv - row $countCourse - invalid exam date";
						}
						//Checking if exam_start is in H:mm format
						if((validateDate($exam_start, "H:mm")) == FALSE){
							$_SESSION['errors'][] = "course.csv - row $countCourse - invalid exam start";
							if((validateDate($exam_end, "H:mm")) == FALSE){
								$_SESSION['errors'][] = "course.csv - row $countCourse - invalid exam end";
							}
						} else {
							//Checking if exam_end is in H:mm format & exam_end is later than exam_start
							if((validateDate($exam_end, "H:mm")) == FALSE){
								$_SESSION['errors'][] = "course.csv - row $countCourse - invalid exam end";
							} else {
								$exam_start_datetime = DateTime::createFromFormat("H:mm", $exam_start);
								$exam_end_datetime = DateTime::createFromFormat("H:mm", $exam_end);
								if($exam_end_datetime < $exam_start_datetime) {
									$_SESSION['errors'][] = "course.csv - row $countCourse - invalid exam end";
								}
							}
						}

						if(count($_SESSION['errors']) == 0){
							$courseObj = new Course($getCourse, $school, $title, $description, $exam_date, $exam_start, $exam_end);
							$courseDAO->add($courseObj);
							$course_processed++; #line added successfully	
						} else {
							//Print out all the errors for user
							//print error for blank fields? CHECK!
							printErrors();
						}
					} else {
						//pass the line in the file 
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank course";
						} 
						if(empty($data[1])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank school";
						}
						if(empty($data[2])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank title";
						}
						if(empty($data[3])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank description";
						}
						if(empty($data[4])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank exam date";
						}
						if(empty($data[5])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank exam start";
						}
						if(empty($data[6])){
							$_SESSION['errors'][] = "course.csv - row $countCourse - blank exam end";
						}
						printErrors();
					}
					$countCourse++;
				}

				fclose($course); // close the file handle
				unlink($course_path); // delete the temp file

				// SECTION 
        		# sectionid is the individual sections like S1, S2, etc
        		#skip header
        		$data = fgetcsv($section); #will get array in data (2 fields cause csv files only have 2 columns)
        		#give a file to read  
        		while ( ($data = fgetcsv($section) ) !== false){ #double == to check for boolean also. 
          			$countSect = 1;
          			//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$courseid = trim($data[0]);
					$sectionid = trim($data[1]);
					$day = trim($data[2]);
					$start = trim($data[3]);
					$end = trim($data[4]);
					$instructor = trim($data[5]);
					$venue = trim($data[6]);
					$size = trim($data[7]);
					//Check for any field 
          			if(!(empty($courseid) || empty($sectionid) || empty($day) || empty($start) || empty($end) || empty($instructor) || empty($venue)|| empty($size))){
						//check if course is in course.csv
						if(!($courseDAO->retrieve($courseid))) {
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid course";
						} else {
							//check if the first character should be an S followed by a positive numeric number (1-99). Check only if course is valid.
							//intval returns 0 if the parameter cannot be converted to int successfully.
							$sectionNum = intval(substr($sectionid, 1));
							if($sectionid[0] !== 'S' || !($sectionNum >= 1 && $sectionNum <= 99)){
								$_SESSION['errors'][] = "section.csv - row $countSect - invalid section";
							# check if the integers after S in in range 1 - 99  
							} else {
								$_SESSION['errors'][] = "section.csv - row $countSect - invalid section";
							}
						}

						// Check if the day field is a number between 1(inclusive) and 7 (inclusive). 1 - Monday, 2 - Tuesday, ... , 7 - Sunday.
						//$day < 1 && $day > 7
						if($day < 1 && $day > 7){
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid day";
						} 
						//Checking if start is in H:mm format

						if((validateDate($start, "H:mm")) == FALSE){
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid start";
							if((validateDate($end, "H:mm")) == FALSE){
								$_SESSION['errors'][] = "section.csv - row $countSect - invalid end";
							}
						} else {
							//Checking if end is in H:mm format & end is later than start
							if((validateDate($end, "H:mm")) == FALSE){
								$_SESSION['errors'][] = "section.csv - row $countSect - invalid end";
							} else {
								$start_datetime = DateTime::createFromFormat("H:mm", $start);
								$end_datetime = DateTime::createFromFormat("H:mm", $end);
								if($end_datetime < $start_datetime) {
									$_SESSION['errors'][] = "section.csv - row $countSect - invalid end";
								}
							}
						}

						//Checking if the instructor field > 100 characters using strlen()
						if(strlen($instructor) > 100){
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid instructor";
						}
						//Checking if the venue field has > 100 characters using strlen()
						if(strlen($venue) > 100){
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid venue";
						}
						// Check if the field is a positive numeric number.
						if(!is_numeric($size) || $size < 0) {
							$_SESSION['errors'][] = "section.csv - row $countSect - invalid size";
						}
						if(count($_SESSION['errors']) == 0){
							$sectionObj = new Section($courseid, $sectionid, $day, $start, $end, $instructor, $venue, $size);
							$sectionDAO->add($sectionObj);
							$section_processed++; #line added successfully  
						} else {
							//Print out all the errors for user
							//print error for blank fields? CHECK!
							printErrors();
						}
				} else {
					//pass the line in the file 
					//Print out all the errors for user
					if(empty($data[0])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank course";
					} 
					if(empty($data[1])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank section";
					}
					if(empty($data[2])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank day";
					}
					if(empty($data[3])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank start time";
					}
					if(empty($data[4])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank end time";
					}
					if(empty($data[5])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank instructor";
					}
					if(empty($data[6])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank venue";
					}
					if(empty($data[7])){
						$_SESSION['errors'][] = "section.csv - row $countSect - blank size";
					}
					printErrors();
				}
				$countSect++;
			}

				fclose($section); // close the file handle
				unlink($section_path); // delete the temp file
				
				// PRE-REQUISITE 

				#skip header
				$data = fgetcsv($prerequisite); #will get array in data (2 fields cause csv files only have 2 columns)
				#give a file to read  
				while ( ($data = fgetcsv($prerequisite) ) !== false){ #double == to check for boolean also. 
					$countPrereq = 1;
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$courseid = trim($data[0]);
					$prerequisiteid = trim($data[1]);
				
					//Check for any field 
					if(!(empty($courseid) || empty($prerequisiteid))){
						// Check if course code is found in the course.csv
						// Check if prerequisite course code is found in the course.csv

						if(!($courseDAO->retrieve($courseid))) {
							$_SESSION['errors'][] = "prerequisite.csv - row $countPrereq - invalid course";
						}
						if(!($courseDAO->retrieve($prerequisiteid))) {
							$_SESSION['errors'][] = "prerequisite.csv - row $countPrereq - invalid prerequisite";
						}
					
						if(count($_SESSION['errors']) == 0){
							//Convert edollar to string before storing it into database as pdo dun have double. :/ need to change database? 
							$prerequisiteObj = new Prerequisite($courseid, $prerequisiteid);
							$prerequisiteDAO->add($prerequisiteObj);
							$prerequisite_processed++; #line added successfully  
						} else {
						//Print out all the errors for user
						//print error for blank fields? CHECK!
						printErrors();
						}
					} else {
						//pass the line in the file (apparently dun need to write any code?? try try)
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'][] = "prerequisite.csv - row $countPrereq - blank course";
						} 
						if(empty($data[1])){
							$_SESSION['errors'][] = "prerequisite.csv - row $countPrereq - blank prerequisite";
						}
						printErrors();
					}
					$countPrereq++;
				}

				fclose($prerequisite); // close the file handle
				unlink($prerequisite_path); // delete the temp file

				// COURSE COMPLETED

				#skip header
				$data = fgetcsv($course_completed); #will get array in data (2 fields cause csv files only have 2 columns)
				#give a file to read  
				while ( ($data = fgetcsv($course_completed) ) !== false){ #double == to check for boolean also. 
					$countCourseCompleted = 1;
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$code = trim($data[1]);
				
					//Check for any field 
					if(!(empty($userid) || empty($code))){
						// Check if userid is found in the student.csv
						// Check if course code is found in the course.csv

						if(!($studentDAO->retrieve($userid))) {
							$_SESSION['errors'][] = "course_completed.csv - row $countCourseCompleted - invalid userid";
						}
						if(!($courseDAO->retrieve($code))) {
							$_SESSION['errors'][] = "course_completed.csv - row $countCourseCompleted - invalid code";
						}
					
						if(count($_SESSION['errors']) == 0){
							//Convert edollar to string before storing it into database as pdo dun have double. :/ need to change database? 
							$courseCompletedObj = new Prerequisite($userid, $code);
							$courseCompletedDAO->add($courseCompletedObj);
							$course_completed_processed++; #line added successfully  
						} else {
						//Print out all the errors for user
						//print error for blank fields? CHECK!
						printErrors();
						}
					} else {
						//pass the line in the file (apparently dun need to write any code?? try try)
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'][] = "course_completed.csv - row $countCourseCompleted - blank userid";
						} 
						if(empty($data[1])){
							$_SESSION['errors'][] = "course_completed.csv - row $countCourseCompleted - blank code";
						}
						printErrors();
					}
					$countCourseCompleted++;
				}

				fclose($course_completed); // close the file handle
				unlink($course_completed_path); // delete the temp file

				// COURSE COMPLETED

				#skip header
				$data = fgetcsv($bid); #will get array in data (2 fields cause csv files only have 2 columns)
				#give a file to read  
				while ( ($data = fgetcsv($bid) ) !== false){ #double == to check for boolean also. 
					$countBid = 1;
					//Trim all the variables to ensure that there's no whitespace from both sides of the string using trim()
					$userid = trim($data[0]);
					$amount = trim($data[1]);
					$code = trim($data[2]);
					$section = trim($data[3]);
				
					//Check for any field 
					if(!(empty($userid) || empty($amount) || empty($code) || empty($section))){
						// Check if userid is found in the student.csv
						if(!($studentDAO->retrieve($userid))) {
							$_SESSION['errors'][] = "bid.csv - row $countBid - invalid userid";
						}
						// Check if bidding amount is a numeric value and >= 10.0
						if((is_numeric($amount) || is_float($amount)) && $amount >= 10.0){
							//Check whether the double has more than 2 decimal places 
							$checkedamount = strval($amount);
							$amountArr = explode(".", $checkedamount);
							if(strlen($amountArr[1]) > 2){
								$_SESSION['errors'][] = "bid.csv - row $countBid - invalid amount";
							} 
						} else {
							$_SESSION['errors'][] = "bid.csv - row $countBid - invalid amount";
						}
						// Check if course code is found in the course.csv
						if(!($courseDAO->retrieve($code))) {
							$_SESSION['errors'][] = "bid.csv - row $countBid - invalid code";
						} elseif (!($sectionDAO->retrieve($section))) {
							// Check if section code is found in section.csv (only for valid course code)
							$_SESSION['errors'][] = "bid.csv - row $countBid - invalid section";
						}
						
						if(count($_SESSION['errors']) == 0){
							//Convert edollar to string before storing it into database as pdo dun have double. :/ need to change database? 
							$bidObj = new Bid($userid, $amount, $code, $section);
							$bidDAO->add($bidObj);
							$bid_processed++; #line added successfully  
						} else {
						//Print out all the errors for user
						//print error for blank fields? CHECK!
						printErrors();
						}
					} else {
						//pass the line in the file (apparently dun need to write any code?? try try)
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'][] = "bid.csv - row $countBid - blank userid";
						} 
						if(empty($data[1])){
							$_SESSION['errors'][] = "bid.csv - row $countBid - blank amount";
						}
						if(empty($data[2])){
							$_SESSION['errors'][] = "bid.csv - row $countBid - blank code";
						}
						if(empty($data[3])){
							$_SESSION['errors'][] = "bid.csv - row $countBid - blank section";
						}
						printErrors();
					}
					$countBid++;
				}

				fclose($bid); // close the file handle
				unlink($bid_path); // delete the temp file
			}
		}
	}

	# Sample code for returning JSON format errors. remember this is only for the JSON API. Humans should not get JSON errors.

	if (!isEmpty($errors))
	{	
		$sortclass = new Sort();
		$errors = $sortclass->sort_it($errors,"bootstrap");
		$result = [ 
			"status" => "error",
			"messages" => $errors
		];
	}

	else
	{	
		$result = [ 
			"status" => "success",
			"num-record-loaded" => [
				"pokemon.csv" => $pokemon_processed,
				"pokemon_type.csv" => $pokemon_type_processed,
				"User.csv" => $User_processed
			]
		];
	}
	header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);

}
?>