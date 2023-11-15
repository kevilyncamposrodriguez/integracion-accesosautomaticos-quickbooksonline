<?php
require_once('vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;
$session_id = session_id();
if (empty($session_id))
{
    session_start();
}
class CustomerModel{
     //put your code here
    public $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = db_config::StartUp();
        } catch (Exception $e) {
            return $result = array("status" => "400", "message" => $e->getMessage());
        }
    }
    
    function saveToken($id, $refreshToken) {
        $sql = "UPDATE cliente SET refreshToken = '" . $refreshToken . "' where realmId = '" . $id . "'";
        $user = $this->pdo->prepare($sql);
        $user->execute();
    }
    
    public function listAll($data)
    {
        try {
           $status = "";
        $client = json_decode($data["client"]);
        $configs = include('libs/config.php');
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $configs['client_id'],
                        'ClientSecret' => $configs['client_secret'],
                        'accessTokenKey' => $client[0]->accessToken,
                        'refreshTokenKey' => $client[0]->refreshToken,
                        'QBORealmID' => $client[0]->realmId,
                        'baseUrl' => "production"
            ));
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($client[0]->refreshToken);
            $dataService->updateOAuth2Token($accessToken);
            $this->saveToken($client[0]->realmId, $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            // Run a query
            $cantidad = $dataService->Query("select count(*) from Customer");
            $cantidad= ceil($cantidad/1000)*1000;
            
            $customers = array();
            for($i=1; $i<$cantidad; $i=$i+1000){
              $customers = array_merge($customers, $dataService->findAll("Customer",$i,1000));
            }
            // Echo some formatted output
            return json_encode($customers);
            
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
     public function create($data) {
        $status = "";
        $client = json_decode($data["client"]);
        $configs = include('libs/config.php');
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $configs['client_id'],
                        'ClientSecret' => $configs['client_secret'],
                        'accessTokenKey' => $client[0]->accessToken,
                        'refreshTokenKey' => $client[0]->refreshToken,
                        'QBORealmID' => $client[0]->realmId,
                        'baseUrl' => "production"
            ));
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshAccessTokenWithRefreshToken($client[0]->refreshToken);
            $dataService->updateOAuth2Token($accessToken);
            $this->saveToken($client[0]->realmId, $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            
            //Add a new Invoice
           $theResourceObj = Customer::create([
                "BillAddr" => [
                    "Line1" => $data["district"],
                    "City" => $data["canton"],
                    "Country" => "Costa Rica",
                    "CountrySubDivisionCode" => $data["province"],
                ],
                 "ShipAddr" => [
                    "Line1" => $data["district"],
                ],
                "SalesTermRef"=> [
                  "value"=> "5"
                ],
                "PaymentMethodRef"=> [
                  "value"=> "1"
                ],
                "CurrencyRef"=> [
                    "value"=> "CRC",
                    "name"=> "Costa Rica Colon"
                ],
                "FullyQualifiedName"=> $data["name"]." ".$data["money"],
                "CompanyName" => $data["name"]." ".$data["money"],
                "DisplayName" => $data["name"]." ".$data["money"],
                "PrimaryPhone" => [
                    "FreeFormNumber" => $data["phone"]
                ],
                "AlternatePhone" => [
                    "FreeFormNumber" => $data["idCardR"]
                ],
                "PrimaryEmailAddr" => [
                    "Address" => $data["email"]
                ]
            ]);

            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();

            if ($error) {
                return false;
            } else {
                return $resultingObj->Id;
            }
        } catch (Exception $e) {
              return $result = array("status" => "400", "message" => $e->getMessage());
            //die($e->getMessage());
        }
    }
}