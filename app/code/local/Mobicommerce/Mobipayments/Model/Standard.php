<?php
class Mobicommerce_Mobipayments_Model_Standard extends Mobicommerce_Mobipayments_Model_Abstract {
	protected $_code = 'mobipayments';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	protected $_canUseCheckout = true;
	protected $_order = null;

	const PAYER_ID       = 'payer_id';
    const PAYER_EMAIL    = 'email';
    const PAYER_STATUS   = 'payer_status';
    const ADDRESS_ID     = 'address_id';
    const ADDRESS_STATUS = 'address_status';
    const PROTECTION_EL  = 'protection_eligibility';
    const FRAUD_FILTERS  = 'collected_fraud_filters';
    const CORRELATION_ID = 'correlation_id';
    const AVS_CODE       = 'avs_result';
    const CVV2_MATCH     = 'cvv2_check_result';
    const CENTINEL_VPAS  = 'centinel_vpas_result';
    const CENTINEL_ECI   = 'centinel_eci_result';
    // Next two fields are required for Brazil
    const BUYER_TAX_ID   = 'buyer_tax_id';
    const BUYER_TAX_ID_TYPE = 'buyer_tax_id_type';

    const PAYMENT_STATUS = 'payment_status';
    const PENDING_REASON = 'pending_reason';
    const IS_FRAUD       = 'is_fraud_detected';
    const PAYMENT_STATUS_GLOBAL = 'paypal_payment_status';
    const PENDING_REASON_GLOBAL = 'paypal_pending_reason';
    const IS_FRAUD_GLOBAL       = 'paypal_is_fraud_detected';

	protected $_paymentMap = array(
        self::PAYER_ID       => 'payer_id',
        self::PAYER_EMAIL    => 'payer_email',
        self::PAYER_STATUS   => 'payer_status',
        self::ADDRESS_ID     => 'paypal_address_id',
        self::ADDRESS_STATUS => 'paypal_address_status',
        self::PROTECTION_EL  => 'protection_eligibility',
        self::FRAUD_FILTERS  => 'paypal_fraud_filters',
        self::CORRELATION_ID => 'paypal_correlation_id',
        self::AVS_CODE       => 'paypal_avs_code',
        self::CVV2_MATCH     => 'paypal_cvv2_match',
        self::CENTINEL_VPAS  => self::CENTINEL_VPAS,
        self::CENTINEL_ECI   => self::CENTINEL_ECI,
        self::BUYER_TAX_ID   => self::BUYER_TAX_ID,
        self::BUYER_TAX_ID_TYPE => self::BUYER_TAX_ID_TYPE,
    );

    protected $_systemMap = array(
        self::PAYMENT_STATUS => self::PAYMENT_STATUS_GLOBAL,
        self::PENDING_REASON => self::PENDING_REASON_GLOBAL,
        self::IS_FRAUD       => self::IS_FRAUD_GLOBAL,
    );
	
	public function getOrderPlaceRedirectUrl() {
		//return Mage::getUrl('mygateway/payment/redirect', array('_secure' => true));
		return null;
	}

	public function getSuccess($messaage=''){
		return $this->successStatus($messaage);
	}

