<?php
$installer = $this;
$installer->startSetup();

$bannersCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
    ->addFieldToFilter('setting_code', 'banner_settings');

if($bannersCollection->count() > 0){
    foreach($bannersCollection as $row){
        $bannersArray = array();
        $banners = $row->getData();
        $banners = $row['value'];
        $banners = explode(',', $banners);
        if(!empty($banners)){
            foreach($banners as $banner){
                $bannersArray[] = array(
                    'url'       => $banner,
                    'is_active' => '1'
                    );
            }
        }
        if(empty($bannersArray)){
            for($i=0; $i<5; $i++){
                $bannersArray[] = array(
                    'url'       => '',
                    'is_active' => '0'
                    );
            }
        }
        $row->setData('value', serialize($bannersArray))->save();
    }
}

$applicationsCollection = Mage::getModel('mobiadmin/applications')->getCollection();
if($applicationsCollection->count() > 0){
    foreach($applicationsCollection as $app){
        $appdata = $app->getData();
        $themeFolderCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
            ->addFieldToFilter('app_code', $appdata['app_code'])
            ->addFieldToFilter('setting_code', 'theme_folder_name');
        if($themeFolderCollection->count() == 0){
            Mage::getModel('mobiadmin/appsetting')->setData(array(
                    'app_code'     => $appdata['app_code'],
                    'setting_code' => 'theme_folder_name',
                    'value'        => 'shopper'
                )
                )->save();
        }
    }
}

$installer->endSetup(); 