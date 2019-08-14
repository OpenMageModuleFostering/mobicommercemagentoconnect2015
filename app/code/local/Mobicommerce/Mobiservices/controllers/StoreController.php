<?php

class Mobicommerce_Mobiservices_StoreController extends Mobicommerce_Mobiservices_Controller_Action {

    public function listAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->getStoreList();
        $this->printResult($information);
    }

    public function detailAction()
    {
    	$data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->getStoreDetail($data);
        $this->printResult($information);	
    }
}