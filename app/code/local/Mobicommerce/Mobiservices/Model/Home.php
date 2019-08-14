<?php

class Mobicommerce_Mobiservices_Model_Home extends Mobicommerce_Mobiservices_Model_Abstract {

	public function _getHomeData($data)
	{
		$homedata['banners']              = Mage::getModel('mobiservices/appsetting')->getHomepageBanners($data);
		$homedata['recentlyViewed']       = Mage::getModel('mobiservices/catalog_catalog')->getRecentlyViewedProducts();
		$homedata['customCheckoutFields'] = Mage::getModel('mobiservices/custom')->getCustomCheckoutFields();
		$homedata['product_slider']       = Mage::getModel('mobiadmin/appwidget')->getProductSliderData($data);

		return $homedata;
	}

	public function getHomeData($data)
	{
		$info = $this->successStatus();
		$info['data'] = $this->_getHomeData($data);
		return $info;
	}
}