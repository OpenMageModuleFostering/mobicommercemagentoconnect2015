<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tab_Popup extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
    {
	    $form = new Varien_Data_Form();
        $this->setForm($form);
		$fieldset = $form->addFieldset('mobile_data', array('legend'=>$this->__('Mobicommerce Mobile Enable Module')));
        $EnableRedirect = Mage::getStoreConfig('mobimobileredirect/isactive_group/isactive_value');
		if($EnableRedirect == 0){
		   $ShowSettting ='Disable';
		}else{
		   $ShowSettting ='Enable';
		}
		$fieldset->addField('note', 'note', array(
		    'label' =>  Mage::helper('mobiadmin')->__('Enable Module'),
            'text'     => Mage::helper('mobiadmin')->__($ShowSettting),
        ));
        
        $fieldset = $form->addFieldset('application_data', array('legend'=>$this->__('Pop Up Setting')));
		$applicationData = Mage::registry('application_data');
		$applicationCode = $applicationData->getAppCode();

        $appPopUpCollection = Mage::getModel('mobiadmin/appsetting')->getCollection();
			$appPopUpCollection = $appPopUpCollection
				->addFieldToFilter('app_code',$applicationCode)
			    ->addFieldToFilter('setting_code','popup_setting');
		$appPopUpData = $appPopUpCollection->getFirstItem();
		$appPopUpValues = $appPopUpData->getValue();
		$appPopUpValues = Mage::helper('mobiadmin')->_jsonUnserialize($appPopUpValues);

		$fieldset->addField('popup_enable', 'select', array(
		  'label'     => Mage::helper('mobiadmin')->__('Enable Pop Up'),
          'name'      => 'popup[enable]',
		  'options'   => array(
				0 => Mage::helper('adminhtml')->__('No'),
				1 => Mage::helper('adminhtml')->__('Yes'),  
		    ),
		  'value'  => $appPopUpValues['enable'],
        ));

		$fieldset->addField('popupimage', 'image', array(
	       'label' => Mage::helper('mobiadmin')->__('Upload Pop Up Image'),
		   'required' => false,
		   'name' => 'popupimage',
		   'value' => $appPopUpValues['popupimage'],
        ));

		$fieldset->addField('popupimage_hidden', 'hidden', array(
		   'required' => false,
		   'name' => 'popupimage_hidden',
		   'value' => $appPopUpValues['popupimage'],
        ));

		$fieldset->addField('cookietime', 'text', array(
	       'label' => Mage::helper('mobiadmin')->__('Set Pop Up Cookie Time'),
		   'required' => false,
		   'name' => 'popup[cookietime]',
		   'value' => $appPopUpValues['cookietime'],
		   'after_element_html' => '<small>'.$this->__('Cookies Lifetime in Seconds.').'</small>',
        ));
    }
}