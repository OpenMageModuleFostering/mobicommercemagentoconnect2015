<?php

class Mobicommerce_Mobiservices_AftershipController extends Mobicommerce_Mobiservices_Controller_Action {

	public function track_mobileAction(){
		$data = $this->getData();
		$track_number = isset($data['track_number']) ? $data['track_number'] : '';
		$carrier_code = isset($data['carrier_code']) ? $data['carrier_code'] : '';
		$content = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('mobicommerce-aftership-mobile-track')->toHtml();
		$content = str_replace(
			array('TRACK_NUMBER', 'CARRIER_CODE'),
			array($track_number, $carrier_code),
			$content);
		echo $content;
		exit;
	}
}