<?php

class Mobicommerce_Mobiservices_Model_Pushservice extends Mobicommerce_Mobiservices_Model_Abstract {

    public function addNotification($data = array())
    {
        $type = isset($data['type']) ? $data['type'] : NULL;
        $message = isset($data['message']) ? $data['message'] : NULL;
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;

        if(empty($type) || empty($message) || empty($licence_key)){
            return $this->errorStatus("Invalid parameters");
        }
        else{
            $serverLicenceKey = NULL;
            $licenceCollection = Mage::getModel("mobiadmin/licence")->getCollection();
            if($licenceCollection->getLastItem())
                $serverLicenceKey = $licenceCollection->getLastItem()->getMlLicenceKey();
            if($licence_key == $serverLicenceKey){
                Mage::getModel('mobiadmin/notification')->setData(array(
                    "type"        => $type,
                    "date_added"  => date("Y-m-d H:i:s"),
                    "message"     => $message,
                    "read_status" => "0"
                    ))->save();

                return $this->successStatus("Notification added");
            }
            else{
                return $this->errorStatus("Invalid licence key");
            }
        }
    }

    public function saveIosBuild($data = array())
    {
        $appcode = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey  = isset($data['appkey']) ? $data['appkey'] : NULL;
        $ios_url = isset($data['ios_url']) ? $data['ios_url'] : NULL;
        $ios_status = isset($data['ios_status']) ? $data['ios_status'] : NULL;
        $udid = isset($data['udid']) ? $data['udid'] : NULL;
        if(!empty($appcode) && !empty($appkey) && !empty($ios_url)){
            $collection = Mage::getModel('mobiadmin/applications')->getCollection()
                ->addFieldToFilter('app_code',$appcode)
                ->addFieldToFilter('app_key',$appkey);

            if($collection){
                foreach($collection as $_collection){
                    $_collection->setData('ios_url', $ios_url)
                        ->setData('ios_status', $ios_status)
                        ->setData('udid', $udid)
                        ->save();
                }
            }
        }
    }

    public function saveAndroidBuild($data = array())
    {
        $appcode = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey  = isset($data['appkey']) ? $data['appkey'] : NULL;
        $android_url = isset($data['android_url']) ? $data['android_url'] : NULL;
        $android_status = isset($data['android_status']) ? $data['android_status'] : NULL;
        if(!empty($appcode) && !empty($appkey) && !empty($android_url)){
            $collection = Mage::getModel('mobiadmin/applications')->getCollection()
                ->addFieldToFilter('app_code',$appcode)
                ->addFieldToFilter('app_key',$appkey);

            if($collection){
                foreach($collection as $_collection){
                    $_collection->setData('android_url', $android_url)
                        ->setData('android_status', $android_status)
                        ->save();
                }
            }
        }
    }

    public function updateDeliveryStatus($data = array())
    {
        $appcode = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey  = isset($data['appkey']) ? $data['appkey'] : NULL;
        $licence_key  = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if(!empty($appcode) && !empty($appkey)){
            $collection = Mage::getModel('mobiadmin/applications')->getCollection()
                ->addFieldToFilter('app_code',$appcode)
                ->addFieldToFilter('app_key',$appkey)
                ->addFieldToFilter('app_license_key', $licence_key);

            if($collection){
                foreach($collection as $_collection){
                    $_collection->setData('android_status', isset($data['android_status']) ? $data['android_status'] : NULL)
                        ->setData('android_url', isset($data['android_url']) ? $data['android_url'] : NULL)
                        ->setData('ios_status', isset($data['ios_status']) ? $data['ios_status'] : NULL)
                        ->setData('ios_url', isset($data['ios_url']) ? $data['ios_url'] : NULL)
                        ->setData('delivery_status', isset($data['delivery_status']) ? $data['delivery_status'] : NULL)
                        ->save();
                    if(isset($data['webapp_url']))
                        $_collection->setData('webapp_url', $data['webapp_url'])->save();
                }
            }
            return $this->successStatus();
        }
        return $this->errorStatus();
    }

