<?php
class Mobicommerce_Mobiadmin_IndexController extends Mage_Adminhtml_Controller_Action
{
	
    public function indexAction()
    {
	    $this->loadLayout();
		$this->_setActiveMenu('mobiadmin');	
		$this->getLayout()->getBlock('head')->setTitle('Manage Mobile Apps');
	    $this->renderLayout();	
	}
    
	public function _setLanguageCode($localeCode)
	{
       Mage::helper('mobiadmin')->setLanguageCodeData($localeCode);
	}

	public function editAction()
    {
		$id = $this->getRequest()->getParam('id', null);
		$model = Mage::getModel('mobiadmin/applications');
		if($id)
		{
			$model->load((int) $id);
            if ($model->getId()) {                
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mobiadmin')->__('Application does not exist'));
                $this->_redirect('*/*/');
            }
		}
		Mage::register('application_data', $model);
        $appLocaleCode = Mage::helper('mobiadmin')->getAppLocaleCode();
		if($appLocaleCode){
		    $this->_setLanguageCode($appLocaleCode);
		}
	    $this->loadLayout();
		$this->_setActiveMenu('mobiadmin');	
		$applicationData = Mage::registry('application_data');
		$this->getLayout()->getBlock('head')->setTitle($this->__('Edit App '.$applicationData->getAppName()));
	    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();	
	}

