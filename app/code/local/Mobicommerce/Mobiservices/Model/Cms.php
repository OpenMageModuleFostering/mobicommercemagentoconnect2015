<?php

class Mobicommerce_Mobiservices_Model_Cms extends Mobicommerce_Mobiservices_Model_Abstract {

	public function getCmsdata($data)
	{
		$information = $this->successStatus();
		$information['data']['CMS'] = Mage::getModel('mobiservices/appsetting')->getCmsdata($data);
		return $information;
	}
}