<?php

class Mobicommerce_Mobiservices_Model_1x4x0_User extends Mobicommerce_Mobiservices_Model_Abstract {

	public function signIn($data)
    {
        $userInfo = array(); 
        try {
	        $customer = Mage::getModel('customer/customer')
	                ->setWebsiteId($this->_getWebsiteId());
	        if ($customer->authenticate($data['username'], $data['password'])) {
	            $this->_getUserSession()->setCustomerAsLoggedIn($customer);
                $this->_loginFromMobile($data);
	        } 
	        $_customer = $this->_getUserSession()->getCustomer();
			$information = $this->successStatus();
	        $information = array_merge($information,$this->_getCustomerProfileData($_customer)); 
            $information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
            if(Mobicommerce_Mobiservices_Model_Custom::REFRESH_CART_AFTER_ADD_PRODUCT){
                $information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
            }
	        return $information;

        } catch (Exception $e) {
        	$this->logout();
            return $this->errorStatus($e->getMessage());
        }    	
    }

    public function _getCustomerProfileData($_customer)
    {
		$userInfo['customer_id'] = $_customer->getId();
        $userInfo['email'] = $_customer->getEmail();
		$userInfo['firstname'] = $_customer->getFirstname();
		$userInfo['lastname'] = $_customer->getLastname();
		$userInfo['fullname'] = $_customer->getName();
        $userInfo['fulldata'] = $_customer->getData();
		$userInfo['cart_qty'] = Mage::helper('checkout/cart')->getSummaryCount();
		
        $information['data'] = $userInfo;
		$information['data']['billing_address']['_data'] = $this->_getPrimaryBillingAddress($_customer);            
		$information['data']['shipping_address']['_data'] = $this->_getPrimaryShippingAddress($_customer);            
        $information['data']['additional_addresses']['_data'] = $this->_getAdditionalAddresses($_customer);            
        //$information['data']['profile'] = array($userInfo);
        $customer_addresses = array();
        if(isset($information['data']['billing_address']['_data']) && !empty($information['data']['billing_address']['_data'])) {
            if(!array_key_exists($information['data']['billing_address']['_data']['id'], $customer_addresses)) {
                $customer_addresses[$information['data']['billing_address']['_data']['id']] = $information['data']['billing_address']['_data'];
            }
        }

        if(isset($information['data']['shipping_address']['_data']) && !empty($information['data']['shipping_address']['_data'])) {
            if(!array_key_exists($information['data']['shipping_address']['_data']['id'], $customer_addresses)) {
                $customer_addresses[$information['data']['shipping_address']['_data']['id']] = $information['data']['shipping_address']['_data'];
            }
        }

        if(isset($information['data']['additional_addresses']['_data']) && !empty($information['data']['additional_addresses']['_data'])) {
            foreach($information['data']['additional_addresses']['_data'] as $key => $value) {
                if(!array_key_exists($value['id'], $customer_addresses)) {
                    $customer_addresses[$value['id']] = $value;
                }
            }
        }
        $information['data']['unique_addresses'] = $customer_addresses;

        return $information;  	
    }

    public function _getPrimaryBillingAddress($customer)
    {
    	return $this->_formatAddress($customer->getPrimaryBillingAddress());
    }

    public function _getPrimaryShippingAddress($customer)
    {
    	return $this->_formatAddress($customer->getPrimaryShippingAddress());
    }

    public function _getAdditionalAddresses($customer)
    {
    	$_addresses = array();
    	$addresses = $customer->getAdditionalAddresses();
		foreach ($addresses as $address) {    	
			$_addresses[] = $this->_formatAddress($address);
		}
		return $_addresses;
    }

