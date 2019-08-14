<?php
class Mobicommerce_Mobipayments_Model_Paymill extends Mage_Core_Model_Abstract {
    
    public function getConfigData(){
        $config = array(
            "private_key" => Mage::helper('paymill/OptionHelper')->getPrivateKey(),
            "public_key"  => Mage::helper('paymill/OptionHelper')->getPublicKey(),
            "description" => Mage::getStoreConfig('payment/paymill_creditcard/checkout_desc', Mage::app()->getStore()->getStoreId()),
            );
        return $config;
    }
}