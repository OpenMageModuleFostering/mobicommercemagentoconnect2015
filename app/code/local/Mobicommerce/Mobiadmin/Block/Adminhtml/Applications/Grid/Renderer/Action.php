<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
	
    public function render(Varien_Object $row)
    {
        $id = $row->getId();
        $mode = $row->getAppMode();
        $appkey = $row->getAppKey();
        $appcode = $row->getAppCode();
        $storeid = $row->getAppStoreid();
        $appName = $row->getAppName();
		$storerooturl = Mage::app()->getStore($storeid)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$buyNowUrl = Mage::helper('mobiadmin')->buyNowUrl();
		$themename = Mage::helper('mobiadmin')->getThemeName($appcode);
		$url = Mage::helper('adminhtml')->getUrl('mobicommerce/index/edit', array('id' => $id));
		if($mode == 'live'){
		    $out = '<a href="'.$url.'">'.$this->__('Edit').'</a>';
		}else{
		    $out = '<form target="_blank" name="buynow" action='.$buyNowUrl.' method="post">
			<a href="'.$url.'">'.$this->__('Edit').'</a>'.
				'<span>&nbsp;&nbsp; &nbsp;</span>'.
				'<input type="hidden" name="app_name" value='.$appName.'>'.
				'<input type="hidden" name="app_preview_code" value='.$appkey.'>'.
				'<input type="hidden" name="app_code" value='.$appcode.'>'.
				'<input type="hidden" name="selectedapp" value="nativeapps">'.
				'<input type="hidden" name="store_rooturl" value='.$storerooturl.'>'.
				'<button>'.$this->__('Buy Now').'</button>
			</form>';
		}
        return $out;
    }
}