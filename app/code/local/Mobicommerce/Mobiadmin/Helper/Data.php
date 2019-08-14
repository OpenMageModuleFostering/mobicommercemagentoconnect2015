<?php
class Mobicommerce_Mobiadmin_Helper_Data extends Mage_Core_Helper_Abstract
{
	public $productInstance;

	public function getAppCmsPage()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$CmsPageCollection = $collection->addFieldToFilter('app_code',$appcode)->addFieldToFilter('setting_code','cms_settings');
		$CmsPageCollection = $CmsPageCollection->getData();
		$CmsPageData = $CmsPageCollection['0'];
		$CmsPageDataValues = $CmsPageData['value'];	
		$allCmsPageSetting = Mage::helper('mobiadmin')->_jsonUnserialize($CmsPageDataValues);		
		$allCmsPageSetting = $allCmsPageSetting['en_US'];		
		return $allCmsPageSetting;
	}

	public function getAppLocaleCode()
	{
		$appdata = Mage::registry('application_data');
		$storeId = $appdata->getAppStoreid();
		$storeLocaleCode = Mage::getStoreConfig('general/locale/code',$storeId);
		return $storeLocaleCode;
	}

	public function getProductSliderCollection()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$collection = Mage::getModel('mobiadmin/appwidget')->getCollection();
		$sliderCollection = $collection->addFieldToFilter('app_code',$appcode)->addFieldToFilter('app_type','product-slider')->setOrder('slider_position', 'ASC');
		return $sliderCollection;
	}

	public function getProductCollectionForSlider()
	{
		$appdata = Mage::registry('application_data');
        $storeId = $appdata->getAppStoreid();
		$collection = Mage::getModel('catalog/product')->getCollection()->setStoreId($storeId)
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
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$appcode)->addFieldToFilter('setting_code','category_icons');
        
		$selectedIconCollection = $collection->getData();
		$selectedIconCollection = $selectedIconCollection['0']['value'];
		$selectedIconCollection = Mage::helper('mobiadmin')->_jsonUnserialize($selectedIconCollection);
        return $selectedIconCollection;
	}

	public function getBannerImages()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$appcode)->addFieldToFilter('setting_code','banner_settings');
		$bannersCollection = $collection->getData();
		$bannersCollection = $bannersCollection['0']['value'];        
		$bannersCollection = Mage::helper('mobiadmin')->_jsonUnserialize($bannersCollection);
		return $bannersCollection;
	}

	public function getCategoryIconSettings()
	{
		$appdata = Mage::registry('application_data');
		$appcode = $appdata->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$appcode)->addFieldToFilter('setting_code','category_icons');

		if($collection->count() > 0){
			$categoryIconsCollection = $collection->getData();
			$categoryIconsCollection = $categoryIconsCollection['0']['value'];        
			$categoryIconsCollection = Mage::helper('mobiadmin')->_jsonUnserialize($categoryIconsCollection);
			return $categoryIconsCollection;
		}
		return null;
	}

	public function getBannerImagesByAppCode($appcode)
	{
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code', $appcode)->addFieldToFilter('setting_code','banner_settings');
		$bannersCollection = $collection->getData();
		$bannersCollection = $bannersCollection['0']['value'];
		$bannersCollection = Mage::helper('mobiadmin')->_jsonUnserialize($bannersCollection);
		return $bannersCollection;
	}

	public function getBestSellerProduct($storeId)
	{  
		$collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->setPageSize(10);
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId}",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'))
			->limit(10);
		
		return $collection;     
	}

	public function getNewProductCollection($storeId)
	{
		$todayDate = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getModel('catalog/product')->setStoreId($storeId)
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

	public function getThemeName($appCode)
	{
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$appCode)->addFieldToFilter('setting_code','theme_folder_name');
		$themeCollection = $collection->getData();
		$themename = $themeCollection['0']['value'];
		return $themename;
	}

	public function setLanguageCodeData($localeCode)
	{
        $languageCollection = Mage::getModel('mobiadmin/multilanguage')->getCollection()->addFieldToFilter('mm_language_code',$localeCode);
        $languageCollectionCount = $languageCollection->count();
		if(empty($languageCollectionCount)){
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
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
		$collection = Mage::getModel('mobiadmin/notification')->getCollection()->addFieldToFilter('read_status','0');
		return $collection->count();
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