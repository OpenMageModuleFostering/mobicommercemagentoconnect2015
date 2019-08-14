<?php
$installer = $this;
$installer->startSetup();

$labels = array(
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Preferred_Language",
		"mm_label"      => "Preferred Language",
		"mm_maxlength"  => "50",
		"mm_text"       => "Preferred Language",
		"mm_help"       => "Label:Preferred Language"
		),
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Preferred_Currency",
		"mm_label"      => "Preferred Currency",
		"mm_maxlength"  => "50",
		"mm_text"       => "Preferred Currency",
		"mm_help"       => "Label:Preferred Currency"
		),
	);

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$languages = $readConnection->fetchAll("SELECT * FROM ".$resource->getTableName('mobicommerce_multilanguage')." GROUP BY mm_language_code");
if($languages){
	$insertArray = array();
	foreach($languages as $_lang){
		foreach($labels as $_label){
			$insertArray[] = "('".$_lang['mm_language_code']."', '".$_label['mm_type']."', '".$_label['mm_label_code']."', '".$_label['mm_label']."', '".$_label['mm_maxlength']."', '".$_label['mm_text']."', '".$_label['mm_help']."')";
		}
	}

	$writeConnection->query("
		INSERT INTO ".$resource->getTableName('mobicommerce_multilanguage')." (`mm_language_code`, `mm_type`, `mm_label_code`, `mm_label`, `mm_maxlength`, `mm_text`, `mm_help`) VALUES 
		".implode(",", $insertArray));
}

$writeConnection->query("ALTER TABLE ".$resource->getTableName('mobicommerce_applications')." ADD `app_storegroupid` INT NULL AFTER `app_storeid`;");
$writeConnection->query("ALTER TABLE ".$resource->getTableName('mobicommerce_applications_settings')." ADD `storeid` INT NULL AFTER `app_code`;");
$writeConnection->query("ALTER TABLE ".$resource->getTableName('mobi_app_widgets')." ADD `storeid` INT NULL AFTER `app_code`;");

$apps = $readConnection->fetchAll("SELECT * FROM ".$resource->getTableName('mobicommerce_applications'));
if(!empty($apps)){
	foreach($apps as $_app){
		$store = $_app['app_storeid'];
		$group = Mage::getModel('core/store')->load($store)->getGroupId();
		$writeConnection->query("UPDATE ".$resource->getTableName('mobicommerce_applications')." SET app_storegroupid = '".$group."' WHERE id = '".$_app['id']."';");
	}

	$stores = array();
	foreach(Mage::getResourceModel('core/website_collection') as $website){
	    foreach($website->getGroups() as $group){
	    	foreach($group->getStores() as $store){
	    		$stores[] = $store->getStoreId();
	    	}
	    }
	}

	/* for banners and cms */
    $settings = $readConnection->fetchAll("SELECT * FROM ".$resource->getTableName('mobicommerce_applications_settings')." WHERE setting_code IN ('banner_settings', 'cms_settings')");
    $writeConnection->query("DELETE FROM ".$resource->getTableName('mobicommerce_applications_settings')." WHERE setting_code IN ('banner_settings', 'cms_settings')");
    foreach($settings as $_setting){
    	foreach($stores as $_store){
    		$writeConnection->query("INSERT INTO ".$resource->getTableName('mobicommerce_applications_settings')." 
    			(app_code,storeid,setting_code,value) 
    			VALUES 
    			('".$_setting['app_code']."','".$_store."','".$_setting['setting_code']."','".$_setting['value']."');");
    	}
    }
    /* for banners and cms - upto here */

    /* for product sliders */
    $sliders = $readConnection->fetchAll("SELECT * FROM ".$resource->getTableName('mobi_app_widgets'));
    $writeConnection->query("DELETE FROM ".$resource->getTableName('mobi_app_widgets'));
    foreach($sliders as $_slider){
    	foreach($stores as $_store){
    		$writeConnection->query("INSERT INTO ".$resource->getTableName('mobi_app_widgets')." 
    			(app_code,storeid,app_type,slider_code,slider_label,slider_status,slider_position,slider_settings,slider_productIds,created_time,update_time) 
    			VALUES 
    			('".$_slider['app_code']."','".$_store."','".$_slider['app_type']."','".$_slider['slider_code']."','".$_slider['slider_label']."','".$_slider['slider_status']."','".$_slider['slider_position']."','".$_slider['slider_settings']."','".$_slider['slider_productIds']."','".$_slider['created_time']."','".$_slider['update_time']."');");
    	}
    }
    /* for product sliders - upto here */
}

$installer->run("");
$installer->endSetup();