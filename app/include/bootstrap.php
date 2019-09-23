<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	# number would increase for every sucessful line
	$student_processed=0;
	$course_processed=0;
    $section_processed=0;
    $prerequisite_processed=0;
    $course_completed_processed=0;
    $bid_processed=0;

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
			$course_path = "$temp_dir/course.csv";
			$section_path = "$temp_dir/section.csv";
			$prerequisite_path = "$temp_dir/prerequisite.csv";
			$course_completed_path = "$temp_dir/course_completed.csv";
			$bid_path = "$temp_dir/bid.csv";
			
			# @fopen -> opens a file for reading
			# file handle : $pokemon_type, $pokemon, $User
			$student = @fopen($student_path, "r");
			$course = @fopen($course_path, "r");
			$section = @fopen($section_path, "r");
			$prerequisite = @fopen($prerequisite_path, "r");
			$course_completed = @fopen($course_completed_path, "r");
			$bid = @fopen($bid_path, "r");
            
			if (empty($student) || empty($course) || empty($section) || empty($prerequisite) || empty($course_completed) || empty($bid)){
				$errors[] = "input files not found";
				if (!empty($student)){
					fclose($student);
					@unlink($student_path);
				} 
				
				if (!empty($course)) {
					fclose($course);
					@unlink($course_path);
				}
				
				if (!empty($section)) {
					fclose($section);
					@unlink($section_path);
				}
                if (!empty($prerequisite)){
					fclose($prerequisite);
					@unlink($prerequisite_path);
				} 
				
				if (!empty($course_completed)) {
					fclose($course_completed);
					@unlink($course_completed_path);
				}
				
				if (!empty($bid)) {
					fclose($bid);
					@unlink($bid_path);
				}

				# must rmb to close file!!! VERY IMPORTANT
				# rmb to delete temporary file if not needed!
				
				
			}
			else {

				# not actually needed O.o (only after files is valid den u should start ur dbms connection)
				// $connMgr = new ConnectionManager();
				// $conn = $connMgr->getConnection();

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
				
				# assign a name to it, will return an array in data.
				# automatically reads all the line. if reading valid line, return true. if return false, meaning there's no more line in the file 
                
                // STUDENT 

				# skip header
				$data = fgetcsv($student);

				# while loop to get all the lines , since it will return false when it reached the end, use false as condition to exit

				# !== checks the type and value ! *very impt
				while(($data = fgetcsv($student)) !== false){
					# need to check whether the lines is correct
					# $data[0] = name , $data[1] = type

					if(!(empty($data[0]) || empty($data[1]) || empty($data[2]) || empty($data[3]) || empty($data[4]))){
						$studentObj = new Student($data[0], $data[1], $data[2], $data[3], $data[4]);
						$studentDAOobj->add($studentObj);
						$student_processed++; #line added successfully
					}
				}

				# to clean up after we r done with adding the data from the file
				fclose($student); // close the file handle
				unlink($student_path); // delete the temp file

				# process each line and check for errors
				
				# for this lab, assume the only error you should check for is that each CSV field 
				# must not be blank 
				
				# for the project, the full error list is listed in the wiki

				// Pokemon Type

				// process each line, check for errors, then insert if no errors

				$pokemon_typeDAOobj = new PokemonTypeDAO();
				$pokemon_typeDAOobj->removeAll();

				$data = fgetcsv($pokemon_type);

				while(($data = fgetcsv($pokemon_type)) !== false){
					# need to check whether the lines is correct
					
					if(!(empty($data[0]))){
						$pokemon_typeDAOobj->add($data[0]);
						$pokemon_type_processed++; #line added successfully
					}
				}

				
				// clean up

				# to clean up after we r done with adding the data from the file
				fclose($pokemon_type); // close the file handle
				unlink($pokemon_type_path); // delete the temp file

				
				// Pokemon 

				// process each line, check for errors, then insert if no errors

				// clean up

				// User 

				// process each line, check for errors, then insert if no errors

				
				$userDAOobj = new UserDAO();
				$userDAOobj->removeAll();

				$data = fgetcsv($User);

				while(($data = fgetcsv($User)) !== false){
					# need to check whether the lines is correct
					# $data[0] = name , $data[1] = type

					if(!(empty($data[0]) || empty($data[1]) || empty($data[2]) || empty($data[3]))){
						$userObj = new User($data[0]);
						$userDAOobj->add($userObj);
						$User_processed++; #line added successfully
					}
				}

				// clean up

				# to clean up after we r done with adding the data from the file
				fclose($User); // close the file handle
				unlink($User_path); // delete the temp file
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