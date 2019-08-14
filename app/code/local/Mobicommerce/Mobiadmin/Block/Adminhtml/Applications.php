<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct()
	{
		$this->_controller = 'adminhtml_applications';
		$this->_blockGroup = 'mobiadmin';
		$this->_headerText = Mage::helper('mobiadmin')->__('Manage Mobile Apps');
		parent::__construct();
		$this->_removeButton('add');
		$this->_addButton('btnAdd', array(
			'label'   => Mage::helper('mobiadmin')->__('Create New Mobile App'),
			'onclick' => "setLocation('" . $this->getUrl('*/*/new', array('page_key' => 'collection')) . "')",
			'class'   => 'add'
			));
	}
}