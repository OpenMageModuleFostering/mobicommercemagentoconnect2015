<?php

class Mobicommerce_Mobiservices_Model_1x4x0_Config extends Mobicommerce_Mobiservices_Model_Abstract {

    protected function _getDefaultGroup($data)
    {
        $collection = Mage::getModel('mobiadmin/applications')->getCollection()
            ->addFieldToFilter('app_code', $data['appcode'])
            ->addFieldToFilter('app_key', $data['app_key']);
        if($collection->getSize()){
            foreach($collection as $_collection){
                return $_collection->getAppStoregroupid();
            }
        }
        else{
            return FALSE;
        }
    }

	public function getAllInitialData($data)
    {
		$groupId = $this->_getDefaultGroup($data);
        if(empty($groupId))
            return $this->errorStatus('Unauthorized Access');

        $store_id = Mage::app()->getStore()->getStoreId();
        $valid_stores = array();
        $group_default_store = 0;

        foreach(Mage::app()->getWebsites() as $website){
            foreach($website->getGroups() as $group){
                if($group->getGroupId() == $groupId){
                    $group_default_store = $group->getDefaultStoreId();
                    foreach($group->getStores() as $_store){
                        $valid_stores[] = $_store->getStoreId();
                    }
                }
            }
        }

        if(!in_array($store_id, $valid_stores)){
            $store_id = $group_default_store;
        }

        if(isset($data['store']) && !empty($data['store'])){
            $store_id = $this->setAppStore($data['store']);
        }
        if(isset($data['currency']) && !empty($data['currency'])){
            $store_id = $this->setAppStore($store_id, $data['currency']);
        }

		$information = $this->successStatus();
        $information['data'] = $this->_getStoreSettings();
        $information['data']['root_category_id'] = Mage::app()->getStore()->getRootCategoryId();
        $information['data']['stores']           = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/store'))->_getAllStores($data);
        $information['data']['appinfo']          = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getAppinfo($data);
        $information['data']['categories']       = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_categoryTreeList($data['appcode']);
        $information['data']['homedata']         = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/home'))->_getHomeData($data);
        $information['data']['CMS']              = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getCmsdata($data);
        $information['data']['language']         = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/language'))->getLanguageData(Mage::app()->getLocale()->getLocaleCode());
        $information['data']['push']             = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getPushdata($data);
        $information['data']['popup']            = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/appsetting'))->getPopupdata($data);
        $information['data']['cart_details']     = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo($data);
        $information['data']['countries']        = $this->_getCounties();
        $information['data']['currentDate']      = date('Y-m-d H:i:s');
		return $information;
	}

	public function _getStoreSettings()
    {
		$options = Mage::getResourceSingleton('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();
        $values = array();
        foreach ($options as $option){
            if ($option['value']){
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
                'store_id'          => $this->_getStoreId(),
                'store_name'        => $this->_getStoreName(),
                'store_code'        => Mage::app()->getStore()->getCode(),
                'country_code'      => $country->getId(),
                'country_name'      => $country->getName(),
                'locale_identifier' => Mage::app()->getLocale()->getLocaleCode(),
                'currency_name'     => Mage::app()->getLocale()->currency($currencyCode)->getName(),
                'currency_symbol'   => Mage::app()->getLocale()->currency($currencyCode)->getSymbol(),
                'currency_code'     => $currencyCode,
            ),
            'storeConfig' => array(
                'web' => array(
                    'add_store_code_to_urls' => Mage::getStoreConfig('web/url/use_store'),
                    ),
                'checkout_config' => array(
                    'enable_guest_checkout' => Mage::getStoreConfig('checkout/options/guest_checkout'),
                    'enable_agreements'     => is_null(Mage::getStoreConfig('checkout/options/enable_agreements')) ? 0 : Mage::getStoreConfig('checkout/options/enable_agreements'),
                ),
                'catalog' => array(
                    'frontend' => array(
                        'default_sort_by' => Mage::getStoreConfig('catalog/frontend/default_sort_by')
                        ),
                    ),
                ),
        );

        if(empty($info['store_info']['currency_symbol'])){
            $info['store_info']['currency_symbol'] = $info['store_info']['currency_code'];
        }
		
		return $info;
	}

    public function _getCounties()
    {
        $list = array();        
        $country_default = Mage::getStoreConfig('general/country/default');
        $countries = Mage::getResourceModel('directory/country_collection')->loadByStore();
        $cache = null;
        foreach ($countries as $country){
            if ($country_default == $country->getId()){
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
            foreach ($list as $key => $row){
                $iso2[$key]  = $row['iso2'];
                $name[$key] = $row['name'];
            }
            array_multisort($name, SORT_ASC, $iso2, SORT_DESC, $list);
        }

        if($cache){
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
        }
        else{
            return array();
        }
    }

    public function _getAgreements()
    {
        if(!Mage::getStoreConfigFlag('checkout/options/enable_agreements')){
            $agreements = array();
            return $agreements;
        }
        else{
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

    public function setAppStore($storeId, $currency = null)
    {
        $store = Mage::getModel('core/store')->load($storeId);
        if($store->getId()){
            Mage::app()->getCookie()->set(
                Mage_Core_Model_Store::COOKIE_NAME, Mage::app()->getStore($storeId)->getCode(), TRUE
                );
            Mage::app()->setCurrentStore(
                Mage::app()->getStore($storeId)->getCode()
            );

            if(!empty($currency))
                Mage::app()->getStore($storeId)->setCurrentCurrencyCode($currency);

            Mage::getSingleton('core/locale')->emulate($storeId);
            return true;
        }
        return false;
    }
}