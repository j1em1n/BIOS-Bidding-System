<?php

require_once 'include/common.php';
require_once 'include/protect.php';

if(isset($_SESSION['userid'])){
    $userid = $_SESSION['userid'];
   // echo $userid;
}

$studentdao = new StudentDAO();
$retrieveall = $studentdao->retrieve($userid);
//var_dump($retrieveall);
$userschool = $retrieveall->school;


$coursedao = new CourseDAO();
$allcourse = $coursedao->retrieveAll();


?>



<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>        
        <h1>Add Bid</h1>
            <select name = "Pick a school">
                <option value = 'SIS'>SIS</option> <? // can use disabled to block optin?>
                <option value = 'SOB'>SOB</option>
                <option value = 'SOE'>SOE</option>
                <option value = 'SOA'>SOA</option>
                <option value = 'SOL'>SOL</option>
                <option value = 'SOSS'>SOSS</option>

            </select>

            <table>

            <tr>
            <b>
                <th></th>
                <th>Course</th>
                <th>School</th>
                <th>Title</th>
                <th>Description</th>
            </b>
            </tr>

                <?php
                foreach($allcourse as $eachcourse){
                    if($eachcourse->school == $userschool){

                        echo 
                        "<tr>
                            <td><input type = 'checkbox' name = 'courseschosen[]'></td>
                            <td>$eachcourse->course</td>
                            <td>$eachcourse->school</td>
                            <td>$eachcourse->title</td>
                            <td>$eachcourse->description</td>
                        </tr>";
                    }
                }
                    
                ?>

            </table>
        
            
            <input type='submit' />
        
        </form>

        <p>
            <a href="index.php">&lt;&lt;Back to main page</a>
        </p>
    </body>
</html>