<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Buy extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('licence_model');
	}

	public function index()
	{
		$appcode = $this->input->post("appcode");
		$appkey = $this->input->post("appkey");
		$mobicommerce_orderid = $this->input->post("mobicommerce_orderid");

		if(empty($appcode) || empty($appkey) || empty($mobicommerce_orderid)){
			printResult($this->errorStatus("Invalid request"));
			exit;
		}
		else{
			$detail = $this->build_model->getBuildDetail($appkey, $appcode);
			if(empty($detail)){
				printResult($this->errorStatus("Invalid appcode or appkey"));
				exit;
			}
			else{
				$params = array(
					"mode"                   => "live",
					"mobicommerce_orderid"   => $mobicommerce_orderid,
					"mobicommerce_orderdate" => date("Y-m-d H:i:s"),
					"delivery_status"        => "pending"
					);

				$this->build_model->updateAppParams($detail['app_id'], $params);

				$dataToSend = array(
					"appcode"              => $appcode,
					"appkey"               => $appkey,
					"licence_key"          => $detail["app_licence_key"],
					"mode"                 => "live",
					"mobicommerce_orderid" => $mobicommerce_orderid
					);

				$fields_string = '';
				foreach($dataToSend as $key => $value){
					$fields_string .= $key.'='.$value.'&'; 
				}
				rtrim($fields_string, '&');

				$url = $detail['app_website_root_url'] . "mobiservices/pushservice/buyapp";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_POST, count($dataToSend));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				$output = curl_exec($ch);
				//print_r($output);
				curl_close($ch);

				$message = "Hello Yash, New order has been placed on MobiCommerce.net. Please start making deliverables.\r\n";
				$message .= "<a href='".BASE_ADMIN_URL."apps/edit/".$detail['app_id']."'>Click here</a> to view app details.";
				$mailParams = array(
					"to"      => "yash.shah@rightwaysolution.com",
					"cc"      => "",//rakesh@rightwaysolution.com
					"subject" => "New order placed on MobiCommerce",
					"message" => $message
					);
				$this->sendMail($mailParams);
				printResult($this->successStatus("App purchased successfully"));
				exit;
			}
		}
	}
}

/* End of file buy.php */
/* Location: ./application/controllers/buy.php */