<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
    public function render(Varien_Object $row)
    {
        $val = $row->getAppStoreid();
        $out = Mage::app()->getStore($val)->getCode();
        return $out;
    }
}