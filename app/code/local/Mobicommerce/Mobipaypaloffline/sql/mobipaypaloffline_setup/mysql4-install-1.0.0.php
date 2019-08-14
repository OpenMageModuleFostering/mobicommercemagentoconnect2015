<?php
$this->startSetup();
$this->run("

ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `mobipaypaloffline_payer_email` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$this->getTable('sales/quote_payment')}` ADD `mobipaypaloffline_payer_name` VARCHAR( 255 ) NOT NULL ;
 
ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `mobipaypaloffline_payer_email` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$this->getTable('sales/order_payment')}` ADD `mobipaypaloffline_payer_name` VARCHAR( 255 ) NOT NULL ;

");
$this->endSetup();