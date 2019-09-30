<?php

require_once '../include/common.php';

$dao = new PokemonDAO();
$result = $dao->retrieveAll();

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);



?>