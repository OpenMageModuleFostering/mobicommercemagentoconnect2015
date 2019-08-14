<?php

class Mobicommerce_Mobiadmin_Block_Adminhtml_Report_Filter_Form  extends Mage_Adminhtml_Block_Report_Filter_Form
{
    /**
     * Add fieldset with general report fields
     *
     * @return Mage_Adminhtml_Block_Report_Filter_Form
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
            );
        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));

        $fieldset->addField('report_type', 'select', array(
            'name'    => 'report_type',
            'options' => $this->_reportTypeOptions,
            'label'   => Mage::helper('reports')->__('Match Period To'),
            ));

        $fieldset->addField('period_type', 'select', array(
            'name'    => 'period_type',
            'options' => array(
                'day'   => Mage::helper('reports')->__('Day'),
                'month' => Mage::helper('reports')->__('Month'),
                'year'  => Mage::helper('reports')->__('Year')
                ),
            'label' => Mage::helper('reports')->__('Period'),
            'title' => Mage::helper('reports')->__('Period')
            ));

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

        $fieldset->addField('from', 'date', array(
            'name'     => 'from',
            'format'   => $dateFormatIso,
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'label'    => Mage::helper('reports')->__('From'),
            'title'    => Mage::helper('reports')->__('From'),
            'required' => true
            ));

        $fieldset->addField('to', 'date', array(
            'name'     => 'to',
            'format'   => $dateFormatIso,
            'image'    => $this->getSkinUrl('images/grid-cal.gif'),
            'label'    => Mage::helper('reports')->__('To'),
            'title'    => Mage::helper('reports')->__('To'),
            'required' => true
            ));

        $fieldset->addField('show_empty_rows', 'select', array(
            'name'    => 'show_empty_rows',
            'options' => array(
                '1' => Mage::helper('reports')->__('Yes'),
                '0' => Mage::helper('reports')->__('No')
                ),
            'label' => Mage::helper('reports')->__('Empty Rows'),
            'title' => Mage::helper('reports')->__('Empty Rows')
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        //return parent::_prepareForm();
    }    
}
