<?php

class Mobicommerce_Mobiservices_Model_1x3x3_Shoppingcart_Cart extends Mobicommerce_Mobiservices_Model_Abstract
{
    public function addtoCart($productData)
    {
        $cart   = $this->_getCart();
        $params = $productData;

        try{
            if(isset($params->qty['qty'])){
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            } 

            $product = null;
            $productId = (int) $params['product'];
            if ($productId) {
                $_product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                if ($_product->getId()) {
                    $product = $_product;
                }
            }
            $related = $params['related_product'];
            $information = $this->successStatus();
            if (!$product) {
                return $this->errorStatus('Product_Is_Unavailable');
            }            

            if ($product->isConfigurable()) {
                $request = $this->_getProductRequest($params);
                $qty = isset($params['qty']) ? $params['qty'] : 0;
                $requestedQty = ($qty > 1) ? $qty : 1;
                $subProduct = $product->getTypeInstance(true)
                    ->getProductByAttributes($request->getSuperAttribute(), $product);

                if (!empty($subProduct)
                    && $requestedQty < ($requiredQty = $subProduct->getStockItem()->getMinSaleQty())
                ){
                    $requestedQty = $requiredQty;
                }

                $params['qty'] = $requestedQty;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getCheckoutSession()->setCartWasUpdated(true);
            Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => Mage::app()->getRequest(), 'response' => Mage::app()->getResponse()));
            $information['data']['cart_details'] = $this->getCartInfo();
            if(Mobicommerce_Mobiservices_Model_Custom::REFRESH_CART_AFTER_ADD_PRODUCT){
                $information['data']['cart_details'] = $this->getCartInfo();
            }
        }
        catch (Mage_Core_Exception $e) {
            $information = $this->errorStatus($e->getMessage());
            return $information;                
        }  catch (Exception $e) {
            $information = $this->errorStatus($e->getMessage());
            return $information;
        }

