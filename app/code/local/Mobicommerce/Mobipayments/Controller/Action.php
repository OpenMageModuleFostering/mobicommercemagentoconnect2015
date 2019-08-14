<?php
abstract class Mobicommerce_Mobipayments_Controller_Action extends Mage_Core_Controller_Front_Action {

    protected $_data;

    public function preDispatch() {
        parent::preDispatch();
        $this->setRequestData();
    }

    public function dataToJson($data) {
        $this->setData($data);
        $this->dispatchEventChangeData($this->getActionName('_return'), $data);
        $this->_data = $this->getData();
        return Mage::helper('core')->jsonEncode($this->_data);
    }    

    public function dispatchEventChangeData($event_name, $data) {
        Mage::dispatchEvent($event_name, array('object' => $this, 'data' => $data));
    }

    public function getActionName($last = '') {
        return $this->getFullActionName() . $last;
    }    

    public function printResult($data) {
        $json_data = $this->dataToJson($data);
        if(isset($_GET['callback']) && $_GET['callback']!=''){
           print $_GET['callback'] ."(".$json_data.")";exit;
        }else{
            header('content-type:application/json');
        }
        echo $json_data;
    }

    public function setRequestData(){ 
        $this->setData($this->getRequest()->getParams());
        $this->dispatchEventChangeData($this->getActionName(), $this->_data);
        $this->_data = $this->getData();
    }

    public function getData() {
        return $this->_data;
    }

    public function setData($data) {
        $this->_data = $data;
    }

}

?>
