<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_AndroidStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $androidStatus = $row->getAndroidStatus();
		if($androidStatus == 0)
		{
			$out = 'Under Processing';
		}else{
			$out = 'Deliverable';
		}
        return $out;
    }
}