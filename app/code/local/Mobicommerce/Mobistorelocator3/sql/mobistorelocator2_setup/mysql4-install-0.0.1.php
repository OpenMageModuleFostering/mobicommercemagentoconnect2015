<?php

$installer = $this;
$installer->startSetup();
$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_storereviews2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_location2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_holiday2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_store2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_image2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_specialday2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_store_value2')};
	DROP TABLE IF EXISTS {$this->getTable('mobistorelocator_rating2')};

	CREATE TABLE {$this->getTable('mobistorelocator_storereviews2')}  (
		`storereview_id` int(11) NOT NULL auto_increment,
		`status_id` int(11) NOT NULL default '2',
		`customer_id` int(11) default NULL,
		`email_id` varchar(255) default NULL,
		`store_id` int(11) NOT NULL default '0',
		`review_date` date NOT NULL,
		`value` varchar(500) NOT NULL default '',  
		`comment` text default NULL,
		`summary` varchar(255) default NULL,
		`nick_name` varchar(255) default NULL,
		PRIMARY KEY  (`storereview_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE {$this->getTable('mobistorelocator_holiday2')}  (
		`holiday_id` int(11) NOT NULL auto_increment,
		`store_id` VARCHAR(255) NOT NULL default '0',
		`date` date NOT NULL,
		`comment` varchar(255) default NULL,
		`holiday_date_to` date NOT NULL,
		PRIMARY KEY  (`holiday_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE {$this->getTable('mobistorelocator_store2')} (
		`store_id` int(11) unsigned NOT NULL auto_increment,
		`store_name` varchar(255) NOT NULL,
		`store_manager` varchar(255) NOT NULL,
		`store_email` varchar(255) NOT NULL,
		`store_phone` varchar(20) NOT NULL,
		`store_fax` varchar(20) NOT NULL,
		`store_image` varchar(255) default NULL,
		`description` text NOT NULL,
		`status` smallint(6) NOT NULL default '0',
		`address` text NOT NULL,
		`address_2` text NOT NULL,
		`state` varchar(255) NOT NULL,
		`suburb` varchar(255) NOT NULL,
		`city` varchar(255) NOT NULL,
		`region_id` INT( 10 ) UNSIGNED NOT NULL,
		`city_id` INT( 10 ) UNSIGNED NOT NULL,
		`suburb_id` INT( 10 ) UNSIGNED NOT NULL,  
		`zipcode` varchar(255) NOT NULL,
		`state_id` int(11) NOT NULL,
		`country` varchar(255) NOT NULL,
		`store_latitude` varchar(20) NOT NULL,
		`store_longitude` varchar(20) NOT NULL,
		`monday_status` smallint(6) NOT NULL default '1',
		`tuesday_status` smallint(6) NOT NULL default '1',
		`wednesday_status` smallint(6) NOT NULL default '1',
		`thursday_status` smallint(6) NOT NULL default '1',
		`friday_status` smallint(6) NOT NULL default '1',
		`saturday_status` smallint(6) NOT NULL default '1',
		`sunday_status` smallint(6) NOT NULL default '1',
		`monday_open` varchar(5) NOT NULL,
		`monday_close` varchar(5) NOT NULL,
		`tuesday_open` varchar(5) NOT NULL,
		`tuesday_close` varchar(5) NOT NULL,
		`wednesday_open` varchar(5) NOT NULL,
		`wednesday_close` varchar(5) NOT NULL,
		`thursday_open` varchar(5) NOT NULL,
		`thursday_close` varchar(5) NOT NULL,
		`friday_open` varchar(5) NOT NULL,
		`friday_close` varchar(5) NOT NULL,
		`saturday_open` varchar(5) NOT NULL,
		`saturday_close` varchar(5) NOT NULL,
		`sunday_open` varchar(5) NOT NULL,
		`sunday_close` varchar(5) NOT NULL,
		`zoom_level` decimal(7,2) default '8',
		`pin_color` varchar(8) default 'f75448',
		`minimum_gap` int(11) NOT NULL default '45',
		`url_id_path` varchar(255) NOT NULL DEFAULT '0',
		PRIMARY KEY  (`store_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE {$this->getTable('mobistorelocator_location2')} (
		`location_id` INT( 10 ) unsigned NOT NULL AUTO_INCREMENT,
		`name` VARCHAR( 255 ) NOT NULL ,
		`type` VARCHAR( 10 ) NOT NULL ,
		`parent_id` VARCHAR( 10 ) NOT NULL ,
		`status` TINYINT( 1 ) NOT NULL ,
		`description` TEXT NOT NULL,
		PRIMARY KEY (`location_id`)
		);

	CREATE TABLE {$this->getTable('mobistorelocator_image2')} (
		`image_id` int(11) unsigned NOT NULL auto_increment,
		`position` int(11),
		`del` int(11),
		`options` int(11),
		`name` varchar(255),
		`store_id` int(11) unsigned NOT NULL,
		    INDEX(`store_id`),
		    FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('mobistorelocator_store2')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		PRIMARY KEY (`image_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
	
    CREATE TABLE {$this->getTable('mobistorelocator_specialday2')}  (
        `specialday_id` int(11) NOT NULL auto_increment,
        `store_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
        `date` date NOT NULL,
        `specialday_date_to` date NOT NULL,      
        `specialday_time_open` varchar(5) NOT NULL,
        `specialday_time_close` varchar(5) NOT NULL,  
        `comment` varchar(255) default NULL,
      PRIMARY KEY (`specialday_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	CREATE TABLE {$this->getTable('mobistorelocator_store_value2')} (
		`value_id` int(10) unsigned NOT NULL auto_increment,
		`storelocator_id` int(11) unsigned NOT NULL,
		`store_id` smallint(5) unsigned  NOT NULL,
		`attribute_code` varchar(63) NOT NULL default '',
		`value` text NOT NULL,
		UNIQUE(`storelocator_id`,`store_id`,`attribute_code`),
		INDEX (`storelocator_id`),
		INDEX (`store_id`),
		FOREIGN KEY (`storelocator_id`) REFERENCES {$this->getTable('mobistorelocator_store2')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core/store')} (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
		PRIMARY KEY (`value_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	CREATE TABLE {$this->getTable('mobistorelocator_rating2')} (
		`mr_id` int(11) NOT NULL AUTO_INCREMENT,
		`mr_name` text,
		`mr_visible_in` text,
		`mr_sort_order` int(11) DEFAULT NULL,
		PRIMARY KEY (`mr_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	INSERT INTO {$this->getTable('mobistorelocator_rating2')} (`mr_id`, `mr_name`, `mr_visible_in`, `mr_sort_order`) VALUES (NULL, 'a:2:{s:7:\"default\";s:7:\"Quality\";s:6:\"stores\";a:0:{}}', NULL, '1'), (NULL, 'a:2:{s:7:\"default\";s:5:\"Value\";s:6:\"stores\";a:0:{}}', NULL, '2'), (NULL, 'a:2:{s:7:\"default\";s:5:\"Price\";s:6:\"stores\";a:0:{}}', NULL, '3');
");

$installer->endSetup(); 