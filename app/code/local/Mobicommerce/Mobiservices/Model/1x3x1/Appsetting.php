<?php

class Mobicommerce_Mobiservices_Model_1x3x1_Appsetting extends Mobicommerce_Mobiservices_Model_Abstract {
	
	public function getCmsdata($data)
	{
		$cmsCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'cms_settings');

		if($cmsCollection->getSize()){
			foreach($cmsCollection as $cmsData){
				return Mage::helper('mobiadmin')->_jsonUnserialize($cmsData['value']);
			}
		}
		else{
			return null;
		}
	}

	public function getAppinfo($data)
	{
		$AppinfoCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'appinfo');

		if($AppinfoCollection->getSize()){
			foreach($AppinfoCollection as $appinfo){
				return Mage::helper('mobiadmin')->_jsonUnserialize($appinfo['value']);
			}
		}
		else{
			return null;
		}
	}

	public function getPushdata($data)
	{
		$pushCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'push_notification');

		if($pushCollection->getSize()){
			foreach($pushCollection as $pushCollection){
				return Mage::helper('mobiadmin')->_jsonUnserialize($pushCollection['value']);
			}
		}
		else{
			return null;
		}
	}

	public function getHomepageBanners($data)
	{
		$bannersArray = array();
		$bannersCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'banner_settings');

		if($bannersCollection->getSize()){
			foreach($bannersCollection as $banners){
				$banners = Mage::helper('mobiadmin')->_jsonUnserialize($banners['value']);
				if($banners){
					foreach($banners as $banner){
						if($banner['is_active'] == '1'){
		    				$bannersArray[] = $banner['url'];
		    			}
					}
				}
			}
		}
		return $bannersArray;
	}

	public function getPopupdata($data)
	{
		$popupCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('setting_code', 'popup_setting');

		if($popupCollection->getSize()){
			foreach($popupCollection as $pushCollection){
				return Mage::helper('mobiadmin')->_jsonUnserialize($pushCollection['value']);
			}
		}
		else{
			return null;
		}
	}
}