	public function saveAction()
	{
		if ( $this->getRequest()->getPost()){
            $AppId = Mage::app()->getRequest()->getPost('appid');
            $postData = $this->getRequest()->getPost();
			$appCode = $postData['appcode'];
			$appKey = $postData['appkey'];
            $error = false;
			/*
			* Saving personalize Data in Media Application Folder
			*/
			$appUrlXmlFile = Mage::getBaseDir('media').DS.'mobi_commerce'.DS.$appCode.DS.'personalizer'.DS.'personalizer.xml';
			
			$doc = new DOMDocument('1.0');
			$doc->formatOutput = true;
			$root = $doc->createElement('mobicommerce_personalizer');
			$root = $doc->appendChild($root);
            foreach($postData['personalizer'] as $keyOption => $option_value){
				$optioncodenode = $doc->createElement($keyOption);
				$newdoc = $root->appendChild($optioncodenode);
			    foreach($option_value as $optioncode => $value){				
				   $em = $doc->createElement($optioncode);       
				   $text = $doc->createTextNode($value);
				   $em->appendChild($text);
				   $newdoc->appendChild($em);
				}
			}
			$doc->save($appUrlXmlFile);
            
			/*
			* Create Css File
			*/

			$theme_folder_name = $postData['themename'];
            if(file_exists ($appUrlXmlFile)){
				$appCssFile = Mage::getBaseDir('media').DS.'mobi_commerce'.DS.$appCode.DS.'personalizer'.DS.'personalizer.css';
				$svgParentFolder =  Mage::getBaseDir('media').DS.'mobi_assets'.DS.'theme_files'.DS.$theme_folder_name.DS.'personalizer'.DS.'svg';
				$svgFolder =  Mage::getBaseDir('media').DS.'mobi_commerce'.DS.$appCode.DS.'personalizer'.DS.'svg';
				$cssOptionPart = array();
                $personalliseXmlData = simplexml_load_file($appUrlXmlFile);
				foreach($personalliseXmlData as $personalliseXmlOption){
				    $OptionCssText = $personalliseXmlOption->css;
					$OptionSvgFilenames = $personalliseXmlOption->svg_filenames;
					$OptionCssText = implode("\r\n", explode('|', $OptionCssText));
				    $OptionCerrentColor = $personalliseXmlOption->current_value;					
					$cssOptionPart[] = str_replace("--COLOR--",$OptionCerrentColor, $OptionCssText);

					if(!empty($OptionSvgFilenames)){
						$OptionSvgFilenames = explode('|', $OptionSvgFilenames);
						if(!empty($OptionSvgFilenames)){
							foreach($OptionSvgFilenames as $svg_filename){
								if(file_exists($svgParentFolder . DS . $svg_filename)){
									$svg_image = file_get_contents($svgParentFolder . DS. $svg_filename);
									preg_match_all('/<style>(.*?)<\/style>/s', $svg_image, $style_tag);
									$old_style_tag = $style_tag[1][0];
									$property = explode('{', $style_tag[1][0]);
									$property = $property[0];
									preg_match_all('/{(.*?)}/s', $style_tag[1][0], $style_tag);
									$param = explode(':', $style_tag[1][0]);
									$param = $param[0];
									$new_style_tag = $property.'{'.$param.':'.$OptionCerrentColor.'!important;}';
									$svg_image = str_replace($old_style_tag, $new_style_tag, $svg_image);
									file_put_contents($svgFolder . DS . $svg_filename, $svg_image);
								}
							}
						}
					}
				}
				file_put_contents($appCssFile,implode($cssOptionPart,"\r\n"));
			}

			/*
			* Saving Application Store Id in Database 
			*/
			
			$setStore = $postData['ddlStore'];
			$appCode = $postData['appcode'];
			$applicationsCollection  = Mage::getModel('mobiadmin/applications')->getCollection();
			$applicationsCollection = $applicationsCollection
				->addFieldToFilter('app_code', $appCode);
			
            foreach($applicationsCollection as $application){
			   $application->setData('app_storeid', $setStore)->save();
			}
			
            
            /*
			* Create Media Url From Media Aplication Path
			*/

            $appinfoimageurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/'. 'mobi_commerce'.'/'.$appCode.'/'.'appinfo'.'/' ;

            /*
			* Create Media Url From Media Aplication Path For Banner
			*/
            $appgalleryimageurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/'. 'mobi_commerce'.'/'.$appCode.'/'.'home_banners'.'/' ;

            /*
			* Saving Push Notification Data With IOSPEM File Uploader
			*/
			if($_FILES['upload_iospem_file']['name'] != '')
			{				
				try{
					$uploader = new Varien_File_Uploader('upload_iospem_file');
                    //$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $uploader->setAllowCreateFolders(true);
					$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce'.DS.$appCode.DS.'appinfo'.DS ;
					$iospemFilename = time() . $_FILES['upload_iospem_file']['name'];
					$uploader->save($media_path, $iospemFilename);
				}catch (Exception $e){
				    Mage::log($e);
                    $this->_redirectError(502);
				}
				$data['upload_iospem_file'] = $iospemFilename;			
			}			
			
			if(!isset($postData['pushnotification']['active_push_notification'])){
			    $postData['pushnotification']['active_push_notification'] = '0';
			}

            if(!isset($postData['pushnotification']['sandboxmode'])){
				$postData['pushnotification']['sandboxmode'] = '0';
			}

			$pushNotificationData = $postData['pushnotification'];
			if($data['upload_iospem_file']){
			    $pushNotificationData['upload_iospem_file_url'] = $appinfoimageurl.$data['upload_iospem_file'];
			    $pushNotificationData['upload_iospem_file'] = $data['upload_iospem_file'];
			}else{
				$pushNotificationData['upload_iospem_file_url'] = $postData['upload_iospem_file']['value'];
				$pushNotificationData['upload_iospem_file'] = $postData['upload_iospem_file_name'];
			}
			if(isset($postData['upload_iospem_file']['delete']) && $postData['upload_iospem_file']['delete'] == 1){
			    $pushNotificationData['upload_iospem_file_url'] = '';
			    $pushNotificationData['upload_iospem_file'] = '';
			}
			$pushNotificationSerData = serialize($pushNotificationData);

            $applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','push_notification');
			foreach($applicationSettingCollection as $pushnotification){
			   $pushnotification->setData('value',$pushNotificationSerData)->save();
			}
            
			/*
			* Saving Application Information Data With App Share Image
			*/
			
			if($_FILES['app_share_image']['name'] != '')
			{
				try{
					$uploader = new Varien_File_Uploader('app_share_image');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $uploader->setAllowCreateFolders(true);
					$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce'.DS.$appCode.DS.'appinfo'.DS ;
					$shareImagename = time() . $_FILES['app_share_image']['name'];
					$uploader->save($media_path, $shareImagename);
				}catch (Exception $e){
				    Mage::log($e);
                    $this->_redirectError(502);
				}
				$data['app_share_image'] = $shareImagename;			
			}			
			$appInfoData = $postData['appinfo'];
			if($data['app_share_image']){
			    $appInfoData['app_share_image'] = $appinfoimageurl.$data['app_share_image'];
			}else{
				$appInfoData['app_share_image'] = $postData['app_share_image']['value'];
			}

			if(isset($postData['app_share_image']['delete']) && $postData['app_share_image']['delete'] == 1)
			{
			    $appInfoData['app_share_image'] = '';
			}

			$appInfoJsonData = serialize($appInfoData);
            $applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','appinfo');    
            foreach($applicationSettingCollection as $appinfo){
			    $appinfo->setData('value',$appInfoJsonData)->save();
			}
			
            
			/*
			* Save Labels and Messages Store Wise Data
			*/
			
            $localeCode = Mage::getStoreConfig('general/locale/code',$setStore);
			
			$languageData = $postData['language_data'];
			foreach ($languageData as $mm_id=>$mm_text){
				$applicationLanguageCollection = Mage::getModel('mobiadmin/multilanguage')->getCollection();
				$applicationLanguageCollection = $applicationLanguageCollection
				   ->addFieldToFilter('mm_language_code',$localeCode)
				   ->addFieldToFilter('mm_id',$mm_id);	
				foreach($applicationLanguageCollection as $applicationLanguage){
					$applicationLanguage->setData('mm_text',$mm_text)->save();
				}
			}	        

			
			/*
			* Save Feature Product Slider Data
			*/
            $featured_products = $postData['featured-products'];
            $featuresliderName = $postData['featured-products-slider-name'];
            $featuresliderPosition = $postData['featured-products-slider-position'];
            $featuresliderStatus = $postData['featured-products-slider-status'];
            $selectedFeatureProduct = array();	

			foreach($featured_products as $index=>$featured_product){					 
				 foreach($featured_products[$index] as $selectedfeature){
				      $selectedFeatureProduct[] = $selectedfeature;
				 }			     
			}
			$selectedFeatureProduct = implode(",",$selectedFeatureProduct);
			
			if(!empty($featuresliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}	
			
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','featured-products');

			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$featuresliderName)
				             ->setData('slider_position',$featuresliderPosition)
				             ->setData('slider_productIds',$selectedFeatureProduct)
				             ->setData('slider_status',$slider_status)
				             ->save();
			}

			/*
			* Save Best Collection Product Slider Data
			*/
            $best_collection = $postData['best-collection'];
            $bestsliderName = $postData['best-collection-slider-name'];
            $bestsliderPosition = $postData['best-collection-slider-position'];
            $bestsliderStatus = $postData['best-collection-slider-status'];
            $selectedBestProduct = array();	
			foreach($best_collection as $index=>$bestCollection){			     
				 foreach($best_collection[$index] as $selectedbest){
				      $selectedBestProduct[] = $selectedbest;
				 }			     
			}
			$selectedBestProduct = implode(",",$selectedBestProduct);			
			if(isset($bestsliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','best-collection');
			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$bestsliderName)
				             ->setData('slider_position',$bestsliderPosition)
				             ->setData('slider_productIds',$selectedBestProduct)
				             ->setData('slider_status',$slider_status)
				             ->save();
			}
            
			/*
			* Save New Arrivals Product Slider Data
			*/			
            $new_arrivals = $postData['new-arrivals'];
            $newsliderName = $postData['new-arrivals-slider-name'];
            $newsliderPosition = $postData['new-arrivals-slider-position'];
            $newsliderStatus = $postData['new-arrivals-slider-status'];
            $selectedNewProduct = array();	

			foreach($new_arrivals as $index=>$new_arrival){			     
				 foreach($new_arrivals[$index] as $selectednew){
				      $selectedNewProduct[] = $selectednew;
				 }			     
			}
			$selectedNewProduct = implode(",",$selectedNewProduct);
			if(isset($newsliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','new-arrivals');
			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$newsliderName)
				             ->setData('slider_position',$newsliderPosition)
				             ->setData('slider_productIds',$selectedNewProduct)
				             ->setData('slider_status',$slider_status)
				             ->save();
			}

			/*
			* Save Best Seller Product Slider Data
			*/		    
            $bestSellers = $postData['best-sellers'];
            $bestSliderName = $postData['best-sellers-slider-name'];
            $bestSliderPosition = $postData['best-sellers-slider-position'];
            $bestSliderStatus = $postData['best-sellers-slider-status'];
            $selectedBestProduct = array();	

			foreach($bestSellers as $index=>$best_seller){			     
				 foreach($bestSellers[$index] as $selectedbest){
				      $selectedBestProduct[] = $selectedbest;
				 }			     
			}
			$selectedBestProduct = implode(",",$selectedBestProduct);
			if(isset($bestSliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','best-sellers');
			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$bestSliderName)
				             ->setData('slider_position',$bestSliderPosition)
				             ->setData('slider_productIds',$selectedBestProduct)
				             ->setData('slider_status',$slider_status)
				             ->save();
			}		

			/*
			* Save Push Notification Setting
			*/
			$pushnotification= $postData['pushnotifications'];
			
            $applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','pushnotifications_settings');    
            foreach($applicationSettingCollection as $appinfo){
			    $appinfo->setData('value',$pushnotification)->save();
			}
            
			/*
			* Save Category Icons
			*/
			if(isset($postData['category-icon'])){
				$category_icons = $postData['category-icon'];
				
				$cate_index = 0;
				$category_iconarray = array();
				foreach($category_icons as $cat_id => $category_icon){
					$category_iconarray[$cate_index]['category_id'] = $cat_id;				
					$category_iconarray[$cate_index]['mobiicon'] = $category_icon;
					$cate_index++;
				}			
				//echo $appCode;

				$category_iconarray = serialize($category_iconarray);	
				
				$applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
				$applicationSettingCollection = $applicationSettingCollection
					->addFieldToFilter('app_code',$appCode)
					->addFieldToFilter('setting_code','category_icons');
				$category_iconsValue = $applicationSettingCollection->getColumnValues('category_icons');
				if(empty($category_iconsValue)){
					$inserData = array('app_code'=>$appCode,'setting_code'=>'category_icons','value'=>$category_iconarray);
					$model = Mage::getModel('mobiadmin/appsetting')->setData($inserData);
					try {
						$insertId = $model->save()->getId();
					} catch (Exception $e){
						echo $e->getMessage();   
					}
				} else {
					foreach($applicationSettingCollection as $categoryIcon){
					   $categoryIcon->setData('value',$category_iconarray)->save();
					}
				}
			}
            
			/*
			* Save Cms Content Data with Contact Information
			*/
            $cmscontentarray = array();
			$cmscontentarray['en_US']['contact_information'] = $postData['contact_information'];
            $cmscontentarray['en_US']['social_media'] = $postData['social_media'];
            $cmscontentarray['en_US']['cms_pages'] = $postData['cms_pages'];
                                   
			$cmscontentarray = serialize($cmscontentarray);
			$applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','cms_settings');
			foreach($applicationSettingCollection as $cmssetting){
			   $cmssetting->setData('value',$cmscontentarray)->save();
			}			
			

			/*
			* Save Banners Url in Database
			*/
			//echo '<pre>';print_r($_FILES);print_r($postData);exit;
			$bannerImages = array();
            $previousBanners = Mage::helper('mobiadmin')->getBannerImagesByAppCode($appCode);
            //echo '<pre>';print_r($previousBanners);exit;
            if(isset($_FILES['banner']['name']) && !empty($_FILES['banner']['name'])){
            	foreach($_FILES['banner']['name'] as $banner_key => $banner){
            		if($_FILES['banner']['name'][$banner_key] == ''){
            			$bannerImages[] = array(
							'url'       => $previousBanners[$banner_key]['url'],
							'is_active' => isset($postData['bannerisactive'][$banner_key]) ? '1': '0'
            				);
            		}
            		else if($_FILES['banner']['error'][$banner_key] != 0){
            			$bannerImages[] = array(
							'url'       => $previousBanners[$banner_key]['url'],
							'is_active' => isset($postData['bannerisactive'][$banner_key]) ? '1': '0'
            				);
            		}
            		else if(!in_array(strtolower(PATHINFO($_FILES['banner']['name'][$banner_key], PATHINFO_EXTENSION)), array('jpg','jpeg','gif','png'))){
            			$bannerImages[] = array(
							'url'       => $previousBanners[$banner_key]['url'],
							'is_active' => isset($postData['bannerisactive'][$banner_key]) ? '1': '0'
            				);
            			Mage::getSingleton('core/session')->addError("Image File Type Must PNG, GIF, JPG");
            		}
            		else{
            			try{
            				/*
							$uploader = new Varien_File_Uploader('banner4');
		                    $uploader->setAllowRenameFiles(false);
		                    $uploader->setFilesDispersion(false);
		                    $uploader->setAllowCreateFolders(true);
		                    */
							$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce'.DS.$appCode.DS.'home_banners'.DS ;
							$filename = rand() . '.' . PATHINFO($_FILES['banner']['name'][$banner_key], PATHINFO_EXTENSION);
							//$uploader->save($media_path, $_FILES['banner']['name'][$banner_key]);
							if (move_uploaded_file($_FILES['banner']['tmp_name'][$banner_key], $media_path . $filename)) {
								$bannerImages[] = array(
									'url'       => $appgalleryimageurl.$filename,
									'is_active' => isset($postData['bannerisactive'][$banner_key]) ? '1': '0'
		            				);
							}
							else{
								$bannerImages[] = array(
									'url'       => $previousBanners[$banner_key]['url'],
									'is_active' => isset($postData['bannerisactive'][$banner_key]) ? '1': '0'
		            				);
								Mage::getSingleton('core/session')->addError("There is some error uploading file");
							}
						}catch (Exception $e){
						    Mage::log($e);
		                    $this->_redirectError(502);
						}
            		}
            	}
            }
            //echo '<pre>';print_r($bannerImages);exit;
			$bannerValue = serialize($bannerImages);
			
            $applicationSettingCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$applicationSettingCollection = $applicationSettingCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','banner_settings');
			foreach($applicationSettingCollection as $bannersColl){
			   $bannersColl->setData('value',$bannerValue)->save();
			}		
			
            /*
			* Save Best Sellers Automated Data
			*/
			$BestSellerCollection = Mage::helper('mobiadmin')->getBestSellerProduct($setStore);
			$BestSellerProducts = $BestSellerCollection;
			$BestSellerProductIds = array();
            foreach($BestSellerProducts as $BestSellerProduct){
			     $BestSellerProductIds[] = $BestSellerProduct->getEntityId();
			}
            $BestSellerProductIds = implode(',',$BestSellerProductIds);
			$sliderStatus = $postData['best-sellers-automated-slider-status'];
			$bestSellerSliderName = $postData['best-sellers-automated-slider-name'];
			$bestSellerSliderPosition = $postData['best-sellers-automated-slider-position'];
			
            $slider_settings = $postData['best-sellers-automated'];
			$slider_settings = json_encode($slider_settings);
			if(!empty($sliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}			
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','best-sellers-automated');
			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$bestSellerSliderName)
				             ->setData('slider_position',$bestSellerSliderPosition)
				             ->setData('slider_productIds',$BestSellerProductIds)
				             ->setData('slider_status',$slider_status)
				             ->setData('slider_settings',$slider_settings)
				             ->save();
			}
            
