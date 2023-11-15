<?php

/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */
$session_id = session_id();
if (empty($session_id)) {
    session_start();
}

class ClientModel {

    public $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = db_config::StartUp();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function all($data) {
        
    }

    public function create($data) {
        
    }

    public function deleted($data) {
        
    }

    public function search($data) {
        try {
            $idCard = $data["idCard"];

            $sql = "SELECT * FROM cliente WHERE idcard= '" . $idCard . "'";
            $clients = $this->pdo->prepare($sql);
            $clients->execute();
            $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
            $result = $clients;
            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function update($data) {
        try {
            $sql = "UPDATE `Cliente`
            SET
            `accesstoken` ='" . $data['accesstoken'] . "',
            `refreshtoken` = '" . $data['refreshtoken'] . "'
            WHERE (`idcard` = '" . $data["idcard"] . "');";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $cret = $result->rowCount();
            if ($cret > 0) {
                return True;
            } else {
                return false;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}
