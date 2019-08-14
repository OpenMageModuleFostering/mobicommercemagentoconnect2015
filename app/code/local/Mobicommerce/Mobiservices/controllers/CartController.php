<?php

class Mobicommerce_Mobiservices_CartController extends Mobicommerce_Mobiservices_Controller_Action {

    public function indexAction()
    {
        $data = $this->getData();        
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartDetails($data);
        $this->printResult($cartInfo);
    }

    public function addtocartAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->addtoCart($data);
        $this->printResult($cartInfo);
    }

    public function updateCartAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->updateCart($data);
        $this->printResult($cartInfo);
    }

    public function deleteItemAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->deleteItem($data);
        $this->printResult($cartInfo);
    }

    public function saveBillingAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->saveBilling($data);
        $this->printResult($cartInfo);
    }

    public function shippingMethodsAction()
    {
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getShippingMethods();
        $this->printResult($cartInfo);
    }

    public function paymentMethodsAction()
    {
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getPaymentMethos();
        $this->printResult($cartInfo);
    }

    public function saveShippingMethodAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->saveShippingMethod($data);
        $this->printResult($cartInfo);
    }

    public function placeOrderAction()
    {
        $data = $this->getData();
        $saveOrder = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->validateOrder($data);
        if ($saveOrder) {
            $this->printResult($saveOrder);
            exit;
        }
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->saveOrder($data);
        $this->printResult($cartInfo);
    }

    public function savePaymentMethodAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->savePaymentMethod($data);
        $this->printResult($cartInfo);
    }

    public function clearCartAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->clearCartData($data);
        $this->printResult($cartInfo);
    }
    
    public function getDiscountAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->setDiscountCode($data);
        $this->printResult($cartInfo);
    }
    
    public function removeDiscountAction()
    {
        $data = $this->getData();
        $cartInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->setDiscountCode($data);
        $this->printResult($cartInfo);
    }
}