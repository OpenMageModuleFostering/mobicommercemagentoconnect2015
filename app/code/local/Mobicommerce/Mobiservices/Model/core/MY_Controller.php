<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

	public $modulename;
	public $userID = null;
	public function __construct($params = array())
	{
		parent::__construct();
		$this->load->model('build_model');

		$this->userID = $this->session->userdata('user_id');
		$this->modulename = $this->uri->segment(1);

		if($this->modulename == 'webadmin'){
			if(isset($params['requireLogin']) && !$params['requireLogin']){

			}
			else{
				if($this->session->userdata('loggedin') != '999'){
					redirect(BASE_ADMIN_URL . 'auth/login');
				}
			}
		}
	}

	public function successStatus()
	{
		return array(
			'status'  => 'success',
			'message' => '',
			'data'    => array()
			);
	}

	public function errorStatus($message = '')
	{
		return array(
			'status'  => 'error',
			'message' => $message,
			'data'    => array()
			);	
	}

	public function pushAndroidUrlToClientServer($params, $url = null)
	{
		if(empty($url))
			return false;

		$shorturl = shorterUrl($url);
		if(empty($shorturl))
			return false;

		$detail = $this->build_model->getBuildDetail($params['app_preview_code'], $params['app_code']);
		if(empty($detail))
			return false;

		$this->build_model->updateAndroidUrl($detail['app_id'], $shorturl);

		/*
		$dataToSend = array(
			'appcode'        => $detail['app_code'],
			'appkey'         => $detail['app_preview_code'],
			'android_status' => 'ready',
			'android_url'    => $shorturl
		    );

		$fields_string = '';
		foreach($dataToSend as $key => $value){
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');

		$url = $detail['app_website_root_url'] . "mobiservices/pushservice/androidbuild";
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
		*/
		return $shorturl;
	}

	public function pushWebappUrlToClientServer($params, $url = null)
	{
		if(empty($url))
			return false;

		$shorturl = shorterUrl($url);
		if(empty($shorturl))
			return false;

		$detail = $this->build_model->getBuildDetail($params['app_preview_code'], $params['app_code']);
		if(empty($detail))
			return false;

		$this->build_model->updateWebappUrl($detail['app_id'], $shorturl);
		
		return $shorturl;
	}

	public function sendMail($params = null)
	{
		if(isset($params['from']) && !empty($params['from'])){
			if(is_array($params['from']))
				$from = $params['from'];
			else
				$from = array($params['from'], "Mobicommerce Build");
		}
		else{
			$from = array('info@mobi-commerce.net', 'Mobicommerce Build');
		}

		$to = $params['to'];
		$to = explode(',', $to);
		$to = array_map('trim', $to);
		$to = array_filter($to);
		$to = array_unique($to);

		$this->email->from($from[0], $from[1]);
		$this->email->to($to);

		if(isset($params['cc']) && !empty($params['cc'])){
			$cc = $params['cc'];
			$cc = explode(',', $cc);
			$cc = array_map('trim', $cc);
			$cc = array_filter($cc);
			$cc = array_unique($cc);
			$this->email->cc($cc);
		}

		if(isset($params['bcc']) && !empty($params['bcc'])){
			$bcc = $params['bcc'];
			$bcc = explode(',', $bcc);
			$bcc = array_map('trim', $bcc);
			$bcc = array_filter($bcc);
			$bcc = array_unique($bcc);
			$this->email->bcc($bcc);
		}

		$this->email->subject($params['subject']);
		$this->email->message($params['message']);
		$this->email->send();
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/controllers/MY_Controller.php */