			/*
			* Save New Products Automated Data in Database
			*/
			$NewProductCollection = Mage::helper('mobiadmin')->getNewProductCollection($setStore);
            $NewProducts = $NewProductCollection;
			$NewProductsIds = array();
            foreach($NewProducts as $NewProduct){
			     $NewProductsIds[] = $NewProduct->getEntityId();
			}
            $NewProductsIds = implode(',',$NewProductsIds);
			$sliderStatus = $postData['new-arrivals-automated-slider-status'];
			$newArrivalsSliderName = $postData['new-arrivals-automated-slider-name'];
			$newArivalsSliderPosition = $postData['new-arrivals-automated-slider-position'];            
			if(!empty($sliderStatus)){
			    $slider_status = '1';
			}else{
			    $slider_status = '0';
			}				
			$productSliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection();
			$productSliderCollection = $productSliderCollection->addFieldToFilter('app_code',$appCode)
				                                               ->addFieldToFilter('slider_code','new-arrivals-automated');
			foreach($productSliderCollection as $productSlider) {
			   $productSlider->setData('slider_label',$newArrivalsSliderName)
				             ->setData('slider_position',$newArivalsSliderPosition)
				             ->setData('slider_productIds',$NewProductsIds)
				             ->setData('slider_status',$slider_status)
				             ->save();
			}          
			
