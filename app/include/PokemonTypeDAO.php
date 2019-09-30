<?php

class PokemonTypeDAO {
    
    /**
     * Get all the possible pokemon types.
     * 
     * @return array of String
     */
    public function retrieveAll() {
        $sql = 'SELECT * FROM pokemon_type ORDER BY name';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
            
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();


        $arr = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr[] = $row['name'];
        }
        return $arr;
    }

    
    public function add($name) {
        $sql = 'INSERT INTO pokemon_type (name) VALUES (:name)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }
    
    public function removeAll() {
        $sql = 'TRUNCATE TABLE pokemon_type';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    

}