	public function _formatAddress($address)
    {
		if(!$address) return array(); 
        return array(
            'id'           => $address->getId(),
            'firstname'    => $address->getFirstname(),
            'lastname'     => $address->getLastname(),
            'fullname'     => $address->getName(),
            'street'       => $address->getStreet(),
            'city'         => $address->getCity(),
            'region'       => $address->getRegion(),
            'region_id'    => $address->getRegionId(),
            'state_code'   => $address->getRegionCode(),
            'postcode'     => $address->getPostcode(),
            'country'      => $address->getCountryModel()->loadByCode($address->getCountry())->getName(),
            'country_id'   => $address->getCountryId(),
            'country_code' => $address->getCountry(),
            'company'      => $address->getCompany(),
            'telephone'    => $address->getTelephone(),
            'fax'          => $address->getFax(),
            'prefix'       => $address->getPrefix(),
            'middlename'   => $address->getMiddleName(),
        );		
	}    

	public function getCustomerProfileData($data)
    {
		if($this->checkUserLoginSession()){
			$_customer = $this->_getUserSession()->getCustomer();
			$information = $this->successStatus();
			$information = array_merge($information,$this->_getCustomerProfileData($_customer)); 
			return $information;
		} else {
			return $this->errorStatus(array('Please_Login_To_Continue'));
		}
	}

    public function logout()
    {
        try {
            $this->_getUserSession()->logout()
                    ->setBeforeAuthUrl(Mage::getUrl());

            $information = $this->successStatus();
            $information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
            return $information;
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }
    }

