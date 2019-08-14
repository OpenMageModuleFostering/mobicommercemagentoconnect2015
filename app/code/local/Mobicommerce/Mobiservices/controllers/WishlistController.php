<?php

class Mobicommerce_Mobiservices_WishlistController extends Mobicommerce_Mobiservices_Controller_Action {

    public function addAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->addWishlistItem($data);
        $this->printResult($information);
    }
    
    public function listAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->getWishlistInfo();
        $this->printResult($information);
    }
    
    public function removeAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->removeWishlistItem($data);
        $this->printResult($information);
    }
    
    public function addtocartAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->addtocartWishlistItem($data);
        $this->printResult($information);
    }

    public function updateWishlistItemAction()
    {
        $data = $this->getData();  
        $information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->updateWishlistItem($data);
        $this->printResult($information);
    }    
}