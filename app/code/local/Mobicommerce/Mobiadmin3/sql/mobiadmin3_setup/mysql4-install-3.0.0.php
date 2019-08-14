<?php

$installer = new Mage_Sales_Model_Resource_Setup();
$installer->startSetup();
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible'  => false,
    'required' => false
	);
$installer->addAttribute('order', 'orderfromplatform', $options);
$installer->endSetup();