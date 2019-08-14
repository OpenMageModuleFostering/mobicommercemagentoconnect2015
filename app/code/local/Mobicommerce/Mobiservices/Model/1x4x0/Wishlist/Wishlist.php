<?php

class Mobicommerce_Mobiservices_Model_1x4x0_Wishlist_Wishlist extends Mobicommerce_Mobiservices_Model_Abstract 
{
	protected function _getWishlist($wishlistId = null)
	{	
		$wishlist = Mage::registry('wishlist');
		if ($wishlist) {
		    return $wishlist;
		}

		try {
		    $customerId = Mage::getSingleton('customer/session')->getCustomerId();
		    /* @var Mage_Wishlist_Model_Wishlist $wishlist */
		    $wishlist = Mage::getModel('wishlist/wishlist');
		    if ($wishlistId) {
			$wishlist->load($wishlistId);
		    } else {
			$wishlist->loadByCustomer($customerId, true);
		    }

		    if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
				$wishlist = null;
				Mage::throwException(
				    Mage::helper('wishlist')->__("Requested wishlist doesn't exist")
				);
		    }

		    Mage::register('wishlist', $wishlist);
		} catch (Mage_Core_Exception $e) {
		    Mage::getSingleton('wishlist/session')->addError($e->getMessage());
		    return false;
		} catch (Exception $e) {
		    Mage::getSingleton('wishlist/session')->addException($e,
			Mage::helper('wishlist')->__('Wishlist could not be created.')
		    );
		    return false;
		}

