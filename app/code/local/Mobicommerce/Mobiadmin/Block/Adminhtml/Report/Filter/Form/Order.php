<?php

class Mobicommerce_Mobiadmin_Block_Adminhtml_Report_Filter_Form_Order  extends Mage_Sales_Block_Adminhtml_Report_Filter_Form_Order
{
	protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');
        $orderCollection = Mage::getModel('sales/order')->getCollection();
		$firstOrderCollection = $orderCollection->getFirstItem()->getData();
		if (array_key_exists('orderfromplatform', $firstOrderCollection)) {
			$fieldset->addField('orderfrom', 'select', array(
				'name'    => 'orderfrom',
				'options' => array(
					'both'         => Mage::helper('reports')->__('Both'),
					'mobicommerce' => Mage::helper('reports')->__('MobiCommerce'),
					'website'      => Mage::helper('reports')->__('Website')
					),
				'label' => Mage::helper('reports')->__('Order From'),
				'title' => Mage::helper('reports')->__('Order From')
			));
		}
        return $this;
    }
}