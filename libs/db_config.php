<?php

class db_config {

    public static function StartUp()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=u343224615_QBOMH;charset=utf8', 'u343224615_admin', 'C@ntafast.2020');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
        return $pdo;
    }

}

?>
