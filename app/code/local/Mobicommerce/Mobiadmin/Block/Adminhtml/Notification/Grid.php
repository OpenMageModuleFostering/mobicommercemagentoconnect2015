<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Notification_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
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
	    $collection = Mage::getModel('mobiadmin/notification')->getCollection();
	    $this->setCollection($collection);
	    return parent::_prepareCollection();
	} 

	protected function _prepareColumns()
    {
        $this->addColumn('type', array(
            'header'   => Mage::helper('mobiadmin')->__('Type'),
            'align'    =>'left',
            'index'    => 'type',
            'width'    => '50px',
            'filter'   => false,
            'renderer' => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Notification_Grid_Renderer_Type',
            ));
 

		$this->addColumn('date_added', array(
            'header' => Mage::helper('mobiadmin')->__('Date Added'),
            'width'  => '50px',
            'index'  => 'date_added',
            'filter' => false,
            ));

		$this->addColumn('message', array(
            'header' => Mage::helper('mobiadmin')->__('Message'),
            'width'  => '500px',
            'index'  => 'message',
            'filter' => false,
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
                'renderer'  => 'Mobicommerce_Mobiadmin_Block_Adminhtml_Notification_Grid_Renderer_Action',
            ));
        return parent::_prepareColumns();
    }

	public function getMainButtonsHtml()
	{
		return '';
	}

	protected function _prepareMassaction()
    {
		$this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
		$this->getMassactionBlock()->addItem(
			'delete', array(
                'label'   => Mage::helper('mobiadmin')->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('mobiadmin')->__('Are you sure?')
                )
		);
		$this->getMassactionBlock()->addItem(
            'mark_as_read', array(
                'label' => Mage::helper('mobiadmin')->__('Mark As Read'),
                'url'   => $this->getUrl('*/*/massRead')             
                )
		);
	}
}