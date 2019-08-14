<?php

class Mobicommerce_Mobiservices_UserController extends Mobicommerce_Mobiservices_Controller_Action {

	public function loginAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->signIn($data);
		$this->printResult($information);
	}

	public function forgetPasswordAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->forgetPassword($data);
		$this->printResult($information);
	}

	public function checkUserLoginAction()
	{
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->checkUserLoginSession();
		$this->printResult($information);
	}

	public function userProfileAction()
	{
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->getCustomerProfileData();
		$this->printResult($information);
	}

	public function logOutAction()
	{
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->logout();
		$this->printResult($information);
	}

	public function registerAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->signUp($data);
		$this->printResult($information);
	}

	public function saveAddressAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->saveCustomerAddress($data);
		$this->printResult($information);
	}
	
	public function getOrderHistoryAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->getOrderList($data);
		$this->printResult($information);
	}

	public function getOrderDetailAction()
	{
		$data = $this->getData();
		$information = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->getOrderDetail($data);
		$this->printResult($information);
	}
}