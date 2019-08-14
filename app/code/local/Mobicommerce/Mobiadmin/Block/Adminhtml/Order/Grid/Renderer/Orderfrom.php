<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Order_Grid_Renderer_Orderfrom extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {                    

        $order = Mage::getModel('sales/order')->load($row->getData('entity_id')); 
		$platform = $order->getOrderfromplatform();
		if(empty($platform)){
			$platform = 'Website';
		}else if($platform == 'mobicommerce'){
			$platform = 'MobiCommerce';
		}
        return $platform;      
    }       
}