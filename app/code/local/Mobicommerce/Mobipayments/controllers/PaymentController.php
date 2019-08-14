<?php
/*
Mobipayments Payment Controller
By: Yash Shah
yash.shah@rightwaysolution.com
*/

class Mobicommerce_Mobipayments_PaymentController extends Mobicommerce_Mobipayments_Controller_Action {
    protected $_order = null;
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		$this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','mobipayments',array('template' => 'mobipayments/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
	}

	public function initPaypalMobileAction(){
		$data = $this->getData();
		//$result = Mage::getModel('mobiservices/shoppingcart_cart')->getCartDetails();
		$session = Mage::getSingleton('checkout/session');
        //$session->setPaypalStandardQuoteId($session->getQuoteId());
		$session->setMobiPaypalQuoteId($session->getQuoteId());
        //$this->getResponse()->setBody($this->getLayout()->createBlock('paypal/standard_redirect')->toHtml());
        //$session->unsQuoteId();
        //$result = Mage::getModel('mobipayments/standard')->getSuccess("Payment Init Successfully");

        $result = array();
        //print_r($this->getRequest()->getPost('agreement', array()));exit;

        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                //$postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $postedAgreements = $data['agreement'];
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result = Mage::getModel('mobipayments/abstract')->errorStatus($this->__('Please agree to all the terms and conditions before placing the order.'));
                    $this->printResult($result);exit;
                }
            }

            $payment = $data['payment'];
            if ($payment) {
                if (version_compare(Mage::getVersion(), '1.8.0.0', '>=') === true) {
                    $payment['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                }
                else{
                    $payment['checks'] = null;
                }
                $this->getOnepage()->getQuote()->getPayment()->importData($payment);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result = Mage::getModel('mobipayments/abstract')->successStatus();
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            $result = Mage::getModel('mobipayments/abstract')->errorStatus($message);
            $this->printResult($result);exit;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $message = $e->getMessage();
            $result = Mage::getModel('mobipayments/abstract')->errorStatus($message);
            $this->printResult($result);exit;
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $message = $e->getMessage();
            $result = Mage::getModel('mobipayments/abstract')->errorStatus($message);
            $this->printResult($result);exit;
        }
        $result['data']['cart_details']= Mage::getModel('mobiservices/shoppingcart_cart')->getCartInfo();
        $this->getOnepage()->getQuote()->save();
        $session->setMobiPaypalInvoiceId($this->_getCheckoutSession()->getLastRealOrderId());
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        /*
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }
        */

        //$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        $session->unsQuoteId();
        $this->printResult($result);
	}

	protected function _getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

	/**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		$data = $this->getData();
		$result = Mage::getModel('mobipayments/standard')->processPayment($data);
		$this->printResult($result);
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
			}
        }
        $result = Mage::getModel('mobiservices/shoppingcart_cart')->getCartDetails($data);
        $this->printResult($result);
	}

    public function ipnAction(){
        //$_POST['txn_id'] = 'PAY-5T061578FA718311AKQV6MXQ';
        $post = $_GET;
        if(!empty($post)){
            $txn_id = $post['txn_id'];

            $orders = Mage::getModel('sales/order_payment_transaction')->getCollection()
                    ->addAttributeToFilter('txn_id', array('eq' => $txn_id));

            $order_ids = array();
            if(!empty($orders)){
                foreach($orders as $order){
                    $txnData = $order->getData();
                    $order_ids[] = $txnData['order_id'];
                }
            }
            $order_ids = array_unique($order_ids);

            if(!empty($order_ids)){
                foreach($order_ids as $order_id){
                    $order = Mage::getModel('sales/order')->load($order_id);
                    $paymentStatus = strtolower($post['payment_status']);

                    switch($paymentStatus){
                        case 'completed':
                            $payment = $order->getPayment();
                            //print_r($payment->getData());exit;
                            $payment->setTransactionId($txn_id)
                                ->setCurrencyCode($post['mc_currency'])
                                ->setPreparedMessage('Payment done')
                                ->setShouldCloseParentTransaction(true)
                                ->setIsTransactionClosed(0)
                                ->registerCaptureNotification(
                                    $post['mc_gross'],
                                    true
                                );
                            $order->save();
                            /*
                            $payment->setIsTransactionApproved(true)
                                ->setIsTransactionClosed(1);
                            */
                            $order->save();
                            //echo '<pre>';print_r($order->getData());exit;
                            break;

                        case 'reverse':
                            $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Administrator declined the payment')->save();
                            break;

                        default:
                            break;
                    }
                }
            }
        }
        else{
            die('404');
        }
    }
    /*
	public function testAction(){
		$orderId = '100000209 ';. 
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($orderId);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
		$order->save();
		print_r($order->getData());
	}
    

    public function _getPaymentMethodsAction(){
        print_r(Mage::getModel('mobipayments/standard')->getModuleData('api_password'));exit;
    }

    public function testAction(){
        file_put_contents('test.txt', $_POST);exit;
        $configData = Mage::getModel('mobipayments/standard')->getPaypalConfig();
        $paymentDetails = Mage::getModel('mobipayments/standard')->__getPaymentDetails(Mage::getModel('mobipayments/standard')->__getAccessToken($configData), 'PAY-12F401981V3164446KQRIU5Q', $configData);
        echo $paymentDetails->transactions[0]->amount->total;
        print_r($paymentDetails);exit;
        $orderId = '100000008';
        $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $payment = $this->_order->getPayment();
        $payment->setTransactionId('PAY-12F401981V3164446KQRIU5Q')
            ->setCurrencyCode('USD')
            ->setParentTransactionId(null)
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification(
                '255.00',
                true
            );
        $this->_order->save();
        print_r('yash');
    }
    */
}