<?php

class Mobicommerce_Mobiservices_Model_1x3x1_Push extends Mobicommerce_Mobiservices_Model_Abstract {

	public function saveDeviceToken($data = array())
    {
        $appcode     = isset($data['appcode']) ? $data['appcode'] : NULL;
        $platform    = isset($data['platform']) ? $data['platform'] : NULL;
        $devicetoken = isset($data['devicetoken']) ? $data['devicetoken'] : NULL;

        if(empty($appcode) || empty($platform) || empty($devicetoken)){
            return $this->errorStatus("Please pass proper data");
        }
        else{
            $collection = Mage::getModel('mobiadmin/devicetokens')->getCollection()
                ->addFieldToFilter('md_appcode', $appcode)
                ->addFieldToFilter('md_devicetype', $platform)
                ->addFieldToFilter('md_devicetoken', $devicetoken);

            if($collection->count() == 0){
                Mage::getModel('mobiadmin/devicetokens')->setData(array(
                    'md_appcode'     => $appcode,
                    'md_devicetype'  => $platform,
                    'md_devicetoken' => $devicetoken
                    ))->save();
            }

            return $this->successStatus();
        }
    }
}