<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
	
   protected function _prepareForm()
   {
       	$form = new Varien_Data_Form();
       	$this->setForm($form);
       	$applicationData = Mage::registry('application_data');
       	$storeId = $applicationData->getAppStoreid();
	   	$applicationCode = $applicationData->getAppCode();
	   	$applicationKey = $applicationData->getAppKey();

       	//Push Notification section
	   	$fieldset = $form->addFieldset('pushnotification', array('legend'=>$this->__('Push Notification <span class="app-scope">[Website]</span>')));
	   	$fieldset->addField('appcode', 'hidden', array(
			'name'     => 'appcode',
			'value'    => $applicationCode,
			'disabled' => false,
       		));

	   	$fieldset->addField('ddlStore', 'hidden', array(
			'name'  => 'ddlStore',
			'value' => $storeId,
       		));

	   	$fieldset->addField('appkey', 'hidden', array(
			'name'     => 'appkey',
			'value'    => $applicationKey,
			'disabled' => false,
       		));

	   $fieldset->addField('appcodeid', 'hidden', array(          
          'name'      => 'appid',
		  'value'  => Mage::app()->getRequest()->getParam('id'),
          'disabled' => false,
       ));

       $collection = Mage::getModel('mobiadmin/appsetting')->getCollection();   
	   $pushnotiCollection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','push_notification');
	   $PushData = $pushnotiCollection->getData();
	   $PushData = $PushData['0'];
       $PushDatas= $PushData['value'];
	   
	   $pushDataValues = Mage::helper('mobiadmin')->_jsonUnserialize($PushDatas);
	   if($pushDataValues['active_push_notification']){
	      $activeNotification = true;
	   }else{
	      $activeNotification = false;
	   }
	   if($pushDataValues['sandboxmode']){
	      $activeSandBoxMode = true;
	   }else{
		   $activeSandBoxMode = false;
	   }
	   $fieldset->addField('active_push_notification', 'checkbox', array(
			'label'    => Mage::helper('mobiadmin')->__('Activate Push Notification'),
			'name'     => 'pushnotification[active_push_notification]',
			'value'    => '1' ,
			'checked'  => $activeNotification,
			'disabled' => false,
       		));

	   	$fieldset->addField('sandboxmode', 'checkbox', array(
			'label'              => Mage::helper('mobiadmin')->__('Sandbox mode'),
			'name'               => 'pushnotification[sandboxmode]',
			'value'              => '1',
			'checked'            => $activeSandBoxMode,
			'disabled'           => false,
			'after_element_html' => '<br><small>'.$this->__('Please Make sure your 2195 port is open to send IOS push notifications.').'</small>',
       		));

       	$fieldset->addField('android_key', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('GCM API Key'),
			'name'  => 'pushnotification[android_key]',
			'value' => $pushDataValues['android_key'],
       		));

	   	$fieldset->addField('android_sender_id', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('Google API Project Number'),
			'name'  => 'pushnotification[android_sender_id]',
			'value' => $pushDataValues['android_sender_id'],
       		));

	   	$fieldset->addField('upload_iospem_file', 'image', array(
			'label'    => Mage::helper('mobiadmin')->__('Upload iOS PEM file'),
			'required' => false,
			'name'     => 'upload_iospem_file',
			'value'    => $pushDataValues['upload_iospem_file_url'],
       		));
	   
       	if(!empty($pushDataValues['upload_iospem_file'])){		   
			$fieldset->addField('note', 'note', array(
				'label' => '',
				'text'  => $pushDataValues['upload_iospem_file'],
				));
	  	}

       	$fieldset->addField('appfilename', 'hidden', array(          
			'name'     => 'upload_iospem_file_name',
			'value'    => $pushDataValues['upload_iospem_file'],
			'disabled' => false,
       		));

	   	$fieldset->addField('pem_password', 'password', array(
			'label' => Mage::helper('mobiadmin')->__('PEM Password'),
			'name'  => 'pushnotification[pem_password]',
			'value' => $pushDataValues['pem_password'],
       		));
       
       //Application Iformation
	   	$fieldset = $form->addFieldset('app_info', array('legend'=>$this->__('Application Information <span class="app-scope">[Website]</span>')));
	   	$collection = Mage::getModel('mobiadmin/appsetting')->getCollection();
	   	$appinfoCollection = $collection->addFieldToFilter('app_code',$applicationCode)->addFieldToFilter('setting_code','appinfo');
	   	$appInfoData = $appinfoCollection->getData();
	   	$appInfoData = $appInfoData['0'];
       	$allAppInfo = $appInfoData['value'];
	   
	   	$allAppInfoValues = Mage::helper('mobiadmin')->_jsonUnserialize($allAppInfo);
	   	$fieldset->addField('android_appname', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('App Name on Google Play Store'),
			'name'  => 'appinfo[android_appname]',
			'value' => $allAppInfoValues['android_appname'],
       		));

	   	$fieldset->addField('android_appweburl', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('App URL on Google Play Store'),
			'name'  => 'appinfo[android_appweburl]',
			'value' => $allAppInfoValues['android_appweburl'],
       		));

	   	$fieldset->addField('ios_appname', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('App Name on Apple Store'),
			'name'  => 'appinfo[ios_appname]',
			'value' => $allAppInfoValues['ios_appname'],
       		));

	   	$fieldset->addField('ios_appweburl', 'text', array(
			'label' => Mage::helper('mobiadmin')->__('App URL on Apple Store'),
			'name'  => 'appinfo[ios_appweburl]',
			'value' => $allAppInfoValues['ios_appweburl'],
       		));

	   	$fieldset->addField('app_description', 'textarea', array(
			'label' => Mage::helper('mobiadmin')->__('Application Description'),
			'name'  => 'appinfo[app_description]',
			'value' => $allAppInfoValues['app_description'],
       		));

	   	$fieldset->addField('app_share_image', 'image', array(
			'label'    => Mage::helper('mobiadmin')->__('Application Image'),
			'required' => false,
			'name'     => 'app_share_image',
			'value'    => $allAppInfoValues['app_share_image'],
       		));
       return parent::_prepareForm();
   }
    
}