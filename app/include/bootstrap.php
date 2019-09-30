<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$userid_processed=0;

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
				
				$studentDAOobj = new StudentDAO();
                $studentDAOobj->removeAll();

                $courseDAOobj = new CourseDAO();
                $courseDAOobj->removeAll();

                $sectionDAOobj = new SectionDAO();
                $sectionDAOobj->removeAll();
                
                $prerequisiteDAOobj = new PrerequisiteDAO();
                $prerequisiteDAOobj->removeAll();
                
                $courseCompletedDAOobj = new CoursecompletedDAO();
                $courseCompletedDAOobj->removeAll();

                $bidDAOobj = new BidDAO();
                $bidDAOobj->removeAll();

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
								$_SESSION['errors'] = "student.csv - row $countStud - there is an existing user with the same userid.";
							}
						} else {
							$_SESSION['errors'] = "student.csv - row $countStud - the userid field must not exceed 128 characters.";
						}
						//Checking if edollar is >= 0.0
						if($edollar >= 0.0){
							//Check whether the double has more than 2 decimal place 
							$checkedollar = strval($edollar);
							$edollarArr = explode(".", $checkedollar);
							if(strlen($edollarArr[1]) > 2){
								$_SESSION['errors'] = "student.csv - row $countStud - edollar shouldn't have more than 2 decimal place.";
							} 
						} else {
							$_SESSION['errors'] = "student.csv - row $countStud - edollar should be more than or equals to 0.0.";
						}
						//Checking if the password field has > 128 characters using strlen()
						if(strlen($pwd) > 128){
							$_SESSION['errors'] = "student.csv - row $countStud - password should not contain more than 128 characters.";
						}
						//Checking if the name field has > 100 characters using strlen()
						if(strlen($name) > 100){
							$_SESSION['errors'] = "student.csv - row $countStud - name should not contain more than 100 characters.";
						}
						if(count($_SESSION['errors']) == 0){
							$studentObj = new Student($userId, $pwd, $name, $school, $edollar);
							$studentDAOobj->add($studentObj);
							$student_processed++; #line added successfully	
						} else {
							//Print out all the errors for user
							//print error for blank fields? CHECK!
							printErrors();
						}
					} else {
						//pass the line in the file (apparently dun need to write any code?? try try)
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'] = "userid field is blank.";
						} 
						if(empty($data[1])){
							$_SESSION['errors'] = "password field is blank.";
						}
						if(empty($data[2])){
							$_SESSION['errors'] = "name field is blank.";
						}
						if(empty($data[3])){
							$_SESSION['errors'] = "school field is blank.";
						}
						if(empty($data[4])){
							$_SESSION['errors'] = "edollar field is blank.";
						}
						printErrors();
					}
					$countStud++;
				}

				fclose($student); // close the file handle
				unlink($student_path); // delete the temp file

				# process each line and check for errors
				# for this lab, assume the only error you should check for is that each CSV field 
				# must not be blank 
				# for the project, the full error list is listed in the wiki

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
							$_SESSION['errors'] = "course.csv - row $countCourse - the title field must not exceed 100 characters.";
						} 
						//Checking if the description field has > 1000 characters using strlen()
						if(strlen($description) > 1000){
							$_SESSION['errors'] = "course.csv - row $countCourse - description should not contain more than 1000 characters.";
						}
						//Checking if the date field is in ymd format
						if(($exam_date = date("Ymd")) == FALSE){
							$_SESSION['errors'] = "course.csv - row $countCourse - exam date is not in yyyymmdd format.";
						}
						if(strtotime($exam_start) == FALSE){
							$_SESSION['errors'] = "course.csv - row $countCourse - exam start time is not in .";
						}
						if(count($_SESSION['errors']) == 0){
							$CourseObj = new Course($getCourse, $school, $title, $description, $exam_date, $exam_start, $exam_end);
							$studentDAOobj->add($studentObj);
							$student_processed++; #line added successfully	
						} else {
							//Print out all the errors for user
							//print error for blank fields? CHECK!
							printErrors();
						}
					} else {
						//pass the line in the file (apparently dun need to write any code?? try try)
						//Print out all the errors for user
						if(empty($data[0])){
							$_SESSION['errors'] = "course field is blank.";
						} 
						if(empty($data[1])){
							$_SESSION['errors'] = "school field is blank.";
						}
						if(empty($data[2])){
							$_SESSION['errors'] = "title field is blank.";
						}
						if(empty($data[3])){
							$_SESSION['errors'] = "description field is blank.";
						}
						if(empty($data[4])){
							$_SESSION['errors'] = "exam date field is blank.";
						}
						if(empty($data[5])){
							$_SESSION['errors'] = "exam start field is blank.";
						}
						if(empty($data[6])){
							$_SESSION['errors'] = "exam end field is blank.";
						}
						printErrors();
					}
					$countCourse++;
				}

				fclose($course); // close the file handle
				unlink($course_path); // delete the temp file

				// User 

				$pokemonUserDAO = new PokemonDAO();
				$pokemonUserDAO->removeAll(); #clearing data (not database)

				$data = fgetcsv($User); 
				while ( ($data = fgetcsv($User) ) !== false){ #double == to check for boolean also. 
					$pokemonObj = new Pokemon ($data[0], $data[1], $data[2], $data[3]);
					$pokemonUserDAO->add($pokemonObj);
					$User_processed++;
				}

				fclose($User); //close file handle
				unlink($User_path); //delete the temp file
			
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