<?php

class Mobicommerce_Mobiservices_Model_1x4x0_Store extends Mobicommerce_Mobiservices_Model_Abstract {

    public function __construct()
    {
        parent::__construct();
    }

    public function _getAllStores($data)
    {
        $stores = array();
        $store_id = Mage::app()->getStore()->getStoreId();
        $groupId = Mage::app()->getStore($store_id)->getGroupId();
        $storeViews = Mage::app()->getGroup($groupId)->getStores();
        foreach($storeViews as $_store){
            $s = $_store->getData();
            if($_store->getStoreId() == Mage::app()->getStore()->getStoreId()){
                $available_currency_codes = Mage::getModel('core/config_data')
                    ->getCollection()
                    ->addFieldToFilter('path','currency/options/allow')
                    ->addFieldToFilter('scope_id', $_store->getStoreId())
                    ->getData();
                if(isset($available_currency_codes[0])){
                    $available_currency_codes = $available_currency_codes[0];
                    if(isset($available_currency_codes['value'])){
                        $s['available_currency_codes'] = explode(',', $available_currency_codes['value']);
                    }
                }
            }
            $stores[] = $s;
        }
        return $stores;
    }

    public function getAllStores($data)
    {
        $information = $this->successStatus();
        $information['data']['stores'] = $this->_getAllStores($data);
        return $information;
    }

    public function getStoreList($data)
    {
        $information = $this->successStatus();
        
        $collection = Mage::getModel('asd_store/store')
            ->getCollection()
            ->addActiveFilter()
            ->addOrder('store_dept' , Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $stores = array();
        if($collection){
            foreach($collection as $_collection){
                $sd = $_collection->getData();
                $pictures = array();
                $pictureCollection = Mage::getModel('asd_store/store_picture')->getCollection()
                    ->addStoreFilter($_collection->getStoreId())
                    ->addOrderByPosition();

                if($pictureCollection){
                    foreach($pictureCollection as $_pcollection){
                        $d = $_pcollection->getData();
                        $d['picture_url'] = $_pcollection->getImageUrl();
                        $pictures[] = $d;
                    }
                }
                $sd['pictures'] = $pictures;
                $stores[] = $sd;
            }
        }

        $information['data']['stores'] = $stores;
        return $information;
    }

    public function getStoreDetail($data)
    {
        $store_id = (int) $data['store_id'];
        $media_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $storeInfo = Mage::getModel('asd_store/store')->load($store_id)->getData();
        if($storeInfo){
            $store_code = $storeInfo['store_code'];

            $pictures = array();
            $pictureCollection = Mage::getModel('asd_store/store_picture')->getCollection()
                ->addStoreFilter($store_id)
                ->addOrderByPosition();

            if($pictureCollection){
                foreach($pictureCollection as $_collection){
                    $d = $_collection->getData();
                    //$d['picture_url'] = $media_url . $d['picture_path'];
                    $d['picture_url'] = $_collection->getImageUrl();
                    $pictures[] = $d;
                }
            }

            $team = array();
            $teamCollection = Mage::getModel('asd_store/store_team')->getCollection()
                ->addActiveFilter()
                ->addStoreFilter($store_code);

            if($teamCollection){
                foreach($teamCollection as $_collection){
                    $d = $_collection->getData();
                    $d['pictures'] = array();
                    $pictureCollection = $_collection->getPictures();
                    if($pictureCollection){
                        foreach($pictureCollection as $_pcollection){
                            $dp = $_pcollection->getData();
                            $dp['picture_url'] = $_pcollection->getImageUrl();
                            $d['pictures'][] = $dp;
                        }
                    }
                    $team[] = $d;
                }
            }

            $opening = array();
            $openingCollection = Mage::getModel('asd_store/store_opening')->load($store_code, 'store_code');
            if($openingCollection){
                $d = $openingCollection->getData();
                $o = array();
                foreach($d as $key => $value){
                    $o['opening_monday_h']    = $d['opening_monday_h'];
                    $o['opening_tuesday_h']   = $d['opening_tuesday_h'];
                    $o['opening_wednesday_h'] = $d['opening_wednesday_h'];
                    $o['opening_thursday_h']  = $d['opening_thursday_h'];
                    $o['opening_friday_h']    = $d['opening_friday_h'];
                    $o['opening_saturday_h']  = $d['opening_saturday_h'];
                    /*
                    $pos = strpos($key, 'opening_');
                    if($pos !== false){
                        $value = str_replace(" à ", "-", $value);
                        $value = str_replace(" et de ", " ", $value);
                        $value = str_replace("de ", "", $value);
                        $value = str_replace("fermé ", "closed", $value);
                        $d[$key] = $value;
                    }
                    */
                }
                $opening[] = $o;
            }

            $events = array();
            $eventsCollection = Mage::getModel('asd_store/store_event')->getCollection()
                ->addActiveFilter()
                ->addFieldToFilter('store_code', array(array('eq' => $store_code),array('eq'=>'any1')));

            if($eventsCollection){
                foreach($eventsCollection as $_collection){
                    //$events[] = $_collection->getData();
                    $d = $_collection->getData();
                    $d['pictures'] = array();
                    $pictureCollection = $_collection->getPictures();
                    if($pictureCollection){
                        foreach($pictureCollection as $_pcollection){
                            $dp = $_pcollection->getData();
                            $dp['picture_url'] = $_pcollection->getImageUrl();
                            $d['pictures'][] = $dp;
                        }
                    }
                    $events[] = $d;
                }
            }

            $spots = array();
            $spotsCollection = Mage::getModel('asd_store/store_fishingSpot')->getCollection()
                ->addActiveFilter()
                ->addStoreFilter($store_code);

            if($spotsCollection){
                foreach($spotsCollection as $_collection){
                    $spots[] = $_collection->getData();
                }
            }

            $information = $this->successStatus();
            $information['data']['store_detail']['store_id'] = (string) $store_id;
            $information['data']['store_detail']['storeInfo'] = $storeInfo;
            $information['data']['store_detail']['pictures'] = $pictures;
            $information['data']['store_detail']['team']     = $team;
            $information['data']['store_detail']['opening']  = $opening;
            $information['data']['store_detail']['events']   = $events;
            $information['data']['store_detail']['spots']    = $spots;
        }
        else{
            $information = $this->errorStatus();
            $information['message'] = Mage::helper('core')->__('Store not found.');
        }
        return $information;
    }
}