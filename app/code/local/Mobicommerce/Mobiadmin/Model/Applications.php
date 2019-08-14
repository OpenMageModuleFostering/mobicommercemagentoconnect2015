<?php
class Mobicommerce_Mobiadmin_Model_Applications extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('mobiadmin/applications');
    }

	public function saveApplicationData($saveData, $store_id=null)
	{
		$appid = null;
		$errors = array();
		if(empty($store_id)){
			$store_id = Mage::app()
				->getWebsite()
				->getDefaultGroup()
				->getDefaultStoreId();
		}

		$app_name        = $saveData['app_name'];
		$app_code        = $saveData['app_code'];
		$app_key         = $saveData['app_preview_code'];
		$app_logo        = $saveData['app_logo'];
		$app_licence_key = $saveData['app_license_key'];
		$android_url     = $saveData['android_url'];
		$ios_url         = $saveData['ios_url'];
		$android_status  = $saveData['android_status'];
		$ios_status      = $saveData['ios_status'];
		$app_storeid     = $store_id;
		$webapp_url      = $saveData['webapp_url'];
		$udid            = $saveData['udid'];

		$appExist = Mage::getModel('mobiadmin/applications')->getCollection()
			->addFieldToFilter('app_code', $app_code)
			->addFieldToFilter('app_key', $app_key)->count();
		if(!$appExist){
			$this->_create_mobi_media_dir($saveData['app_code'], $saveData['app_theme_folder_name']);
			$applicationData = array(
				'app_name'        => $app_name,
				'app_code'        => $app_code,
				'app_key'         => $app_key,
				'app_logo'        => $app_logo,
				'app_license_key' => $app_licence_key,
				'app_storeid'     => $app_storeid,
				'app_mode'        => 'demo',
				'created_time'    => date('Y-m-d H:i:s'),
				'android_url'     => $android_url,
				'android_status'  => $android_status,
				'webapp_url'      => $webapp_url,
				'udid'            => $udid,
				'ios_url'         => $ios_url,
				'ios_status'      => $ios_status,
				);
				//save in application info
			try {
				$appid = Mage::getModel('mobiadmin/applications')->setData($applicationData)->save()->getId();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}

			$appinfo = serialize(array(
				'android_appname'      => $app_name,
				'android_appweburl'    => '',
				'android_appmobileurl' => '',
				'ios_appname'          => $app_name,
				'ios_appweburl'        => '',
				'ios_appmobileurl'     => '',
				'app_description'      => '',
				'app_share_image'      => '',
				));
			
			$appinfoData = array(
				'app_code'     => $app_code,
				'setting_code' => 'appinfo',
				'value'        => $appinfo
			);

			try{
				Mage::getModel('mobiadmin/appsetting')->setData($appinfoData)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}

			$categoryIconSetting = array("MAGENTO_CATEGORY_THUMBNAIL" => 0);
			if(in_array($saveData['app_theme_folder_name'], array("fashion_and_style")))
				$categoryIconSetting = array("MAGENTO_CATEGORY_THUMBNAIL" => 1);
			$categoryIconData = array(
				'app_code'     => $app_code,
				'setting_code' => 'category_icons',
				'value'        => serialize($categoryIconSetting)
				);
			try{
				Mage::getModel('mobiadmin/appsetting')->setData($categoryIconData)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}
	
			$pushValue = array(
				'active_push_notification' => 1,
				'android_key'              => 'AIzaSyC5tw0jTUeFfcm2kvYMDk4AudnnF5DmJuM',
				'android_sender_id'        => '881306584774',
				'upload_iospem_file'       => null,
				'upload_iospem_file_url'   => null,
				'pem_password'             => null,
				'sandboxmode'              => 0
			);
			$pushValue = serialize($pushValue);
			$appNotificationData = array(
				'app_code'     => $app_code,
				'setting_code' => 'push_notification',
				'value'        => $pushValue
			);

			try {
				Mage::getModel('mobiadmin/appsetting')->setData($appNotificationData)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}

			$appthemesetting = array(
				'app_code'     => $app_code,
				'setting_code' => 'theme_folder_name',
				'value'        => $saveData['app_theme_folder_name']
			);

			try {
				Mage::getModel('mobiadmin/appsetting')->setData($appthemesetting)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}

            $popUpData = array(
				'enable'     => '0',
				'cookietime' => '2592000',
				'popupimage' => ''
			);

			$appPopUpsetting = array(
				'app_code'     => $app_code,
				'setting_code' => 'popup_setting',
				'value'        => serialize($popUpData)
			);

			try {
				Mage::getModel('mobiadmin/appsetting')->setData($appPopUpsetting)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}
			
			$app_home_banners = array();
			$dir = Mage::getBaseDir('media').DS.'mobi_assets'.DS.'theme_files'.DS.$saveData['app_theme_folder_name'].DS.'home_banners';
			$cdir = scandir($dir);
			$bannerCount = 0;
			foreach($cdir as $key => $value){
				if(!in_array($value,array(".","..")) && !is_dir($dir . DIRECTORY_SEPARATOR . $value)){
					$img = $this->file_get_contents_curl($dir . DIRECTORY_SEPARATOR . $value);
					file_put_contents(Mage::getBaseDir('media').'/mobi_commerce/'.$app_code.'/home_banners/'.$value, $img);
					$app_home_banners[] = array(
						'url'       => Mage::getBaseUrl('media').'mobi_commerce/'.$app_code.'/home_banners/'.$value,
						'is_active' => (($bannerCount > 1)?'0':'1'),
						);
					$bannerCount++;
				} 
			}

			if(empty($app_home_banners)){
				for($i=0;$i<5;$i++){
					$app_home_banners[] = array(
						'url' => '',
						'is_active' => '0'
						);
				}
			}

			$banner_settings = array(
				'app_code'     => $app_code,
				'setting_code' => 'banner_settings',
				'value'        => serialize($app_home_banners)
				 ); 

			try {
				Mage::getModel('mobiadmin/appsetting')->setData($banner_settings)->save();
			}catch(Exception $e){
				$errors[] = $e->getMessage();   
			}
			
			$now = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
			$setting = json_encode(array('no_of_days' => 90));

			/* code to get 10 random featured products */
			$featuredProducts = Mage::getModel('mobiservices/catalog_catalog')->getRandomProducts($store_id, 10);
			$featuredProductsIds = array();
			if(!empty($featuredProducts)){
				foreach ($featuredProducts as $key => $value) {
					$featuredProductsIds[] = $value['entity_id'];
				}
			}
			$featuredProductsIds = implode(',', $featuredProductsIds);
			/* code to get 10 random featured products - upto here */

			$productSliderSettings = array(
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'featured-products',
					'slider_label'      => 'Featured Products',
					'slider_status'     => '1',
					'slider_position'   => '1',
					'slider_settings'   => '',
					'slider_productIds' => $featuredProductsIds,
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'best-collection',
					'slider_label'      => 'Best Collection',
					'slider_status'     => '0',
					'slider_position'   => '2',
					'slider_settings'   => '',
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'new-arrivals',
					'slider_label'      => 'New Arrivals',
					'slider_status'     => '0',
					'slider_position'   => '3',
					'slider_settings'   => '',
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'best-sellers',
					'slider_label'      => 'Best Seller',
					'slider_status'     => '0',
					'slider_position'   => '4',
					'slider_settings'   => '',
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'new-arrivals-automated',
					'slider_label'      => 'New Arrivals',
					'slider_status'     => '1',
					'slider_position'   => '5',
					'slider_settings'   => '',
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'best-sellers-automated',
					'slider_label'      => 'Best Seller (Automated)',
					'slider_status'     => '0',
					'slider_position'   => '6',
					'slider_settings'   => $setting,
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				array(
					'app_code'          => $app_code,
					'app_type'          => 'product-slider',
					'slider_code'       => 'recently-viewed-automated',
					'slider_label'      => 'Recently Viewed',
					'slider_status'     => '1',
					'slider_position'   => '7',
					'slider_settings'   => '',
					'slider_productIds' => '',
					'created_time'      => $now,
					'update_time'       => $now,
					),
				);
			foreach($productSliderSettings as $productSliderSetting){
				try{
					Mage::getModel('mobiadmin/appwidget')->setData($productSliderSetting)->save();
				}
				catch(Exception $e){
					$errors[] = $e->getMessage();
				}
			}
			$contact_data = '<div class="detail-box no-border"><div class="li-row"><div><h3 style="margin:0">Your Store Name</h3><span class="small"><p>Full Address of the  store</p></span></div></div><a href="" class="li-row ui-link"><div>Tel: +xx-xxx-xxx-xxxx</div></a><a href="" class="li-row ui-link"><div>Email: email@yourdomain.com</div></a></div>';
			$cms_contents = array(
				"en_US" => array(
					"contact_information" => array(
						"company_name"    => "Your Company Name",
						"company_address" => "Your company addresss here",
						"phone_number"    => "+0-000-000-0000",
						"email_address"   => "mail@yourdomain.com",
						),
					"social_media" => array(
						"facebook"       => "1",
						"facebook_url"   => "https://www.facebook.com/mobi.commerce.platform",
						"twitter"        => "1",
						"twitter_url"    => "https://twitter.com/mobicommerceapp",
						"linkedin"       => null,
						"linkedin_url"   => null,
						"googleplus"     => "1",
						"googleplus_url" => "https://plus.google.com/113187082679438496885/",
						"youtube"        => null,
						"youtube_url"    => null,
						"pinterest"      => null,
						"pinterest_url"  => null,
						"blog"           => null,
						"blog_url"       => null,
						),
					"cms_pages" => array(
						"page_1" => array(
							"page_title"       => "About Us",
							"active"           => "1",
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => "Coming Soon"
							),
						"page_2" => array(
							"page_title"       => "Privacy Policy",
							"active"           => "1",
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => "Coming Soon"
							),
						"page_3" => array(
							"page_title"       => "Term and Conditions",
							"active"           => "1",
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => "Coming Soon"
							),
						"page_4" => array(
							"page_title"       => "Contact Us",
							"active"           => "1",
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => $contact_data
							),
						"page_5" => array(
							"page_title"       => null,
							"active"           => null,
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => null
							),
						"page_6" => array(
							"page_title"       => null,
							"active"           => null,
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => null
							),
						"page_7" => array(
							"page_title"       => null,
							"active"           => null,
							"link_to_external" => null,
							"page_url"         => null,
							"page_content"     => null
							),
						),
					)
				);
			$cms_contents = serialize($cms_contents);
			$cmsData = array(
				'app_code'     => $app_code,
				'setting_code' => 'cms_settings',
				'value'        => $cms_contents
			);
			try{
				Mage::getModel('mobiadmin/appsetting')->setData($cmsData)->save();
			}
			catch(Exception $e){
				$errors[] = $e->getMessage();
			}
        }
		return array(
			'appid' => $appid,
			'errors' => $errors
			);
	}

	protected function file_get_contents_curl($url) {
		if(function_exists('file_get_contents'))
			return file_get_contents($url);
		else{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

			$data = curl_exec($ch);
			curl_close($ch);

			return $data;
		}
	}

	protected function _create_mobi_media_dir($app_code = null, $app_theme_folder_name = null)
	{
        $base_dir = Mage::getBaseDir('media');
        if(!(is_dir($base_dir.'/mobi_commerce') && file_exists($base_dir.'/mobi_commerce')))
            mkdir($base_dir.'/mobi_commerce', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code) && file_exists($base_dir.'/mobi_commerce/'.$app_code)))
            mkdir($base_dir.'/mobi_commerce/'.$app_code, 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/home_banners') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/home_banners')))
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/home_banners', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/appinfo') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/appinfo')))
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/appinfo', 0777, true);
        
        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/certificates') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/certificates')))
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/certificates', 0777, true);

        if(!(is_dir($base_dir.'/mobi_commerce/'.$app_code.'/personalizer') && file_exists($base_dir.'/mobi_commerce/'.$app_code.'/personalizer'))){
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/personalizer', 0777, true);
            mkdir($base_dir.'/mobi_commerce/'.$app_code.'/personalizer/svg', 0777, true);

            if(!empty($app_theme_folder_name)){
                copy($base_dir . '/mobi_assets/theme_files/' . $app_theme_folder_name . '/personalizer/personalizer.xml', $base_dir . '/mobi_commerce/' . $app_code . '/personalizer/personalizer.xml');
                copy($base_dir . '/mobi_assets/theme_files/' . $app_theme_folder_name . '/personalizer/personalizer.css', $base_dir . '/mobi_commerce/' . $app_code . '/personalizer/personalizer.css');
            }
        }

        if(!empty($app_theme_folder_name)){
            $dir = $base_dir . '/mobi_assets/theme_files/' . $app_theme_folder_name . '/personalizer/svg';
            if(file_exists($dir)){
                $scandir = scandir($dir);
                foreach ($scandir as $key => $value) 
                { 
                    if (!in_array($value,array(".",".."))) 
                    {
                        $currfile = $dir . DIRECTORY_SEPARATOR . $value;
                        if (!is_dir($currfile))
                        {
                            $filename = PATHINFO($value, PATHINFO_BASENAME);
                            if(!file_exists($base_dir . '/mobi_commerce/' . $app_code . '/personalizer/svg/' . $filename)){
                                copy($base_dir . '/mobi_assets/theme_files/' . $app_theme_folder_name . '/personalizer/svg/' . $filename, $base_dir . '/mobi_commerce/' . $app_code . '/personalizer/svg/' . $filename);
                            }
                        }
                    } 
                }
            }

            $sourcePersonalizerFolder = $base_dir.DS.'mobi_assets'.DS.'theme_files'.DS.$app_theme_folder_name.DS.'personalizer';
            $destinationPersonalizerFolder = $base_dir.DS.'mobi_commerce'.DS.$app_code.DS.'personalizer';

            $sourcePersonalizerXml = $sourcePersonalizerFolder.DS.'personalizer.xml';
            $destinationPersonalizerXml = $destinationPersonalizerFolder.DS.'personalizer.xml';

            if(file_exists ($destinationPersonalizerXml)){
                $sourcePersonalizerXml = (array) simplexml_load_file($sourcePersonalizerXml);
                $destinationPersonalizerXml = (array) simplexml_load_file($destinationPersonalizerXml);
                $destinationPersonalizerCss = $destinationPersonalizerFolder.DS.'personalizer.css';
                $cssArray = array();

                $doc = new DOMDocument('1.0');
                $doc->formatOutput = true;
                $root = $doc->createElement('mobicommerce_personalizer');
                $root = $doc->appendChild($root);

                foreach($sourcePersonalizerXml as $key => $element){
                    $css = $element->css;
                    $svgFilenames = (string) $element->svg_filenames;
                    $defaultValue = (string) $element->default_value;
                    $currentValue = (string) $element->default_value;
                    if(isset($destinationPersonalizerXml[$key])){
                        $currentValue = (string) $destinationPersonalizerXml[$key]->current_value;
                    }
                    $css = implode("\r\n", explode('|', $css));
                    $cssArray[] = str_replace("--COLOR--", $currentValue, $css);

                    if(!empty($svgFilenames)){
                        $svgFilenames = explode('|', $svgFilenames);
                        if(!empty($svgFilenames)){
                            foreach($svgFilenames as $svg_filename){
                                if(file_exists($sourcePersonalizerFolder . DS . 'svg' . DS . $svg_filename)){
                                    $svg_image = file_get_contents($sourcePersonalizerFolder . DS . 'svg' . DS . $svg_filename);
                                    preg_match_all('/<style>(.*?)<\/style>/s', $svg_image, $style_tag);
                                    $old_style_tag = $style_tag[1][0];
                                    $property = explode('{', $style_tag[1][0]);
                                    $property = $property[0];
                                    preg_match_all('/{(.*?)}/s', $style_tag[1][0], $style_tag);
                                    $param = explode(':', $style_tag[1][0]);
                                    $param = $param[0];
                                    $new_style_tag = $property.'{'.$param.':'.$currentValue.'!important;}';
                                    $svg_image = str_replace($old_style_tag, $new_style_tag, $svg_image);
                                    file_put_contents($destinationPersonalizerFolder . DS . 'svg' . DS . $svg_filename, $svg_image);
                                }
                            }
                        }
                    }

                    $newdoc = $root->appendChild($doc->createElement($key));
                    foreach($element as $optioncode => $value){
                        $em = $doc->createElement($optioncode);
                        if($optioncode == 'current_value'){
                            $value = $currentValue;
                        }
                        else if($optioncode == 'default_value'){
                            $value = $defaultValue;
                        }
                        $text = $doc->createTextNode($value);
                        $em->appendChild($text);
                        $newdoc->appendChild($em);
                    }
                }
                file_put_contents($destinationPersonalizerCss, implode($cssArray, "\r\n"));
                $doc->save($destinationPersonalizerFolder.DS.'personalizer.xml');
            }
        }
    }

	public function deleteapps($appcodes = array()) 
	{
		$deleteCount = 0;
		if(!empty($appcodes)){
			$appcodes = array_map('trim', $appcodes);
			$appcodes = array_unique($appcodes);
			$appcodes = array_filter($appcodes);
			if(!empty($appcodes)){
				$records = Mage::getModel('mobiadmin/appwidget')->getCollection()->addFieldToFilter('app_code',array('in' => $appcodes));

				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = Mage::getModel('mobiadmin/appsetting')->getCollection()->addFieldToFilter('app_code',array('in' => $appcodes));

				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = Mage::getModel('mobiadmin/devicetokens')->getCollection()->addFieldToFilter('md_appcode',array('in' => $appcodes));

				if($records->count()){
					foreach($records as $_record){
						$_record->delete();
					}
				}

				$records = Mage::getModel('mobiadmin/applications')->getCollection()->addFieldToFilter('app_code',array('in' => $appcodes));

				if($records->count()){
					foreach($records as $_record){
						$deleteCount++;
						$_record->delete();
					}
				}
				foreach($appcodes as $_appcode){
					$dir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'mobi_commerce' . DS . $_appcode;
					if(file_exists($dir) && is_dir($dir)){
						Mage::helper('mobiservices/mobicommerce')->rrmdir($dir);
					}
				}
			}
		}
        return $deleteCount;
	}
}