<?php

class Mobicommerce_Mobiservices_Model_1x3x3_Language extends Mobicommerce_Mobiservices_Model_Abstract {
	
	public function getLanguageData($locale_identifier = 'en_US')
	{
		$languageArray = array();
		$languageCollection = Mage::getModel('mobiadmin/multilanguage')->getCollection()
			->addFieldToFilter('mm_language_code', $locale_identifier);

		if($languageCollection->getSize()){
			foreach($languageCollection as $languageData){
				$languageArray[$languageData['mm_label_code']] = array(
					//'label'     => $languageData['mm_label'],
					//'maxlength' => $languageData['mm_maxlength'],
					'text'      => $languageData['mm_text'],
					//'help'      => $languageData['mm_help'],
					//'type'      => $languageData['mm_type']
					);
			}
			return array($locale_identifier => array('labels' => $languageArray));;
		}
		else{
			Mage::helper('mobiadmin')->setLanguageCodeData($locale_identifier);
			return $this->getLanguageData($locale_identifier);
		}
	}
}