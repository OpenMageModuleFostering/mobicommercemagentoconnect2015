<?php

class Mobicommerce_Mobiadmin_Model_Resource_Applications extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('mobiadmin/applications', 'id');
    }
}