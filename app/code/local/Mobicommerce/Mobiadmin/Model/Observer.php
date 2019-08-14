<?php
class Mobicommerce_Mobiadmin_Model_Observer
{
	public function hookToControllerActionPreDispatch($observer)
    {
		if(!file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_assets' . DS . 'mobicommerce.ini')){
			$collection = Mage::getModel('mobiadmin/licence')->getCollection();
			$count = $collection->count();
			if(empty($count)) {
				$this->sendLicenceData();
			}else{
				file_put_contents(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_assets' . DS . 'mobicommerce.ini', md5(sha1($collection->getLastItem()->getMlLicenceKey())));
			}
		}
    }

	public function sendLicenceData()
	{
		$website_url = Mage::getBaseUrl();
		$sales_email = Mage::getStoreConfig('trans_email/ident_sales/email');
		$countryCode = Mage::getStoreConfig('general/country/default');
		$country = Mage::getModel('directory/country')->loadByCode($countryCode)->getName();
		$adminSession = Mage::getSingleton('admin/session');

		$curlData = array();
		$curlData['website_url'] = $website_url;
		$curlData['sales_email'] = $sales_email;
		$curlData['admin_email'] = '';
		if($adminSession->getUser()){
			$curlData['admin_email'] = $adminSession->getUser()->getEmail();
		}

		$curlData['country'] = $country;
		$fields_string = '';
		foreach($curlData as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');

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
		$orderCollection = Mage::getModel('sales/order')->getCollection();
		$firstOrderCollection = $orderCollection->getFirstItem()->getData();
		if (array_key_exists('orderfromplatform', $firstOrderCollection)) {
			if($platform){
				$observer->getEvent()->getOrder()->setOrderfromplatform($platform);
			}else{
				$observer->getEvent()->getOrder()->setOrderfromplatform('');
			}
		}
	}
}
?>