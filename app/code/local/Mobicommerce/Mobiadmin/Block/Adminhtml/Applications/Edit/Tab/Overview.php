<?php
class Mobicommerce_Mobiadmin_Block_Adminhtml_Applications_Edit_Tab_Overview extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mobiadmin/application/edit/tab/overview.phtml');
    }
}