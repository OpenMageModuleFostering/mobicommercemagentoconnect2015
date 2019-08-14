<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tab_Pushnotifications extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
	{
	    $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('push_notification', array('legend'=>$this->__('Push Notifications')));
		$applicationData = Mage::registry('application_data');
        $storeId = $applicationData->getId();
	    $applicationCode = $applicationData->getAppCode();
        
		$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();   
	    $pushnotiCollection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','pushnotifications_settings');
        $PushData = $pushnotiCollection->getData();
	    $PushData = $PushData['0'];
        $PushValue = $PushData['value'];

		$fieldset->addField('note', 'note', array(
          'text'     => Mage::helper('mobiadmin')->__($this->__('Send push notification to users. Enter the message and send to all users who are using the App.')),
        ));

		$fieldset->addField('pushnotifications', 'textarea', array(
			'label'              => Mage::helper('mobiadmin')->__($this->__('Push Notifications')),
			'name'               => 'pushnotifications',
			'value'              => $PushValue,
			'maxlength'          => '255',
			'rows'               => '3',
			'after_element_html' => '<br><small>'.$this->__('Notification Maximum Text Length is 255 Characters').'</small>',
        ));
    }
}