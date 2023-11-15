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
require 'model/DefaultModel.php';
class InvoiceController {

    private $clientModel;
    private $invoiceModel;
    private $customerModel;
    private $defaultModel;

    //cointructor
    public function __construct() {
        $this->clientModel = new ClientModel();
        $this->invoiceModel = new InvoiceModel();
        $this->customerModel = new CustomerModel();
        $this->defaultModel = new DefaultModel();
    }

    public function all() {
        $data = array();
        //$data["invoices"] = $this->invoiceModel->all($data);
        echo $client = $this->clientModel->search("3101632811");
    }

    public function create() {
        $data = array(
            "idCard" => "3101632811",
            "realmId" => "193514832996164");
        //$client = $this->clientModel->search($data);
        $result = $this->invoiceModel->create($request, $client);
        return $result;
    }

    public function saveTE($data) {
        $idCreate = $this->defaultModel->create();
        $datos = array();
        $key = $data->getParsedBody()['key'];
        $key = base64_decode($key);
        if ($key == 'C@nta4ast@pp') {
            
            $xml = $data->getParsedBody()['xml'];
            $xml = simplexml_load_string($xml);
            $datos["clave"] = (string) $xml->Clave;
            $datos["idCard"] = (string) $xml->Emisor->Identificacion->Numero;            
            $datos["client"] = $this->clientModel->search($datos);
            $datos["consecutive"] = (string) $xml->NumeroConsecutivo;
            $datos["date"] = substr($xml->FechaEmision, 0, 10);
            $datos["total"] = (string) $xml->ResumenFactura->TotalComprobante;
            $datos["description"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Detalle;
            $datos["price"] = (string) $xml->DetalleServicio->LineaDetalle[0]->PrecioUnitario;
            $datos["qty"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Cantidad;
                $datos["sub"] = (string)$xml->DetalleServicio->LineaDetalle[0]->SubTotal;
                $datos["tax"] =(string) $xml->DetalleServicio->LineaDetalle[0]->Impuesto[0];
           
            
            $xml->asXML("files/".$datos["consecutive"].".xml");
            $result = $this->invoiceModel->createTE($datos);
            $this->defaultModel->update($result, $idCreate,"T".$datos["consecutive"]);
            return $result;
        } else {
            $result = array("status" => "400", "message" => "clave de acceso incorrecta");
            $this->defaultModel->update($result, $idCreate,"T".$datos["consecutive"]);
            return $result;
        }
    }
     public function saveFE($data) {
        $idCreate = $this->defaultModel->create();
        $datos = array();
        $key = $data->getParsedBody()['key'];
        $key = base64_decode($key);
        if ($key == 'C@nta4ast@pp') {
            
            $xml = $data->getParsedBody()['xml'];
            $xml = simplexml_load_string($xml);
            $datos["clave"] = (string) $xml->Clave;
            $datos["idCard"] = (string) $xml->Emisor->Identificacion->Numero;            
            $datos["client"] = $this->clientModel->search($datos);
            $datos["consecutive"] = (string) $xml->NumeroConsecutivo;
            $datos["date"] = substr($xml->FechaEmision, 0, 10);
            $datos["total"] = (string) $xml->ResumenFactura->TotalComprobante;
            $datos["description"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Detalle;
            $datos["price"] = (string) $xml->DetalleServicio->LineaDetalle[0]->PrecioUnitario;
            $datos["qty"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Cantidad;
            $datos["idCardR"] = (string) $xml->Receptor->Identificacion->Numero;
            $datos["sub"] = (string) $xml->DetalleServicio->LineaDetalle[0]->SubTotal;
            $datos["district"] = (string) $xml->Receptor->Ubicacion->Distrito;
            $datos["canton"] = (string) $xml->Receptor->Ubicacion->Canton;
            $datos["province"] = (string) $xml->Receptor->Ubicacion->Provincia;
            $datos["name"] = (string) $xml->Receptor->NombreComercial;
            $datos["money"] = (string) $xml->Receptor->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
            $datos["phone"] = (string) $xml->Receptor->Telefono->NumTelefono;
            $datos["email"] = (string) $xml->Receptor->CorreoElectronico;
            $datos["tax"] = (string) $xml->DetalleServicio->LineaDetalle[0]->Impuesto[0];
            $xml->asXML("files/".$datos["consecutive"].".xml");
            $results = $this->customerModel->listAll($datos);
            $results = json_decode($results,true);
            $r = false;
            foreach($results as $result){
               if($result["AlternatePhone"]["FreeFormNumber"]==$datos["idCardR"]){
                   $r=$result["Id"];
                   break;
               } 
            }
            
            if($r==false){
               $r = $this->customerModel->create($datos);
            }
            
            if($r==false){
                $result = array("status" => "400", "message" => "Proveedor no encontrado y no se pudo crear");
                $this->defaultModel->update($result, $idCreate,"F".$datos["consecutive"]);
                return $result;
            }else{
               $datos["idCustomer"] = $r;
               $result = $this->invoiceModel->createFE($datos);
               $this->defaultModel->update($result, $idCreate,"F".$datos["consecutive"]);
               return $result;
            }
        } else {
            $result = array("status" => "400", "message" => "clave de acceso incorrecta");
            $this->defaultModel->update($result, $idCreate,"F".$datos["consecutive"]);
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
