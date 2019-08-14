<?php

class Mobicommerce_Mobiservices_Model_1x3x3_Cms extends Mobicommerce_Mobiservices_Model_Abstract {

	public function getCmsdata($data)
	{
		$information = $this->successStatus();
		$information['data']['CMS'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getCmsdata($data);
		return $information;
	}
}