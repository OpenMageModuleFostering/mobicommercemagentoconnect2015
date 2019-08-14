<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_applications3')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_applications_settings3')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_category_icon3')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_widget3')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_category_widget3')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_notification')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_pushhistory')};

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_licence')} (
        `ml_id` int(11) NOT NULL AUTO_INCREMENT,
        `ml_licence_key` varchar(255) NOT NULL,
        `ml_debugger_mode` enum('yes','no') NOT NULL DEFAULT 'yes',
        `ml_installation_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`ml_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_notification')} (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `type` varchar(20) NOT NULL,
        `date_added` datetime NOT NULL,
        `message` varchar(25000) NOT NULL,
        `read_status` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_applications3')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `app_name` varchar(255) NOT NULL DEFAULT '',
        `app_code` varchar(255) DEFAULT NULL,
        `app_key` varchar(255) NOT NULL DEFAULT '',
        `app_license_key` varchar(255) NOT NULL DEFAULT '',
        `app_storegroupid` int(11) DEFAULT NULL,
        `created_time` datetime DEFAULT NULL,
        `update_time` datetime DEFAULT NULL,
        `app_mode` varchar(100) NOT NULL DEFAULT 'demo' COMMENT 'License Version',
        `android_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'Android URL',
        `android_status` varchar(100) DEFAULT NULL COMMENT 'Android Status',
        `ios_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'iOS URL',
        `ios_status` varchar(100) DEFAULT NULL COMMENT 'iOS Status',
        `udid` text NOT NULL COMMENT 'UDID',
        `delivery_status` varchar(100) DEFAULT NULL COMMENT 'Deleivery Status',
        `addon_parameters` text NOT NULL COMMENT 'AddOn Parameters',
        `version_type` varchar(45) DEFAULT NULL COMMENT 'PRO OR LITE',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_applications_settings3')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `app_code` varchar(255) DEFAULT NULL,
        `storeid` int(11) DEFAULT NULL,
        `setting_code` varchar(255) NOT NULL DEFAULT '',
        `value` longtext,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_category_icon3')} (
        `mci_id` bigint(11) NOT NULL AUTO_INCREMENT,
        `mci_category_id` bigint(20) DEFAULT NULL,
        `mci_thumbnail` varchar(255) DEFAULT NULL,
        `mci_banner` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`mci_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_devicetokens')} (
        `md_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `md_appcode` varchar(45) NOT NULL,
        `md_userid` BIGINT( 20 ) NULL,
        `md_devicetype` enum('android','ios') DEFAULT NULL,
        `md_devicetoken` varchar(255) DEFAULT NULL,
        `md_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`md_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE {$this->getTable('mobicommerce_devicetokens')} 
        ADD `md_store_id` INT( 11 ) NULL AFTER `md_userid` ,
        ADD `md_enable_push` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `md_store_id`;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_widget3')} (
        `widget_id` int(11) NOT NULL AUTO_INCREMENT,
        `widget_app_code` varchar(255) NOT NULL,
        `widget_store_id` int(11) DEFAULT NULL,
        `widget_label` varchar(255) NOT NULL,
        `widget_code` varchar(255) NOT NULL,
        `widget_status` int(11) NOT NULL DEFAULT '0',
        `widget_position` int(11) NOT NULL DEFAULT '0',
        `widget_data` text NOT NULL,
        PRIMARY KEY (`widget_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_category_widget3')} (
        `widget_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `widget_category_id` bigint(20) DEFAULT NULL,
        `widget_label` varchar(255) DEFAULT NULL,
        `widget_code` varchar(255) DEFAULT NULL,
        `widget_status` int(11) NOT NULL DEFAULT '0',
        `widget_position` int(11) NOT NULL DEFAULT '0',
        `widget_data` text DEFAULT NULL,
        PRIMARY KEY (`widget_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_pushhistory')} (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `appcode` varchar(25) DEFAULT NULL,
        `date_submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_to_send`  datetime DEFAULT NULL,
        `store_id` int(11) DEFAULT NULL,
        `device_type` enum('android','ios','both') DEFAULT NULL,
        `heading` varchar(100) DEFAULT NULL,
        `message` varchar(255) DEFAULT NULL,
        `deeplink` varchar(255) DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `send_to_type` enum('all', 'customer_group', 'specific_customer') DEFAULT 'all',
        `send_to` text,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();