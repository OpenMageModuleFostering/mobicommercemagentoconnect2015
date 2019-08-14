<?php

class Mobicommerce_Mobiservices_Model_1x0x3_Catalog_Catalog extends Mobicommerce_Mobiservices_Model_Abstract {

	public function _categoryTreeList($storeId = null, $appcode = null)
	{
		if(!empty($storeId))
			$parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
		else
	    	$parentId = Mage::app()->getStore()->getRootCategoryId();
	    $tree = Mage::getResourceSingleton('catalog/category_tree')->load();
	    $root = $tree->getNodeById($parentId);
	    if($root && $root->getId() == 1) { 
	    	$root->setName(Mage::helper('catalog')->__('Root')); 
	    }
	    $collection = Mage::getModel('catalog/category')->getCollection() 
			->addAttributeToSelect('name') 
			->addAttributeToFilter('is_active','1')
			//->addAttributeToFilter('display_mode',array('nlike'=>'PAGE'))
            ->addAttributeToFilter('include_in_menu','1');

	    $tree->addCollectionData($collection, true); 

        $categories = $this->_nodeToArray($root);
        $categories = $this->_make_tree_to_list($categories['children']);
        $categories = $this->_remove_category_children($categories);
        $categories = $this->_attachCategoryIcon($categories, $appcode);
        return $categories;
    }

