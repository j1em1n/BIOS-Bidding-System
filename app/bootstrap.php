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
			<table>
				<tr>
					<td>Bootstrap file: </td>
					<td><input id='bootstrap-file' type="file" name="bootstrap-file"></td>
					<td><input type="submit" name="submit" value="Import"></td>
				</tr>
			</table>	
		</form>
				<p>
					<input class = "button1" type = "button" value = "Home" onclick = "window.location.href='admin_index.php'"><br>
				</p>
		<?php
			printSuccess();
			printErrors();
		?>
	</body>
</html>