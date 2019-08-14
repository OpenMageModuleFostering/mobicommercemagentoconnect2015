<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_applications2')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_applications_settings2')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_category_icon2')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_widget2')};
    DROP TABLE IF EXISTS {$this->getTable('mobicommerce_category_widget2')};

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

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_applications2')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `app_name` varchar(255) NOT NULL DEFAULT '',
        `app_code` varchar(255) DEFAULT NULL,
        `app_key` varchar(255) NOT NULL DEFAULT '',
        `app_logo` varchar(255) NOT NULL DEFAULT '',
        `app_license_key` varchar(255) NOT NULL DEFAULT '',
        `app_storegroupid` int(11) DEFAULT NULL,
        `created_time` datetime DEFAULT NULL,
        `update_time` datetime DEFAULT NULL,
        `app_mode` varchar(100) NOT NULL DEFAULT 'demo' COMMENT 'License Version',
        `ios_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'iOS URL',
        `android_url` varchar(100) NOT NULL DEFAULT '' COMMENT 'Android URL',
        `ios_status` varchar(100) DEFAULT NULL COMMENT 'iOS Status',
        `android_status` varchar(100) DEFAULT NULL COMMENT 'Android Status',
        `udid` text NOT NULL COMMENT 'UDID',
        `delivery_status` varchar(100) DEFAULT NULL COMMENT 'Deleivery Status',
        `addon_parameters` text NOT NULL COMMENT 'AddOn Parameters',
        `webapp_url` text COMMENT 'Mobile Website URL',
        `version_type` varchar(45) DEFAULT NULL COMMENT 'PRO OR LITE',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_applications_settings2')} (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `app_code` varchar(255) DEFAULT NULL,
        `storeid` int(11) DEFAULT NULL,
        `setting_code` varchar(255) NOT NULL DEFAULT '',
        `value` longtext,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_category_icon2')} (
        `mci_id` bigint(11) NOT NULL AUTO_INCREMENT,
        `mci_category_id` bigint(20) DEFAULT NULL,
        `mci_thumbnail` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`mci_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_devicetokens')} (
        `md_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `md_appcode` varchar(45) NOT NULL,
        `md_devicetype` enum('android','ios') DEFAULT NULL,
        `md_devicetoken` varchar(255) NOT NULL,
        `md_created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`md_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ALTER TABLE {$this->getTable('mobicommerce_devicetokens')} ADD `md_userid` BIGINT( 20 ) NULL AFTER `md_appcode` ;

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_widget2')} (
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

    CREATE TABLE IF NOT EXISTS {$this->getTable('mobicommerce_category_widget2')} (
        `widget_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `widget_category_id` bigint(20) DEFAULT NULL,
        `widget_label` varchar(255) DEFAULT NULL,
        `widget_code` varchar(255) DEFAULT NULL,
        `widget_status` int(11) NOT NULL DEFAULT '0',
        `widget_position` int(11) NOT NULL DEFAULT '0',
        `widget_data` text DEFAULT NULL,
        PRIMARY KEY (`widget_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();