    public function removeapps($data = array())
    {
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if($licence_key && isset($data['appstodelete']) && !empty($data['appstodelete'])){
            $licenceCollection = Mage::getModel("mobiadmin/licence")->getCollection();
            $serverLicenceKey = NULL;
            if($licenceCollection->getLastItem())
                $serverLicenceKey = $licenceCollection->getLastItem()->getMlLicenceKey();
            if($licence_key == $serverLicenceKey){
                $appcodes = array();
                foreach($data['appstodelete'] as $_app){
                    $appcodes[] = $_app['appcode'];
                }
                $deletedAppsCount = Mage::getModel('mobiadmin/applications')->deleteapps($appcodes);
                return $this->successStatus($deletedAppsCount . " Apps deleted");
            }
        }
        return $this->errorStatus("No records found");
    }

    public function buyapp($data = array())
    {
        $appcode              = isset($data['appcode']) ? $data['appcode'] : NULL;
        $appkey               = isset($data['appkey']) ? $data['appkey'] : NULL;
        $licence_key          = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        $mode                 = isset($data['mode']) ? $data['mode'] : NULL;
        $mobicommerce_orderid = isset($data['mobicommerce_orderid']) ? $data['mobicommerce_orderid'] : NULL;
        $services_purchased   = isset($data['services_purchased']) ? $data['services_purchased'] : NULL;

        if(empty($appcode) || empty($appkey) || empty($licence_key) || empty($mode) || empty($mobicommerce_orderid)){
            return $this->errorStatus("Invalid parameters");
        }
        else{
            $appCollection = Mage::getModel('mobiadmin/applications')->getCollection()
                ->addFieldToFilter('app_code', $appcode)
                ->addFieldToFilter('app_key', $appkey)
                ->addFieldToFilter('app_license_key', $licence_key);

            $services_purchased = explode(',', $services_purchased);

            if($appCollection->count() > 0){
                foreach($appCollection as $_app){
                    $_app->setData("app_mode", $mode);
                    $_app->setData("android_url", isset($data['android_url']) ? $data['android_url'] : NULL);
                    $_app->setData("android_status", isset($data['android_status']) ? $data['android_status'] : NULL);
                    $_app->setData("ios_url", isset($data['ios_url']) ? $data['ios_url'] : NULL);
                    $_app->setData("ios_status", isset($data['ios_status']) ? $data['ios_status'] : NULL);
                    $_app->setData("delivery_status", isset($data['delivery_status']) ? $data['delivery_status'] : NULL);
                    $_app->setData('webapp_url', isset($data['webapp_url']) ? $data['webapp_url'] : NULL)->save();
                    $_app->setData("addon_parameters", serialize(array(
                        "mobicommerce_orderid" => $mobicommerce_orderid,
                        "services_purchased" => $services_purchased
                        )));
                    $_app->setData("update_time", date("Y-m-d H:i:s"));
                    $_app->save();
                }

                $licenceCollection = Mage::getModel("mobiadmin/licence")->getCollection();
                if($licenceCollection->count() > 0){
                    foreach($licenceCollection as $_licence){
                        $_licence->setData("ml_debugger_mode", 'no');
                        $_licence->save();
                    }
                }
                return $this->successStatus($appCollection->count() . " apps updated");
            }
            else{
                return $this->errorStatus("No apps found");
            }
        }
    }

    public function getPluginVersion()
    {
        $return = $this->successStatus();
        $return['data']['moduleStatus'] = Mage::helper('core')->isModuleEnabled('Mobicommerce_Mobiservices');
        $return['data']['moduleVersion'] = (string) Mage::getConfig()->getNode()->modules->Mobicommerce_Mobiservices->version;
        return $return;
    }

    public function getModulesList($data = array())
    {
        $licence_key = isset($data['licence_key']) ? $data['licence_key'] : NULL;
        if($licence_key){
            $licenceCollection = Mage::getModel("mobiadmin/licence")->getCollection();
            $serverLicenceKey = NULL;
            $debuggerMode = false;
            if($licenceCollection->getLastItem()){
                $serverLicenceKey = $licenceCollection->getLastItem()->getMlLicenceKey();
                $debuggerMode = $licenceCollection->getLastItem()->getMlDebuggerMode();
                if($debuggerMode == 'yes'){
                    $debuggerMode = true;
                }
            }
            if($licence_key == $serverLicenceKey && $debuggerMode){
                $return = $this->successStatus();
                $return['data']['modules'] = array_keys((array)Mage::getConfig()->getNode('modules')->children());
                return $return;
            }
        }
        return $this->errorStatus("Oops");
    }
}