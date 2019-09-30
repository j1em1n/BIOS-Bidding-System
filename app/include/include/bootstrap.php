<?php
require_once 'common.php';

function doBootstrap() {
		

	$errors = array();
	# need tmp_name -a temporary name create for the file and stored inside apache temporary folder- for proper read address
	$zip_file = $_FILES["bootstrap-file"]["tmp_name"];

	# Get temp dir on system for uploading
	$temp_dir = sys_get_temp_dir();

	# keep track of number of lines successfully processed for each file
	$student_processed=0;
	$section_processed=0;
	$course_processed=0;
	$course_completed_processed=0;
	$prerequisite_processed=0;
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
					@unlink($course_completed_path);
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
				
				$studentDAOobj = new UserDAO();
				$studentDAOobj->removeAll(); #clearing data (not database)

				# then read each csv file line by line (remember to skip the header)
				# $data = fgetcsv($file) gets you the next line of the CSV file which will be stored 
				# in the array $data
				# $data[0] is the first element in the csv row, $data[1] is the 2nd, ....
				
				#skip header
				$data = fgetcsv($student); #will get array  in data (2 fields cause csv files only have 2 columns)
				#give a file to read i.e. $pokemon 
				while ( ($data = fgetcsv($student) ) !== false){ #double == to check for boolean also. 
					# $data[0] = name $data[1] = type
					$studentObj = new Student ($data[0], $data[1], data[2], $data[3], $data[4]);
					$studentDAOobj->add($studentObj);
					$pokemon_processed++;
				}

				fclose($pokemon); //close file handle
				unlink($pokemon_path); //delete the temp file

				# process each line and check for errors
				# for this lab, assume the only error you should check for is that each CSV field 
				# must not be blank 
				# for the project, the full error list is listed in the wiki

				// Pokemon Type

				$pokemonTypeDAO = new PokemonDAO();
				$pokemonTypeDAO->removeAll(); #clearing data (not database)
				$data = fgetcsv($pokemon_type); 

				while ( ($data = fgetcsv($pokemon_type) ) !== false){ #double == to check for boolean also. 
					# $data[0] = name $data[1] = type
					$pokemonType = new Pokemon ($data[0]);
					$pokemonTypeDAO->add($pokemonType);
					$pokemon_type_processed++;
				}

				fclose($pokemon_type); //close file handle
				unlink($pokemon_type_path); //delete the temp file

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
				"student.csv" => $student_processed,
				"section.csv" => $section_processed,
				"course.csv" => $course_processed,
				"course_completed.csv" => $course_completed_processed,
				"prerequisite.csv" => $prerequisite_processed,
				"bid.csv" => $bid_processed

			]
		];
	}
	header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);

}
?>