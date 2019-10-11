<?php

require_once 'common.php';

//status_entered
$status_entered = "";
if(isset($_POST['round_num'])){
    $status_entered = "open";
    $round_num = $_POST['round_num'];

    $rounddao = new RoundDAO();
    $newRoundStatus = $rounddao->updateRoundStatus($status_entered, $round_num);
    //var_dump($newRoundStatus);


    if($newRoundStatus == True){
        //display
        echo "Round successfully changed";
    } else {
        //error
        $_SESSION['errors'] = 'Round unsuccessfully changed';
        printErrors();
    }
}


?>

<html>
<form action ="AdminRound.php" method = "POST" name = "round_num">

Choose a round to open: 
    <select name = 'round_num'>
        <option value = '1'>1</option>
        <option value = '2'>2</option>

    </select>
    <br><br>

    <input type = 'submit'>
</form>






</html>