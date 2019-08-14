<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mobicommerce_Mobipaypaloffline
 * @copyright   Copyright (c) 2014 Mobicommerce. (http://www.mobi-commerce.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for Bank Transfer payment method form
 */
class Mobicommerce_Mobipaypaloffline_Block_Info_Mobipaypaloffline extends Mage_Payment_Block_Info
{
    /**
     * Block construction. Set block template.
     */
    protected function _construct()
    {
        parent::_construct();
        //$this->setTemplate('mobipaypaloffline/info/mobipaypaloffline.phtml');
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);

        $payer_email = $info->getMobipaypalofflinePayerEmail();
        $payer_name = $info->getMobipaypalofflinePayerName();

        $payer_email = $payer_email ? $payer_email : '--NOT SPECIFIED--';
        $payer_name = $payer_name ? $payer_name : '--NOT SPECIFIED--';

        $transport->addData(array(
            Mage::helper('payment')->__('Payer Email') => $payer_email,
            Mage::helper('payment')->__('Payer Name') => $payer_name
        ));
        return $transport;
    }

    /**
     * Retrieve payment method model
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    /*
    public function getMethod()
    {
        $method = $this->getData('method');

        if (!($method instanceof Mage_Payment_Model_Method_Abstract)) {
            Mage::throwException($this->__('Cannot retrieve the payment method model object.'));
        }
        return $method;
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    /*
    public function getMethodCode()
    {
        return $this->getMethod()->getCode();
    }

    /**
     * Retrieve field value data from payment info object
     *
     * @param   string $field
     * @return  mixed
     */
    /*
    public function getInfoData($field)
    {
        return $this->escapeHtml($this->getMethod()->getInfoInstance()->getData($field));
    }
    */

}
