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

require 'model/DefaultModel.php';

class DefaultController {

    private $defaultModel;

    //cointructor
    public function __construct() {
        $this->defaultModel = new DefaultModel();
    }

    public function all() {
       
    }

    public function create() {
        return $this->defaultModel->create();
    }
    public function update($data, $id) {
        $this->defaultModel->update($data, $id);
    }
   
    public function delete() {
        
    }

    public function index() {
        
    }

    public function search() {
        
    }

}
