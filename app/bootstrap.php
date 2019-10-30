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
		<p>
			<a href="admin_index.php">Home</a>
		</p>
		<?php
			printErrors();
			printSuccess();
		?>
	</body>
</html>