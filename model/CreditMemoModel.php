<?php

/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */
require_once('vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\CreditMemo;

class CreditMemoModel {

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

    public function create($data, $dataService) {
        $status = "";
        $client = json_decode($data["client"]);
        try {

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            $priceHour = bcdiv($data["price"], 1, 2);
            $quantityHour = bcdiv($data["qty"], 1, 2);
            //Add a new Invoice
            $theResourceObj = CreditMemo::create([
                        "DocNumber" => $data["consecutive"],
                        "TxnDate" => $data["date"],
                        "Line" => [
                            [
                                "Amount" => $data["sub"],
                                 "Description" => "6743000000000-".$data["description"],
                                "DetailType" => "SalesItemLineDetail",
                                "SalesItemLineDetail" => [
                                    "ItemRef" => [
                                        "value" => 6,
                                        "name" => "6743000000000-Estacionamiento publico"
                                    ],
                                    "UnitPrice" => $priceHour,
                                    "Qty" => $quantityHour,
                                    "TaxCodeRef" => [
                                        "value" => "10"
                                    ]
                                ]
                            ]
                        ],
                        "TxnTaxDetail" => [
                            "TotalTax" => $data["tax"]->Monto,
                            "TaxLine" => [
                                [
                                    "Amount" => $data["tax"]->Monto,
                                    "DetailType" => "TaxLineDetail",
                                    "TaxLineDetail" => [
                                        "TaxRateRef" => [
                                            "value" => "26"
                                        ],
                                        "PercentBased" => true,
                                        "TaxPercent" => $data["tax"]->Tarifa,
                                        "NetAmountTaxable" => $data["sub"]
                                    ]
                                ]
                            ]
                        ],
                        "CustomField" => [
                            [
                                "DefinitionId" => "2",
                                "Type" => "StringType",
                                "StringValue" => $data["ref"]
                            ]
                        ],
                        "CustomerRef" => [
                            "value" => $data["idCustomer"]
                        ],
                        "SalesTermRef" => [
                            "value" => "5"
                        ],
                        "BillEmail" => [
                            "Address" => $data["email"]
                        ]
            ]);

            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();

            if ($error) {
                return $result = array("status" => "400", "message" => $error->getResponseBody());
            } else {
                return $result = array("status" => "200", "message" => 'Documento creado ' . $resultingObj->Id);
            }
        } catch (Exception $e) {
            return $result = array("status" => "400", "message" => $e->getMessage());
            //die($e->getMessage());
        }
    }

}
