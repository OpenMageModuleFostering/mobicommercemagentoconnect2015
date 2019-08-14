<?php

class Mobicommerce_Mobiservices_CmsController extends Mobicommerce_Mobiservices_Controller_Action {

    public function cmsdataAction() {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/cms'))->getCmsdata($data);
        $this->printResult($information);
    }   
}