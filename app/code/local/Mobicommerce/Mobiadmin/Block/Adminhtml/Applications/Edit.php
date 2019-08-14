<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
    public function __construct()
    {
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'mobiadmin';
		$this->_controller = 'adminhtml_applications';
		$this->_updateButton('save', 'label','Save');
		$this->_removeButton('delete');
	}

	public function getHeaderText()
    {
        if( Mage::registry('application_data') && Mage::registry('application_data')->getId()){
            return $this->__('Edit App Settings - ').$this->htmlEscape(
            Mage::registry('application_data')->getAppName()).'<br />';
        }
        else{
            return 'Add a application';
        }
    }

	protected function _prepareLayout() 
	{
		parent::_prepareLayout();
		if(Mage::getSingleton('cms/wysiwyg_config')->isEnabled()){
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
	}
}