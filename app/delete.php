<?php


require_once 'include/common.php';
require_once 'include/protect.php';


$dao = new PokemonDAO();
$dao->remove($_GET['name']);

header("Location: index.php");