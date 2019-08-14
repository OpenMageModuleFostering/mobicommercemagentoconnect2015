<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_IosStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $iosStatus = $row->getIosStatus();
		if($iosStatus == 0)
		{
			$out = 'Under Processing';
		}else{
			$out = 'Deliverable';
		}
        return $out;
    }
}