<?php
$installer = $this;
$installer->startSetup();

$labels = array(
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Estimate_Shipping_And_Tax",
		"mm_label"      => "Estimate Shipping And Tax",
		"mm_maxlength"  => "100",
		"mm_text"       => "Estimate Shipping And Tax",
		"mm_help"       => "Label:Estimate Shipping And Tax"
		),
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Get_A_Quote",
		"mm_label"      => "Get A Quote",
		"mm_maxlength"  => "50",
		"mm_text"       => "Get A Quote",
		"mm_help"       => "Label:Get A Quote"
		),
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Update_Total",
		"mm_label"      => "Update Total",
		"mm_maxlength"  => "50",
		"mm_text"       => "Update Total",
		"mm_help"       => "Label:Update Total"
		),
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Update_Order_Data",
		"mm_label"      => "Update Order Data",
		"mm_maxlength"  => "50",
		"mm_text"       => "Update Order Data",
		"mm_help"       => "Label:Update Order Data"
		),
	array(
		"mm_type"       => "label",
		"mm_label_code" => "Position",
		"mm_label"      => "Position",
		"mm_maxlength"  => "50",
		"mm_text"       => "Position",
		"mm_help"       => "Label:Position"
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
		".implode(",", $insertArray)."
			");
}

$apps = $readConnection->fetchAll("SELECT * FROM ".$resource->getTableName('mobicommerce_applications')." GROUP BY app_code");
if($apps){
	$now = date('Y-m-d H:i:s');
	foreach($apps as $_app){
		$writeConnection->query("
		INSERT INTO ".$resource->getTableName('mobi_app_widgets')." (`app_code`, `app_type`, `slider_code`, `slider_label`, `slider_status`, `slider_position`, `slider_settings`, `slider_productIds`, `created_time`, `update_time`) VALUES 
		('".$_app['app_code']."', 'product-slider', 'recently-viewed-automated', 'Recently Viewed', '1', '7', '', '', '".$now."', '".$now."')
			");
	}
}

$installer->run("");
$installer->endSetup();