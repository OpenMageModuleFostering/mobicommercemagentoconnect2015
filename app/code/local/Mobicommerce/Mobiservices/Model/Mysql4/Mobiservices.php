<?php

class Mobicommerce_Mobiservices_Model_Mysql4_Mobiservices extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the followers_id refers to the key field in your database table.
        $this->_init('mobiservices/mobiservices', 'id');
    }
}