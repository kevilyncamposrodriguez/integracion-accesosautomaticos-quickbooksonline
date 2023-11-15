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

require 'model/InvoiceModel.php';
require 'model/ClientModel.php';
require 'model/CustomerModel.php';
require 'model/CreditMemoModel.php';
require 'model/DefaultModel.php';

class CreditMemoController {

    private $clientModel;
    private $invoiceModel;
    private $customerModel;
    private $creditMemoModel;
    private $defaultModel;

    //cointructor
    public function __construct() {
        $this->clientModel = new ClientModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->creditMemoModel = new CreditMemoModel();
        $this->defaultModel = new DefaultModel();
    }

    public function all() {
        $data = array();
        //$data["invoices"] = $this->invoiceModel->all($data);
        $client = $this->clientModel->search("3101632811");
    }

    public function saveNC($data) {
        $idCreate = $this->defaultModel->create();
        $datos = array();
        $key = $data->getParsedBody()['key'];
        $key = base64_decode($key);
        if ($key == 'C@nta4ast@pp') {
            $xml = $data->getParsedBody()['xml'];
            $xml = simplexml_load_string($xml);
            $datos["idCard"] = (string) $xml->Emisor->Identificacion->Numero;            
            $datos["client"] = $this->clientModel->search($datos);
            $datos["consecutive"] = (string) $xml->NumeroConsecutivo;
            $datos["date"] = substr($xml->FechaEmision, 0, 10);
            $datos["total"] = (string) $xml->ResumenFactura->TotalComprobante;
            $datos["description"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Detalle;
            $datos["price"] = (string) $xml->DetalleServicio->LineaDetalle[0]->PrecioUnitario;
            $datos["qty"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Cantidad;
            $datos["tax"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Impuesto[0];
            $datos["ref"] = substr($xml->InformacionReferencia->Numero,21,20);
            $datos["sub"] = (string) $xml->DetalleServicio->LineaDetalle[0]->SubTotal;
            $datos["email"] = (string) $xml->Receptor->CorreoElectronico;
            $xml->asXML("files/".$datos["consecutive"].".xml");
            $dataService = $this->defaultModel->getDataService($datos["client"]);
            if($xml->InformacionReferencia->TipoDoc == "04"){
                $datos["idCustomer"] = 1;
            }else{
                $results = $this->customerModel->listAll($datos, $dataService);
                $results = json_decode($results,true);
                $r = false;
                foreach($results as $result){
                   if($result["AlternatePhone"]["FreeFormNumber"]==$datos["idCardR"]){
                       $r=$result["Id"];
                       break;
                   } 
                }$datos["idCustomer"] = $r;
            }
            
            $result = $this->creditMemoModel->create($datos,$dataService);
            $this->defaultModel->update($result, $idCreate,"NC".$datos["consecutive"]);
            return $result;
        } else {
            $result = array("status" => "400", "message" => "clave de acceso incorrecta");
            $this->defaultModel->update($result, $idCreate,"NC".$datos["consecutive"]);
            return $result;
        }
    }

    public function delete() {
        
    }

    public function index() {
        
    }

    public function search() {
        
    }

    public function update() {
        
    }

}
