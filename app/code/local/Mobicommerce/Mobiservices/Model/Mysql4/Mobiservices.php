<?php

class Mobicommerce_Mobiservices_Model_Mysql4_Mobiservices extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('mobiservices/mobiservices', 'id');
    }
}