		return $wishlist;
	}		
	
	public function addWishlistItem($wishlistData)
	{
		$session = Mage::getSingleton('customer/session');
		
		if(!$session->isLoggedIn())
		{
			$information = $this->errorStatus("Please_Login_To_Continue");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;			
		}
		$params = $wishlistData;
		$wishlist = $this->_getWishlist();
		if (!$wishlist) {
		    return $this->norouteAction();
		}
		
		$productId = (int)$params['product'];
		if (!$productId) {			
			$information = $this->errorStatus("Product_Does_Not_Exists");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;	    
		}
		
		$product = Mage::getModel('catalog/product')->load($productId);
		if (!$product->getId() || !$product->isVisibleInCatalog()) {			
			$information = $this->errorStatus("Cannot_Specify_Product");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;	 
		}

	    $requestParams = $params;
	    $buyRequest = new Varien_Object($requestParams);
	    
	    $result = $wishlist->addNewItem($product, $buyRequest);
	   
	    if (is_string($result)) {
		Mage::throwException($result);
	    }
	    $wishlist->save();

	    Mage::dispatchEvent(
			'wishlist_add_product',
			array(
				'wishlist' => $wishlist,
				'product'  => $product,
				'item'     => $result
			)
	    );

	    $session->setAddActionReferer($referer);
	    
	    Mage::helper('wishlist')->calculate();
		$information = $this->successStatus();		    
	    $information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
		return $information;
	}
	
	public function getWishlistInfo() 
	{
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		$wishlist = Mage::getModel('wishlist/wishlist');
		
		$list = array(
			'wishlist'       => NULL,
			'wishlist_items' => NULL,						
		);
		if($customerId) {
			$wishlist->loadByCustomer($customerId, true);
		}		
		else{
			return $list;			
		}
		    
	    $items = $wishlist->getItemCollection();
		$wishlistItems = array();
	    $i = 0; 
	    if(count($items) > 0)
	    {
		    foreach($items as $item)
		    {
		    	$_product = $item->getProduct();

				$wishlistItems[$i] = $item->getData();
				$wishlistItems[$i]['product_type'] = $_product->getTypeID();
				$wishlistItems[$i]['price'] = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getFinalPrice());
				$wishlistItems[$i]['product_Data'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->productInfo($item);
				$wishlistItems[$i]['wihlist_options'] = $this->_getWishlistItemOptions($item);
				$wishlistItems[$i]['options'] = $this->_getWishlistOptionsWithValues($_product, $wishlistItems[$i]['wihlist_options'], $wishlistItems[$i]['product_Data']['data']['product_details']['options']);
				$i++;
		    }
		    $list = array(
				'wishlist'       => $wishlist->getData(),
				'wishlist_items' => $wishlistItems,				
			    );
		}
		return $list;
	}

	/*
	 * added by yash
	 * to get wishlist item options
	 */
	protected function _getWishlistItemOptions($item)
	{
		$woptions = $item->getOptions();
		$options = array();
		$options_ids = null;
		$info_buyRequest = null;
		if($woptions){
			foreach($woptions as $r){
				$att = $r->getData();
				if($att['code'] == 'attributes'){
					$attributes = $att['value'];
					$attributes = unserialize($attributes);
				    foreach ($attributes as $attr_id => $attr_value)
				    {
				        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attr_id);
				        $attr_options = $attribute->getSource()->getAllOptions(false);   
				        foreach ($attr_options as $option)
				        {
				            if ($option['value'] == $attr_value)
				            {
				                $options[$attribute->getFrontendLabel()] = $option['label'];
				            }
				        }
					}
					$wishlistItems[$i]['t'][] = $options;
				}
				else if($att['code'] == 'info_buyRequest'){
					$info_buyRequest = $att['value'];
					$info_buyRequest = unserialize($info_buyRequest);
					if($info_buyRequest){
						$options_ids = array(
							"info_buyRequest"  => $info_buyRequest
							);
					}
				}
			}
		}

		return array(
			'options_text'    => $options,
			'info_buyRequest' => $info_buyRequest
			);
	}
	/* added by yash - upto here */
	
	public function removeWishlistItem($wishlistData)
	{
		$session = Mage::getSingleton('customer/session');
		if(!$session->isLoggedIn()){
			$information = $this->errorStatus("Please_Login_To_Continue");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;				
		}
		
		$wishlist_item_id = (int) $wishlistData['wishlist_item_id'];
		
		if(!$wishlist_item_id){
			$information = $this->errorStatus("Please_Pass_Item_Id");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;				
		}
		
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		$wishlist = Mage::getModel('wishlist/wishlist')->getCollection()->addFieldToFilter('customer_id',$customerId)->getFirstItem()->load();
		$wishlistId = $wishlist->getWishlistId();
		
		if(!$wishlistId){
			$information = $this->errorStatus("Wishlist_Does_Not_Exists");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;				
		}
		
		$item = Mage::getModel('wishlist/item')->getCollection()->addFieldToFilter('wishlist_id',$wishlistId);
		if(count($item) > 0){
			foreach($item as $_item){
				if($_item->wishlist_item_id == $wishlist_item_id){
					$_item->delete();
				}
			}
			$wishlist->save();
		}
		
		$information = $this->getWishlistInfo();
		Mage::helper('wishlist')->calculate();
		
		$information = $this->successStatus();		    		
		$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
		return $information;
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
		}else{
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
	
	public function addtocartWishlistItem($data)
	{
		$session = Mage::getSingleton('customer/session');
		
		if(!$session->isLoggedIn()){
			$information = $this->errorStatus("Please_Login_To_Continue");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;			
		}
		
		$itemId = $data['item_id'];

		/* @var $item Mage_Wishlist_Model_Item */
		$item = Mage::getModel('wishlist/item')->load($itemId);
		if (!$item->getId()) {
		    $information = $this->errorStatus("Please_Pass_Item_Id");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
		}
		$wishlist = $this->_getWishlist($item->getWishlistId());
		if (!$wishlist) {
		    $information = $this->errorStatus("Item_Does_Not_Exists");
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
		}

		// Set qty
		$qty = (int)$data['qty'];
		if(empty($qty)){
			$qty = 1;
		}
		
		/* @var $session Mage_Wishlist_Model_Session */
		$session = Mage::getSingleton('wishlist/session');
		$cart    = Mage::getSingleton('checkout/cart');

		try{
		    $options = Mage::getModel('wishlist/item_option')->getCollection()
			    ->addItemFilter(array($itemId));
		    $item->setOptions($options->getOptionsByItem($itemId));

		    $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
			$data,
			array('current_config' => $item->getBuyRequest())
		    );

		    $item->mergeBuyRequest($buyRequest);
		    if ($item->addToCart($cart, true)) {
				$cart->save()->getQuote()->collectTotals();
		    }

		    $wishlist->save();
		    Mage::helper('wishlist')->calculate();

		} catch (Mage_Core_Exception $e) {
		    if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
				$information = $this->errorStatus(Mage::helper('wishlist')->__('This product(s) is currently out of stock'));
				$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
				return $information;
		    } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
				$information = $this->errorStatus($e->getMessage());
				$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
				return $information;
		    } else {
				$information = $this->errorStatus($e->getMessage());
				$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
				return $information;
		    }
		} catch (Exception $e) {
		    $information = $this->errorStatus(Mage::helper('wishlist')->__('Cannot add item to shopping cart'));
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
		}

		Mage::helper('wishlist')->calculate();
		
		$information = $this->successStatus();		    		
		$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
		return $information;
	}

	public function updateWishlistItem($data)
	{
		$session = Mage::getSingleton('customer/session');
		if(!$session->isLoggedIn())
		{
			$information = $this->errorStatus("Please_Login_To_Continue");
			$information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
		}

        $productId = (int) $data['product'];
        if (!$productId) {
            $information = $this->errorStatus("Please_Pass_Product_Id");
			$information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $information = $this->errorStatus(Mage::helper('wishlist')->__('Cannot specify product.'));
			$information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
        }

        try {
            $id = (int) $data['id'];
            /* @var Mage_Wishlist_Model_Item */
            $item = Mage::getModel('wishlist/item');
            $item->load($id);
            $wishlist = $this->_getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $information = $this->errorStatus("Cannot_Load_Wishlist_Item");
				$information['data']['cart_details'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
				return $information;
            }

            $buyRequest = new Varien_Object($data);
            $wishlist->updateItem($id, $buyRequest)
                ->save();

            Mage::helper('wishlist')->calculate();
            Mage::dispatchEvent('wishlist_update_item', array(
                'wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id))
            );

            Mage::helper('wishlist')->calculate();

            $message = Mage::helper('wishlist')->__('%1$s has been updated in your wishlist.', $product->getName());
            $information = $this->successStatus($message);
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo();
			return $information;
        } catch (Mage_Core_Exception $e) {
            $information = $this->errorStatus($e->getMessage());
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
        } catch (Exception $e) {
            $session->addError(Mage::helper('wishlist')->__('An error occurred while updating wishlist.'));
            Mage::logException($e);
            $information = $this->errorStatus(Mage::helper('wishlist')->__('An error occurred while updating wishlist.'));
			$information['data']['cart_details']= Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/shoppingcart_cart'))->getCartInfo(); 
			return $information;
        }
	}

	protected function _getWishlistOptionsWithValues($_product = null, $wihlistOptions = null, $productOptions = null)
	{
		$options = array();
		switch($_product->getTypeID()){
			case 'bundle':
				$bundleOptions = $wihlistOptions['info_buyRequest']['bundle_option'];
				$bundleOptionsQty = $wihlistOptions['info_buyRequest']['bundle_option_qty'];
				$bundleProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['bundle']);
				if(!empty($bundleOptions)){
					foreach ($bundleOptions as $key => $value) {
						$_option = array();
						if(is_array($value)){
							foreach($value as $mkey => $mvalue) {
								$_option[] = array(
									"option_title" => $bundleProductOptions[$key]['option_title'],
									"option_value" => (isset($bundleOptionsQty[$key]) ? $bundleOptionsQty[$key] : '1') . " x " . $bundleProductOptions[$key]['options'][$mvalue]['option_value']
									);
							}
						}
						else{
							//echo '<pre>';print_r($bundleOptionsQty);exit;
							$_option[] = array(
								"option_title" => $bundleProductOptions[$key]['option_title'],
								"option_value" => (isset($bundleOptionsQty[$key]) ? $bundleOptionsQty[$key] : '1') . " x " . $bundleProductOptions[$key]['options'][$value]['option_value']
								);
						}
						$options[] = $_option;
					}
				}
				break;
			case 'grouped':
				$groupedOptions = $wihlistOptions['info_buyRequest']['super_group'];
				$groupedProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['super_group']);
				if(!empty($groupedOptions)){
					foreach ($groupedOptions as $key => $value) {
						$_option = array();
						if(!is_array($value)){
							$_option = array(
								"option_title" => $groupedProductOptions[$key]['option_title'],
								"option_value" => $value
								);
						}
						$options[] = $_option;
					}
				}
				break;
			case 'configurable':
				$configurableOptions = $wihlistOptions['info_buyRequest']['super_attribute'];
				$configurableProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['product_super_attributes']);
				//print_r($productOptions['product_super_attributes']);exit;
				if(!empty($configurableOptions)){
					foreach ($configurableOptions as $key => $value) {
						$_option = array();
						if(!is_array($value)){
							$_option = array(
								"option_title" => $configurableProductOptions[$key]['label'],
								"option_value" => $configurableProductOptions[$key]['prices'][$value]['store_label']
								);
						}
						$options[] = $_option;
					}
				}
				break;
			case 'downloadable':
				//print_r($wihlistOptions['info_buyRequest']);exit;
				$downloadableOptions = $wihlistOptions['info_buyRequest']['links'];
				$downloadableProductOptions = $this->_setProductOptionArray($_product->getTypeID(), $productOptions['link']);
				//print_r($downloadableProductOptions);exit;
				if(!empty($downloadableOptions)){
					$_option = array();
					foreach ($downloadableOptions as $key => $value) {
						if(!is_array($value)){
							$_option[] = $downloadableProductOptions[$value]['title'];
						}
					}
					$options[] = array(
						"option_title" => "Links",
						"option_value" => implode(", ", $_option),
						);
				}
				break;
			default:
				break;
		}

		$simpleOptions = $wihlistOptions['info_buyRequest']['options'];
		$simpleProductOptions = $this->_setProductOptionArray('simple', $productOptions['product_options']);
		if(!empty($simpleOptions)){
			foreach ($simpleOptions as $key => $value) {
				$_option = array();
				if(in_array($simpleProductOptions[$key]['type'], array('field', 'area', 'date', 'date_time', 'time'))){
					$_option = array(
						"option_title" => $simpleProductOptions[$key]['title'],
						"option_value" => $value
						);
				}
				elseif(in_array($simpleProductOptions[$key]['type'], array('drop_down', 'radio'))){
					$_option = array(
						"option_title" => $simpleProductOptions[$key]['title'],
						"option_value" => $simpleProductOptions[$key]['options'][$value]['title']
						);
				}
				elseif(in_array($simpleProductOptions[$key]['type'], array('checkbox', 'multiple'))){
					if(is_array($value)){
						foreach($value as $mkey => $mvalue) {
							$_option[] = $simpleProductOptions[$key]['options'][$mvalue]['title'];
						}
						$_option = array(
							"option_title" => $simpleProductOptions[$key]['title'],
							"option_value" => implode(", ", $_option)
							);
					}
					else{
						$_option = array(
							"option_title" => $simpleProductOptions[$key]['title'],
							"option_value" => $simpleProductOptions[$key]['options'][$value]['title']
							);
					}
				}
				$options[] = $_option;
			}
		}
		return $options;
	}

	protected function _setProductOptionArray($productType, $options = array())
	{
		if(empty($options))
			return false;

		$outputOptions = array();
		if($productType == 'configurable'){
			foreach($options as $key => $value){
				$innerOptions = array();
				if(!empty($value['prices'])){
					foreach($value['prices'] as $ikey => $ivalue){
						$innerOptions[$ivalue['value_index']] = $ivalue;
					}
				}
				$outputOptions[$value['attribute_id']] = $value;
				$outputOptions[$value['attribute_id']]['prices'] = $innerOptions;
				unset($outputOptions[$value['attribute_id']]['product_attribute']);
			}
		}
		else if($productType == 'downloadable'){
			foreach($options as $key => $value){
				$innerOptions = array();
				if(!empty($value['options'])){
					foreach($value['options'] as $ikey => $ivalue){
						$innerOptions[$ivalue['link_id']] = $ivalue;
					}
				}
				$outputOptions[$value['link_id']] = $value;
				$outputOptions[$value['link_id']]['options'] = $innerOptions;
			}
		}
		else{
			foreach($options as $key => $value){
				$innerOptions = array();
				if(!empty($value['options'])){
					foreach($value['options'] as $ikey => $ivalue){
						if(isset($ivalue['option_type_id']))
							$innerOptions[$ivalue['option_type_id']] = $ivalue;
						else
							$innerOptions[$ivalue['option_id']] = $ivalue;
					}
				}
				$outputOptions[$value['option_id']] = $value;
				$outputOptions[$value['option_id']]['options'] = $innerOptions;
			}
		}
		//print_r($outputOptions);exit;
		return $outputOptions;
	}
}
?>
