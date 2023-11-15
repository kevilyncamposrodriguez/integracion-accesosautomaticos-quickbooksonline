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
require_once('vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class DefaultModel {

    public $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = db_config::StartUp();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function saveToken($id, $refreshToken) {
        $sql = "UPDATE cliente SET refreshToken = '" . $refreshToken . "' where realmId = '" . $id . "'";
        $user = $this->pdo->prepare($sql);
        $user->execute();
    }

    public function all($data) {
        
    }

    public function create() {
         try {
            $sql = "INSERT INTO `apirest`(`id`) VALUES ('')";
            $result = $this->pdo->prepare($sql);
            $result = $result->execute();
            if($result === true){
              return $this->pdo->lastInsertId();
            }else{
              return "error";
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function deleted($data) {
        
    }

    public function search($data) {
       
    }

    public function update($data, $id, $consecutivo) {
        try {

            $sql = "UPDATE `apirest` SET estatus = '".$data['status']."',
                                         mensaje = '".$data['message']."',
                                         consecutivo = '".$consecutivo."'WHERE id = '".$id."'";
            $result = $this->pdo->prepare($sql);
            if($result->execute()){
              return "ok";
            }else{
              return "error";
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function getDataService($data) {
        $data = json_decode($data,true);
        $config = include('libs/config.php');;
        try {
            $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'RedirectURI' => $config['oauth_redirect_uri'],
                    'refreshTokenKey' => $data[0]["refreshToken"],
                    'QBORealmID' => $data[0]["realmId"],
                    'scope' => $config['oauth_scope'],
                    'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data[0]["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            return $dataService;
        }catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

}
