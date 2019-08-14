<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Widget_Grid_Container 
{    
	public function __construct() 
	{		
	    $this->_controller = 'adminhtml_notification';
		$this->_blockGroup = 'mobiadmin';
		$this->_headerText = Mage::helper('mobiadmin')->__('Notifications');	 
		parent::__construct(); 
		$this->_removeButton('add');
	} 
}