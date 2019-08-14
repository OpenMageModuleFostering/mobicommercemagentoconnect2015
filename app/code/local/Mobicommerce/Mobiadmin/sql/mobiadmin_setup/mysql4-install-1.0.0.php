<?php

$installer = $this;

$installer->startSetup();
//Install Notification table mobicommerce_notification 
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$installer->getTable('mobiadmin/notification')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mobiadmin/notification')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `date_added` datetime NOT NULL,
  `message` varchar(25000) NOT NULL,
  `read_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);

SQLTEXT;


$installer->run($sql);



//Install licence table mobicommerce_licence 
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$installer->getTable('mobiadmin/licence')};
CREATE TABLE IF NOT EXISTS {$installer->getTable('mobiadmin/licence')} (
  `ml_id` int(11) NOT NULL AUTO_INCREMENT,
  `ml_licence_key` varchar(255) NOT NULL,
  `ml_debugger_mode` enum('yes','no') NOT NULL DEFAULT 'yes',
  `ml_installation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ml_id`)
);
SQLTEXT;
$installer->run($sql);


$connection = $installer->getConnection();
    $connection->addColumn($installer->getTable('mobiadmin/applications'),
        'app_mode',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 100,
            'nullable' => false,
            'default' => 'demo',
            'comment' => 'License Version'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'ios_url',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
			'default' =>'',
            'comment' => 'iOS URL'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'android_url',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
			'default' =>'',
            'comment' => 'Android URL'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'ios_status',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => '',
            'comment' => 'iOS Status'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'android_status',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => '',
            'comment' => 'Android Status'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'udid',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 10000,
            'nullable' => false,
            'comment' => 'UDID'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'delivery_status',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => '',
            'comment' => 'Deleivery Status'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'addon_parameters',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 10000,
            'nullable' => false,
			'default' =>'',
            'comment' => 'AddOn Parameters'
        )
    );
	$connection->addColumn($installer->getTable('mobiadmin/applications'),
        'webapp_url',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'comment' => 'Mobile Website URL'
        )
    );
 
$installer->endSetup();

	 