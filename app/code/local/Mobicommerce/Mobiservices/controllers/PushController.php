<?php

class Mobicommerce_Mobiservices_PushController extends Mobicommerce_Mobiservices_Controller_Action {

	public function saveDeviceTokenAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/push'))->saveDeviceToken($data);
		$this->printResult($information);
	}
}