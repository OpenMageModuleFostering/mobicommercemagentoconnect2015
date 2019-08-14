<?php

class Mobicommerce_Mobiservices_VersionController extends Mobicommerce_Mobiservices_Controller_Action {

	public function checkVersionAction()
	{
		$currentVersion = (string) Mage::getConfig()->getNode()->modules->Mobicommerce_Mobiservices->version;
		$information = array(
			'status'  => 'success',
			'message' => '',
			'data'    => array(
				'version' => $currentVersion
				)
			);
		$this->printResult($information);
	}
}