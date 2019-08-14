<?php

require_once '../app/Mage.php';
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app("default");

$icon_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'mobi_category_icons/';

$get = $_REQUEST;
$svg_filename = $get['svg_filename'];//'icon-menu.svg';
$color = isset($get['color'])?$get['color']:'';//'333333';
$color = '#' . $color;
$new_style_tag = array();

header('Pragma: public');
header('Content-type: image/svg+xml');

$svg_image = file_get_contents($icon_url.$svg_filename);
if(!empty($color)){
	preg_match_all('/<style>(.*?)<\/style>/s', $svg_image, $style_tag);
	//echo "<pre>";print_r($style_tag);exit;
	$old_style_tag = $style_tag[1][0];
	$allStyles = explode("\r\n", $old_style_tag);
	if(!empty($allStyles)){
		foreach($allStyles as $allStyle){
			$allStyle = trim((string)$allStyle);
			if(!empty($allStyle)){
				$property = explode('{', $allStyle);
				$property = $property[0];
				preg_match_all('/{(.*?)}/s', $allStyle, $style_tag);
				if(isset($style_tag[1][0]))
				{
					$param = explode(':', $style_tag[1][0]);
					$param = $param[0];
					$new_style_tag[] = $property.'{'.$param.':'.$color.'!important;}';			
				}
			}
		}
	}

	$new_style_tag = implode("\r\n", $new_style_tag);
	$svg_image = str_replace($old_style_tag, $new_style_tag, $svg_image);
}

echo $svg_image;exit;