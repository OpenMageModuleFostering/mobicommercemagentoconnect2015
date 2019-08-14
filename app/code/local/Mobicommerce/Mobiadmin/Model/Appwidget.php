<?php
class Mobicommerce_Mobiadmin_Model_Appwidget extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('mobiadmin/appwidget');
    }

	public function getProductSliderData($data)
	{
		$appcode = isset($data['appcode'])?$data['appcode']:NULL;
		$productsliderCollection =Mage::getModel('mobiadmin/appwidget')->getCollection()
			->addFieldToFilter('app_code', $data['appcode'])
			->addFieldToFilter('slider_status', '1')
			->setOrder('slider_position', 'ASC');
		$sliderArray = array();
		$key = 0;
        if($productsliderCollection->count()>0) {
			foreach($productsliderCollection as $productslider){
				$productslider = $productslider->getData();
				if($productslider['slider_code'] == 'new-arrivals-automated'){
					$new_products = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSort("entity_id","DESC")->addAttributeToSelect('*');
					$new_products->getSelect()->limit(10);	
					Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($new_products);
					Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($new_products);
					Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($new_products);
					
					if(!empty($new_products)){
						$j = 0;
						$products = array();
						foreach($new_products as $_new_products){
							if($j == 10) break;
							if($this->_checkProductAvailabilityParam($_new_products)){
								$products[$j]['entity_id']             = $_new_products->getId();
								$products[$j]['entity_type_id']        = $_new_products->getEntityTypeId();
								$products[$j]['attribute_set_id']      = $_new_products->getAttributeSetId();
								$products[$j]['type_id']               = $_new_products->getTypeId();
								$products[$j]['sku']                   = $_new_products->getSku();
								$products[$j]['name']                  = $_new_products->getName();
								$products[$j]['price']                 = $_new_products->getPrice();
								$products[$j]['final_price']           = $_new_products->getFinalPrice();
								$products[$j]['special_price']         = $_new_products->getSpecialPrice();
								$products[$j]['is_salable']            = $_new_products->getIsSalable();
								$products[$j]['status']                = $_new_products->getStatus();
								$products[$j]['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_new_products, 'thumbnail')->resize(200)->__toString();
								
								$prices = $this->_productPrices($_new_products);
								if ($prices) 
									$products[$j] = array_merge($products[$j], $prices);
								$j++;
							}
						}
						$sliderArray[$key] = $productslider;
						$sliderArray[$key]['slider_productIds'] = $products;
						
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
					
					if(!empty($bestseller_products)){
						$k = 0;
						$products = array();
						foreach($bestseller_products as $_bestseller_products){
							if($k == 10) break;
							if($this->_checkProductAvailabilityParam($_bestseller_products)){
								$products[$k]['entity_id']             = $_bestseller_products->getId();
								$products[$k]['entity_type_id']        = $_bestseller_products->getEntityTypeId();
								$products[$k]['attribute_set_id']      = $_bestseller_products->getAttributeSetId();
								$products[$k]['type_id']               = $_bestseller_products->getTypeId();
								$products[$k]['sku']                   = $_bestseller_products->getSku();
								$products[$k]['name']                  = $_bestseller_products->getName();
								$products[$k]['price']                 = $_bestseller_products->getPrice();
								$products[$k]['final_price']           = $_bestseller_products->getFinalPrice();
								$products[$k]['special_price']         = $_bestseller_products->getSpecialPrice();
								$products[$k]['is_salable']            = $_bestseller_products->getIsSalable();
								$products[$k]['status']                = $_bestseller_products->getStatus();
								$products[$k]['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_bestseller_products, 'thumbnail')->resize(200)->__toString();
								$prices = $this->_productPrices($_bestseller_products);
								if ($prices) 
									$products[$k] = array_merge($products[$k], $prices);
								
								$k++;
							}
						}
						$sliderArray[$key] = $productslider;
						$sliderArray[$key]['slider_productIds'] = $products;
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
									'price'                 => $_product->getPrice(),
									'final_price'           => $_product->getFinalPrice(),
									'special_price'         => $_product->getSpecialPrice(),
									'is_salable'            => $_product->getIsSalable(),
									'status'                => $_product->getStatus(),
									'product_thumbnail_url' => Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString(),
			        				);

								$prices = $this->_productPrices($_product);
								if ($prices) 
									$p = array_merge($p, $prices);

								$recentlyViewedProductsArray[] = $p;
							}
			        	}
			        	if(!empty($recentlyViewedProductsArray)){
			        		$sliderArray[$key] = $productslider;
							$sliderArray[$key]['slider_productIds'] = $recentlyViewedProductsArray;
			        	}
			        }
				}
				else{
					$productids = explode(",",$productslider['slider_productIds']);
					if(!empty($productids)){
						$products = array();
						$l = 0;
						for($m=0; $m<10;$m++){
							$_products = '';
							$_products = Mage::getModel('catalog/product')->load($productids[$m]);
							if($this->_checkProductAvailabilityParam($_products)){
								$products[$l]['entity_id']             = $_products->getId();
								$products[$l]['entity_type_id']        = $_products->getEntityTypeId();
								$products[$l]['attribute_set_id']      = $_products->getAttributeSetId();
								$products[$l]['type_id']               = $_products->getTypeId();
								$products[$l]['sku']                   = $_products->getSku();
								$products[$l]['name']                  = $_products->getName();
								$products[$l]['price']                 = $_products->getPrice();
								$products[$l]['final_price']           = $_products->getFinalPrice();
								$products[$l]['special_price']         = $_products->getSpecialPrice();
								$products[$l]['is_salable']            = $_products->getIsSalable();
								$products[$l]['status']                = $_products->getStatus();
								$products[$l]['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_products, 'thumbnail')->resize(200)->__toString();
								
								$prices = $this->_productPrices($_products);
								
								if ($prices) 
									$products[$l] = array_merge($products[$l], $prices);
								$l++;
							}
						}
						
						$sliderArray[$key] = $productslider;
						$sliderArray[$key]['slider_productIds'] = $products;
					}
				}
				$key++;
			}
			return $sliderArray;
		}
	}

	protected function _productPrices($product){
		$prices = array();
		// ----- Get Price for bundle and ground products 
		$type = $product->getTypeId();
		switch ($type) {          
			case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
				$productPrice = $product->getPriceModel();
				if (version_compare(Mage::getVersion(), '1.4.2.0', '>=') === true) {
					list($_minimalPriceTax, $_maximalPriceTax) = $productPrice->getTotalPrices($product, null, null, false);      
				}else{
					list($_minimalPriceTax, $_maximalPriceTax) = $productPrice->getPrices($product, null, null, false);      
				}
			   
				if ($product->getPriceType() == 1) {
					$_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
					$_weeeTaxAmountInclTaxes = $_weeeTaxAmount;
					if (Mage::helper('weee')->isTaxable()) {
						$_attributes = Mage::helper('weee')->getProductWeeeAttributesForRenderer($product, null, null, null, true);
						$_weeeTaxAmountInclTaxes = Mage::helper('weee')->getAmountInclTaxes($_attributes);
					}
					if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($product, array(0, 1, 4))) {
						$_minimalPriceTax += $_weeeTaxAmount;
						$_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
					}
					if ($_weeeTaxAmount && Mage::helper('weee')->typeOfDisplay($product, 2)) {
						$_minimalPriceInclTax += $_weeeTaxAmountInclTaxes;
					}
					
				}     
				$prices = array(
					'min_price' => $_minimalPriceTax,
					'max_price' => $_maximalPriceTax,
					);
				break;            
			case Mage_Catalog_Model_Product_Type::TYPE_GROUPED :
				$_taxHelper = Mage::helper('tax');
				$_minimalPriceValue = $product->getMinimalPrice();
				//$_exclTax = $_taxHelper->getPrice($product, $_minimalPriceValue);
				//$_inclTax = $_taxHelper->getPrice($product, $_minimalPriceValue, true);
				
				/* custom code added for getting minimum and maximum price for grouped product */
				$groupedProduct = $product;
				$aProductIds = $groupedProduct->getTypeInstance()->getChildrenIds($groupedProduct->getId());

				$group_prices = array();
				foreach ($aProductIds as $ids) {
					foreach ($ids as $id) {
						$aProduct = Mage::getModel('catalog/product')->load($id);
						$group_prices[] = $aProduct->getPriceModel()->getPrice($aProduct);
					}
				}
				if(!empty($group_prices))
				{
					$prices = array(
						'min_price' => min($group_prices),
						'max_price' => max($group_prices)
						);
				}

				break;
		}
		return $prices;
	}

	protected function _checkProductAvailabilityParam($_product){
		if($_product->getId() && $_product->getIsSalable() && $_product->isVisibleInSiteVisibility() && in_array(Mage::app()->getStore()->getId(), $_product->getStoreIds()) && $_product->getStatus() == '1')
			return true;
		else
			return false;
	}
}