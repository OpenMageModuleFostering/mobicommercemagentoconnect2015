<?php
class Mobicommerce_Mobiservices_Helper_Shoppingcart extends Mage_Core_Helper_Abstract {

    public function formatOptionsCart($options) {
        $data = array();
        foreach ($options as $option) {
            $data[] = array(
                'option_title' => $option['label'],
                'option_value' => $option['value'],
                'option_price' => isset($option['price']) == true ? $option['price'] : 0,
            );
        }
        return $data;
    }

    public function getOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        return array_merge(
            $this->getBundleOptions($item), 
            $this->formatOptionsCart(Mage::helper('catalog/product_configuration')->getCustomOptions($item))
            );
    }

    /**
     * it is for magento < 1.5.0.0
     * @param Mage_Sales_Model_Quote_Item $item
     * @return options
     */
    public function getUsedProductOption(Mage_Sales_Model_Quote_Item $item) {
        $typeId = $item->getProduct()->getTypeId();
        switch ($typeId) {
            case Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE:
                return $this->getConfigurableOptions($item);
                break;
            case Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE:
                return $this->getGroupedOptions($item);
                break;
        }

        return $this->getCustomOptions($item);
    }

    public function getConfigurableOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        $attributes = $product->getTypeInstance(true)
            ->getSelectedAttributesInfo($product);
        return array_merge($attributes, $this->getCustomOptions($item));
    }

    public function getGroupedOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        $options = array();
        /**
         * @var Mage_Catalog_Model_Product_Type_Grouped
         */
        $typeInstance = $product->getTypeInstance(true);
        $associatedProducts = $typeInstance->getAssociatedProducts($product);

        if ($associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                $option = array(
                    'label' => $associatedProduct->getName(),
                    'value' => ($qty && $qty->getValue()) ? $qty->getValue() : 0
                );

                $options[] = $option;
            }
        }

        $options = array_merge($options, $this->getCustomOptions($item));
        $isUnConfigured = true;
        foreach ($options as &$option) {
            if ($option['value']) {
                $isUnConfigured = false;
                break;
            }
        }
        return $isUnConfigured ? array() : $options;
    }

    public function getCustomOptions(Mage_Sales_Model_Quote_Item $item) {
        $options = array();
        $product = $item->getProduct();
        
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $product->getOptionById($optionId)) {

                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                            ->setOption($option)
                            ->setQuoteItemOption($quoteItemOption);

                    $options[] = array(
                        'label'       => $option->getTitle(),
                        'value'       => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
                        'option_id'   => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }
        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }
        return $this->formatOptionsCart($options);
    }

    public function getBundleOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        $options = array();
        $product = $item->getProduct();
        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
        $bundleOptionsIds = $optionsQuoteItemOption ? unserialize($optionsQuoteItemOption->getValue()) : array();
        if ($bundleOptionsIds) {
            /**
             * @var Mage_Bundle_Model_Mysql4_Option_Collection
             */
            $optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

            // get and add bundle selections collection
            $selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

            $selectionsCollection = $typeInstance->getSelectionsByIds(
                    unserialize($selectionsQuoteItemOption->getValue()), $product
            );

            $bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
            foreach ($bundleOptions as $bundleOption) {
                if ($bundleOption->getSelections()) {
                    $bundleSelections = $bundleOption->getSelections();
                    $option = array();
                    foreach ($bundleSelections as $bundleSelection) {
                        $check = array();
                        $qty = Mage::helper('bundle/catalog_product_configuration')->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
                        if ($qty) {
                            $check[] = $qty . ' x ' . $this->escapeHtml($bundleSelection->getName());
                            $option[] = array(
                                'option_title' => $bundleOption->getTitle(),
                                'option_value' => $qty . ' x ' . $this->escapeHtml($bundleSelection->getName()),
                                'option_price' => Mage::helper('bundle/catalog_product_configuration')->getSelectionFinalPrice($item, $bundleSelection),
                            );
                        }
                    }
                    
                    if ($check)
                        $options[] = $option;
                    
                }
            }
        }

        return $options;
    }

    public function getDownloadableOptions(Mage_Catalog_Model_Product_Configuration_Item_Interface $item) {
        $options = array();
        $product = $item->getProduct();
        /**
         * @var Mage_Bundle_Model_Product_Type
         */
        $typeInstance = $product->getTypeInstance(true);

        // get bundle options
        $optionsQuoteItemOption = $item->getOptionByCode('downloadable_link_ids');
        $downlodableOptionsIds = $optionsQuoteItemOption ? $optionsQuoteItemOption->getValue() : array();
        if(!empty($downlodableOptionsIds)){
            $downlodableOptionsIds = explode(",", $downlodableOptionsIds);
        }
        $optionsCollection = $typeInstance->getLinks($product);
        $option = array();
        foreach ($optionsCollection as $_item) {
            if (in_array($_item->getId(), $downlodableOptionsIds)) {
                $option[] = $_item->getTitle();                
            }
        }
        if(!empty($option)){
            $options[] = array(
                'option_title' => "Links",
                'option_value' => implode(", ", $option),
                );
        }
        //return $options;
        return array_merge(
            $options,
            $this->formatOptionsCart(Mage::helper('catalog/product_configuration')->getCustomOptions($item))
            );
    }
}