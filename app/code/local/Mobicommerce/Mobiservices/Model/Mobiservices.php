<?php

class Mobicommerce_Mobiservices_Model_Mobiservices extends Mage_Core_Model_Abstract {
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('mobiservices/mobiservices');
    }
}