        return $information;
    }

    public function setDiscountCode($data)
    {
        $couponCode = $data['coupon_code'];
        $return = array();
        $information = '';
        
        if ($data['remove'] == 1) {
            $couponCode = '';
        }
        
        try {
            $this->_getCart()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getCart()->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
            $total = $this->_getCart()->getQuote()->getTotals();
            $return['discount'] = 0;
            if ($total['discount'] && $total['discount']->getValue()) {
            $return['discount'] = abs($total['discount']->getValue());
            }
            $return['grand_total'] = $total['grand_total']->getValue();
            $return['sub_total'] = $total['subtotal']->getValue();
            if (isset($total['tax']) && $total['tax']->getValue()) {
            $tax = $total['tax']->getValue(); //Tax value if present
            } else {
            $tax = 0;
            }
            $return['tax'] = $tax;

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getCart()->getQuote()->getCouponCode()) {
                    $return['coupon_code'] = (string) $data->coupon_code;
                    $event_name = $this->getControllerName();
                    $event_value = array(
                        'object' => $this,
                        );
                    $data_change = $this->changeData($return, $event_name, $event_value);
                    $information = $this->successStatus();
                    $information['data'] = array(array('fee' => $data_change));
                    $information['message'] = Mage::helper('core')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
                    $information['data']['cart_details']= $this->getCartInfo(); 
                    return $information;
                } else {
                    $return['coupon_code'] = '';
                    $event_name = $this->getControllerName();
                    $event_value = array(
                        'object' => $this,
                        );
                    $data_change = $this->changeData($return, $event_name, $event_value);
                    $information = $this->errorStatus();
                    $information['data'] = array(array('fee' => $data_change));
                    $information['message'] = Mage::helper('core')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
                    $information['data']['cart_details']= $this->getCartInfo(); 
                    return $information;
                }
            } else { 
                $event_name = $this->getControllerName();
                $event_value = array(
                    'object' => $this,
                    );
                $data_change = $this->changeData($return, $event_name, $event_value);
                $information = $this->successStatus();
                $information['data'] = array(array('fee' => $data_change));
                $information['message'] = Mage::helper('core')->__('Coupon code was canceled.');
                $information['data']['cart_details']= $this->getCartInfo(); 
                return $information;
            }
        } catch (Mage_Core_Exception $e) {
            $information = $this->errorStatus($e->getMessage());
        } catch (Exception $e) {
            $information = $this->errorStatus($e->getMessage());
        }
        $information['data']['cart_details']= $this->getCartInfo(); 
        return $information;
    }

    public function checkCartStatus(&$information)
    {
        $cart = $this->_getCart();
        $message_error = array();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();
            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                        ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description') ? Mage::getStoreConfig('sales/minimum_order/description') : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);
            }
            $messages = array();
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {                    
                    $messages[] = $message;
                    $message_error[] = "NOT CHECKOUT" . $message->getText();
                }
            }           
        }
        if (count($message_error)) {
            $information['message'] = $message_error;
        }
        $cart->getCheckoutSession()->getMessages(true);
        $this->_getCheckoutSession()->setCartWasUpdated(true);
    }

    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);

        $productId = $product->getId();

        if ($product->getStockItem()) {
            $minimumQty = $product->getStockItem()->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && $request->getQty() < $minimumQty
                    && !$this->_getCart()->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
        }

        if ($productId) {
            try {
                $result = $this->_getCart()->getQuote()->addProduct($product, $request);
            } catch (Mage_Core_Exception $e) {
                $this->_getCheckoutSession()->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                $redirectUrl = ($product->hasOptionsValidationFail()) ? $product->getUrlModel()->getUrl(
                                $product, array('_query' => array('startcustomization' => 1))
                        ) : $product->getProductUrl();
                $this->_getCheckoutSession()->setRedirectUrl($redirectUrl);
                if ($this->_getCheckoutSession()->getUseNotice() === null) {
                    $this->_getCheckoutSession()->setUseNotice(true);
                }
                Mage::throwException($result);
            }
        } else {
            Mage::throwException(Mage::helper('checkout')->__('The product does not exist.'));
        }

        Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $product));
        $this->_getCart()->getCheckoutSession()->setLastAddedProductId($productId);
        return $result;
    }

    public function getCartDetails()
    {
        $information = $this->successStatus();
        $information['data']['cart_details'] = $this->getCartInfo();
        return $information;
    }

    public function getProductOptions($item)
    {
        $options = array();
        if (version_compare(Mage::getVersion(), '1.5.0.0', '>=') === true) {
            $helper = Mage::helper('catalog/product_configuration');
            if ($item->getProductType() == "simple") {
                $options = Mage::helper('mobiservices/shoppingcart')->formatOptionsCart($helper->getCustomOptions($item));
            } elseif ($item->getProductType() == "configurable") {
                $options = Mage::helper('mobiservices/shoppingcart')->formatOptionsCart($helper->getConfigurableOptions($item));
            } elseif ($item->getProductType() == "bundle") {
                $options = Mage::helper('mobiservices/shoppingcart')->getOptions($item);
            } elseif ($item->getProductType() == "virtual") {
                $options = Mage::helper('mobiservices/shoppingcart')->getOptions($item);
            } elseif ($item->getProductType() == "downloadable") {
                $options = Mage::helper('mobiservices/shoppingcart')->getDownloadableOptions($item);
            }
        } else {
            //Zend_debug::dump(get_class($item));die();
            if ($item->getProductType() != "bundle") {
                $options = Mage::helper('mobiservices/shoppingcart')->getUsedProductOption($item);
            } else {
                $options = Mage::helper('mobiservices/shoppingcart')->getOptions($item);
            }
        }       
        return $options;
    }

    public function getCartInfo()
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $quote->collectTotals()->save();
        $quote->save();

        $list = array();
        $allItems = $quote->getAllVisibleItems();
        $shippingRequired = true;
        $nonShippingRequiredProducts = 0;
        foreach ($allItems as $item) {
            $product = $item->getProduct();
            $options = $this->getProductOptions($item);

            $getHasError = $item->getHasError();
            $errorDescription = false;
            if($getHasError)
                $errorDescription = $this->_remove_cart_duplicate_error($item->getErrorInfos());

            $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

            $list[] = array(
                'item_id'               => $item->getId(),
                'product_id'            => $product->getId(),
                'name'                  => $product->getName(),
                'price'                 => $item->getPrice(),
                'product_type'          => $item->getProductType(),
                'row_total'             => $item->getRowTotal(),
                'product_thumbnail_url' => Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(200)->__toString(),
                'qty'                   => $item->getQty(),
                'max_qty'               => (int) $inventory->getQty(),
                'qty_increments'        => (int) $inventory->getQtyIncrements(),
                'options'               => $options,
                'hasError'              => $getHasError,
                'errorDescription'      => $errorDescription,
                );
            if(in_array($item->getProductType(), array('downloadable', 'virtual'))){
                $nonShippingRequiredProducts++;
            }
        }
        $info['items'] = $list;        
        $cart_array = array_merge($info, $this->getCartTotals(), $this->getCartAddresses());
        if(empty($list))
            $cart_array['cart_qty'] = 0;

        if($nonShippingRequiredProducts == count($list) && $nonShippingRequiredProducts > 0){
            $shippingRequired = false;
        }
        $cart_array['shippingRequired'] = $shippingRequired;        

        $sessionCustomer = Mage::getSingleton("customer/session");
        $userinfo = array();
        if($sessionCustomer->isLoggedIn()) {
            $_customer = $sessionCustomer->getCustomer();
            $userinfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->_getCustomerProfileData($_customer);
        }
        $cart_array['userinfo'] = $userinfo;
        $cart_array['wishlist'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/wishlist_wishlist'))->getWishlistInfo();
        return $cart_array;
    }

    protected function _remove_cart_duplicate_error($errors = null)
    {
        $final_errors = array();
        $codes_array = array();
        if(!empty($errors)){
            foreach($errors as $err){
                if(!in_array($err['code'], $codes_array)){
                    $final_errors[] = $err;
                    $codes_array[] = $err['code'];
                }
            }
        }
        return $final_errors;
    }

    public function getCartAddresses()
    {
        $cartShippingAddress = $this->_getQuote()->getShippingAddress();
        if($cartShippingAddress) {
            $addr['shipping_address'] = $this->_getAddress($cartShippingAddress);
        }

        $cartBillingAddress = $this->_getQuote()->getBillingAddress();
        if($cartBillingAddress) {
            $addr['billing_address'] = $this->_getAddress($cartBillingAddress);
        }
        return $addr;
    }

    protected function _getAddress($address)
    {
        $info[] = array(
            'ID'                   => $address->getID(),
            'firstname'            => $address->getFirstname(),
            'lastname'             => $address->getLastname(),
            'company'              => $address->getCompany(),
            'street'               => $address->getStreet(),
            'city'                 => $address->getCity(),
            'region'               => $address->getRegion(),
            'region_id'            => $address->getRegionId(),
            'postcode'             => $address->getPostcode(),
            'country_id'           => $address->getCountryId(),
            'telephone'            => $address->getTelephone(),
            'fax'                  => $address->getFax(),
            'shipping_method'      => $address->getShippingMethod(),
            'shipping_description' => $address->getShippingDescription(),
            'shipping_amount'      => $address->getShippingAmount(),
            );
        return $info;
    }

    public function getCartTotals()
    {
        $this->_getQuote()->collectTotals()->save();
        $total = $this->_getCart()->getQuote()->getTotals();
        $cartdata = $this->_getCart()->getQuote()->getData();
        $return['discount'] = 0;
        $return['couponcode'] = $this->_getCart()->getQuote()->getCouponCode();
        $return['cart_qty'] = Mage::helper('checkout/cart')->getSummaryCount();
        if ($total['discount'] && $total['discount']->getValue()) {
            $return['discount_amount'] = abs($total['discount']->getValue());
        }
        $return['grand_total'] = $total['grand_total']->getValue();
        $return['subtotal'] = $total['subtotal']->getValue();
        if (isset($total['tax']) && $total['tax']->getValue()) {
            $tax = $total['tax']->getValue(); //Tax value if present
        } else {
            $tax = 0;
        }
        $return['tax_amount'] = $tax;
        if(!empty($cartdata) && $cartdata['items_qty'] > 0){
            try{
                $return['paymentinfo'] = array(
                    'code'  => strtoupper($this->_getCart()->getQuote()->getPayment()->getMethodInstance()->getCode()),
                    'title' => $this->_getCart()->getQuote()->getPayment()->getMethodInstance()->getTitle(),
                    'fee'   => isset($cartdata['cod_fee'])?$cartdata['cod_fee']:0,
                    );
                if(isset($cartdata['codfee']) && !empty($cartdata['codfee'])){
                    $return['paymentinfo']['fee'] = $cartdata['codfee'];
                }

                if(Mobicommerce_Mobiservices_Model_Custom::ROUNDUP_CART_VALUES){
                    $return['grand_total'] = round($return['grand_total']);
                    $return['tax_amount'] = round($return['tax_amount']);
                    $return['subtotal'] = round($return['subtotal']);
                }
            }
            catch(Exception $e){}
        }
        return $return;
    }

    public function updateCart($data)
    {
        $cartData = $data['cart'];
        $information = $this->successStatus();
        try {
            if (count($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );

                foreach($cartData as $index => $data){
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                
                $cart = $this->_getCart();
                if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                
                if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
                    $cartData = $cart->suggestItemsQty($cartData);
                }               
                $cart->updateItems($cartData)
                        ->save();                       
            }           
        } catch (Mage_Core_Exception $e) {
            $information = $this->errorStatus($e->getMessage());
            return $information;
        } catch (Exception $e) {
            $information = $this->errorStatus($e->getMessage());
            return $information;
        }

        $this->_getCheckoutSession()->setCartWasUpdated(true);
        $information['data']['cart_details']= $this->getCartInfo();
        $information['data']['shipping_methods'] = $this->_getShippingMethods();
        return $information;
    }

    public function deleteItem($data)
    {
        $id = (int) $data['item_id'];
        if ($id) {
            try {                
                $this->_getCart()->removeItem($id)->save();
            } catch (Mage_Core_Exception $e) {
                $information = $this->errorStatus($e->getMessage());
                return $information;                
            } catch (Exception $e) {
                $information = $this->errorStatus($e->getMessage());
                return $information;
            }
        }
        $information = $this->successStatus('Item_Has_Been_Deleted_From_Cart');
        $this->_getCheckoutSession()->setCartWasUpdated(true);
        $information['data']['cart_details'] = $this->getCartInfo();
        return $information;
    }

    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    public function saveBilling($data)
    {
        try {            
            if ($this->checkUserLoginSession()) {
                $this->_getOnepage()->saveCheckoutMethod('customer');
            } elseif (isset($data['customer_password']) && $data['customer_password']) {
                $this->_getOnepage()->saveCheckoutMethod('register');
            } else {
                $this->_getOnepage()->saveCheckoutMethod('guest');
            }
        } catch (Exception $e) {
            $information = $this->errorStatus($e->getMessage());
            $information['data']['cart_details'] = $this->getCartInfo(); 
        }
        
        $billing_address = $data['billing'];
        $customerBillingAddressId = $data['billing_address_id'];        
        if (isset($data['email'])) {
            $billing_address['email'] = trim($billing_address['email']);
        }
        $result['billing'] = $this->_getOnepage()->saveBilling($billing_address, $customerBillingAddressId);
        
        if(isset($result['billing']['error'])){
            $information = $this->errorStatus($result['billing']['message']);
            $information['data']['cart_details'] = $this->getCartInfo(); 
            return $information;
        }

        if(isset($billing_address['use_for_shipping'])){
            if($billing_address['use_for_shipping']!="1"){
                $shipping_address = $data['shipping'];
                $customerShippingAddressId = $data['shipping_address_id'];  
                $result['shipping'] = $this->_getOnepage()->saveShipping($shipping_address, $customerShippingAddressId);

                if(isset($result['shipping']['error'])){
                    $information = $this->errorStatus($result['shipping']['message']);
                    $information['data']['cart_details'] = $this->getCartInfo(); 
                    return $information;
                }
            } 
        }

        $this->_getCheckoutSession()->getQuote()->getShippingAddress()->collectShippingRates()->save();
        $info =  $this->successStatus();     
        $info['data']['cart_details']     = $this->getCartInfo(); 
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        $info['data']['payment_methods']  = $this->_getPaymentMethos();
     
        return $info;    
    }

    public function _getPaymentMethos()
    {
        $quote = $this->_getCheckoutSession()->getQuote();
        $store = $quote->getStoreId();
        $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
        $methodsResult = array();
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        foreach ($methods as $key => $method) {
            if ($this->_canUsePaymentMethod($method, $quote) && 
                    (!in_array($method->getCode(), $this->_getRestrictedMethods()) &&
                    (in_array($method->getCode(), $this->_getAllowedMethods()) || $method->getConfigData('cctypes')))
                    && ($total != 0
                    || $method->getCode() == 'free'
                    || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))){
                    
            } else {
                if(!($this->_canUsePaymentMethod($method, $quote) && $method->getCode() == 'mobipayments')){
                    unset($methods[$key]);
                }
            }
        }

        foreach ($methods as $method) {
            $list[] = $this->getPaymentMethodDetail($method);
        }
        return $list;
    }

    public function getPaymentMethodDetail($method)
    {
        $code = $method->getCode();
        $list = $this->_getPaymentMethodTypes();
        if (in_array($code, $this->_getAllowedMethods())) {
            $type = $list[$code];
        }else{
            $type = 1;
        }
        $detail = array();
        $detail['title'] = $method->getTitle();
        $detail['code'] = strtoupper($method->getCode());
        if ($type == 0) {
            if ($code == "checkmo") {
                $detail['payable_to']            = $method->getConfigData('payable_to');
                $detail['payable_to_label']      = "Payable To";
                $detail['mailing_address']       = $method->getConfigData('mailing_address');
                $detail['mailing_address_label'] = "Mailing Address";
                $detail['show_type']             = 0;
            } else if(in_array($code, array('banktransfer', 'cashondelivery', 'mobipaypaloffline'))) {
                $detail['instructions'] = $method->getConfigData('instructions');
                $detail['show_type']    = 0;
            }
            else if(in_array($code, array('bankpayment'))){
                $instructions = $method->getCustomText();
                $custom_text = $method->getCustomText();
                $accounts = unserialize($method->getConfigData('bank_accounts'));
                if($accounts){
                    $account_holder = $accounts['account_holder'];
                    if($account_holder){
                        foreach($account_holder as $ah_key => $ah){
                            if(!empty($ah)){
                                if(!empty($instructions))
                                    $instructions .= "<br />";
                                $instructions .= "Intestatario: ".$ah;
                                if(!empty($accounts['account_number'][$ah_key]))
                                    $instructions .= "<br />Account Number: ".$accounts['account_number'][$ah_key];
                                if(!empty($accounts['account_number'][$ah_key]))
                                    $instructions .= "<br />Bank Name: ".$accounts['bank_name'][$ah_key];
                                if(!empty($accounts['iban'][$ah_key]))
                                    $instructions .= "<br />IBAN: ".$accounts['iban'][$ah_key];
                                if(!empty($accounts['bic'][$ah_key]))
                                    $instructions .= "<br />BIC: ".$accounts['bic'][$ah_key];
                            }
                        }
                    }
                }
                $detail['instructions'] = $instructions;
                $detail['show_type']    = 0;
            }
            else if(in_array($code, array('cashondeliverypayment'))) {
                $detail['cost_default'] = $method->getConfigData('cost');
                $detail['instructions'] = $method->getConfigData('cost');
                $detail['show_type']    = 0;
            }
            else {
                $detail['show_type'] = 0;
            }
        } elseif ($type == 1) {
            if($code == 'paymill_creditcard') {
                try{
                    $detail['configData'] = Mage::getModel('mobipayments/paymill')->getConfigData();
                }
                catch(Exception $e){
                    $detail['configData'] = null;
                }
            }
            $detail['cc_types']  = $this->_getPaymentMethodAvailableCcTypes($method);
            $detail['useccv']    = $method->getConfigData('useccv');
            $detail['show_type'] = 1;
        } elseif ($type == 2) {
            $detail['email']      = $method->getConfigData('business_account');
            $detail['client_id']  = $method->getConfigData('client_id');
            $detail['is_sandbox'] = $method->getConfigData('is_sandbox');
            $detail['bncode']     = "Magestore_SI_MagentoCE";
            $detail['show_type']  = 2;
        } elseif($type == 9) {
            $detail['show_type'] = 9;
            $detail['urls'] = array(
                'redirect_url' => $method->getOrderPlaceRedirectUrl(),
                'success_url'  => $method->getPaidSuccessUrl(),
                'cancel_url'   => $method->getPaidCancelUrl(),
                'notify_url'   => $method->getPaidNotifyUrl(),
                'condition'    => 'EQUAL',
                );

            if(in_array($method->getCode(), array(
                'msp_ideal',
                'msp_deal', 
                'msp_banktransfer', 
                'msp_visa', 
                'msp_mastercard',
                'msp_maestro',
                'msp_babygiftcard'
                ))){
                $detail['urls']['success_url'] = Mage::getUrl("msp/standard/return", array("_secure" => true));
                $detail['urls']['cancel_url'] = Mage::getUrl("msp/standard/cancel", array("_secure" => true));
                $detail['urls']['condition'] = 'LIKE';
            }
            else if(in_array($method->getCode(), array(
                'payuapi'
                ))){
                $detail['cc_types']  = $this->_getPaymentMethodAvailableCcTypes($method);
                $detail['note_1']    = $method->getConfigData('VG6YYEN1');
                $detail['note_2']    = $method->getConfigData('VG6YYEN2');
                
                $cartTotals = $this->getCartTotals();
                $grandTotal = $cartTotals['grand_total'];

                $installment_options = array();
                $installment_key_pair = array(
                    array(
                        "name"      => "Axess",
                        "keycode"   => "V7H1993D1",
                        "valuecode" => "VGD8UEY31",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "Bonus",
                        "keycode"   => "V7H1993D2",
                        "valuecode" => "VGD8UEY32",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "Maximum",
                        "keycode"   => "V7H1993D3",
                        "valuecode" => "VGD8UEY33",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "Finans",
                        "keycode"   => "V7H1993D4",
                        "valuecode" => "VGD8UEY34",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "World",
                        "keycode"   => "V7H1993D5",
                        "valuecode" => "VGD8UEY35",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "Asya",
                        "keycode"   => "V7H1993D6",
                        "valuecode" => "VGD8UEY36",
                        "is_active" => false,
                        "options"   => array()
                        ),
                    array(
                        "name"      => "Halkbank",
                        "keycode"   => "V7H1993D7",
                        "valuecode" => "VGD8UEY37",
                        "is_active" => false,
                        "options"   => array()
                        )
                    );
                foreach($installment_key_pair as $installment_key => $installment_pair){
                    if($method->getConfigData($installment_pair['keycode'])){
                        $installment_key_pair[$installment_key]['is_active'] = true;
                        $installment_key_pair[$installment_key]['options_str'] = $method->getConfigData($installment_pair['valuecode']);
                        $installment_key_pair[$installment_key]['options'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/custom'))->_processPayuapiInstallmentOptionsString($installment_key_pair[$installment_key]['options_str'], $grandTotal, $interest_type = 'simple', $force_yearly_interest = true);
                        $installment_options[$installment_pair['keycode']] = $installment_key_pair[$installment_key];
                    }
                }
                $detail['installment_options'] = $installment_options;
            }
            else if(in_array($method->getCode(), array(
                'paypal_standard',
                ))){
                $detail['urls']['success_url'] = Mage::getUrl('paypal/standard/success');
                $detail['urls']['cancel_url'] = Mage::getUrl('paypal/standard/cancel');
            }
            else if(in_array($method->getCode(), array(
                'paypal_express',
                ))){
                $detail['urls']['redirect_url'] = Mage::getUrl('paypal/express/start');
                $detail['urls']['success_url'] = Mage::getUrl('paypal/express/review');
                $detail['urls']['update_order_url'] = Mage::getUrl('paypal/express/updateOrder');
                $detail['urls']['cancel_url'] = Mage::getUrl('checkout/cart');
            }

            if(empty($detail['urls']['success_url']))
                $detail['urls']['success_url'] = Mage::getUrl("checkout/onepage/success", array("_secure" => true));
            if(empty($detail['urls']['cancel_url']))
                $detail['urls']['cancel_url'] = Mage::getUrl("checkout/onepage/failure", array("_secure" => true));
        }
        return $detail;
    }

    protected function _getRestrictedMethods()
    {
        return array(
            'authorizenet_directpost',
            );
    }

    protected function _getAllowedMethods()
    {
        return array(
            'paypal_standard',
            'paypal_express',
            'paypal_direct',
            'ccavenue',
            'zooz',
            'transfer_mobile',
            'cashondelivery',
            'phoenix_cashondelivery',
            'cashondeliverypayment',
            'checkmo',
            'banktransfer',
            'bankpayment',
            'paymill_creditcard',
            'payfast',
            'payuapi',
            'payucheckout_shared',
            'mobipaypaloffline',
            'msp_ideal',
            'msp_deal',
            'msp_banktransfer',
            'msp_visa',
            'msp_mastercard',
            'msp_maestro',
            'msp_babygiftcard',
            'atos_standard',
            'atos_euro',
            'atos_cofidis3x',
        );
    }

    protected function _getPaymentMethodTypes()
    {
        return array(
            'paypal_standard'        => 9,
            'paypal_express'         => 9,
            'paypal_direct'          => 1,
            'ccavenue'               => 9,
            'zooz'                   => 2,
            'transfer_mobile'        => 0,
            'cashondelivery'         => 0,
            'phoenix_cashondelivery' => 0,
            'cashondeliverypayment'  => 0,
            'checkmo'                => 0,
            'banktransfer'           => 0,
            'bankpayment'            => 0,
            'paymill_creditcard'     => 1,
            'mobipaypaloffline'      => 0,
            'payfast'                => 9,
            'payucheckout_shared'    => 9,
            'msp_ideal'              => 9,
            'msp_deal'               => 9,
            'msp_banktransfer'       => 9,
            'msp_visa'               => 9,
            'msp_mastercard'         => 9,
            'msp_maestro'            => 9,
            'msp_babygiftcard'       => 9,
            'payuapi'                => 9,
            'atos_standard'          => 9,
            'atos_euro'              => 9,
            'atos_cofidis3x'         => 9,
        );        
    }    

    protected function _assignMethod($method, $quote)
    {
        $method->setInfoInstance($quote->getPayment());
    }

    protected function _canUsePaymentMethod($method, $quote)
    {
        //if (!($method->isGateway() || $method->canUseInternal())) {
        if (!($method->isGateway() || $method->canUseCheckout())) {
            return false;
        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
    }

    protected function _getPaymentMethodAvailableCcTypes($method)
    {
        $ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }

    public function _getShippingMethods()
    {
        $shipping = $this->_getCheckoutSession()->getQuote()->getShippingAddress();
        $methods = $shipping->getGroupedAllShippingRates();
        $list = array();
        foreach($methods as $_ccode => $_carrier) {
            foreach($_carrier as $_rate) {
                $list[] = $_rate->getData();
            }
        }

        $method_group = array();
        if(!empty($list)){
            foreach($list as $key => $value){
                if(array_key_exists($value['carrier'], $method_group)){
                    $method_group[$value['carrier']]++;
                }
                else{
                    $method_group[$value['carrier']] = 1;
                }
                $list[$key]['carrier_index'] = $method_group[$value['carrier']];
            }
        }

        return $list;    
    }

    public function _getShippingMethodsBabyLife()
    {
        $shipping = $this->_getCheckoutSession()->getQuote()->getShippingAddress();
        $methods = $shipping->getGroupedAllShippingRates();
        $list = array();
        $groups = array();
        foreach ($methods as $_ccode => $_carrier) {
            foreach ($_carrier as $rate) {
                if($rate->isDeleted()) continue;
                if($rate->getCarrier() == 'vendor_multirate') continue;
                $tmp = explode(VES_VendorsShipping_Model_Shipping::DELEMITER, $rate->getCode());
                if(sizeof($tmp) != 2) continue;
                $vendorId = $tmp[1];
                $vendor = Mage::getModel('vendors/vendor')->load($vendorId);
                if(!$vendor->getId()) continue;
                if(!isset($groups[$vendorId])) $groups[$vendorId] = array();
                $groups[$vendorId]['title'] = $vendor->getTitle();
                if(!isset($groups[$vendorId]['rates'])) $groups[$vendorId]['rates'] = array();
                $groups[$vendorId]['rates'][] = $rate->getData();
            }
        }
        $list = array();
        $method_group = array();
        if(!empty($groups)){
            foreach($groups as $key => $value){
                $career_index=1;
                foreach($value['rates'] as $_rate){
                    $_rate['carrier_index'] = $career_index;
                    $_rate['carrier_title'] = $value['title'];
                    $methodGroup = explode('||', $_rate['method']);
                    $_rate['methodGroup'] = 'vendor_shipping_method__'. $methodGroup[1];
                    $list[] = $_rate;
                    $career_index++;
                }
            }
        }

        return $list;    
    }

    public function getShippingMethods()
    {
        $info = $this->successStatus();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        $info['data']['cart_details'] = $this->getCartInfo(); 
        return $info;
    }

    public function getPaymentMethos()
    {
        $info = $this->successStatus();
        $info['data']['payment_methods'] = $this->_getPaymentMethos();
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    public function saveShippingMethod($data)
    {
        $method = $data['shipping_method'];
        if(Mobicommerce_Mobiservices_Model_Custom::IS_SHIPPING_METHOD_CUSTOM_FIELDS){
            $_POST = $_GET;
        }
        try {        
            $result = $this->_getOnepage()->saveShippingMethod($method);
            if (!$result) {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                        array(
                            'request' => Mage::app()->getRequest(),
                            'quote'   => $this->_getOnepage()->getQuote())
                        );
                $this->_getOnepage()->getQuote()->collectTotals();
                $this->_getOnepage()->getQuote()->collectTotals()->save();            
                $info = $this->successStatus();
                //$info['data']['payment_methods'] = $this->_getPaymentMethos();
                $info['data']['cart_details'] = $this->getCartInfo();
                return $info;
            }  else {
                if(isset($result['message']))
                    return $this->errorStatus($result['message']);
                else
                    return $this->errorStatus(array($result));
            }      
        } catch (Exception $e) {
            $info = $this->errorStatus($e->getMessage());
            return $info;
        }
    }

    public function _savePaymentMethod($data)
    {
        try {        
            $data = $data['payment'];
            if($data == "") return false;
            $result = $this->_getOnepage()->savePayment($data);
            return true;
        
        } catch (Exception $e) {
            if (is_array($e->getMessage())) {
                Mage::getSingleton('core/session')->setErrorPayment($e->getMessage());
                return false;
            } else {
                Mage::getSingleton('core/session')->setErrorPayment(array($e->getMessage()));
                return false;
            }
        }
    }

    public function savePaymentMethod($data)
    {
        $paymentStatus = $this->_savePaymentMethod($data);
        if(!$paymentStatus){
            $error = Mage::getSingleton('core/session')->getErrorPayment();    
            $info = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->errorStatus($error);
            return $info;
        }
        $info = $this->successStatus();
        $info['data']['agreements'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/config'))->_getAgreements();
        $info['data']['cart_details'] = $this->getCartInfo();
        return $info;
    }

    public function validateOrder($data)
    {
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            $this->_getCheckoutSession()->addError($this->__('The onepage checkout is disabled.'));
            return $this->errorStatus('The onepage checkout is disabled.');
        }

        $quote = $this->_getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            if(!$quote->hasItems()){
                $error = $this->errorStatus('Not_All_Products_Are_Available_In_The_Requested_Quantity');
                $error['data']['cart_details'] = $this->getCartInfo();
                return $error;
            }
            else if($quote->getHasError())
            {
                $error = $this->errorStatus('Not_All_Products_Are_Available_In_The_Requested_Quantity');
                $error['data']['cart_details'] = $this->getCartInfo();
                return $error;
            }
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            $this->_getCheckoutSession()->addError($error);
            $error['data']['cart_details'] = $this->getCartInfo();
            return $error;
        }
        return null;
    }

    public function saveOrder($data)
    {
        $information = null;
        $redirectUrl = null;
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();    
            if ($requiredAgreements) {
                $postedAgreements = $data['agreement'];
                if($postedAgreements){
                    $diff = array_diff($requiredAgreements, $postedAgreements);
                    
                    if ($diff) {
                        $information = $this->errorStatus('Please_Agree_To_All_The_Terms_And_Conditions_Before__Placing_The_Order');
                        return $information;
                    }
                } else {
                    $information = $this->errorStatus('Please_Agree_To_All_The_Terms_And_Conditions_Before__Placing_The_Order');
                    return $information;
                }
            }
            $payment = $data['payment'];
            if ($payment) {
                //$dataPayment = $check_card;
                if (version_compare(Mage::getVersion(), '1.8.0.0', '>=') === true) {
                    $payment['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                }
            }
            $this->_getOnepage()->getQuote()->getPayment()->importData($payment);
            $this->_getOnepage()->saveOrder();
            $redirectUrl = $this->_getOnepage()->getCheckout()->getRedirectUrl();
            // } catch (Mage_Payment_Model_Info_Exception $e) {
            //} catch (Mage_Core_Exception $e) {                       
        } catch (Exception $e) {
            $_error = $this->errorStatus($e->getMessage());
            $this->_getOnepage()->getCheckout()->setUpdateSection(null);
            return $_error;
        }
        $this->_getOnepage()->getQuote()->save();
        $_result = $this->successStatus();
        $_returndata = array(
            'invoice_number' => $this->_getCheckoutSession()->getLastRealOrderId(),
            'redirectUrl' => $redirectUrl
        );
        $_result['data'] = $_returndata;
        $_result['message'] = Mage::helper('core')->__('Your order has been received.   Thank you for your purchase!');

        $cart_session = $this->_getOnepage()->getCheckout();
        $lastOrderId = $cart_session->getLastOrderId();
        $this->_oldQuote = $cart_session->getData('old_quote');
        $cart_session->clear();
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));

        $_result['data']['order_data'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/user'))->_getOrderHistory();
        return $_result;
    }

    public function clearCartData($data = null)
    {
        $cart_session = $this->_getOnepage()->getCheckout();
        $cart_session->clear();

        $info = $this->successStatus();
        return $info;
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    public function _getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    public function changeData($data_change, $event_name, $event_value)
    {
        $this->_data = $data_change;
        // dispatchEvent to change data
        $this->eventChangeData($event_name, $event_value);
        return $this->getCacheData();
    }

    public function setEstimateShipping($data)
    {
        $country  = (string) isset($data['country_id'])?$data['country_id']:null;
        $postcode = (string) isset($data['estimate_postcode'])?$data['estimate_postcode']:null;
        $city     = (string) isset($data['estimate_city'])?$data['estimate_city']:null;
        $regionId = (string) isset($data['region_id'])?$data['region_id']:null;
        $region   = (string) isset($data['region'])?$data['region']:null;

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();

        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        return $info;
    }

    public function updateEstimateShipping($data)
    {
        $code = (string) isset($data['estimate_method'])?$data['estimate_method']:null;
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $info = $this->successStatus();
        $info['data']['cart_details'] = $this->getCartInfo();
        $info['data']['shipping_methods'] = $this->_getShippingMethods();
        return $info;
    }
}