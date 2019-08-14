<?php

class Mobicommerce_Mobiservices_Model_1x4x0_Appwidget extends Mobicommerce_Mobiservices_Model_Abstract {
	
	public function getProductSliderData($data)
	{
		$appcode = isset($data['appcode'])?$data['appcode']:NULL;
		$storeid = Mage::app()->getStore()->getStoreId();
		$productsliderCollection = Mage::getModel('mobiadmin/appwidget')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('slider_status', '1')
			->addFieldToFilter('storeid', $storeid)
			->setOrder('slider_position', 'ASC');
		$sliderArray = array();
        if($productsliderCollection->count() > 0){
			foreach($productsliderCollection as $productslider){
				$productslider = $productslider->getData();
				if($productslider['slider_code'] == 'new-arrivals-automated'){
					$new_products = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSort("entity_id","DESC")->addAttributeToSelect('*');
					Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($new_products);
					Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($new_products);
					Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($new_products);
					$new_products->getSelect()->limit(10);
					
					if(!empty($new_products)){
						$products = array();
						foreach($new_products as $_product){
							$pdata = array();
							$pdata['entity_id']             = $_product->getId();
							$pdata['entity_type_id']        = $_product->getEntityTypeId();
							$pdata['attribute_set_id']      = $_product->getAttributeSetId();
							$pdata['type_id']               = $_product->getTypeId();
							$pdata['sku']                   = $_product->getSku();
							$pdata['name']                  = $_product->getName();
							$pdata['price']                 = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getPrice());
							$pdata['final_price']           = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getFinalPrice());
							$pdata['special_price']         = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getSpecialPrice());
							$pdata['is_salable']            = $_product->getIsSalable();
							$pdata['status']                = $_product->getStatus();
							$pdata['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString();
							
							$prices = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_productPrices($_product);
							if($prices)
								$pdata = array_merge($pdata, $prices);

							$products[] = $pdata;
						}

						$productslider['slider_productIds'] = $products;
						$sliderArray[] = $productslider;
					}
				}
				else if($productslider['slider_code'] == 'best-sellers-automated'){
					$settings       = $productslider['slider_settings'];
					$settings_array = json_decode($settings);
					$no_of_days     = $settings_array->no_of_days;
					
					$storeId = Mage::app()->getStore()->getId();
					$today   = time();
					$last    = $today - (60*60*24*$no_of_days);

					$from = date("Y-m-d 00:00:00", $last);
					$to = date("Y-m-d 23:59:59", $today);
					
					$bestseller_products = Mage::getResourceModel('reports/product_collection')
						->addAttributeToSelect('*')		
						->addOrderedQty($from, $to)
						->setStoreId($storeId)
						->addStoreFilter($storeId)
						->setOrder('ordered_qty', 'desc'); 

					Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($bestseller_products);
					Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($bestseller_products);
					Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($bestseller_products);

					$bestseller_products->getSelect()->limit(10);
					
					if(!empty($bestseller_products)){
						$products = array();
						foreach($bestseller_products as $_product){
							$pdata['entity_id']             = $_product->getId();
							$pdata['entity_type_id']        = $_product->getEntityTypeId();
							$pdata['attribute_set_id']      = $_product->getAttributeSetId();
							$pdata['type_id']               = $_product->getTypeId();
							$pdata['sku']                   = $_product->getSku();
							$pdata['name']                  = $_product->getName();
							$pdata['price']                 = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getPrice());
							$pdata['final_price']           = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getFinalPrice());
							$pdata['special_price']         = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getSpecialPrice());
							$pdata['is_salable']            = $_product->getIsSalable();
							$pdata['status']                = $_product->getStatus();
							$pdata['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString();
							$prices = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_productPrices($_product);
							if($prices)
								$pdata = array_merge($pdata, $prices);

							$products[] = $pdata;
						}

						$productslider['slider_productIds'] = $products;
						$sliderArray[] = $productslider;
					}				
				}
				else if($productslider['slider_code'] == 'recently-viewed-automated'){
					$limit = 10;
					$recentlyViewedProducts = Mage::getSingleton('Mage_Reports_Block_Product_Viewed')->setPageSize($limit)->getItemsCollection();
			        $recentlyViewedProductsArray = array();
			        if($recentlyViewedProducts){
			        	foreach($recentlyViewedProducts as $_product){
			        		if($this->_checkProductAvailabilityParam($_product)){
			        			$p = array(
									'entity_id'             => $_product->getId(),
									'entity_type_id'        => $_product->getEntityTypeId(),
									'attribute_set_id'      => $_product->getAttributeSetId(),
									'type_id'               => $_product->getTypeId(),
									'sku'                   => $_product->getSku(),
									'name'                  => $_product->getName(),
									'price'                 => Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getPrice()),
									'final_price'           => Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getFinalPrice()),
									'special_price'         => Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getSpecialPrice()),
									'is_salable'            => $_product->getIsSalable(),
									'status'                => $_product->getStatus(),
									'product_thumbnail_url' => Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString(),
			        				);

								$prices = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_productPrices($_product);
								if ($prices) 
									$p = array_merge($p, $prices);

								$recentlyViewedProductsArray[] = $p;
							}
			        	}
			        	if(!empty($recentlyViewedProductsArray)){
							$productslider['slider_productIds'] = $recentlyViewedProductsArray;
							$sliderArray[] = $productslider;
			        	}
			        }
				}
				else{
					$productids = explode(",",$productslider['slider_productIds']);
					if(!empty($productids)){
						$products = array();
						foreach($productids as $_productid){
							if(!empty($_productid)){
								$_product = Mage::getModel('catalog/product')->load($_productid);
								if($this->_checkProductAvailabilityParam($_product)){
									$pdata['entity_id']             = $_product->getId();
									$pdata['entity_type_id']        = $_product->getEntityTypeId();
									$pdata['attribute_set_id']      = $_product->getAttributeSetId();
									$pdata['type_id']               = $_product->getTypeId();
									$pdata['sku']                   = $_product->getSku();
									$pdata['name']                  = $_product->getName();
									$pdata['price']                 = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getPrice());
									$pdata['final_price']           = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getFinalPrice());
									$pdata['special_price']         = Mage::helper('mobiservices/mobicommerce')->getProductPriceByCurrency($_product->getSpecialPrice());
									$pdata['is_salable']            = $_product->getIsSalable();
									$pdata['status']                = $_product->getStatus();
									$pdata['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString();
									
									$prices = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->_productPrices($_product);
									
									if($prices)
										$pdata = array_merge($pdata, $prices);

									$products[] = $pdata;
								}
							}
						}
						
						$productslider['slider_productIds'] = $products;
						$sliderArray[] = $productslider;
					}
				}
			}
			return $sliderArray;
		}
	}

	protected function _checkProductAvailabilityParam($_product){
		if($_product->getId() && $_product->getIsSalable() && $_product->isVisibleInSiteVisibility() && in_array(Mage::app()->getStore()->getId(), $_product->getStoreIds()) && $_product->getStatus() == '1')
			return true;
		else
			return false;
	}
}