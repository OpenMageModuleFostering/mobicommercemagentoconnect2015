<?php

class Mobicommerce_Mobiservices_Model_1x4x0_Home extends Mobicommerce_Mobiservices_Model_Abstract {

	public function _getHomeData($data)
	{
		$homedata['banners']              = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getHomepageBanners($data);
		$homedata['customCheckoutFields'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/custom'))->getCustomCheckoutFields();
		$homedata['product_slider']       = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appwidget'))->getProductSliderData($data);
		return $homedata;
	}

	public function getHomeData($data)
	{
		$info = $this->successStatus();
		$info['data'] = $this->_getHomeData($data);
		return $info;
	}
}