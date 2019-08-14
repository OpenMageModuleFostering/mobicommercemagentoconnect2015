<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_Licence extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
    public function render(Varien_Object $row)
    {
        $appMode = $row->getAppMode();
		if(!empty($appMode)){
            $appMode = '<span class="app-mode">'.$appMode.' Version</span>';
		}
        return $appMode;
    }
}