	public function processPayment($data){
		//if($this->getRequest()->isPost()) {
		if($data) {
			
			/*
			/* Your gateway's code to make sure the reponse you
			/* just got is from the gatway and not from some weirdo.
			/* This generally has some checksum or other checks,
			/* and is provided by the gateway.
			/* For now, we assume that the gateway's response is valid
			*/

			$session = Mage::getSingleton('checkout/session');
			$quoteId = $session->getMobiPaypalQuoteId(false);
	        $session->setQuoteId($session->getMobiPaypalQuoteId(true));
	        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
			//return $this->successStatus("$quoteId processed successfully");
			$validated = true;
			//$orderId = '100000001'; // Generally sent by gateway
			
			if($validated) {
				$response = $data['response'];
				$paymentId = $response['id'];
				//return $this->errorStatus($paymentId);
				$configData = $this->getPaypalConfig();
				$accessToken = $this->__getAccessToken($configData);
				if(!empty($accessToken)){
					$paymentDetails = $this->__getPaymentDetails($accessToken, $paymentId, $configData);
					
					// Payment was successful, so update the order's state, send order email and move to the success page
					$orderId = $session->getMobiPaypalInvoiceId(true);

					//$this->_order = Mage::getModel('sales/order');
					//$this->_order->loadByIncrementId($orderId);
					$this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

					/* added newly */
					$parentTransactionId = null;
					$skipFraudDetection = false;
			        $this->_importPaymentInformation($paymentDetails);
			        $payment = $this->_order->getPayment();
			        //$payment->setTransactionId($paymentDetails->id)
			        $payment->setTransactionId($paymentDetails->transactions[0]->related_resources[0]->sale->id)
			            ->setCurrencyCode($paymentDetails->transactions[0]->amount->currency)
			            ->setParentTransactionId($parentTransactionId)
			            ->setIsTransactionClosed(0)
			            ->registerCaptureNotification(
			                $paymentDetails->transactions[0]->amount->total,
			                true
			            );
			        $this->_order->save();
			        /* added newly - upto here */

					//$this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
					
					$this->_order->sendNewOrderEmail();
					$this->_order->setEmailSent(true);
					
					$this->_order->save();
				
					Mage::getSingleton('checkout/session')->unsQuoteId();
					$return = $this->successStatus("$orderId processed successfully");
					$return['data']['invoice_number'] = $orderId;
					$return['data']['order_data'] = Mage::getModel('mobiservices/user')->_getOrderHistory();
					//Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
					return $return;
				}
				else{
					return $this->errorStatus("Invalid Access Token");
				}
			}
			else {
				// There is a problem in the response we got
				$this->cancelAction();
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else{
			return $this->errorStatus("Post data not received at php side");
		}
	}

	protected function _importPaymentInformation($response)
    {
        $payment = $this->_order->getPayment();
        $was = $payment->getAdditionalInformation();
        $from = array(
        	'payer_id' => $response->payer->payer_info->payer_id,
			'payer_email'            => $response->payer->payer_info->email,
			'payer_status'           => $response->payer->status,
			'protection_eligibility' => $response->transactions[0]->related_resources[0]->sale->protection_eligibility,
			'payment_mode'           => $response->transactions[0]->related_resources[0]->sale->payment_mode,
			'payment_status'         => $response->transactions[0]->related_resources[0]->sale->state,
			'pending_reason'         => $response->transactions[0]->related_resources[0]->sale->reason_code,
        	);
        /*
        if (isset($from['payment_status'])) {
            $from['payment_status'] = $this->_filterPaymentStatus($this->getRequestData('payment_status'));
        }
        */

        // collect fraud filters
        $this->importToPayment($from, $payment);

        /**
         * Detect pending payment, frauds
         * TODO: implement logic in one place
         * @see Mage_Paypal_Model_Pro::importPaymentInfo()
         */
        /*
        if ($this->_info->isPaymentReviewRequired($payment)) {
            $payment->setIsTransactionPending(true);
            if ($fraudFilters) {
                $payment->setIsFraudDetected(true);
            }
        }
        */
        $fraudFilters = false;
        if ($from['pending_reason'] == 'PAYMENT_REVIEW') {
            $payment->setIsTransactionPending(true);
            if ($fraudFilters) {
                $payment->setIsFraudDetected(true);
            }
        }

        if (strtolower($from['payment_status']) == 'completed') {
            $payment->setIsTransactionApproved(true);
        } elseif (strtolower($from['payment_status']) == 'failed') {
            $payment->setIsTransactionDenied(true);
        }

        return $was != $payment->getAdditionalInformation();
    }

    public function importToPayment($from, Mage_Payment_Model_Info $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = array($from, 'getDataUsingMethod');
        }
        Varien_Object_Mapper::accumulateByMap($from, array($payment, 'setAdditionalInformation'), $fullMap);
    }

	protected function _filterPaymentStatus($paymentStatus)
    {
        switch ($paymentStatus) {
            case 'Created': // break is intentionally omitted
            case 'Completed': return Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED;
            case 'approved': return Mage_Paypal_Model_Info::PAYMENTSTATUS_COMPLETED;
            case 'Denied':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_DENIED;
            case 'Expired':   return Mage_Paypal_Model_Info::PAYMENTSTATUS_EXPIRED;
            case 'Failed':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_FAILED;
            case 'Pending':   return Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING;
            case 'pending':   return Mage_Paypal_Model_Info::PAYMENTSTATUS_PENDING;
            case 'Refunded':  return Mage_Paypal_Model_Info::PAYMENTSTATUS_REFUNDED;
            case 'Reversed':  return Mage_Paypal_Model_Info::PAYMENTSTATUS_REVERSED;
            case 'Canceled_Reversal': return Mage_Paypal_Model_Info::PAYMENTSTATUS_UNREVERSED;
            case 'Processed': return Mage_Paypal_Model_Info::PAYMENTSTATUS_PROCESSED;
            case 'Voided':    return Mage_Paypal_Model_Info::PAYMENTSTATUS_VOIDED;
        }
        return '';
    }

	public function __getAccessToken($configData){
		//https://api.sandbox.paypal.com/v1/oauth2/token
		//https://api.sandbox.paypal.com/v1/payments/payment/PAY-5YK922393D847794YKER7MUI
		if($configData['isSandbox'] == '1')
			$url = "https://api.sandbox.paypal.com/v1/oauth2/token";
		else
			$url = "https://api.paypal.com/v1/oauth2/token";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Accept: application/json',
		    'Accept-Language: en_US'
		    ));
		curl_setopt($ch,CURLOPT_POST,true); 
		//curl_setopt($ch,CURLOPT_POSTFIELDS,array('grant_type' => 'client_credentials')); 
		curl_setopt($ch,CURLOPT_POSTFIELDS,'grant_type=client_credentials');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $configData['apiUsername'].':'.$configData['apiPassword']);

		$res = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		//print_r($res);exit;
		curl_close($ch);
		try{
			$res = json_decode($res);
			return $res->access_token;
		}
		catch(Exception $e){
			return null;
		}
	}

	public function __getPaymentDetails($accessToken = null, $paymentId = null, $configData = null){
		if($configData['isSandbox'] == '1')
			$url = "https://api.sandbox.paypal.com/v1/payments/payment/";
		else
			$url = "https://api.paypal.com/v1/payments/payment/";
		$ch = curl_init($url.$paymentId);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Authorization: Bearer '.$accessToken
		    ));
		//curl_setopt($ch,CURLOPT_POST,true); 
		//curl_setopt($ch,CURLOPT_POSTFIELDS,array('grant_type' => 'client_credentials')); 
		//curl_setopt($ch,CURLOPT_POSTFIELDS,'grant_type=client_credentials');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		//curl_setopt($ch, CURLOPT_USERPWD, 'AXK9pBDQ_6bsGFE6tjiIiIj758F7YxE18dzQhE8k3r9mlDxxmF5Yysz9egNt:EE0j1xBSbSJMwkTH6we0WjtMEDjtz1dwnOl4DsdzP_-tEbcRLhg8K0TOSi_P');

		$res = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		curl_close($ch);
		$res = json_decode($res);
		return $res;
	}

	public function getConfigParam($key){
		return $this->getConfigData($key);
	}

	public function getPaypalConfig(){
		$data = array(
			'code'                     => $this->_code,
			'title'                    => $this->getConfigParam('title'),
			'clientId'                 => $this->getConfigParam('clientid'),
			'title'                    => $this->getConfigParam('title'),
			'apiUsername'              => $this->getConfigParam('api_username'),
			'apiPassword'              => $this->getConfigParam('api_password'),
			'merchantName'             => $this->getConfigParam('merchant_name'),
			'privacyPolicyUrl'         => $this->getConfigParam('privacy_policy_url'),
			'aggrementUrl'             => $this->getConfigParam('aggrement_url'),
			'title'                    => $this->getConfigParam('title'),
			'isSandbox'                => $this->getConfigParam('dropdown'),
			'allow_direct_credit_card' => $this->getConfigParam('allow_direct_credit_card'),
			'newOrderStatus'           => $this->getConfigParam('order_status'),
			);
		return $data;
	}

	public function isAvailable($quote = null){
		$session = Mage::getSingleton('checkout/session');
        $activePaypalMobile = $session->getActivePaypalMobile();

        if($activePaypalMobile == '1')
        	return parent::isAvailable($quote);
        else
        	return false;
	}
}
?>