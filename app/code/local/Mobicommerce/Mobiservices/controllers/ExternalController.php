<?php

class Mobicommerce_Mobiservices_ExternalController extends Mobicommerce_Mobiservices_Controller_Action {

	public function getApplicationSettingsAction()
	{
		$data = $this->getData();
		$return = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/external'))->getApplicationSettings($data);
		$this->printResult($return);
	}
	
	public function setApplicationSettingsAction()
	{
		$data = $this->getData();
		$return = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/external'))->setApplicationSettings($data);
		$this->printResult($return);
	}

	public function setDeviceTokenAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/push'))->saveDeviceToken($data);
		$this->printResult($information);
	}
}