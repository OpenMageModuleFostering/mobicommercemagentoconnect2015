<?php

class Mobicommerce_Mobiservices_Model_1x3x1_Config extends Mobicommerce_Mobiservices_Model_Abstract {

    protected $store = FALSE;
    protected function _setStoreId($data)
    {
        $storeCollection = Mage::getModel('mobiadmin/applications')->getCollection()
            ->addFieldToFilter('app_code', $data['appcode'])
            ->addFieldToFilter('app_key', $data['app_key']);
        if($storeCollection->getSize()){
            foreach($storeCollection as $store){
                $store_id = $store['app_storeid'];
                if(empty($store_id)){
                    $store_id = Mage::app()
                        ->getWebsite()
                        ->getDefaultGroup()
                        ->getDefaultStoreId();
                }
                $this->store = $store_id;
                return $store_id;
            }
        }
        else{
            return FALSE;
        }
    }

	public function getAllInitialData($data)
    {
		$information = array();	
		$store_id = $this->_setStoreId($data);
        if($store_id === FALSE || $store_id === null)
            return $this->errorStatus('Unauthorized Access');

		$this->setAppStore($store_id);
		$storeInfo = $this->_getStoreSettings();
		$information = $this->successStatus();
        $information['data'] = $storeInfo;
        $information['data']['root_category_id'] = Mage::app()->getStore()->getRootCategoryId();
        $information['data']['appinfo']          = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getAppinfo($data);
        $information['data']['mobipaypal']       = Mage::helper('core')->isModuleEnabled('Mobicommerce_Mobipayments') ? Mage::getModel('mobipayments/standard')->getPaypalConfig() : null;
        $information['data']['categories']       = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_categoryTreeList($store_id, $data['appcode']);
        $information['data']['homedata']         = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/home'))->_getHomeData($data);
        $information['data']['CMS']              = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getCmsdata($data);
        $information['data']['language']         = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/language'))->getLanguageData($storeInfo['store_info']['locale_identifier']);
        $information['data']['push']             = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getPushdata($data);
        $information['data']['popup']            = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getPopupdata($data);
        $information['data']['countries']        = $this->_getCounties();
        $information['data']['cart_details']     = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo($data);
        //$information['data']['social']     = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/social'))->getSocialPlatforms();

        $logged_user = null;
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $_customer = Mage::getSingleton('customer/session')->getCustomer();
            $model = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'));
            $logged_user = $model->_getCustomerProfileData($_customer);
        }
        $information['data']['logged_user'] = $logged_user;
        $information['data']['currentDate'] = date('Y-m-d H:i:s');
		return $information;
	}

	public function _getStoreSettings()
    {
		$options = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option['value']) {
                $values[] = array(
                    'label' => $option['label'],
                    'value' => $option['value'],
                );
            }
        }

        $country_code = Mage::getStoreConfig('general/country/default');
        $country = Mage::getModel('directory/country')->loadByCode($country_code);
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $info = array(
            'store_info' => array(
                'country_code'      => $country->getId(),
                'country_name'      => $country->getName(),
                'locale_identifier' => Mage::app()->getLocale()->getLocaleCode(),
                'currency_symbol'   => Mage::app()->getLocale()->currency($currencyCode)->getSymbol(),
                'currency_code'     => $currencyCode,
                'store_id'          => $this->_getStoreId(),
                'store_name'        => $this->_getStoreName(),
            ),
            'checkout_config' => array(
                'enable_guest_checkout' => Mage::getStoreConfig('checkout/options/guest_checkout'),
                'enable_agreements'     => is_null(Mage::getStoreConfig('checkout/options/enable_agreements')) ? 0 : Mage::getStoreConfig('checkout/options/enable_agreements'),
            ),
        );
		
		return $info;
	}

	public function setAppStore($storeId)
    {
		if($storeId!=""){
			Mage::app()->getCookie()->set(
					Mage_Core_Model_Store::COOKIE_NAME, Mage::app()->getStore($storeId)->getCode(), TRUE
				); 
            Mage::app()->setCurrentStore(
                    Mage::app()->getStore($storeId)->getCode()
            );
            Mage::getSingleton('core/locale')->emulate($storeId);
		}
	}

    public function _getCounties()
    {
        $list = array();        
        $country_default = Mage::getStoreConfig('general/country/default');
        $countries = Mage::getResourceModel('directory/country_collection')->loadByStore();
        $cache = null;
        foreach ($countries as $country) {
            if ($country_default == $country->getId()) {
                $cache = array(
                    'iso2'   => $country->getId(),
                    'name'   => $country->getName(),
                    'states' => $this->_getStates(array('country_code'=>$country->getId())),
                );
            }
            else{
                $list[] = array(
                    'iso2'   => $country->getId(),
                    'name'   => $country->getName(),
                    'states' => $this->_getStates(array('country_code'=>$country->getId())),
                );   
            }            
        }
        if(!empty($list)){
            $iso2 = array();
            $name = array();
            foreach ($list as $key => $row) {
                $iso2[$key]  = $row['iso2'];
                $name[$key] = $row['name'];
            }
            array_multisort($name, SORT_ASC, $iso2, SORT_DESC, $list);
        }

        if ($cache){
            array_unshift($list, $cache);
        }        
        return $list;        
    }

    public function _getStates($data)
    {
        $code = $data['country_code'];
        $list = array();
        if ($code) {
            $states = Mage::getModel('directory/country')->loadByCode($code)->getRegions();
            foreach ($states as $state) {
                $list[] = array(
                    'region_id' => $state->getRegionId(),
                    'name'      => $state->getName(),
                    'code'      => $state->getCode(),
                );
            }
            return $list;
        } else {
            return array();
        }
    }

    public function _getAgreements()
    {
        if (!Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
            $agreements = array();
            return $agreements;
        } else {
            $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
            return $agreements->getData();
        }
    }

    public function getAgreements()
    {
       $info =  $this->successStatus();
       $info['data'] = $this->_getAgreements();
       return $info;
    }

    public function deleteApplication($data)
    {
        if(!empty($data['appcode']) && !empty($data['app_key'])){
            $applicationCollection = Mage::getModel('mobiadmin/applications')->getCollection()
                ->addFieldToFilter('app_code', $data['appcode'])
                ->addFieldToFilter('app_key', $data['app_key']);
            if($applicationCollection->count() > 0){
                //delete from mobicommerce_applications
                foreach($applicationCollection as $application){
                    $application->delete();
                }
                //delete from mobicommerce_applications_settings
                $applicationCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
                    ->addFieldToFilter('app_code', $data['appcode']);
                if($applicationCollection->count() > 0){
                    foreach($applicationCollection as $application){
                        $application->delete();
                    }
                }
                //delete from mobicommerce_devicetokens
                $applicationCollection = Mage::getModel('mobiadmin/devicetokens')->getCollection()
                    ->addFieldToFilter('md_appcode', $data['appcode']);
                if($applicationCollection->count() > 0){
                    foreach($applicationCollection as $application){
                        $application->delete();
                    }
                }
                //delete from mobi_app_widgets
                $applicationCollection = Mage::getModel('mobiadmin/appwidget')->getCollection()
                    ->addFieldToFilter('app_code', $data['appcode']);
                if($applicationCollection->count() > 0){
                    foreach($applicationCollection as $application){
                        $application->delete();
                    }
                }
                $this->rrmdir(Mage::getBaseDir('media').DS.'mobi_commerce'.DS.$data['appcode']);
                return $this->successStatus();
            }
            else{
                return $this->errorStatus('Unauthorized Access');
            }
        }
        else{
            return $this->errorStatus('Unauthorized Access');
        }
    }

    /* function to remove entire directory with all files in it */
    protected function rrmdir($dir, $include_basedir = true)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                } 
            } 
            reset($objects); 
            if($include_basedir)
                rmdir($dir); 
        } 
    }
}