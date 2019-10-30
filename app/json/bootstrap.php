<?php
    require_once '../include/common.php';
    require_once '../include/bootstrap.php';
    require_once '../include/token.php';

    $errors = commonValidationsJSON(basename(__FILE__));
    $success = array();

    if (!empty($errors)) {
        $result = jsonErrors($errors);
    } else {
        $result = bootstrapJSON();
    }
    
    header('Content-Type: application/json');
	echo json_encode($result, JSON_PRETTY_PRINT);
?>