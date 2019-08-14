<?php
class Mobicommerce_Mobiadmin_Helper_Data extends Mage_Core_Helper_Abstract
{
	public $productInstance;

	public function getAppCmsPage()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$storeid = Mage::app()->getRequest()->getParam('store', null);

		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','cms_settings');
		$collection->addFieldToFilter('storeid', $storeid);
		$data = $collection->getFirstItem()->getValue();
		$data = Mage::helper('mobiadmin')->_jsonUnserialize($data);		
		$data = $data['en_US'];
		return $data;
	}

	public function getAppLocaleCode()
	{
		$storeid = Mage::app()->getRequest()->getParam('store', null);
		$locale = Mage::getStoreConfig('general/locale/code', $storeid);
		return $locale;
	}

	public function getProductSliderCollection()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$storeid = Mage::app()->getRequest()->getParam('store', null);
		$collection = Mage::getModel('mobiadmin/appwidget')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('storeid', $storeid)
			->addFieldToFilter('app_type', 'product-slider')
			->setOrder('slider_position', 'ASC');
		return $collection;
	}

	public function getProductCollectionForSlider()
	{
		$storeid = Mage::app()->getRequest()->getParam('store', null);
		$collection = Mage::getModel('catalog/product')->getCollection()
			->setStoreId($storeid)
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
		$collection->setPageSize(200);
		return $collection;		
	}

	public function getAppsSelectedCatIcons()
	{
		$id = Mage::app()->getRequest()->getParam('id', null);
		$model = Mage::getModel('mobiadmin/applications');
		$model->load((int) $id);
		$appcode = $model->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'category_icons');
        
		$data = $collection->getData();
		$data = $data['0']['value'];
		$data = Mage::helper('mobiadmin')->_jsonUnserialize($data);
        return $data;
	}

	public function getBannerImages()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$storeid = Mage::app()->getRequest()->getParam('store');
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'banner_settings')
	    	->addFieldToFilter('storeid', $storeid);

		$data = $collection->getData();
		$data = $data['0']['value'];        
		$data = Mage::helper('mobiadmin')->_jsonUnserialize($data);
		return $data;
	}

	public function getCategoryIconSettings()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'category_icons');

		if($collection->getSize() > 0){
			$data = $collection->getData();
			$data = $data['0']['value'];        
			$data = Mage::helper('mobiadmin')->_jsonUnserialize($data);
			return $data;
		}
		return null;
	}

	public function getBannerImagesByAppCode($appcode)
	{
		$storeid = Mage::app()->getRequest()->getParam('store');
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('storeid', $storeid)
			->addFieldToFilter('setting_code', 'banner_settings');
		$data = $collection->getData();
		$data = $data['0']['value'];
		$data = Mage::helper('mobiadmin')->_jsonUnserialize($data);
		return $data;
	}

	public function getBestSellerProduct($storeid)
	{  
		$collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->setPageSize(10);
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeid}",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'))
			->limit(10);
		
		return $collection;     
	}

	public function getNewProductCollection($storeid)
	{
		$todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getModel('catalog/product')->setStoreId($storeid)
			->getCollection()
			->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
			->addAttributeToFilter('news_to_date', array('or'=> array(
			    0 => array('date' => true, 'from' => $todayDate),
			    1 => array('is' => new Zend_Db_Expr('null')))
			), 'left')
			->addAttributeToSort('news_from_date', 'desc')
			->addAttributeToSort('created_at', 'desc'); 
		$collection->getSelect()->limit(10);
		return $collection;		
	}

	public function getThemeName($appcode)
	{
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'theme_folder_name');
		$data = $collection->getData();
		return $data['0']['value'];
	}

	public function setLanguageCodeData($locale)
	{
        $collection = Mage::getModel('mobiadmin/multilanguage')->getCollection()->addFieldToFilter('mm_language_code', $locale);
        $count = $collection->getSize();
		if(empty($count)){
			$resource = Mage::getSingleton('core/resource');
			$writeConnection = $resource->getConnection('core_write');
			$query = "INSERT INTO ".Mage::getSingleton('core/resource')->getTableName('mobicommerce_multilanguage')." (mm_language_code, mm_type, mm_label_code, mm_label, mm_maxlength, mm_text, mm_help) SELECT '".$localeCode."' AS mm_language_code, mm_type, mm_label_code, mm_label, mm_maxlength, mm_text, mm_help FROM ".Mage::getSingleton('core/resource')->getTableName('mobicommerce_multilanguage')." WHERE mm_language_code = 'en_US'";
			$writeConnection->query($query);
		}
	}	

	public function _jsonUnserialize($data = null)
	{
		$jsonData = json_decode($data, true);
		if(is_array($jsonData)){
			return $jsonData;
		}
		else{
			return unserialize($data);
		}
	}

	public function buyNowUrl()
	{
		return 'http://www.mobi-commerce.net/index.php/mobiweb/index/addtocart';
	}

	public function getCountUnreadNotification()
	{
		$table = Mage::getSingleton('core/resource')->getTableName('mobicommerce_notification');
		$tableExists = Mage::getSingleton('core/resource')->getConnection('core_write')->showTableStatus($table);
		if($tableExists){
			$collection = Mage::getModel('mobiadmin/notification')->getCollection()->addFieldToFilter('read_status', '0');
			return $collection->count();
		}
		return 0;
	}

	public function curlBuildUrl()
	{
		return 'http://build.mobi-commerce.net/';
	}

	public function mobicommerceEmailId()
	{
		return 'plugin@mobi-commerce.net';
	}
}