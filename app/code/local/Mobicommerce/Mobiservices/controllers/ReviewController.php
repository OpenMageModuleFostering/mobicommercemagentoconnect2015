<?php

class Mobicommerce_Mobiservices_ReviewController extends Mobicommerce_Mobiservices_Controller_Action {

    public function submitReviewAction() {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/review'))->submitReview($data);
        $this->printResult($information);
    }

    public function getRatingOptionsAction(){
    	$data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/review'))->getRatingOptions($data);
        $this->printResult($information);	
    }
}