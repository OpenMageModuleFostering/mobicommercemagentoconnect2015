<?php
class Mobicommerce_Mobiadmin_Model_Observer
{
	public function createMobicommerceIniIfNotExists($observer)
    {
		if(!file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_assets' . DS . 'mobicommerce.ini')){
			$collection = Mage::getModel('mobiadmin/licence')->getCollection();
			$count = $collection->count();
			if(empty($count)){
				$this->sendLicenceData();
			}else{
				file_put_contents(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_assets' . DS . 'mobicommerce.ini', md5(sha1($collection->getLastItem()->getMlLicenceKey())));
			}
		}
    }

	public function sendLicenceData()
	{
		$website_url  = Mage::getBaseUrl();
		$sales_email  = Mage::getStoreConfig('trans_email/ident_sales/email');
		$countryCode  = Mage::getStoreConfig('general/country/default');
		$country      = Mage::getModel('directory/country')->loadByCode($countryCode)->getName();
		$adminSession = Mage::getSingleton('admin/session');

		$curlData = array();
		$curlData['website_url'] = $website_url;
		$curlData['sales_email'] = $sales_email;
		$curlData['admin_email'] = '';
		if($adminSession->getUser()){
			$curlData['admin_email'] = $adminSession->getUser()->getEmail();
		}

		$curlData['country'] = $country;
		$fields_string = http_build_query($curlData);

		$ch = curl_init();
		$url = Mage::helper('mobiadmin')->curlBuildUrl().'install'; 
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($curlData));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		$result = curl_exec($ch);	
		curl_close($ch);	

		$result = json_decode($result, true);
		$licence_key = $result['data']['licence_key'];
		if(!empty($licence_key)) {
			file_put_contents(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_assets' . DS . 'mobicommerce.ini', md5(sha1($licence_key)));
			$data = array(
				'ml_licence_key' => $licence_key,
				);
			try {
				Mage::getModel('mobiadmin/licence')->setData($data)->save();
			}catch(Exception $e){
				echo $e->getMessage();
			}	
		}
	}

	public function saveCustomData($event)
    {
        $quote = $event->getSession()->getQuote();
        $quote->setData('order_platform', $event->getRequestModel()->getPost('order_platform'));
        return $this;
    }

	public function sales_convert_quote_to_order(Varien_Event_Observer $observer)
	{
		$platform = Mage::app()->getRequest()->getParam('platform');
		$collection = Mage::getModel('sales/order')->getCollection();
		if($collection->getSize() > 0){
			$firstOrderCollection = $collection->getFirstItem()->getData();
			if(array_key_exists('orderfromplatform', $firstOrderCollection)){
				if($platform){
					$observer->getEvent()->getOrder()->setOrderfromplatform($platform);
				}else{
					$observer->getEvent()->getOrder()->setOrderfromplatform('');
				}
			}
		}
	}

	public function applyStoreIdToExistingApps($observer)
    {
    	$store = $observer->getEvent()->getStore()->getStoreId();
    	$this->__addAppsForStore($store, $group);
    }

    public function deleteStoreIdToExistingApps($observer)
    {
    	$store = $observer->getEvent()->getStore()->getStoreId();
    	$this->__deleteAppsForStore($store);
    }

    /**
	 * When adding new store, it will insert settings fro banners, cms and product slider
	 * When updating store info, it will delete existing app and re insert new details
     */
    public function __addAppsForStore($store)
    {
    	if(!empty($store)){
    		/* for banners and cms */
    		$settings = array("banner_settings", "cms_settings");
    		foreach($settings as $_setting){
	    		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
					->addFieldToFilter('setting_code', $_setting);

				$collection->getSelect()->group('app_code');
				if($collection->getSize()){
					foreach($collection as $_collection){
						$data = $_collection->getData();
						Mage::getModel('mobiadmin/appsetting')->setData(array(
							'app_code'     => $data['app_code'],
							'storeid'      => $store,
							'setting_code' => $data['setting_code'],
							'value'        => $data['value'],
							))->save();
					}
				}
			}
			/* for banners and cms - upto here */

			/* for product sliders */
    		$collection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$collection->getSelect()->group(array('app_code', 'slider_code'));
			if($collection->getSize()){
				foreach($collection as $_collection){
					$data = $_collection->getData();
					Mage::getModel('mobiadmin/appwidget')->setData(array(
						'app_code'          => $data['app_code'],
						'storeid'           => $store,
						'app_type'          => $data['app_type'],
						'slider_code'       => $data['slider_code'],
						'slider_label'      => $data['slider_label'],
						'slider_status'     => $data['slider_status'],
						'slider_position'   => $data['slider_position'],
						'slider_settings'   => $data['slider_settings'],
						'slider_productIds' => $data['slider_productIds'],
						'created_time'      => date('Y-m-d'),
						'update_time'       => date('Y-m-d'),
						))->save();
				}
			}
			/* for product sliders - upto here */
    	}
    }

    /**
	 * When deleting store view, all records for that store view will be deleted from following 2 tables
	 * mobicommerce_applications_settings
	 * mobi_app_widgets
     */
    public function __deleteAppsForStore($store)
    {
    	if(!empty($store)){
    		/* for banners and cms */
    		$settings = array("banner_settings", "cms_settings");
    		foreach($settings as $_setting){
	    		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
					->addFieldToFilter('setting_code', $_setting)
					->addFieldToFilter('storeid', $store);
				if($collection->getSize()){
					foreach($collection as $_collection){
						$_collection->delete();
					}
				}
			}
			/* for banners and cms - upto here */

			/* for product sliders */
    		$collection = Mage::getModel('mobiadmin/appwidget')->getCollection()
    			->addFieldToFilter('storeid', $store);
			if($collection->getSize()){
				foreach($collection as $_collection){
					$_collection->delete();
				}
			}
			/* for product sliders - upto here */
    	}
    }
}
?>