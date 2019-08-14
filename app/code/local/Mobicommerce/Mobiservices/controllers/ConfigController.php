<?php

class Mobicommerce_Mobiservices_ConfigController extends Mobicommerce_Mobiservices_Controller_Action {

	public function indexAction()
	{
		$data = $this->getData();
		//Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/external'))->__install102ScriptAction();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/config'))->getAllInitialData($data);
		$this->printResult($information);		
	}

	public function countriesAction()
	{
		$countries = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/config'))->getCounties();
		$this->printResult($countries);
	}

	public function statesAction()
	{
		$data = $this->getData();
		$states = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/config'))->getStates($data);
		$this->printResult($states);
	}

	public function homeDataAction()
	{
		$data = $this->getData();
		$states = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/home'))->getHomeData($data);
		$this->printResult($states);
	}

	public function getAgreementsAction()
	{
		$data = $this->getData();
		$states = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/config'))->getAgreements($data);
		$this->printResult($states);
	}
}