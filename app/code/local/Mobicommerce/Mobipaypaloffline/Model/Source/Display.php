<?php

/**
 * Display mobipaypaloffline payment method options
 */
class Mobicommerce_Mobipaypaloffline_Model_Source_Display
{
    protected $_displayOptions = array(
        'MOBILE'  => 'Mobile Only',
        'WEBSITE' => 'Website Only',
        'BOTH'    => 'Mobile And Website both'
    );

    public function toOptionArray()
    {
        $displayOptions = $this->_displayOptions;
        $options = array();
        foreach ($displayOptions as $code => $label) {
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        return $options;
    }
}
