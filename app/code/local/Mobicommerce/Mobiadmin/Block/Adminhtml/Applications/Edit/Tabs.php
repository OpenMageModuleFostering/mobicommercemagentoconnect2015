<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	
	public function __construct()
	{
		parent::__construct();
		$this->setId('application_data');
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('App Settings'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('overview', array(
			'label'   => $this->__('Overview'),
			'title'   => $this->__('Overview'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_overview')->toHtml()
			));

		$this->addTab('general_settings', array(
			'label'   => $this->__('General Settings'),
			'title'   => $this->__('General Settings'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_form')->toHtml()
			));

		$this->addTab('theme_personalization', array(
			'label'   => $this->__('Theme Personalization'),
			'title'   => $this->__('Theme Personalization'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_personalization')->toHtml()
			));

		$this->addTab('banners', array(
			'label'   => $this->__('Banners'),
			'title'   => $this->__('Banners'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_banners')->toHtml()
			));

		$this->addTab('labels_message', array(
			'label'   => $this->__('Labels and Messages'),
			'title'   => $this->__('Labels and Messages'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_labels')->toHtml()
			));

		$this->addTab('cms_contents', array(
			'label'   => $this->__('CMS Contents'),
			'title'   => $this->__('CMS Contents'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_cms')->toHtml()
			));

		$this->addTab('push_notification', array(
			'label'   => $this->__('Push Notifications'),
			'title'   => $this->__('Push Notifications'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_pushnotifications')->toHtml()
			));


		$this->addTab('product_sliders', array(
			'label'   => $this->__('Product Sliders'),
			'title'   => $this->__('Product Sliders'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_productslider')->toHtml()
			));

		$this->addTab('category_icons', array(
			'label'   => $this->__('Category Icons'),
			'title'   => $this->__('Category Icons'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_categoryicon')->toHtml()
			));

		$this->addTab('mobile_website_settings', array(
			'label'   => $this->__('Mobile Website Settings'),
			'title'   => $this->__('Mobile Website Settings'),
			'content' => $this->getLayout()->createBlock('mobiadmin/adminhtml_applications_edit_tab_popup')->toHtml()
			));

		return parent::_beforeToHtml();
	}
}