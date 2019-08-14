<?php
class Mobicommerce_Mobiadmin_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public $productInstance;

	public function getAppCmsPage()
	{
		$applicationData = Mage::registry('application_data');
		$applicationCode = $applicationData->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$CmsPageCollection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','cms_settings');
		$CmsPageCollection = $CmsPageCollection->getData();
		$CmsPageData = $CmsPageCollection['0'];
		$CmsPageDataValues = $CmsPageData['value'];	
		$allCmsPageSetting = Mage::helper('mobiadmin')->_jsonUnserialize($CmsPageDataValues);		
		$allCmsPageSetting = $allCmsPageSetting['en_US'];		
		return $allCmsPageSetting;
	}

	public function getAppLocaleCode()
	{
		$applicationData = Mage::registry('application_data');
		$storeId = $applicationData->getAppStoreid();
		$storeLocaleCode = Mage::getStoreConfig('general/locale/code',$storeId);
		return $storeLocaleCode;
	}

	public function getProductSliderCollection()
	{
		$applicationData = Mage::registry('application_data');
		$applicationCode = $applicationData->getAppCode();
		$collection = Mage::getModel('mobiadmin/appwidget')->getCollection();
		$CmsPageCollection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('app_type','product-slider')->setOrder('slider_position', 'ASC');
		return $CmsPageCollection;
	}

	public function getProductCollectionForSlider()
	{
		$applicationData = Mage::registry('application_data');
        $storeId = $applicationData->getAppStoreid();
		$collection = Mage::getModel('catalog/product')->getCollection()->setStoreId($storeId)
			->addAttributeToSelect('*')
			->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
		
		return $collection;		
	}

	public function getAppsSelectedCatIcons()
	{
		$id = Mage::app()->getRequest()->getParam('id', null);
		$model = Mage::getModel('mobiadmin/applications');
		$model->load((int) $id);
		$applicationData = $model;
		$applicationCode = $applicationData->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','category_icons');
        
		$slectedIconscollection = $collection->getData();
		$slectedIconscollection = $slectedIconscollection['0']['value'];
		
		$slectedIconscollection = Mage::helper('mobiadmin')->_jsonUnserialize($slectedIconscollection);
		
        return $slectedIconscollection;
	}

	public function getBannerImages()
	{
		$applicationData = Mage::registry('application_data');
		$applicationCode = $applicationData->getAppCode();
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','banner_settings');
		$slectedBannerscollection = $collection->getData();
		$slectedBannerscollection = $slectedBannerscollection['0']['value'];        
		$slectedBannerscollection = Mage::helper('mobiadmin')->_jsonUnserialize($slectedBannerscollection);
		return $slectedBannerscollection;
	}

	public function getCategoryIconValueByCatId($catId)
	{
		$selectedIcons  = Mage::helper('mobiadmin')->getAppsSelectedCatIcons();
		foreach($selectedIcons as $selectedIcon){
			if($selectedIcon['category_id']==$catId){
			   $mobiicon = $selectedIcon['mobiicon'];
			   return $mobiicon;
			}
		}
	}
	public function getBannerImagesByAppCode($appCode)
	{
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
		$collection = $collection->addFieldToFilter('app_code',$appCode)->addFieldToFilter('setting_code','banner_settings');
		$slectedBannerscollection = $collection->getData();
		$slectedBannerscollection = $slectedBannerscollection['0']['value'];
		$slectedBannerscollection = Mage::helper('mobiadmin')->_jsonUnserialize($slectedBannerscollection);
		return $slectedBannerscollection;
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
        $collection = Mage::getModel('catalog/product')->setStoreId( $storeId )
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
		$slectedThemecollection = $collection->getData();
		$themeName = $slectedThemecollection['0']['value'];
		return $themeName;
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