	public function getCatalogSearch($data) 
	{
		$keyword = $data['q'];
		$_helper = Mage::helper('catalogsearch');
		$queryParam = str_replace('%20', ' ', $keyword);
		Mage::app()->getRequest()->setParam($_helper->getQueryParamName(), $queryParam);
		/** @var $query Mage_CatalogSearch_Model_Query */
		$query = $_helper->getQuery();
		$query->setStoreId(Mage::app()->getStore()->getId());

		if ($query->getQueryText() != '') {
		    $check = false;
		    if (Mage::helper('catalogsearch')->isMinQueryLength()) {
			$query->setId(0)
				->setIsActive(1)
				->setIsProcessed(1);
		    } else {
				if ($query->getId()) {
				    $query->setPopularity($query->getPopularity() + 1);
				} else {
				    $query->setPopularity(1);
				}

				if ($query->getRedirect()) {
				    $query->save();
				    //break
				    $check = true;
				} else {
				    $query->prepare();
				}
		    }
		    if ($check == FALSE) {
			Mage::helper('catalogsearch')->checkNotes();
			if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
			    $query->save();
			}
		    }		    
		} else {
		    return $this->statusError();
		}
		if (method_exists($_helper, 'getEngine')) {
		    $engine = Mage::helper('catalogsearch')->getEngine();
		    if ($engine instanceof Varien_Object) {
			$isLayeredNavigationAllowed = $engine->isLeyeredNavigationAllowed();
		    } else {
			$isLayeredNavigationAllowed = true;
		    }
		} else {
		    $isLayeredNavigationAllowed = true;
		}
		$layer = Mage::getSingleton('catalogsearch/layer');		
		$collection = $layer->getProductCollection();
		return $collection;
	}
    
	function _nodeToArray(Varien_Data_Tree_Node $node) 
	{ 
		$result = array(); 		
		$result['category_id'] = $node->getId(); 
		$result['parent_id']   = $node->getParentId(); 
		$result['name']        = $node->getName(); 
		$result['is_active']   = $node->getIsActive(); 
		$result['position']    = $node->getPosition(); 
		$result['level']       = $node->getLevel(); 		
		$result['children']    = array();
		$category = Mage::getModel('catalog/category')->load($node->getId());
		//$result['imageurl']       = $this->getResizedImage($category->getImageUrl(),300,300);
		$result['imageurl']       = $this->getResizedImage(Mage::getBaseUrl('media').'catalog/category/'.$category->getThumbnail(),300,300);
		$result['products_count'] = $this->_getProductCountForCategory($category);    

		foreach ($node->getChildren() as $child) {
			$result['children'][] = $this->_nodeToArray($child); 
		}

		return $result; 
	} 

    public function _remove_category_children($categories = array())
    {
        if(!empty($categories))
        {
            foreach($categories as $key => $category)
            {
                $categories[$key]['children'] = count($category['children']);
            }
        }
        return $categories;
    }

    public function _make_tree_to_list($categories = null, $category_result = array())
    {
        if(!empty($categories))
        {
            foreach($categories as $category)
            {
                $category_result[] = $category;
                if(count($category['children']) > 0)
                {
                    $category_result = $this->_make_tree_to_list($category['children'], $category_result);
                }
            }
        }
        return $category_result;
    }

    public function _getProductCountForCategory($category)
    {
        $product_total=0;
        $storeId = $this->_getStoreId();
        $pCollection = $category->getProductCollection()
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->addFinalPrice();

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($pCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($pCollection);
        $pCollection->addUrlRewrite(0);

        $product_total = $pCollection->getSize();
        return $product_total;
    }

	public function getCatrgories()
	{
	    $categoriesTree = $this->successStatus();
	    $categoriesTree['data']['categories'] = $this->_categoryTreeList();
	    return $categoriesTree;
	}

	public function productList($data)
	{
		$storeId = $this->_getStoreId();
		
		$pCollection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')					
			->addAttributeToFilter('status', '1')
			->addAttributeToFilter('visibility', '4')
			->setStoreId($storeId)
			->addFinalPrice();

		if($data['category_id'] != '')
		{
			$pCollection->joinField('category_id',
				'catalog/category_product_index',
				'category_id',
				'product_id=entity_id',
				"{{table}}.store_id='".$storeId."' AND {{table}}.visibility IN (4) AND {{table}}.category_id = '".$data['category_id']."'",
				'INNER');
				//->addAttributeToFilter('category_id', array('in' => $data['category_id']));
		}
		
		if($data['q'] != '')
		{
			$pCollection = $this->getCatalogSearch($data);
		}
		//echo '<pre>';print_r($data);exit;
		if(!empty($data['filter']))
		{
			$filterData = array();
			$filter = (string)$data['filter'];
			if(!empty($filter)):
				$filter = explode('&', $filter);
				if(!empty($filter)):
					foreach($filter as $fdata):
						$explode = explode('=', $fdata);
						if(count($explode) == 2):
							if(array_key_exists($explode[0], $filterData))
								$filterData[$explode[0]][] = $explode[1];
							else
								$filterData[$explode[0]] = array($explode[1]);
						endif;
					endforeach;
				endif;
			endif;

			if(!empty($filterData)):
				foreach($filterData as $fkey => $fdata):
					if(count($fdata) == 1):
						$filterData[$fkey] = $fdata[0];
					endif;
				endforeach;
			endif;
			$data['filter'] = $filterData;
			//print_r($filterData);exit;
			if(!empty($data['filter'])):
				//$pCollection->addAttributeToSelect('*');
				foreach($data['filter'] as $key => $value)
				{ 
					if(is_array($value))
					{ 
						//print_r($key);exit;
						if($key == "price")
						{
							foreach($value as $option)
							{
								$option = explode("-",$option);
								$option = explode("-",$value);
								if($option[0] == '') $option[0] = 0;
								if($option[1] == '') $option[1] = 100000000;
								$pCollection->addAttributeToFilter($key,array('from'=>$option[0],'to'=>$option[1]));
							}
						}
						else
						{
							$optionArray = array();
							foreach($value as $option)
							{
								//$pCollection->addAttributeToFilter($key,$option);
								$optionArray[] = array('attribute'=> $key, 'finset'=> $option);
								//echo $key . ":" . $option;exit;
							}
							$pCollection->addAttributeToFilter($optionArray);
						}
					}
					else
					{
						if($key == "price")
						{
							//foreach($value as $option)
							{
								//$option = explode("-",$option);
								$option = explode("-",$value);
								if($option[0] == '') $option[0] = 0;
								if($option[1] == '') $option[1] = 100000000;
								$pCollection->addAttributeToFilter($key,array('from'=>$option[0],'to'=>$option[1]));
							}
						}
						else{
							//print_r(array($key, $value));exit;
							$value = (int)$value;
							//$pCollection->addAttributeToFilter($key,array("finset"=>$value));
							$pCollection->joinField($key.'_idx',
								'catalog_product_index_eav',
								null,
								'entity_id=entity_id',
								"{{table}}.store_id='".$storeId."' AND {{table}}.value = '".$value."'",
								'INNER');
							//$pCollection->addAttributeToFilter('color', 27);
							/*
							$pCollection->addAttributeToFilter(
								array(
						            array('attribute' => 'color', 'null' => true),
						            array('attribute' => 'color', 'in' => array(27)),
						        ),
						        '',
        						'INNER'
						        );
						    */
							//$pCollection->addFieldToFilter($key,array('eq' => $option));
						}
					}
				}
			endif;
		}
		//echo $pCollection->getSelect()->__toString();exit;
		switch ($data['order']) 
		{
		  	case "price-l-h": 
			    $pCollection->setOrder('price', 'asc');
			    break;
		  	case "price-h-l":
			    $pCollection->setOrder('price', 'desc');
			    break;
		  	case "rating-h-l":
			    $pCollection->joinField('rating_score', 
					   'review_entity_summary', 
					   'rating_summary', 
					   'entity_pk_value=entity_id', 
					   array('entity_type'=>1, 'store_id'=> Mage::app()->getStore()->getId()),
					   'left'
			    );
			    $pCollection->setOrder('rating_score', 'desc');
			    break;
		  	case "name-a-z":
			    $pCollection->setOrder('name', 'asc');
			    break;
		  	case "name-z-a":
			    $pCollection->setOrder('name', 'desc');
			    break;
		  	case "newest_first":
			    $pCollection->setOrder('entity_id', 'desc');
			    break;
		  	default: 
			    $pCollection->setOrder('ordered_qty', 'asc');   //most_popular
		}
		
		if(isset($data['category_id']) && !empty($data['category_id'])):
			$category = Mage::getModel("catalog/category")->load($data['category_id']);
			$layer = Mage::getModel("catalog/layer");
			$layer->setCurrentCategory($category);
			$attributes = $layer->getFilterableAttributes();
			$filter = array(
				"message" => "",
				"data"    => array()
				);

			try{
				if(count($attributes)>0)
				{
					foreach ($attributes as $attribute) 
					{	
						if ($attribute->getAttributeCode() == 'price') {
						    $filterBlockName = 'catalog/layer_filter_price';
						} elseif ($attribute->getBackendType() == 'decimal') {
						    $filterBlockName = 'catalog/layer_filter_decimal';
						} else {
						    $filterBlockName = 'catalog/layer_filter_attribute';
						}
						
						$result = Mage::getBlockSingleton($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
						if($result->getItems())
						{
							$attributeCode = (string)$attribute->getAttributeCode();
							$fd = array();
							$fd['attributeCode']  = $attributeCode;
							$fd['type']  = $attribute->getFrontendInput();
							$fd['label'] = $attribute->getFrontendLabel();
							$fd['code']  = $attribute->getId();
							$j = 0;
							foreach($result->getItems() as $option) 
							{ 
							    $fd['options'][$j]['label'] = $option->getLabel();
							    $fd['options'][$j]['value'] = $option->getValue();
							    $fd['options'][$j]['count'] = $option->getCount();
							    $j++;
							}
							$filter['data'][] = $fd;
						}
					}
				}
			}
			catch(Exception $e){
				$filter['message'] = $e->getMessage();
			}
		endif;
		
		$pCollection->addUrlRewrite(0);

		if(!isset($data['page']) || $data['page']=="") $data['page']=1;
		if(!isset($data['limit']) || $data['limit']=="") $data['limit']=20;
		$data['offset'] = ($data['page']-1) * $data['limit'];

		$productList = array();
		$product_total = $pCollection->getSize();
		$pCollection->setPageSize($data['offset'] + $data['limit']);

		if ($data['offset'] > $product_total)
		    return $this->errorStatus(array('opps! No information found'));
		$check_limit = 0;
		$check_offset = 0;

		foreach ($pCollection as $product) { 
		    if (++$check_offset <= $data['offset']) {
			continue;
		    }
		    if (++$check_limit > $data['limit'])
			break;
			
			$stock = true;
			if (version_compare(Mage::getVersion(), '1.4.0.1', '=') === true) {                			
				if (!$product->isSaleable()) $stock = false;
			}

		    $info_product = array(
				'product_id'              => $product->getId(),
				'entity_id'               => $product->getId(),
				'name'                    => $product->getName(),
				'type_id'                 => $product->getTypeId(),
				'price'                   => $product->getPrice(),
				'special_price'           => $product->getFinalPrice(),
				'stock_status'            => $stock,
				'reviews'                 => $ratings[5],
				'product_small_image_url' => Mage::helper('catalog/image')->init($product, 'small_image')->resize(300)->__toString(),
				'product_image_url'       => Mage::helper('catalog/image')->init($product, 'small_image')->resize(300)->__toString(),
				//'product_image'         => $this->getImageProduct($product, null, $width, $height),
				//'manufacturer_name'     => $product->getAttributeText('manufacturer') == false ? '' : $product->getAttributeText('manufacturer'),
				'product_reviews'         => $this->_getProductReviews($product->getId())
			    );

		    $prices = $this->_productPrices($product);
			//---- add bundle and grouped price into product info array
		    if ($prices) {
				$info_product = array_merge($info_product, $prices);
		    }

		    $requestObj = Mage::app()->getFrontController()->getRequest();
				$event_name = $requestObj->getRequestedRouteName() . '_' .
			    $requestObj->getRequestedControllerName() . '_' .
				$requestObj->getRequestedActionName();            

		    $name_of_event= $event_name. '_product_detail';
		    //$event_name = $this->getControllerName() . '_product_detail';
		    $event_value = array(
				'object' => $this,
				'product' => $product
			    );
		    Mage::dispatchEvent($name_of_event, $event_value);
		    $productList[] = $info_product;
		}
		
		$information = $this->successStatus();
		$information['message'] = array($product_total);
		$information['data']['products'] = $productList;
		$information['data']['filters'] = $filter;
		$information['data']['product_count'] = $product_total;
		
		return $information;
    }

    public function getRandomProducts($randomCount = 10)
    {
    	$storeId = $this->_getStoreId();
    	$pCollection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')					
			->addAttributeToFilter('status', '1')
			->addAttributeToFilter('visibility', '4')					
			->setStoreId($storeId)
			->addFinalPrice();
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($pCollection);
		$pCollection->getSelect()->order('RAND()');
		$pCollection->getSelect()->limit($randomCount);

		$products = array();
		if(!empty($pCollection)){
            foreach ($pCollection as $key => $_product) {
            	$row = array();
                $row['entity_id']             = $_product->getId();
				$row['entity_type_id']        = $_product->getEntityTypeId();
				$row['attribute_set_id']      = $_product->getAttributeSetId();
				$row['type_id']               = $_product->getTypeId();
				$row['sku']                   = $_product->getSku();
				$row['name']                  = $_product->getName();
				$row['price']                 = $_product->getPrice();
				$row['final_price']           = $_product->getFinalPrice();
				$row['special_price']         = $_product->getSpecialPrice();
				$row['is_salable']            = $_product->getIsSalable();
				$row['status']                = $_product->getStatus();
				$row['product_thumbnail_url'] = Mage::helper('catalog/image')->init($_product, 'thumbnail')->resize(200)->__toString();
				$products[] = $row;
            }
        }

		return $products;
    }

    function getProductRatingStar($productId)
    {
        $reviews = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();

        $starReview = array();
        $starReview[0] = 0;
        $starReview[1] = 0;
        $starReview[2] = 0;
        $starReview[3] = 0;
        $starReview[4] = 0;
        $starReview[5] = 0;
        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
                $starReview[5]++;
                $tmp2 = 0;
                foreach ($review->getRatingVotes() as $vote) {
                    $tmp2 += ($vote->getPercent() / 20);
                }
                $tmp1 = (int) ($tmp2 / count($review->getRatingVotes()));
                $tmp3 = $tmp2 % 3;
                $tmp1 = $tmp3 < 5 ? $tmp1 : $tmp1 + 1;
                if ($tmp1 == 1) {
                    $starReview[0]++;
                } elseif ($tmp1 == 2) {
                    $starReview[1]++;
                } elseif ($tmp1 == 3) {
                    $starReview[2]++;
                } elseif ($tmp1 == 4) {
                    $starReview[3]++;
                } elseif ($tmp1 == 5) {
                    $starReview[4]++;
                } elseif ($tmp1 == 0) {
                    $starReview[5]--;
                }
            }
        }
        return $starReview;
    }

    /**
     * added by yash
     * for getting product reviews
     */ 
    function _getProductReviews($productId)
    {
        $reviews = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();

        $reviews_array = array();
        $ratings = array();

        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
            	$reviewData = $review->getData();
            	$averageUserVoting = 0;
            	
            	$votes = Mage::getModel('rating/rating_option_vote')
				    ->getResourceCollection()
				    ->setReviewFilter($reviewData['review_id'])
				    ->setStoreFilter(Mage::app()->getStore()->getId())
				    ->load();
				$votesData = $votes->getData();
				if(!empty($votesData)):
					foreach($votesData as $vdata):
						$averageUserVoting += $vdata['value'];
						$ratings[] = $vdata['value'];
					endforeach;
					$averageUserVoting = round(($averageUserVoting / count($votesData)), 2);
				endif;
			    $reviewData['votes'] = $votesData;
			    $reviewData['averageUserVoting'] = $averageUserVoting;
			    
                //$reviews_array[] = $review->getData();
                $reviews_array[] = $reviewData;
            }
        }
        $averageRating = round((array_sum($ratings)/count($ratings)),2);

        $result_array = array(
			'reviewsCount'  => count($reviews),
			'averageRating' => $averageRating,
			'reviews'       => $reviews_array,
        	);
        return $result_array;
    }

    public function productInfo($data)
    {
    	$product_id = $data['product_id'];
    	$product = Mage::getModel('catalog/product')->load($product_id);
        if (!$product->getId()) {
            $information = $this->errorStatus("Product_Does_Not_Exists");
            return $information;
        }

        $option = $this->_getAllProductOptions($product);
        $prices = $this->_productPrices($product);

        $images=array();
		$i=0;
		foreach ($product->getMediaGallery('images') as $image) {
        	if ($image['disabled']) {
            	continue;
            }            
			$images[$i]['full_image_url'] = Mage::helper('catalog/image')->init($product, 'thumbnail',$image['file'])->resize(300)->__toString();
			$images[$i]['id'] = isset($image['value_id']) ? $image['value_id'] : null;
			$images[$i]['position'] = $image['position'];
			$images[$i]['label'] = $image['label'];
			$i++;
		}
		if(empty($images)){
			$images[] = array(
				'full_image_url' => Mage::helper('catalog/image')->init($product, 'image')->resize(200)->__toString(),
				'id'             => '0',
				'position'       => '1',
				'label'          => 'Base Image',
				);
		}

		$stock = true;
		if (version_compare(Mage::getVersion(), '1.4.0.1', '=') === true) {                			
			if (!$product->isSaleable()) $stock = false;
        }
        
        $productInfo = array(
			'product_id'            => $product_id,
			'name'                  => $product->getName(),
			'url'                   => $product->getProductUrl(),
			'type'                  => $product->getTypeId(),
			'attributeSetName'		=> Mage::getModel("eav/entity_attribute_set")->load($product->getAttributeSetId())->getAttributeSetName(),
			'price'                 => $product->getPrice(),
			'special_price'         => $product->getFinalPrice(),
			'description'           => Mage::helper('catalog/output')->productAttribute($product, $product->getDescription(), 'description'),
			'short_description'     => Mage::helper('catalog/output')->productAttribute($product, $product->getShortDescription(), 'short_description'),
			'max_qty'               => (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty(),
			'product_images'        => $images,
			'product_thumbnail_url' => Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(200)->__toString(),
			'stock_status'          => $stock,
			'options'               => $option,
			'product_reviews'       => $this->_getProductReviews($product_id)
        );	

        if ($prices) {
            $productInfo = array_merge($productInfo, $prices);
        }
        
        $productInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/custom'))->getCustomProductDetailFields($product, $productInfo);

        $requestObj = Mage::app()->getFrontController()->getRequest();
		$event_name = $requestObj->getRequestedRouteName() . '_' .
    	    $requestObj->getRequestedControllerName() . '_' .
        	$requestObj->getRequestedActionName();            

        $name_of_event= $event_name. '_product_detail';
       
        $event_value = array(
            'object' => $this,
            'product' => $product
        );
        Mage::dispatchEvent($name_of_event, $event_value);
        $information = '';

        if (count($productInfo)) {
        	if(isset($data['addRecentViews']) && $data['addRecentViews'] == '1'){
        		Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
        		Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());
        	}
            $information = $this->successStatus();
            $information['data']['product_details'] = $productInfo;
	    	$information['data']['related_products'] = $this->getRelatedProducts($product);
            $information['data']['product_details']['ratingOptions'] = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/review'))->_getRatingOptions(array(
				"product_id" => $product_id,
				));
            if(isset($data['addRecentViews']) && $data['addRecentViews'] == '1'){
            	$information['data']['recentlyViewed'] = $this->getRecentlyViewedProducts();
            }
        } else {
            $information = $this->errorStatus();
        }
        return $information;
    }

    public function _productPrices($product)
    {
        // ----- Get Price for bundle and ground products 
        $type = $product->getTypeId();
        switch ($type) {          
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
                /*
                    ----- Bundle price
                */
                $prices = array();
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

                // --- ENd Budle price 
                break;            
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED :
                /*
                    ----- Grouped price
                */
                $prices = array();
                $_taxHelper = Mage::helper('tax');
                $_minimalPriceValue = $product->getMinimalPrice();
                //$_exclTax = $_taxHelper->getPrice($product, $_minimalPriceValue);
                //$_inclTax = $_taxHelper->getPrice($product, $_minimalPriceValue, true);  
                //print_r($product->getData());exit; 
                
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
                // -----END -- Grouped Price         
        }
        // ----- end Price  
        return $prices;
    }

    public function getAttributes($product)
    {
        $result = array();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                // Zend_debug::dump(get_class_methods($attribute));die();
                $result[] = array(
                    'title' => $attribute->getFrontendLabel(),
                    'value' => $attribute->getFrontend()->getValue($product),
                );
            }
        }
        return $result;
    }

    public function getProductOptions($product)
    {
        $type = $product->getTypeId();
        switch ($type) {
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                return $this->getSimpleProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
                return $this->getBundleProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE :
                return $this->getConfigurableProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED :
                return $this->getGroupedProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL :
                return $this->getVirtualProductOptions($product);
                break;
        }
    }

    public function _getAllProductOptions($product)
    {
        $type = $product->getTypeId();

        $options = array(
            'product_options'          => $this->getSimpleProductOptions($product),
            'product_super_attributes' => array(),
            'super_group'              => array(),
            'link'                     => array(),
            'sample_links'             => array(),
            'bundle'                   => array(),
            'virtual'                  => array(),
            );

        switch ($type) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE :
                $options['bundle'] = $this->getBundleProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE :
                $options['product_super_attributes'] = $this->getConfigurableProductOptions($product);
                break;
            case 'downloadable' :
                $links = $this->getDownloadableLinks($product);
                $options['link'] = $links['links'];
                $options['sample_links'] = $links['samples'];
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED :
                $options['super_group'] = $this->getGroupedProductOptions($product);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL :
                $options['virtual'] =  $this->getVirtualProductOptions($product);
                break;
        }

        return $options;
    }

    public function getSimpleProductOptions($product)
    {
		$options=array();
		foreach ($product->getOptions() as $o) {
            $_tmpOptions = $o->getData();
            if($o->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_TEXT)
		    {
		     	$_tmpTextType= array(
					'price'          => $o->getPrice(true),
					'price_type'     => $o->getPriceType(),
					'sku'            => $o->getSku(),
					'max_characters' => $o->getMaxCharacters(),
		     		);
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		 	 }
		     if($o->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE)
		     {
		     	 $_tmpTextType= array(
					'price'      => $o->getPrice(true),
					'price_type' => $o->getPriceType(),
					'sku'        => $o->getSku(),
		     	 	);
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		 	 }	     
		     if($o->getGroupByType()== Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT)
		     {
			     $values = $o->getValues();
			     $_tmp['options']= array();
			     foreach ($values as $v) {
			         $_tmp['options'][] = $v->getData();
			     }
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmp);
		     }

		     if($o->getGroupByType()== Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE)
		     {
		     	 $_tmpTextType= array(
					'file_extension' => $o->getFileExtension(),
					'image_size_x'   => $o->getImageSizeX(),
					'image_size_y'   => $o->getImageSizeY(),
		     	 	);
		     	$_tmpOptions = array_merge($_tmpOptions, $_tmpTextType);
		     }
		     $options[]=$_tmpOptions;
		 }
        return $options;
    }

    public function getBundleProductOptions($product)
    {
        $typeInstance = $product->getTypeInstance(true);
        $typeInstance->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product), $product
        );

        $attributes = $optionCollection->appendSelections($selectionCollection, false, false);        

        $options = array();
        //$coreHelper   = Mage::helper('core');
        foreach ($attributes as $_attribute) {
        	$_tmpOptions = array (
						'option_id'    => $_attribute->getId(),
						'option_title' => $_attribute->getTitle(),
						'position'     => $_attribute->getPosition(),
						'required'     => $_attribute->getRequired(),
						'option_type'  => $_attribute->getType(),
        				);
            $_tmp['options'] = array();
            foreach ($_attribute->getSelections() as $_selection) {
                $_tmp['options'][] = array (
					'option_id'                       => $_selection->getSelectionId(),
					'option_value'                    => $_selection->getName(),
					'option_selection_qty'            => $_selection->getSelectionQty(),    
					'option_selection_can_change_qty' => $_selection->getSelectionCanChangeQty(),    
					'option_position'                 => $_selection->getPosition(),    
					'option_is_default'               => $_selection->getIsDefault(),                    		            		
					'option_price'                    => $product->getPriceModel()->getSelectionPreFinalPrice($product, $_selection, 1),
                	);
            }
            $_tmpOptions = array_merge($_tmpOptions, $_tmp);
	        $options[] = $_tmpOptions;
        }
        return $options;
    }

    public function getConfigurableProductOptions($product)
    {
		$options    = array();
		$currentProduct = $product;    	
    	$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        if (!$this->hasAllowProducts()) {
            $products = array();
            $skipSaleableCheck = true;
            if (version_compare(Mage::getVersion(), '1.7.0.0', '>=') === true) {                
                $skipSaleableCheck = Mage::helper('catalog/product')->getSkipSaleableCheck();
            }
            $allProducts = $product->getTypeInstance(true)
                    ->getUsedProducts(null, $product);
            foreach ($allProducts as $_product) {
                if ($_product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $_product;
                }
            }
            $this->setAllowProducts($products);
        }
        $products = $this->getData('allow_products');
        //return $products;

        $list_value = array();
        $information = array();
        foreach ($products as $_product) {
			$productId  = $_product->getId();
            foreach ($attributes as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $_product->getData($productAttribute->getAttributeCode());
                // if (!in_array($attributeValue, $list_value))
                    // $list_value[] = $attributeValue;
				if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        foreach ($attributes as $attribute) {
            $attInfo = $attribute->getData();
            $attributeId =  $attribute->getProductAttribute()->getId();
            if(!empty($attInfo['prices']))
            {
                foreach($attInfo['prices'] as $p_key => $p)
                {
                    $productsIndex = array();
                    if (isset($options[$attributeId][$p['value_index']])) {
                        $productsIndex = $options[$attributeId][$p['value_index']];
                    }
                    $attInfo['prices'][$p_key]['dependence_option_ids'] = $productsIndex;
                }
            }
            $information[] = $attInfo;       
        }
        return $information;
    }

    public function getDownloadableLinks($product)
    {
        $linkArr = array();
        $links = $product->getTypeInstance(true)->getLinks($product);
        foreach ($links as $item) {
            $tmpLinkItem = array(
				'link_id'             => $item->getId(),
				'title'               => $item->getTitle(),
				'price'               => $item->getPrice(),
				'number_of_downloads' => $item->getNumberOfDownloads(),
				'is_shareable'        => $item->getIsShareable(),
				'link_url'            => $item->getLinkUrl(),
				'link_type'           => $item->getLinkType(),
				'sample_file'         => $item->getSampleFile(),
				'sample_url'          => $item->getSampleUrl(),
				'sample_type'         => $item->getSampleType(),
				'sort_order'          => $item->getSortOrder()
            );
            $file = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBasePath(), $item->getLinkFile()
            );

            if ($item->getLinkFile() && !is_file($file)) {
                Mage::helper('core/file_storage_database')->saveFileToFilesystem($file);
            }

            if ($item->getLinkFile() && is_file($file)) {
                $name = Mage::helper('downloadable/file')->getFileFromPathFile($item->getLinkFile());
                $tmpLinkItem['file_save'] = array(
                    array(
						'file'   => $item->getLinkFile(),
						'name'   => $name,
						'size'   => filesize($file),
						'status' => 'old'
	                    ));
            }
            $sampleFile = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBaseSamplePath(), $item->getSampleFile()
            );
            if ($item->getSampleFile() && is_file($sampleFile)) {
                $tmpLinkItem['sample_file_save'] = array(
                    array(
						'file'   => $item->getSampleFile(),
						'name'   => Mage::helper('downloadable/file')->getFileFromPathFile($item->getSampleFile()),
						'size'   => filesize($sampleFile),
						'status' => 'old'
                    	));
            }
            if ($item->getNumberOfDownloads() == '0') {
                $tmpLinkItem['is_unlimited'] = 1;
            }
            if ($product->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            /*
            if ($product->getStoreId() && Mage::helper('downloadable')->getIsPriceWebsiteScope()) {
                $tmpLinkItem['website_price'] = $item->getWebsitePrice();
            }
            */
            $linkArr[] = $tmpLinkItem;
        }
        unset($item);
        unset($tmpLinkItem);
        unset($links);

        $samples = $product->getTypeInstance(true)->getSamples($product)->getData();
        return array('links' => $linkArr, 'samples' => $samples);
    }

	public function getGroupedProductOptions($product)
	{
		$options = array();
		$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
		$_minprice = NULL;
        if (count($associatedProducts)) {
            foreach ($associatedProducts as $product) {
                if ($product->isSaleable()) {
                    if ($_minprice == NULL) {
                        $_minprice = $product->getFinalPrice();
                    } else {
                        if ($_minprice > $product->getFinalPrice())
                            $_minprice = $product->getFinalPrice();
                    }
                    $options[] = array(
						'option_id'    => $product->getId(),
						'option_value' => $product->getName(),
						'option_title' => $product->getName(),
						'option_type'  => 'text',
						'option_price' => $product->getFinalPrice(),
                    	);
                }
            }
        }

        return $options;
	}
    
	public function getVirtualProductOptions($product)
	{
		return array();
	}
	
	public function getResizedImage($url, $width, $height = null, $quality = 100) 
	{
		if (!$url)
			return false;
		$imageName = substr(strrchr($url,"/"),1);
		$imageUrl = Mage::getBaseDir('media').DS."catalog".DS."category".DS.$imageName;
		if (!is_file( $imageUrl ))
			return false;
	 
		$imageResized = Mage::getBaseDir('media').DS."catalog".DS."category".DS."cache".DS."cat_resized".DS.$imageName;
		if (!file_exists($imageResized) && file_exists($imageUrl) || file_exists($imageUrl) && filemtime($imageUrl) > filemtime($imageResized)):
			$imageObj = new Varien_Image($imageUrl);
			$imageObj->constrainOnly(true);
			$imageObj->keepAspectRatio(false);
			$imageObj->keepFrame(false);
			$imageObj->quality($quality);
			$imageObj->resize($width, $height);
			$imageObj->save($imageResized);
		endif;
	 
		if(file_exists($imageResized)){
			$img_url = Mage::getBaseUrl('media' )."catalog/category/cache/cat_resized/".$imageName;
			return $img_url;
		}
		else{
			return $url;
		}
	}
	
	/**
	 * Added by Yash to get recently viewed products
	 * @param int $data['limit']
	 * 14-10-2014
	 */
	public function getRecentlyViewedProducts($data = null)
	{
		$limit = isset($data['limit'])?$data['limit']:10;
		$recentlyViewedProducts = Mage::getSingleton('Mage_Reports_Block_Product_Viewed')->setPageSize($limit)->getItemsCollection();
        $recentlyViewedProductsArray = array();
        if($recentlyViewedProducts){
        	foreach($recentlyViewedProducts as $row){
        		$productData = $row->getData();
        		$recentlyViewedProductsArray[] = array(
					"entity_id"             => $productData['entity_id'],
					"entity_type_id"        => $productData['entity_type_id'],
					"attribute_set_id"      => $productData['attribute_set_id'],
					"type_id"               => $productData['type_id'],
					"sku"                   => $productData['sku'],
					"price"                 => $productData['price'],
					"final_price"           => $productData['final_price'],
					"min_price"             => $productData['min_price'],
					"max_price"             => $productData['max_price'],
					"special_price"         => $productData['special_price'],
					"name"                  => $productData['name'],
					"is_salable"            => $productData['is_salable'],
					"status"                => $productData['status'],
					"product_thumbnail_url" => Mage::helper('catalog/image')->init($row, 'thumbnail')->resize(200)->__toString(),
        			);
        	}
        }
        return $recentlyViewedProductsArray;
	}

	public function getRelatedProducts($product)
	{
		$website_id = Mage::app()->getWebsite()->getId();
		$limit = isset($data['limit'])?$data['limit']:5;
		$relatedProductIds = $product->getRelatedProductIds();
		$relatedProductsArray = array();
		if($relatedProductIds)
		{	
			foreach($relatedProductIds as $id)
			{
				$productData = Mage::getModel('catalog/product')->load($id);
				//if($productData['is_salable']){
				if($productData->getIsSalable() && $productData->isVisibleInSiteVisibility() && in_array($website_id, $productData->getWebsiteIds())){
					$row = array(
							"entity_id"             => $productData['entity_id'],
							"entity_type_id"        => $productData['entity_type_id'],
							"attribute_set_id"      => $productData['attribute_set_id'],
							"type_id"               => $productData['type_id'],
							"sku"                   => $productData['sku'],
							"price"                 => $productData->getPrice(),
							"final_price"           => $productData->getFinalPrice(),
							"special_price"         => $productData->getFinalPrice(),
							"name"                  => $productData['name'],
							"is_salable"            => $productData['is_salable'],
							"status"                => $productData['status'],
							"product_thumbnail_url" => Mage::helper('catalog/image')->init($productData, 'thumbnail')->resize(200)->__toString(),
						);

					$prices = $this->_productPrices($productData);
					//---- add bundle and grouped price into product info array
				    if ($prices) {
						$row = array_merge($row, $prices);
				    }

					$relatedProducts[] = $row;
				}
			}
		}

		return $relatedProducts;
	}
	
	public function getAllProducts()
	{
		$storeId = $this->_getStoreId();
		$products = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')					
			->addAttributeToFilter('status', '1')
			->setStoreId($storeId);
		
		Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($products);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
		$allProducts = array();
		if(!empty($products)){
			foreach ($products as $key => $value) {
				$row = $value->getData();
				$row['name'] = $value->getName();
				$allProducts[] = $row;
			}
		}
		return $allProducts;
		//return $products->getData();
	}

	protected function _attachCategoryIcon($categories, $appcode)
	{
		$iconCategories = array();
		$iconCollection = Mage::getModel('mobiadmin/appsetting')->getCollection()
			->addFieldToFilter('app_code', $appcode)
			->addFieldToFilter('setting_code', 'category_icons');
		if($iconCollection->getSize()){
			foreach($iconCollection as $iconrow){
				$row = $iconrow->getData();
				$row = Mage::helper('mobiadmin')->_jsonUnserialize($row['value']);
				if(!empty($row)){
					foreach($row as $cat){
						$iconCategories[$cat['category_id']] = $cat['mobiicon'];
					}
				}
			}
		}
		
		if(!empty($categories)){
			$baseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'mobi_category_icons/';
			foreach($categories as $key => $cat){
				if(array_key_exists($cat['category_id'], $iconCategories)){
					if(!empty($iconCategories[$cat['category_id']])){
						$categories[$key]['mobiicon'] = $iconCategories[$cat['category_id']];
						$categories[$key]['mobiiconurl'] = Mage::getStoreConfig(Mage_Core_Model_Url::XML_PATH_SECURE_URL) . 'MobiSettings/category_svg.php';
					}
					else{
						$categories[$key]['mobiicon'] = null;
						$categories[$key]['mobiiconurl'] = null;
					}
				}
				else{
					$categories[$key]['mobiicon'] = null;
					$categories[$key]['mobiiconurl'] = null;
				}
			}
		}
		return $categories;
	}
}
?>
