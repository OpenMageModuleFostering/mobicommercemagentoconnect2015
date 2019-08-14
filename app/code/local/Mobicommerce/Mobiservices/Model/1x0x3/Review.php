<?php

/**
 * Added by Yash
 * For rating andf review related functions
 * Date: 27-10-2014
 */
class Mobicommerce_Mobiservices_Model_1x0x3_Review extends Mobicommerce_Mobiservices_Model_Abstract {

    /**
     * Submit new review action
     * @param productId=1&nickname=&title=&detail=&ratings[1]=1to5&ratings[2]:1to5&ratings[3]:1to5
     */
    public function submitReview($data = null)
    {
        $rating = $data['ratings'];
        $productId = isset($data['productId'])?$data['productId']:0;
        if (($product = $this->_initProduct($productId)) && !empty($data)) {
            $session    = Mage::getSingleton('core/session');
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('review/review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('rating/rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $responseData = $this->successStatus(Mage::helper('core')->__('Your review has been accepted for moderation.'));
                    $productInfo = Mage::getModel(Mage::getBlockSingleton('mobiservices/connector')->_getConnectorModel('mobiservices/catalog_catalog'))->productInfo(array('product_id' => $productId));
                    $responseData['data']['product_details'] = $productInfo['data']['product_details'];
                    return $responseData;
                }
                catch (Exception $e) {
                    return $this->errorStatus(Mage::helper('core')->__('Unable to post the review.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    $errorMessages = array();
                    foreach ($validate as $errorMessage) {
                        $errorMessages[] = $errorMessage;
                    }
                    return $this->errorStatus(implode(",", $errorMessages));
                }
                else {
                    return $this->errorStatus(Mage::helper('core')->__('Unable to post the review.'));
                }
            }
            return $this->errorStatus("Please_Pass_Product_Id");
        }

    }

    /**
     * Initialize and check product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct($productId = 0)
    {
        Mage::dispatchEvent('review_controller_product_init_before', array('controller_action'=>$this));
        $categoryId = 0;//(int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $productId;

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }

        try {
            Mage::dispatchEvent('review_controller_product_init', array('product'=>$product));
            Mage::dispatchEvent('review_controller_product_init_after', array(
                'product'           => $product,
                'controller_action' => $this
            ));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }

    /**
     * Added by Yash
     * To get rating options to show in product detail page
     * Date: 28-10-2014
     */
    public function _getRatingOptions($data)
    {
    	$ratingsOptions = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product') # TOFIX
            ->setPositionOrder()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->load();
        $ratingsOptions->addEntitySummaryToItem($data['product_id'], Mage::app()->getStore()->getId());

        /*
        $ratingVoteUserArray = array();
        if(isset($data['productReviews']) && !empty($data['productReviews'])):
            foreach($data['productReviews'] as $productReviews):
                if(!empty($productReviews['votes'])):
                    foreach($productReviews['votes'] as $votes):
                        if(array_key_exists($votes['rating_id'], $ratingVoteUserArray))
                            $ratingVoteUserArray[$votes['rating_id']]++;
                        else
                            $ratingVoteUserArray[$votes['rating_id']] = 1;
                    endforeach;
                endif;
            endforeach;
        endif;
        */

        //$ratingData = $ratingsOptions->getData();
        $ratingData = array();
        if($ratingsOptions):
            $key = 0;
            foreach($ratingsOptions as $_rating):
                $ratingData[$key] = $_rating->getData();
                $ratingData[$key]['summary'] = $_rating->getSummary();
                $ratingData[$key]['options'] = $_rating->getOptions();
                if(empty($ratingData[$key]['summary']))
                    $ratingData[$key]['summary'] = 0;
                /*
                $ratingData[$key]['userCount'] = 0;
                if(isset($ratingVoteUserArray[$ratingData[$key]['rating_id']]))
                    $ratingData[$key]['userCount'] = $ratingVoteUserArray[$ratingData[$key]['rating_id']];
                */
                $key++;
            endforeach;
        endif;

    	return $ratingData;
    }
}