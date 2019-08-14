<?php

class Mobicommerce_Mobiservices_StoreController extends Mobicommerce_Mobiservices_Controller_Action {

    /**
     * Get all stores
     */
    public function storesAction()
    {
        $data = $this->getData();
        $result = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->getAllStores($data);
        $this->printResult($result);    
    }

    /**
     * Get all store locators
     */
    public function listAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->getStoreList();
        $this->printResult($information);
    }

    /**
     * Get store locator detail
     */
    public function detailAction()
    {
    	$data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->getStoreDetail($data);
        $this->printResult($information);	
    }
}