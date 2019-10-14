<?php
require_once 'include/protect.php';
require_once 'include/common.php';

?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="include/style.css">
	</head>
	<body>
		<form id='bootstrap-form' action="bootstrap_process.php" method="post" enctype="multipart/form-data">
			Bootstrap file: 
			<input id='bootstrap-file' type="file" name="bootstrap-file"></br>
			<input type="submit" name="submit" value="Import">
		</form>
		<?php
			printErrors();
			if(isset($_SESSION['success'])) {
				echo "<h3>{$_SESSION['success']}</h3>";
				unset($_SESSION['success']);
			}
		?>
	</body>
</html>