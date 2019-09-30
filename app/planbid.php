<?php

require_once 'include/common.php';
require_once 'include/protect.php';

$pokemon = new Pokemon();
$errors = [];

if ( isset($_POST['name']) && isset($_POST['type'])) {
    $pokemon = new Pokemon();
    $pokemon->name = $_POST['name'];
    $pokemon->type =  $_POST['type'];
    
    if (empty($_POST['name'])) {
        $errors[] = '"name" cannot be empty';
    }
    if (empty($_POST['type'])) {
        $errors[] = '"type" cannot be empty';
    }    

    if (empty($errors)) {

        $dao = new PokemonDAO();


        if ($dao->retrieve($pokemon->name)) {
            $errors[] = "duplicate record with type $pokemon->name" ;
        }
        else {

            $dao->add($pokemon);

            // send back to main page
            header("Location: index.php");
            exit();
        }
    }

} 

$pokemonTypeDAO = new PokemonTypeDAO();
$typeArr = $pokemonTypeDAO->retrieveAll();

?>



<html>
    <head>
        <link rel="stylesheet" type="text/css" href="include/style.css">
    </head>
    <body>        
        <h1>Add Pokemon</h1>
        
        <ol>
        <?php
            foreach ($errors as $value) {
                echo "<li>$value</li>";
            }
        ?>
        </ol>

        <form action='add.php' method='post'>
            <table>
                <tr>
                    <th>
                        Name
                    </th>
                    <td>
                        <input type='text' name='name' value='<?php echo $pokemon->name; ?>' />
                    </td>
                </tr>
                <tr>
                    <th>
                        Type
                    </th>
                    <td>
                        <select name='type'>
                            <option value=''>-- SELECT --</option>
                        <?php
                            foreach($typeArr as $type) {
                                $selected = ($type == $pokemon->type) ? "selected" : "";
                                echo "
                                    <option $selected>$type</option>
                                ";
                            }
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <input type='submit' />
        
        </form>

        <p>
            <a href="index.php">&lt;&lt;Back to main page</a>
        </p>
    </body>
</html>