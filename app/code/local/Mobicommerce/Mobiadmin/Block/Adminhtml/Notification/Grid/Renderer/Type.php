<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Notification_Grid_Renderer_Type extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $val = $row->getType();
        $out = '<span class="grid-severity-notice"><span>'.$val.'</span></span>';
        return $out;
    }
}