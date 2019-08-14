<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Notification_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $id = $row->getId();
        $markasreadstatus = $row->getReadStatus();
		$deleteurl = Mage::helper('adminhtml')->getUrl('mobicommerce/index/deletenotification', array('id' => $id));

		$markAsReadurl = Mage::helper('adminhtml')->getUrl('mobicommerce/index/readnotification', array('id' => $id));

		if($markasreadstatus != 0) {
		    $out = '<a onclick="deleteConfirm(\'Are you sure?\', this.href); return false;" href="'.$deleteurl.'">Delete</a>';
		} else {
		    $out = '<a onclick="deleteConfirm(\'Are you sure?\', this.href); return false;" href="'.$deleteurl.'">Delete</a>'.'<span>&nbsp;&nbsp; &nbsp;</span>'.'<a href="'.$markAsReadurl.'">Mark As Read</a>';
		}
        return $out;
    }
}