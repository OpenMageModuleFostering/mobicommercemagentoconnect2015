<?php
class Mobicommerce_Mobiadmin_Model_Resource_Applications_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract 
{
    protected function _construct()
    {
            $this->_init('mobiadmin/applications');
    }
}