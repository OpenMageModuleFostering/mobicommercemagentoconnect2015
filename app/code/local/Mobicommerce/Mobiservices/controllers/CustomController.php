<?php

class Mobicommerce_Mobiservices_CustomController extends Mobicommerce_Mobiservices_Controller_Action {

	public function updateSpecificLanguageLengthAction()
	{
		Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/custom'))->updateLanguageLength();
	}

	public function updateLanguageLabelsAction()
	{
		Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/custom'))->updateLangLabelEnglish();	
	}

	public function createAction()
	{
		Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/external'))->__createNewLanguageWords();	
	}
}