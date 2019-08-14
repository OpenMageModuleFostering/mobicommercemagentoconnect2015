<?php

class Mobicommerce_Mobiservices_CatalogController extends Mobicommerce_Mobiservices_Controller_Action {

    public function categoriesAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->getCatrgories();
        $this->printResult($information);
    }

    public function productListAction()
    {
        $data = $this->getData();               
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->productList($data);
        $this->printResult($information);
    }

    public function productInfoAction()
    {
        $data = $this->getData();               
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->productInfo($data);
        $this->printResult($information);
    }
    
    public function catalogSearchAction()
    {
        $data = $this->getData();        
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->productList($data);
        $this->printResult($information);
    }
}