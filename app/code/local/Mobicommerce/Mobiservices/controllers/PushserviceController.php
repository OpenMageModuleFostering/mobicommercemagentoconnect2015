<?php

class Mobicommerce_Mobiservices_PushserviceController extends Mobicommerce_Mobiservices_Controller_Action {

	public function addnotificationAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->addNotification($data);
		$this->printResult($information);
	}

	public function iosbuildAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->saveIosBuild($data);
		$this->printResult($information);
	}

	public function androidbuildAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->saveAndroidBuild($data);
		$this->printResult($information);
	}

	public function updateDeliveryStatusAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->updateDeliveryStatus($data);
		$this->printResult($information);
	}

	public function removeappsAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->removeapps($data);
		$this->printResult($information);
	}

	public function buyappAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->buyapp($data);
		$this->printResult($information);
	}

	public function aboutAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->getPluginVersion($data);
		$this->printResult($information);
	}

	public function getmodulesAction()
	{
		$data = $this->getData();
		$information = Mage::getModel('mobiservices/pushservice')->getModulesList($data);
		$this->printResult($information);	
	}
}