			if(isset($_FILES['popupimage']['name']) && !empty($_FILES['popupimage']['name']))
			{
				try {
					$uploader = new Varien_File_Uploader('popupimage');
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce'.DS.$appCode.DS.'appinfo'.DS ;
					$imgFilename = time() . $_FILES['popupimage']['name'];
					$uploader->save($media_path, $imgFilename);
					$data['popupimage'] = $appinfoimageurl.$imgFilename; 
				}catch(Exception $e){
                    Mage::log($e);
                    $this->_redirectError(502);
				}
			}
            
			/*
			* Save Pop Up Setting
			*/
            $PopUpData = array();
			$PopUpData['enable'] = $postData['popup']['enable'];
			$PopUpData['cookietime'] = $postData['popup']['cookietime'];
            if(isset($data['popupimage'])){
                 $PopUpData['popupimage'] = $data['popupimage'];
			}else{
				 $PopUpData['popupimage'] = $postData['popupimage_hidden'];
			}	
			if(isset($postData['popupimage']['delete']) && $postData['popupimage']['delete'] == 1)
			{
			    $PopUpData['popupimage'] = '';
			}
            $PopUpData = serialize($PopUpData);
            $appPopUpCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();

			$appPopUpCollection = $appPopUpCollection
				->addFieldToFilter('app_code',$appCode)
			    ->addFieldToFilter('setting_code','popup_setting');

