<?php

class Mobicommerce_Mobiservices_Model_1x3x1_Social extends Mobicommerce_Mobiservices_Model_Abstract {

    protected $socialPlugin = "MAGEGIANT_SOCIAL_LOGIN";
    public function __construct()
    {
        parent::__construct();
    }

    public function getSocialPlatforms()
    {
        $platforms = array($this->socialPlugin);
        switch($this->socialPlugin){
            case 'MAGEGIANT_SOCIAL_LOGIN':
            default:
                if (Mage::helper('core')->isModuleEnabled('Magegiant_SocialLogin')) {
                    $platforms = Mage::getBlockSingleton('sociallogin/sociallogin')->getSocialButton();
                }
                break;
        }
        return $platforms;
    }
}