<?php
class Mobicommerce_Mobipayments_Model_Abstract extends Mage_Payment_Model_Method_Abstract {
    
    public function successStatus($success = array('SUCCESS')) {
        return array('status' => 'SUCCESS',
            'message' => $success,           
        );
    }

    public function errorStatus($error = array('0','opps! unknown Error ')) {
        return array(
            'status' => 'FAIL',
            //'message' => array('errorcode' => $error[0], 'description' => $error[1]), 
            'message' => is_array($error)?$error[0]:$error,
        );
    }


    public function checkUserLoginSession(){
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

      
    public function _getStoreId(){
        return Mage::app()->getStore()->getStoreId();
        //return Mage::app()->getStore()->getId()
    }
    public function _getStoreName(){
        return Mage::app()->getStore()->getName();
        //return Mage::app()->getStore()->getId()
    }

    public function _getWebsiteId(){
        return Mage::app()->getStore()->getWebsiteId();
    }
    public function eventChangeData($name_event, $value) {
        Mage::dispatchEvent($name_event, $value);
    }
}