            if($appPopUpCollection->count() == '0'){
				$appinfoData = array(
					'app_code'     => $appCode,
					'setting_code' => 'popup_setting',
					'value'        => $PopUpData
				);

				try {
					Mage::getModel('mobiadmin/appsetting')->setData($appinfoData)->save();
				}catch(Exception $e){
					$errors[] = $e->getMessage();   
				}
			    
			}else{
			    foreach($appPopUpCollection as $appPopUp){
					$appPopUp->setData('value',$PopUpData)->save();
				}
			}
			

			/*
			* Sending Android and IOS Push Notification			
			* $pushNotificationData push notification Data From Application Setting Tabs
			* $pushnotification pushnotification setting From PushNotification Tabs
            * 
            */
			$this->androidpushnotification($pushnotification, $pushNotificationData, $appCode);
			$this->iospushnotification($appCode);

			if(isset($postData['udid']) && !empty($postData['udid']))
			{
                $udids = $postData['udid'];
				$datatosend = array('udid' => $udids);
				
				$ch = curl_init();
				$url = Mage::helper('mobiadmin')->curlBuildUrl().'/build/submitudid/'.$appKey.'/'.$appCode; 
				//echo $url;
				curl_setopt($ch, CURLOPT_HEADER, FALSE);
				curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POST, count($datatosend));
				curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($datatosend));
				$result = curl_exec($ch);		
				curl_close($ch);
				
				$result = json_decode($result, true);
				
				if(isset($result)) {
					if($result['status'] == 'success')
					{
						foreach($applicationsCollection as $application){
						   $application
							   ->setData('udid', $udids)
							   ->setData('ios_url', $result['data']['ios_url'])
							   ->setData('ios_status', $result['data']['ios_status'])
							   ->save();
						}
					
					} else {
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($result['message']));
						$this->_redirect('*/*/edit', array(
							'id'    => $AppId,
							'_current'=>true
						));
					}
				}
			}
            if(!empty($errors) && isset($errors)){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($errors));
			}else{
				$message = $this->__('Application is successfully Save.');
                Mage::getSingleton('core/session')->addSuccess($message);
			}

            $AppId = Mage::app()->getRequest()->getPost('appid');
			$this->_redirect('*/*/edit', array(
                'id'    => $AppId,
                '_current'=>true
            )); 
		}		
	}

  	public function deleteAction() 
	{
		$appId = Mage::app()->getRequest()->getParam('id');
		if( $appId ) {
            try {
                $applicationCollection = Mage::getModel('mobiadmin/applications')->getCollection();
				$applicationCollection = $applicationCollection->addFieldToFilter('id',$appId);
				$appData = $applicationCollection->getData();
				$appData = $appData['0'];
                $appCode = $appData['app_code'];

				/*
				* Delete Interies From Widget Table
				* `mobi_app_widgets`
				*/
                $appwidgetModel = Mage::getModel('mobiadmin/appwidget')->getCollection()->addFieldToFilter('app_code',$appCode);
				foreach ($appwidgetModel as $appwidgetModel) {
					$appwidgetModel->delete();
				}
                /*
				* Delete Interies From Application Setting Table 
				* `mobicommerce_applications_settings` 
				*/

                $applicationSetting = Mage::getModel('mobiadmin/appsetting')->getCollection()->addFieldToFilter('app_code',$appCode);
				foreach ($applicationSetting as $application) {
					$application->delete();
				}

				/**
				* Delete Interies from Deveice Token table 
				* `mobicommerce_devicetokens` 
				*/
                
				$deviceTokenCollection = Mage::getModel('mobiadmin/devicetokens')->getCollection()->addFieldToFilter('md_appcode',$appCode);
				foreach ($deviceTokenCollection as $deviceToken) {
					$deviceToken->delete();
				}

                /**
				* Delete Interies From Main Table Of Application 
				* `mobicommerce_applications` 
				*/
				$appCollection = Mage::getModel('mobiadmin/applications')->getCollection()->addFieldToFilter('id',$this->getRequest()->getParam('id'));
                foreach($appCollection as $appDelete){
				    $appDelete->delete();
				}                  
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Application was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
	}

	public function iconsAction()
    {	
		$id = $this->getRequest()->getParam('id', null);
		$model = Mage::getModel('mobiadmin/applications')->load($id);
		if($id){
		    Mage::register('application_data', $model);
		    $this->loadLayout();
            $myBlock = $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_icon');
            $myBlock->setTemplate('mobiadmin/application/edit/tab/icon.phtml');
            $myHtml =  $myBlock->toHtml();      
            $this->getResponse()->setHeader('Content-Type', 'text/html')
                                ->setBody($myHtml);
            return;	        
		}
	}
	
	public function androidpushnotification($pushsetting,$pushnotificationData,$appCode)
	{	
		$devicetokens = array(); 
		$android_key = $pushnotificationData['android_key'];
		$message = $pushsetting;
		$deviceCollection = Mage::getModel('mobiadmin/devicetokens')->getCollection()
			->addFieldToFilter('md_appcode',$appCode)
			->addFieldToFilter('md_devicetype','android');
		foreach($deviceCollection as $device) {
		    $devicetokens[] = $device->getData('md_devicetoken');
		}
		if(!empty($android_key) && !empty($devicetokens) && !empty($message)){
			$msg = array(
				'message' => $message,
				'title'   => $message,
				'vibrate' => 1,
				'sound'   => 1
			);
			$fields = array(
				'registration_ids' => $devicetokens,
				'data'             => $msg
			);

			$headers = array(
				'Authorization: key=' . $android_key,
				'Content-Type: application/json'
			);
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			curl_close( $ch );
		}
	}

	function iospushnotification($appCode)
	{	
		$deviceToken = array();
		$pemFile     = "";
		$passphrase  = "";
		$message     = "";
		$sandboxmode    = false;

        $deviceCollection = Mage::getModel('mobiadmin/devicetokens')->getCollection()
			->addFieldToFilter('md_appcode',$appCode)
			->addFieldToFilter('md_devicetype','android');
		foreach($deviceCollection as $device) {
		    $deviceToken[] = $device->getData('md_devicetoken');
		}
		
        $pushCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code',$appCode)
			->addFieldToFilter('setting_code','push_notification');
		 
		if($pushCollection->count() != '0') {
            $pushUnSerData = $pushCollection->getData();
            $a = unserialize($pushUnSerData['value']);
			
			$passphrase = $a['pem_password'];
			if(!empty($a['upload_iospem_file_url'])):
				$pemFile = pathinfo($a['upload_iospem_file_url'], PATHINFO_BASENAME);
				$pemFile = Mage::getBaseDir('media').'/mobi_commerce/'.$data['app_code'].'/certificates/'.$pemFile;
			endif;

			if(isset($a['sandboxmode']) && $a['sandboxmode'] == '1'):
				$sandboxmode = true;
			endif;
		}
        $pushMessageCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code',$appCode)
			->addFieldToFilter('setting_code','pushnotifications_settings');

        if($pushMessageCollection->count() != '0') {
			$pushMessage = $pushMessageCollection->getData();
            $message = $pushMessage['value'];
		}
		

		if(!empty($pemFile) && !empty($message)){
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

			// Open a connection to the APNS server
			$push_url = "ssl://gateway.push.apple.com:2195";
			if($sandboxmode){
				$push_url = "ssl://gateway.sandbox.push.apple.com:2195";
			}

			$fp = stream_socket_client(
				$push_url, $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

			if (!$fp)
				return "Failed to connect: $err $errstr" . PHP_EOL;

			//echo 'Connected to APNS' . PHP_EOL;

			// Create the payload body
			$body['aps'] = array(
				'alert' => $message,
				'sound' => 'default'
				);

			// Encode the payload as JSON
			$payload = json_encode($body);

			if(!empty($deviceToken))
			{
				foreach ($deviceToken as $key => $value) {
					$msg = chr(0) . pack('n', 32) . pack('H*', $value) . pack('n', strlen($payload)) . $payload;

					// Send it to the server
					$result = fwrite($fp, $msg, strlen($msg));

					if (!$result){
						//echo 'Message not delivered' . PHP_EOL;
					}
					else{
						//echo 'Message successfully delivered' . PHP_EOL;		
					}
				}
			}

			// Close the connection to the server
			fclose($fp);
			return true;
		}
		return false;
	}

	public function newAction()
	{
	    $this->loadLayout();
		$this->_setActiveMenu('mobiadmin');	
		$this->getLayout()->getBlock('head')->setTitle('Create New Mobile App');
	    $this->renderLayout();
	}

	public function notificationAction()
	{
	    $this->loadLayout();
		$this->_setActiveMenu('mobiadmin');	
		$this->getLayout()->getBlock('head')->setTitle('Mobicommerce Notification');
	    $this->renderLayout();
	}

	public function massReadAction()
	{
		$ids = Mage::app()->getRequest()->getParam('ids'); 
		
		if(is_array($ids)){		   		
			foreach($ids as $id)
			{
			   $model = Mage::getModel('mobiadmin/notification');
                  
               $model->setId($id)->setReadStatus('1')
                    ->save();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The message has been marked as read.'));
			$this->_redirect('mobicommerce/index/notification');
		}
		$this->_redirect('mobicommerce/index/notification');
	   
	}

	public function massDeleteAction()
	{
	    $ids = Mage::app()->getRequest()->getParam('ids'); 
		
		if(is_array($ids)){		   		
			foreach($ids as $id)
			{
			   $model = Mage::getModel('mobiadmin/notification');
                  
               $model->setId($id)
                    ->delete();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message was successfully deleted'));
			$this->_redirect('mobicommerce/index/notification');
		}
		$this->_redirect('mobicommerce/index/notification');
	}
    
	public function deletenotificationAction()
	{
		if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('mobiadmin/notification');
                  
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                      
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Message was successfully deleted'));
                $this->_redirect('mobicommerce/index/notification');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('mobicommerce/index/notification');
	}

	public function readnotificationAction()
	{
		if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('mobiadmin/notification');
                  
                $model->setId($this->getRequest()->getParam('id'))
                    ->setReadStatus('1')->save();
                      
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The message has been marked as read.'));
                $this->_redirect('mobicommerce/index/notification');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('mobicommerce/index/notification');	   
	}

	public function createAppAction()
	{	
		
		$refererUrl = $this->_getRefererUrl();
		$validation = true;
		if(!empty($_FILES)) {
			if($_FILES['appsplash']['name'] != '' && strtolower(PATHINFO($_FILES['appsplash']['name'], PATHINFO_EXTENSION)) != 'png'){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Splash must be png'));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				$validation = false;
			}

			if($_FILES['applogo']['name'] != '' && strtolower(PATHINFO($_FILES['applogo']['name'], PATHINFO_EXTENSION)) != 'png'){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Logo must be png'));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				$validation = false;
			}

			if($_FILES['appicon']['name'] != '' && strtolower(PATHINFO($_FILES['appicon']['name'], PATHINFO_EXTENSION)) != 'png'){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Icon must be png'));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				$validation = false;
			}

			if(!$validation)
			{
				$url = $refererUrl;
				Mage::app()->getFrontController()->getResponse()->setRedirect($url);
				return;
			}
		}
		
		$postData = Mage::app()->getRequest()->getPost();
		if(!isset($postData)){
		    $url = $refererUrl;
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
			return;
		}
		$this->_sendEmailBeforeCreateApp($postData);
		$resource = Mage::getSingleton('core/resource');
		$connection = $resource->getConnection('core_read');
		$app_table_name = $resource->getTableName('mobiadmin/applications');
		$tableStatus = $connection->showTableStatus($app_table_name);
		$next_app_id = $tableStatus['Auto_increment'];		
		$stores = Mage::app()->getStores();
		$storeIds = array();
		foreach($stores as $store){
		    $storeIds[] = $store->getData('store_id');
		}
		
		$curlData = array();
		
		$curlData = $postData;
		$mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'mobi_commerce/';
		$mediaMobiAssetUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'mobi_assets/defaults/';
		
		
		//Upload AppSplash
		if(isset($_FILES['appsplash']['name']) && !empty($_FILES['appsplash']['name']))
		{			
			try {
				$uploader = new Varien_File_Uploader('appsplash');
				$uploader->setAllowRenameFiles(false);
				$uploader->setAllowCreateFolders(true);
				$uploader->setFilesDispersion(false);
				$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce';
				$imgFilename =  time() .$_FILES['appsplash']['name'];				
				$uploader->save($media_path, $imgFilename);
				$curlData['appsplash'] = $mediaUrl.$imgFilename; 
			}catch(Exception $e){
				Mage::log($e);
				$this->_redirectError(502);
			}
		}

		//Upload applogo
		if(isset($_FILES['applogo']['name']) && !empty($_FILES['applogo']['name']))
		{
			try {
				$uploader = new Varien_File_Uploader('applogo');
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce';
				$imgFilename =  time() .$_FILES['applogo']['name'];
				$uploader->save($media_path, $imgFilename);
				$curlData['applogo'] = $mediaUrl.$imgFilename; 
			} catch(Exception $e) {
				Mage::log($e);
				$this->_redirectError(502);
			}
		}

		//Upload appicon
		if(isset($_FILES['appicon']['name']) && !empty($_FILES['appicon']['name']))
		{
			try {
				$uploader = new Varien_File_Uploader('appicon');
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);
				$media_path = Mage::getBaseDir('media') .DS. 'mobi_commerce';
				$imgFilename =  time() .$_FILES['appicon']['name'];
				$uploader->save($media_path, $imgFilename);
				$curlData['appicon'] = $mediaUrl.$imgFilename; 
			}catch(Exception $e){
				Mage::log($e);
				$this->_redirectError(502);
			}
		}

		$db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$db->closeConnection();
		$db->getConnection();
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$db->closeConnection();
		$db->getConnection();
		
		if(isset($curlData['appsplash'])){
			$size = getimagesize($curlData['appsplash']);
			$maxWidth = 1536;
            $maxHeight = 2048;
			if ($size[0] != $maxWidth || $size[1] != $maxHeight)
            {
				@unlink($curlData['appsplash']);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Appsplash Icon dimenssion must be 1536X2048'));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				$url = $refererUrl;
			    Mage::app()->getFrontController()->getResponse()->setRedirect($url);			
			    return;
			}
		}
		if(isset($curlData['appicon'])){
			$size = getimagesize($curlData['appicon']);
			$maxWidth = 1024;
            $maxHeight = 1024;
			if ($size[0] != $maxWidth || $size[1] != $maxHeight)
            {
				@unlink($curlData['appicon']);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('App Icon dimenssion must be 1024X1024'));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				$url = $refererUrl;
			    Mage::app()->getFrontController()->getResponse()->setRedirect($url);
			    return;
			}
		}
		if(!isset($curlData['appsplash']))
		{
			$curlData['appsplash'] = $mediaMobiAssetUrl.'splash.png'; 
		}
		if(!isset($curlData['applogo'])) {
			$curlData['applogo'] = $mediaMobiAssetUrl.'logo.png'; 
		}
		if(!isset($curlData['appicon'])) {
			$curlData['appicon'] = $mediaMobiAssetUrl.'icon.png'; 
		}
        $storeId = $curlData['store'];
        $curlData['approoturl'] = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		$curlData['media_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		/* code for licence key */
		$LicenceModel = Mage::getModel('mobiadmin/licence')->getCollection();
		$licencekey = "";
		if($LicenceModel->getLastItem())
		{
			$licencekey = $LicenceModel->getLastItem()->getMlLicenceKey();
		}
		$curlData['applicencekey'] = $licencekey;
		/* code for licence key - upto here */
		
		$fields_string = '';
        foreach($curlData as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
        rtrim($fields_string, '&');
        
		$ch = curl_init();

		$url = Mage::helper('mobiadmin')->curlBuildUrl().'build/add'; 
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE); // remove body 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($curlData));
		curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($curlData));
		$result = curl_exec($ch);	
		curl_close($ch);	
		
		$result = json_decode($result, true);		
		
		$db = Mage::getSingleton('core/resource')->getConnection('core_read');
		$db->closeConnection();
		$db->getConnection();
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$db->closeConnection();
		$db->getConnection();
		
		if(isset($result)) {
		    if($result['status'] == 'success')
		    {
				$saveData = array();
				$saveData['app_name'] = $curlData['appname'];
				$saveData['app_code'] = $result['data']['appcode'];
				$saveData['app_preview_code'] = $result['data']['appkey'];
				$saveData['app_logo'] = $curlData['applogo'];
				$saveData['app_theme_folder_name'] = $curlData['apptheme'];
				$saveData['app_licence_key'] = '';
				$android_url = $result['data']['android_url'];
				$ios_url = $result['data']['ios_url'];
				$android_status = $result['data']['android_status'];
				$ios_status = $result['data']['ios_status'];
				$webapp_url = $result['data']['webapp_url'];
				$saveData['webapp_url'] = $webapp_url;
				$udids = $curlData['udid'];
				if(!empty($udids))
				{
                    $saveData['udid'] = $udids;
				}
				$saveData['android_url'] = $android_url;
				$saveData['ios_url'] = $ios_url;
				$saveData['android_status'] = $android_status;
				$saveData['ios_status'] = $ios_status;
				if(!empty($licencekey)) {
                    $saveData['app_license_key'] = $licencekey;
				}
				if($saveData['app_code']) {
				    Mage::getModel('mobiadmin/applications')->saveApplicationData($saveData ,$storeId);
				}else{
				    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($result['message']));
					Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Your mobile app has been succesfully created, you can now personalize it through app setting given below.'));
				$this->_redirect('mobicommerce/index/edit', 
					array(
                    'id'    => $next_app_id,
                    '_current'=>true
                ));
		    }else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($result['message']));
				Mage::getSingleton('core/session')->setData( 'createapp', Mage::app()->getRequest()->getPost());
			    $url = $refererUrl;
			    Mage::app()->getFrontController()->getResponse()->setRedirect($url);
				return;
			}
		}else{
		   $url = $refererUrl;
		   Mage::app()->getFrontController()->getResponse()->setRedirect($url);
	       return;
		}
	}

	public function sendemailAction()
	{
	    $postData = Mage::app()->getRequest()->getPost();
		$isAjax = Mage::app()->getRequest()->getParam('isAjax');
		if($isAjax) {
			$user = Mage::getSingleton('admin/session');
			$userEmail = $user->getUser()->getEmail();
			$userFirstname = $user->getUser()->getFirstname();
		    
		    $fromEmail = $userEmail;
			$fromName = $userFirstname; 
			$toEmail = $postData['emailid'];
			if($postData['templatetype'] == 'android'){
				$body = "<b>Hello</b>, <br><br> ".$fromName." has sent you a MobiCommerce Android Demo App to review, by clicking the URL you can download and install the Mobile app in your Mobile Device.<br><br> MobiCommerce Android  App URL: ".$postData['appurl']." <br><br><i>'".$this->__('Note: If you have any mobicommerce demo app installed in your mobile device please uninstall that before installing a new mobicommerce demo app')."'</i> <br><br> Regards";
			}elseif($postData['templatetype'] == 'ios')
			{
				$body = "<b>Hello</b>, <br><br> ".$fromName." has sent you a MobiCommerce iOS Demo App to review, by clicking the URL you can download and install the Mobile app in your Mobile Device.<br><br> MobiCommerce iOS  App URL: ".$postData['appurl']." <br><br><i>'".$this->__('Note: If you have any mobicommerce demo app installed in your mobile device please uninstall that before installing a new mobicommerce demo app')."'</i> <br><br> Regards";
			}elseif($postData['templatetype'] == 'website')
			{
				$body = "<b>Hello</b>, <br><br> ".$fromName." has sent you a MobiCommerce provided Mobile Website to review, by clicking the URL you can review mobile website in your Mobile Devices.<br><br> MobiCommerce Mobile Website URL: ".$postData['appurl']."  <br><br> Regards";
			}
			$subject = "Mobicommerce App URL";
			$mail = new Zend_Mail();
			$mail->setBodyText('Mobicommerce App Url');
			$mail->setBodyHtml($body);
			$mail->setFrom($fromEmail, $fromName);
			$mail->addTo($toEmail);
			$mail->setSubject($subject);
			//$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
			try {
			    $mail->send();
				$response['status'] = "success";
				$response['success'] = 'Successfully sent Email.';
				$this->getResponse()->setBody(json_encode($response));	
			} catch(Exception $ex) {
				$response['status'] = "fail";
			    $response['error'] = 'Unable to send email.';
				$this->getResponse()->setBody(json_encode($response));
			}
		}		
	}

	public function _sendEmailBeforeCreateApp($postdata)
	{
		if(!empty($postdata))
		{
			$appName = $postdata['appname'];
			$storeId = $postdata['store'];
            $storeUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
			$emailId = $postdata['primaryemail'];
			$phone = $postdata['phone'];
			if(!empty($postdata['udid']))
			{
				$udid = $postdata['udid'];
			}
			$body = "App Name:- ".$appName." <br>  Store Url:-  ".$storeUrl." <br> Email Id :- ".$emailId." <br> Phone Number:- ".$phone."";
            $toEmail = Mage::helper('mobiadmin')->mobicommerceEmailId();
			$subject = "Create App Request From ".$storeUrl;
			$user = Mage::getSingleton('admin/session');
			$userEmail = $user->getUser()->getEmail();
			$userFirstname = $user->getUser()->getFirstname();
		    
		    $fromEmail = $userEmail;
			$fromName = $userFirstname; 
			$mail = new Zend_Mail();
			$mail->setBodyText('Mobicommerce Create App Request');
			$mail->setBodyHtml($body);
			$mail->setFrom($fromEmail, $appName);
			$mail->addTo($toEmail, 'Mobicommerce');
			$mail->setSubject($subject);
			try {
                $mail->send();
			}catch (Exception $e)
			{
				 
			}
		}
	}
}