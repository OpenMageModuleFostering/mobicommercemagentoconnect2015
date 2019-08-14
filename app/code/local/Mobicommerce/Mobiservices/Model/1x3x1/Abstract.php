<?php

class Mobicommerce_Mobiservices_Model_1x3x1_Abstract extends Mage_Core_Model_Abstract {
    
    public function successStatus($success = array('SUCCESS'))
    {
        return array(
            'status'  => 'SUCCESS',
            'message' => $success,           
        );
    }

    public function errorStatus($error = array('0','opps! unknown Error '))
    {
        return array(
            'status' => 'FAIL',
            'message' => is_array($error)?$error[0]:$error,
        );
    }

    public function checkUserLoginSession()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
      
    public function _getStoreId()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    public function _getStoreName()
    {
        return Mage::app()->getStore()->getName();
    }

    public function _getWebsiteId()
    {
        return Mage::app()->getStore()->getWebsiteId();
    }

    public function eventChangeData($name_event, $value)
    {
        Mage::dispatchEvent($name_event, $value);
    }
}