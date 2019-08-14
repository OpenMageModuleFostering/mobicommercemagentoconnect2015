<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tab_Labels extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm()
    {
	    $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('application_data', array('legend'=>$this->__('Label <span class="app-scope">[Language]</span>')));
        $storeid = $this->getRequest()->getParam('store', null);
		$locale = Mage::getStoreConfig('general/locale/code',$storeid);

        $collection = Mage::getModel('mobiadmin/multilanguage')->getCollection()->addFieldToFilter('mm_type','label')->addFieldToFilter('mm_language_code', $locale);
        $labels = $collection->getData();
		foreach ($labels as $label) {
			$fieldset->addField('message-label'.$label['mm_id'], 'text', array(
				'label'      => Mage::helper('mobiadmin')->__($label['mm_label']),
				'required'   => false,
				'name'       => "language_data[".$label['mm_id']."]",
				'value'      => $label['mm_text'],
				'maxlength'  => $label['mm_maxlength'],
			));
		}
        
		$fieldset2 = $form->addFieldset('', array('legend'=>$this->__('Message <span class="app-scope">[Language]</span>')));
		$collection = Mage::getModel('mobiadmin/multilanguage')->getCollection()->addFieldToFilter('mm_type','message')->addFieldToFilter('mm_language_code', $locale);
		$labels = $collection->getData();
		foreach ($labels as $label){
			$fieldset2->addField('message-label'.$label['mm_id'], 'text', array(
				'label'      => Mage::helper('mobiadmin')->__($label['mm_label']),
				'required'   => false,
				'name'       => "language_data[".$label['mm_id']."]",
				'value'      => $label['mm_text'],
				'maxlength'  => $label['mm_maxlength'],
			));
		}
	}
}