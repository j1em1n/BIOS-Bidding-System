<?php
require_once 'include/protect.php';
require_once 'include/common.php';

?>

<form id='bootstrap-form' action="bootstrap_process.php" method="post" enctype="multipart/form-data">
	Bootstrap file: 
	<input id='bootstrap-file' type="file" name="bootstrap-file"></br>
	<input type="submit" name="submit" value="Import">
</form>
