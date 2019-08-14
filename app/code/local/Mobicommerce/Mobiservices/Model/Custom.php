<?php

class Mobicommerce_Mobiservices_Model_Custom extends Mobicommerce_Mobiservices_Model_Abstract {

    const REFRESH_CART_AFTER_ADD_PRODUCT = false;
    const IS_SHIPPING_METHOD_CUSTOM_FIELDS = false;
    const ROUNDUP_CART_VALUES = false;
    const DNB_DESIGNTOOL_APPLIED = false;

	public function getCustomCheckoutFields(){
        $customFields = array();
        $site = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        
        // P01
        $customFields = array(
            "register" => array(
                array(
                    "code"              => "taxvat",
                    "type"              => "text",
                    "name"              => "Codice Fiscale",
                    "required"          => true,
                    "validation"        => "",
                    "error_message"     => "Questo è un campo obbligatorio.",
                    "registerDependent" => false
                    )
                ),
            "billing" => array(
                array(
                    "code"              => "taxvat",
                    "type"              => "text",
                    "name"              => "Codice Fiscale",
                    "required"          => true,
                    "validation"        => "",
                    "error_message"     => "Questo è un campo obbligatorio.",
                    "registerDependent" => true
                    ),
                ),
            );
        // F03
        $customFields = array(
            "shipping_method" => array(
                array(
                    "code"              => "adj[delivery_date]",
                    "type"              => "date",
                    "name"              => "Leveringsdatum",
                    "required"          => true,
                    "validation"        => "",
                    "error_message"     => "Please enter delivery date",
                    "registerDependent" => false,
                    "params" => array(
                        "default_value" => "",
                        "description"   => "",
                        "format"        => "dd/mm/yyyy"
                        )
                    )
                ),
            );
        $customFields = array();
        return $customFields;
    }

