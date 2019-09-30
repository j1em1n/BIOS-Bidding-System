<?php

require_once '../include/common.php';

try {
    $sql = 'CALL `init_data`();';       
        
    $connMgr = new ConnectionManager();
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo '{"status":"success"}';
}
catch (Exception $ex) {
    $arr = [ 
        "status" => "success",
        "exception" => $ex
    ];
    echo json_encode($arr);
}
