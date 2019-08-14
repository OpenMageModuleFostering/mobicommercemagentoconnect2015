<?php

class Mobicommerce_Mobipaypaloffline_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'mobipaypaloffline';
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;

    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'mobipaypaloffline/form_mobipaypaloffline';
    protected $_infoBlockType = 'mobipaypaloffline/info_mobipaypaloffline';

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setMobipaypalofflinePayerEmail($data->getMobipaypalofflinePayerEmail())
            ->setMobipaypalofflinePayerName($data->getMobipaypalofflinePayerName());
        return $this;
    }
 
    public function validate()
    {
        parent::validate();
 
        $info = $this->getInfoInstance();
        $payerEmail = $info->getMobipaypalofflinePayerEmail();
        $payerName = $info->getMobipaypalofflinePayerName();
        if(empty($payerEmail)){
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Email is required field');
        }
 
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    public function isAvailable($quote = null){
        $session = Mage::getSingleton('checkout/session');
        $is_enabled = parent::isAvailable($quote);
        $activePaypalMobile = $session->getActivePaypalMobile();

        if(!$is_enabled)
            return false;
        else{
            $display_option =  $this->getConfigData('display_option');
            if($display_option == 'BOTH'){
                return true;
            }
            else if($display_option == 'MOBILE'){
                if($activePaypalMobile == '1')
                    return true;
                else
                    return false;
            }
            else if($display_option == 'WEBSITE'){
                if($activePaypalMobile == '1')
                    return false;
                else
                    return true;
            }
        }
    }

}
?>