    public function getCustomProductDetailFields($_product, $productInfo){
        $fields = array(
            array(
                "code"     => "sku",
                "type"     => "text",
                "relateTo" => "description",
                "name"     => "Codice",
                "value"    => "",
                ),
            array(
                "code"     => "manufacturer",
                "type"     => "dropdown",
                "relateTo" => "description",
                "name"     => "Marchio",
                "value"    => "",
                ),
            array(
                "code"     => "consegna_time",
                "type"     => "text",
                "relateTo" => "description",
                "name"     => "Tempo di Consegna",
                "value"    => "",
                ),
            array(
                "code"     => "generic_group",
                "type"     => "dropdown",
                "relateTo" => "description",
                "name"     => "Reparto",
                "value"    => "",
                ),
            array(
                "code"     => "custom_stock_status",
                "type"     => "dropdown",
                "relateTo" => "stock",
                "name"     => "Disponibilità",
                "value"    => "",
                ),
            );
        $fields = array(
            array(
                "code"     => "size_chart",
                "type"     => "text",
                "relateTo" => "staticblock_identifier",
                "name"     => "SIZE CHART & GARMENT INFO",
                "value"    => "",
                ),
            );
        // F17
        $fields = array(
            array(
                "code"     => "payuapi",
                "type"     => "payment_method_installments",
                "relateTo" => "payment_method_installments",
                "name"     => "TAKSİT SEÇENEKLERİ",
                "value"    => null,
                ),
            array(
                "code"     => "Options",
                "type"     => "group_attribute",
                "relateTo" => "group_attribute",
                "name"     => "ÖZELLİKLER",
                "value"    => "",
                ),
            array(
                "code"     => "returns_custom_tab_tr",
                "type"     => "staticblock_identifier",
                "relateTo" => "staticblock_identifier",
                "name"     => "GARANTİ",
                "value"    => "",
                ),
            array(
                "code"     => "payment_custom_tab",
                "type"     => "staticblock_identifier",
                "relateTo" => "staticblock_identifier",
                "name"     => "Ödeme",
                "value"    => "",
                ),
            array(
                "code"     => "shipping_custom_tab_tr",
                "type"     => "staticblock_identifier",
                "relateTo" => "staticblock_identifier",
                "name"     => "KARGO",
                "value"    => "",
                ),
            );
        // Dnb mobi
        $fields = array(
            array(
                "code"     => "is_customizable",
                "type"     => "dropdown",
                "relateTo" => "DNBDESIGNTOOL_BUTTON",
                "name"     => "Personalize",
                "value"    => "",
                ),
            );
        $fields = null;
        $outputFields = array();
        if(!empty($fields)){
            foreach($fields as $field_key => $field){
                try{
                    if(in_array($field['type'], array('text', 'dropdown'))){
                        if($_product->offsetExists($field['code'])){
                            switch ($field['type']) {
                                case 'text':
                                default:
                                    $fields[$field_key]['value'] = $_product->getResource()->getAttribute($field['code'])->getFrontend()->getValue($_product);
                                    break;
                                case 'dropdown':
                                    if($field['code'] == 'is_customizable'){
                                        $fields[$field_key]['value'] = strtolower($_product->getResource()->getAttribute($field['code'])->getFrontend()->getValue($_product)) == 'sí' ? 'Yes' : false;
                                    }
                                    else{
                                        $fields[$field_key]['value'] = $_product->getAttributeText($field['code']);
                                    }
                                    break;
                            }
                            switch ($field['relateTo']) {
                                case 'stock':
                                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
                                    $fields[$field_key]['value'] = str_replace('{qty}', (int)$stock->getQty(), $fields[$field_key]['value']);
                                    break;
                                case 'staticblock_identifier':
                                    $fields[$field_key]['value'] = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($fields[$field_key]['value'])->toHtml();
                                    break;
                                default:
                                    break;
                            }
                            $outputFields[] = $fields[$field_key];
                        }
                    }
                    else if(in_array($field['type'], array('staticblock_identifier'))){
                        $fields[$field_key]['value'] = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($fields[$field_key]['code'])->toHtml();
                        $outputFields[] = $fields[$field_key];
                    }
                    else if(in_array($field['type'], array('group_attribute'))){
                        $groupId = Mage::getModel('eav/entity_attribute_group')->getCollection()
                            ->addFieldToFilter('attribute_set_id', array('eq' => $_product->getAttributeSetId()))
                            ->addFieldToFilter('attribute_group_name', array('eq' => $field['code']))
                            ->getFirstItem()->getId();

                        $product_attributes = array();
                        foreach($_product->getAttributes($groupId) as $attribute) {
                            $product_attributes[] = array(
                                'frontend_label' => $attribute->getFrontend()->getLabel($_product),
                                'store_label'    => $attribute->getStoreLabel(),
                                'value'          => $attribute->getFrontend()->getValue($_product)
                                );
                        }
                        $fields[$field_key]['value'] = $product_attributes;
                        $outputFields[] = $fields[$field_key];
                    }
                    else if(in_array($field['type'], array('payment_method_installments'))){
                        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
                        $product_price = $_product->getFinalPrice();
                        if($payments){
                            foreach ($payments as $method_code => $method) {
                                if($method_code == $field['code']){
                                    switch($method_code){
                                        case 'payuapi';
                                            $installment_options = array();
                                            $installment_key_pair = array(
                                                array(
                                                    "name"      => "Axess, Bonus, Maximum, Finans, World, Asya, Halkbank",
                                                    "keycode"   => "V7H1993D1",
                                                    "valuecode" => "VGD8UEY31",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                /*
                                                array(
                                                    "name"      => "Installment - Bonus",
                                                    "keycode"   => "V7H1993D2",
                                                    "valuecode" => "VGD8UEY32",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                array(
                                                    "name"      => "Installment - Maximum",
                                                    "keycode"   => "V7H1993D3",
                                                    "valuecode" => "VGD8UEY33",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                array(
                                                    "name"      => "Installment - Finans",
                                                    "keycode"   => "V7H1993D4",
                                                    "valuecode" => "VGD8UEY34",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                array(
                                                    "name"      => "Installment - World",
                                                    "keycode"   => "V7H1993D5",
                                                    "valuecode" => "VGD8UEY35",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                array(
                                                    "name"      => "Installment - Asya",
                                                    "keycode"   => "V7H1993D6",
                                                    "valuecode" => "VGD8UEY36",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    ),
                                                array(
                                                    "name"      => "Installment - Halkbank",
                                                    "keycode"   => "V7H1993D7",
                                                    "valuecode" => "VGD8UEY37",
                                                    "is_active" => false,
                                                    "options"   => array()
                                                    )
                                                */
                                                );
                                            foreach($installment_key_pair as $installment_key => $installment_pair){
                                                if($method->getConfigData($installment_pair['keycode'])){
                                                    $installment_key_pair[$installment_key]['is_active']   = true;
                                                    $installment_key_pair[$installment_key]['options_str'] = $method->getConfigData($installment_pair['valuecode']);
                                                    $installment_key_pair[$installment_key]['options']     = $this->_processPayuapiInstallmentOptionsString($installment_key_pair[$installment_key]['options_str'], $product_price, $interest_type = 'simple', $force_yearly_interest = true);

                                                    $installment_options[] = $installment_key_pair[$installment_key];
                                                }
                                            }

                                            $fields[$field_key]['value'] = $installment_options;
                                            $outputFields[] = $fields[$field_key];
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
                catch(Exception $e){
                    
                }
            }
        }
        if(empty($outputFields))
            $outputFields = null;
        //$outputFields = null;
        $productInfo['customAttributes'] = $outputFields;
        return $productInfo;
    }

    /**
     * Coded by Yash
     * Date: 18-12-2014
     * For calculating simple and compund interest for installment options
     */
    public function _processPayuapiInstallmentOptionsString($str = null, $price = 0, $interest_type = 'simple', $force_yearly_interest = false){
        $str = trim($str);
        if(empty($str))
            return null;

        /**
         * Formula for simple interest
         * A = P(1 + rt)
         * P = principal amount
         * r = rate of interest
         * t = time(in tems of years)
         * calculating months to years
         * 2 months = 2 / 12 = 0.17 years
         */
        $return_options = array();
        $installment_options = explode(';', $str);
        foreach($installment_options as $ioption){
            $month_interest_pair = explode('=', $ioption);
            if(count($month_interest_pair) == 2){
                $month_count = $month_interest_pair[0];
                $interest_rate = $month_interest_pair[1];
                if($interest_type == 'simple'){
                    $t = 1;
                    if($force_yearly_interest)
                        $t = ceil($month_count / 12);
                    else
                        $t = round($month_count / 12, 2);

                    $r = $interest_rate / 100;
                    $total_amount = $price * (1 + ($r * $t));
                    $return_options[] = array(
                        'month_count'        => $month_count,
                        'installment_amount' => round($total_amount / $month_count),
                        'total_amount'       => round($total_amount),
                        'exact_total_amount' => round($total_amount, 2)
                        );
                }
                else if($interest_type == 'compound'){

                }
            }
        }
        return $return_options;
    }
}