    public function signUp($data)
    {
        $message = array();

        if($data['firstname']=="") return $this->errorStatus(array("Customer_First_Name_Is_Required"));
        if($data['lastname']=="") return $this->errorStatus(array("Customer_Last_Name_Is_Required"));
        if($data['email']=="") return $this->errorStatus(array("Customer_Email_Is_Required"));
        if($data['password']=="") return $this->errorStatus(array("Customer_Password_Is_Required"));

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($data['email']);

        if ($customer->getId()) {
            $message[] = Mage::helper('core')->__('Account_Already_Exists');
            $information = $this->errorStatus($message);
            return $information;
        } else {
	        $customer->setFirstname($data['firstname']);
	        $customer->setLastname($data['lastname']);
	        $customer->setEmail($data['email']);	        
	        $customer->setPassword($data['password']);

            if(isset($data['customAttributes']) && !empty($data['customAttributes'])){
                foreach($data['customAttributes'] as $key => $value){
                    $customer->setData($key, $value);
                }
            }
        }
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();
            $this->_getUserSession()->loginById($customer->getId());            
            //return $this->checkUserLoginSession();exit;
			if($this->checkUserLoginSession()){
				$_customer = $this->_getUserSession()->getCustomer();

                /* added by Yash for sending welcome email to user */
                $session = $this->_getUserSession();
                if ($_customer->isConfirmationRequired()) {
                    /** @var $app Mage_Core_Model_App */
                    $app = $this->_getApp();
                    /** @var $store  Mage_Core_Model_Store*/
                    $store = $app->getStore();
                    $_customer->sendNewAccountEmail(
                        'confirmation',
                        $session->getBeforeAuthUrl(),
                        $store->getId()
                    );
                } else {
                    $session->setCustomerAsLoggedIn($_customer);
                    $session->renewSession();
                    $url = $this->_welcomeCustomer($_customer);
                }
                $this->_loginFromMobile($data);
                /* added by Yash for sending welcome email to user - upto here */

				$information = $this->successStatus();
				$information = array_merge($information, $this->_getCustomerProfileData($_customer));
                $information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
				return $information;
			} else {
				return $this->errorStatus(array('User is not logged in'));
			}
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }
    }

    protected function _loginFromMobile($data)
    {
        if(isset($data['isMobiPaypalApplicable']) && $data['isMobiPaypalApplicable'] == '1'){
            $session = Mage::getSingleton('checkout/session');
            $session->setActivePaypalMobile("1");
        }
    }

    public function saveCustomerAddress($data)
    {
        $return = array();
        $result = true;
        $errors = false;
        $customer = $this->_getUserSession()->getCustomer();
        $address = Mage::getModel('customer/address');
        $addressId = $data['id'];
        if (version_compare(Mage::getVersion(), '1.4.2.0', '<') === true) {
            $address->setData($data);
        }
        if ($addressId && $addressId != '') {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->setId($existsAddress->getId());
            }
        } else {
            $address->setId(null);
        }

        if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                    ->setEntity($address);
        }
        try {
            if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
                $addressForm->compactData($data);
            }
            $address->setCustomerId($customer->getId());
            if($data['primary_billing']=="1") $address->setIsDefaultBilling('1');
            if($data['primary_shipping']=="1") $address->setIsDefaultShipping('1');
            $address->setCustomerId($customer->getId());
            $addressErrors = $address->validate();
            if ($addressErrors !== true) {
                $errors = true;
            }

            if (!$errors) {
                $address->save();
				$information = $this->successStatus();
				$information = array_merge($information,$this->_getCustomerProfileData($customer)); 
				return $information;
            } else {
            	return $this->errorStatus(array('Cannot_Save_Customer_Address'));
            }
        } catch (Exception $e) {
            return $this->errorStatus($e->getMessage());
        }
        return $return;
    }

    public function forgetPassword($data)
    {
        $email = $data['user_email'];
        if (is_null($email)) {
            return $this->errorStatus('Please_enter_valid_email');
        } else {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                return $this->errorStatus(array(Mage::helper('core')->__('Please_enter_valid_email')));
            }
        	$customer = Mage::getModel('customer/customer')->getCollection()
                ->addFieldToFilter('email', $email)
                ->getFirstItem();

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $e) {
                    return $this->errorStatus($e->getMessage());
                }
                $information = $this->successStatus();
                $information['message'] = array(Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email)));
                return $information;
            } else {
                $information = $this->errorStatus(array(Mage::helper('customer')->__('Customer is not exist')));
                return $information;
            }
        }
    }

    protected function _getUserSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get App
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return Mage::app();
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param Mage_Core_Model_Store|string|int $store
     * @return bool
     */
    protected function _isVatValidationEnabled($store = null)
    {
        try{
            return  $this->_getHelper('customer/address')->isVatValidationEnabled($store);
        }
        catch(Exception $e){
            return false;
        }
    }

    /**
     * Get Helper
     *
     * @param string $path
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($path)
    {
        return Mage::helper($path);
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType =  $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            Mage::app()->getStore()->getId()
        );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Get Url method
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function _getUrl($url, $params = array())
    {
        return Mage::getUrl($url, $params);
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    public function getOrderList($data)
    {
    	$session = Mage::getSingleton('customer/session');
    	if(!$session->isLoggedIn()){
    		$information = $this->errorStatus("Please_Login_To_Continue");
    		$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
    		return $information;			
    	}

        $list = $this->_getOrderHistory($data);
        $information = $this->successStatus();
        $information['data']['order_data'] = $list;
        $information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
        return $information;
    }

    /* added by yash */
    public function _getOrderHistory($data)
    {
        $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('customer_id', $this->_getSession()->getCustomer()->getId())
                ->setOrder('entity_id', 'DESC');
        $orders_list = array();
        if(count($orders) > 0){
            foreach ($orders as $order) {
                $orders_list[] = $order->getData();
            }
        }
        return $orders_list;
    }
     /* added by yash - upto here */

    public function getOrderDetail($data)
    {
    	$session = Mage::getSingleton('customer/session');
    	if(!$session->isLoggedIn()){
    		$information = $this->errorStatus("Please_Login_To_Continue");
    		$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
    		return $information;			
    	}
    	
    	$id = $data['order_id'];
    	
    	$detail = array();
        $order = Mage::getModel('sales/order')->load($id);
	
        if (count($order->getData()) == 0) {
            return $this->errorStatus();
        }
        $shipping = $order->getShippingAddress();
        $billing  = $order->getBillingAddress();
        
        $detail = array(
            'order_id'        => $id,
            'order_date'      => $order->getUpdatedAt(),
            'order_code'      => $order->getIncrementId(),
            'order_total'     => $order->getGrandTotal(),
            'order_subtotal'  => $order->getSubtotal(),
            'tax'             => $order->getTaxAmount(),
            's_fee'           => $order->getShippingAmount(),
            'order_gift_code' => $order->getCouponCode(),
            'discount'        => abs($order->getDiscountAmount()),
            'order_note'      => $order->getCustomerNote(),
            'order_items'     => $this->getProductFromOrderDetail($order, $width, $height),
            'payment_method'  => $order->getPayment()->getMethodInstance()->getTitle(),
            'shipping_method' => $order->getShippingDescription(),
            'card_4digits'    => ''
        );

        if($shipping){
            $shipping_street = $shipping->getStreetFull();
            $detail['shippingAddress'] = array(
                'name'         => $shipping->getName(),
                'street'       => $shipping_street,
                'city'         => $shipping->getCity(),
                'state_name'   => $shipping->getRegion(),
                'state_code'   => $shipping->getRegionCode(),
                'zip'          => $shipping->getPostcode(),
                'country_name' => $shipping->getCountryModel()->loadByCode($billing->getCountry())->getName(),
                'country_code' => $shipping->getCountry(),
                'phone'        => $shipping->getTelephone(),
                'email'        => $order->getCustomerEmail(),
            );
        }
        if($billing){
            $billing_street  = $billing->getStreetFull();
            $detail['billingAddress'] = array(
                'name'         => $billing->getName(),
                'street'       => $billing_street,
                'city'         => $billing->getCity(),
                'state_name'   => $billing->getRegion(),
                'state_code'   => $billing->getRegionCode(),
                'zip'          => $billing->getPostcode(),
                'country_name' => $billing->getCountryModel()->loadByCode($billing->getCountry())->getName(),
                'country_code' => $billing->getCountry(),
                'phone'        => $billing->getTelephone(),
                'email'        => $order->getCustomerEmail(),
            );
        }

        /**
         * Added by Yash
         * Added on: 16-12-2014
         * For getting tracking number for aftership extension
         */
        $tracking_info = array();
         $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();
        if($shipmentCollection){
            foreach ($shipmentCollection as $shipment){
                foreach($shipment->getAllTracks() as $tracknum){
                    //$tracknum->getNumber();
                    $tracking_info[] = $tracknum->getData();
                }
            }
        }
        $detail['tracking_info'] = $tracking_info;
        /* upto here */

        $information = $this->successStatus();
        $information['data']['order_details'] = $detail;
        $information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
        return $information;
    }

    public function getProductFromOrderDetail($order, $width, $height)
    {
        $productInfo = array();
        $itemCollection = $order->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            $options = array();
            if ($item->getProductOptions()) { 
                $options = $this->getOptions($item->getProductType(), $item->getProductOptions());
    	    }
    	    
            $product_id = $item->getProductId();
            $product = $item->getProduct();
            if (version_compare(Mage::getVersion(), '1.7.0.0', '<') === true) {
                $product = Mage::getModel('catalog/product')->load($product_id);
            }
            
            $image = Mage::helper('catalog/image')->init($product, 'small_image')->resize(300)->__toString();
            $productInfo[] = array(
                'product_id'              => $product_id,
                'product_name'            => $item->getName(),
                'product_price'           => $item->getPrice(),
                'product_subtotal'        => $item->getRowTotal(),
                'product_subtotal_inctax' => $item->getRowTotalInclTax(),
                'product_image'           => $image,
                'product_qty'             => $item->getQtyToShip(),
                'options'                 => $options,
            );
        }
	    
        return $productInfo;
    }

    public function getOptions($type, $options)
    {
        $list = array();
        if ($type == 'bundle') {
            foreach ($options['bundle_options'] as $option) {
                foreach ($option['value'] as $value) {
                    $list[] = array(
                        'option_title' => $option['label'],
                        'option_value' => $value['title'],
                        'option_price' => $value['price'],
                    );
                }
            }
        } else {
            if (isset($options['additional_options'])) {
                $optionsList = $options['additional_options'];
            } elseif (isset($options['attributes_info'])) {
                $optionsList = $options['attributes_info'];
            } elseif (isset($options['options'])) {
                $optionsList = $options['options'];
            }	    
            foreach ($optionsList as $option) {
                $list[] = array(
                    'option_title' => $option['label'],
                    'option_value' => $option['value'],
                    'option_price' => isset($option['price']) == true ? $option['price'] : 0,
                );
            }
        }
        return $list;
    }
}