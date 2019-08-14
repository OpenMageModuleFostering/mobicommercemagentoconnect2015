<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid extends Mage_Adminhtml_Block_Widget_Grid  {
    
    public function __construct() 
	{
		parent::__construct();
		$this->setId('id');
		$this->setDefaultSort('id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	} 

	protected function _prepareCollection() 
	{
	    $collection = Mage::getModel('mobiadmin/applications')->getCollection();
	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('mobiadmin')->__('ID'),
            'align'  =>'right',
            'width'  => '10px',
            'index'  => 'id',
            'filter' => false,
            ));
 
        $this->addColumn('app_name', array(
            'header' => Mage::helper('mobiadmin')->__('App Name'),
            'align'  =>'left',
            'index'  => 'app_name',
            'width'  => '50px',
            ));

		$this->addColumn('app_key', array(
            'header' => Mage::helper('mobiadmin')->__('App Key'),
            'width'  => '150px',
            'index'  => 'app_key',
            ));

		$this->addColumn('license_type', array(
            'header'   => Mage::helper('mobiadmin')->__('License Type'),
            'width'    => '150px',
            'index'    => 'app_mode',
            'renderer' => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_Licence',
            'filter'   => false,
            ));
        
		$this->addColumn('android_status', array(
            'header'   => Mage::helper('mobiadmin')->__('Android Status'),
            'width'    => '150px',
            'index'    => 'android_url',
            'renderer' => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_AndroidStatus',
            'filter'   => false,
            ));

		$this->addColumn('ios_status', array(
            'header'   => Mage::helper('mobiadmin')->__('iOS Status'),
            'width'    => '150px',
            'index'    => 'ios_url',
            'renderer' => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_IosStatus',
            'filter'   => false,
            ));

		$this->addColumn('action',
            array(
                'header'    =>  Mage::helper('mobiadmin')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'renderer'  => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Grid_Renderer_Action',
                'filter'    => false,
                ));
        return parent::_prepareColumns();
    }
}