<?php

class Mobicommerce_Mobiservices_Model_Mysql4_Mobiservices_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mobiservices/mobiservices');
    }
}