<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Build extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('licence_model');
	}

	public function add()
	{
		$requiredParams = array(
			"appname"      => "Application Name",
			"approoturl"   => "Website URL",
			"appsplash"    => "Splash Screen",
			"applogo"      => "Logo",
			"appicon"      => "Icon",
			"apptheme"     => "Theme",
			"email"        => "Email",
			"primaryemail" => "Primary Email",
			"phone"        => "Phone",
			);

		foreach($requiredParams as $_param => $_name){
			$_value = $this->input->get_post($_param, false);
			if($_value === false){
				printResult($this->errorStatus("$_name field is required"));
				exit;
			}
		}

		$udid = $this->input->get_post("udid");
		if(empty($udid))
			$udid = NULL;

		if(!validateUDID($udid)){
			printResult($this->errorStatus("Please submit valid UDID"));
			exit;
		}
		$licence_key = $this->input->get_post("applicencekey");
		if(empty($licence_key)){
			$licence_key = $this->licence_model->addLicence(array(
				"ail_website_url" => $this->input->get_post("approoturl"),
				"ail_admin_email" => $this->input->get_post("email"),
				"ail_sales_email" => $this->input->get_post("primaryemail"),
				"ail_country"     => $this->input->get_post("country"),
				));
		}

		$params = array(
			"app_code"              => uniqid(),
			"app_preview_code"      => substr(md5(microtime()), rand(0, 26), 6),
			"app_licence_key"		=> $licence_key,
			"app_params"            => NULL,
			"app_options"           => NULL,
			"app_theme_folder_name" => $this->input->get_post("apptheme"),
			"app_name"              => $this->input->get_post("appname"),
			"app_website_root_url"  => $this->input->get_post("approoturl"),
			"app_splash_screen"     => $this->input->get_post("appsplash"),
			"app_logo"              => $this->input->get_post("applogo"),
			"app_icon"              => $this->input->get_post("appicon"),
			"customer_email"        => $this->input->get_post("email"),
			"customer_email2"       => $this->input->get_post("primaryemail"),
			"customer_phone"        => $this->input->get_post("phone"),
			"udid"                  => $udid,
			"mode"                  => "demo",
			"bundle_id"             => "com.mobicommerce.clientdemo",
			"android_status"        => "pending",
			"ios_status"            => "pending",
			"delivery_status"       => "pending",
			"android_url"           => NULL,
			"ios_url"               => NULL,
			"webapp_url"			=> NULL,
			"app_created_on"        => date('Y-m-d H:i:s')
			);

		$appId = $this->build_model->addBuild($params);
		if(!$appId){
			printResult($this->errorStatus("Your app is not created"));
			exit;
		}
		else{
			$webapp_url = $this->pushWebappUrlToClientServer($params, BUILD_ROOT_URL . 'webapps/' . $params['app_preview_code'] . '/www/index.html');

			$response = generateApplication($params);
			$this->db->reconnect();

			$android_url = NULL;
			if($response["data"]["android_response"]["status"]){
				$android_url = $this->pushAndroidUrlToClientServer($params, base_url() . 'download/android/'.$params["app_preview_code"].'/' . $params["app_code"]);
			}

			$return = $this->successStatus();
			$return['data']['appcode']     = $params["app_code"];
			$return['data']['appkey']      = $params["app_preview_code"];
			$return['data']['licence_key'] = $licence_key;
			$return['data']['android_url'] = $android_url;
			$return['data']['webapp_url']  = $webapp_url;

			$message = $this->load->view("email_templates/build_success", "", true);
			$message = str_replace(
				array(
					'{{VAR_ANDROIDAPP_URL}}',
					'{{VAR_WEBAPP_URL}}'
					),
				array(
					$android_url,
					$webapp_url
					),
				$message
				);

			if(empty($udid)){
				$message = str_replace(getStringBetween($message, '{{VAR_IOS_BLOCK}}', '{{/VAR_IOS_BLOCK}}'), '', $message);
			}
			else{
				$message = str_replace(array(
					'{{VAR_IOS_BLOCK}}', 
					'{{/VAR_IOS_BLOCK}}'
					),
				array(
					'',
					''
					),
				$message);
			}
			/*
			$message = "";
			$message .= "Hi Admin,\r\n";
			$message .= "There is new build created with following parameters.\r\n";
			$message .= "Webapp URL: ".$webapp_url. "\r\n";
			$message .= "Email: ".$params["customer_email"]. "\r\n";
			$message .= "Appname: ".$params["app_name"]. "\r\n";
			$message .= "Website: ".$params["app_website_root_url"]. "\r\n";
			$message .= "Phone: ".$params["customer_phone"]. "\r\n";
			$message .= "UDID: ".$udid. "\r\n";
			$message .= "Appcode: ".$params["app_code"]. "\r\n";
			$message .= "Appkey: ".$params["app_preview_code"]. "\r\n";
			$message .= "Theme: ".$params["app_theme_folder_name"]. "\r\n";
			*/

			$mailParams = array(
				"to"      => $params["customer_email2"],
				"cc"      => "yash.shah@rightwaysolution.com",
				"subject" => "Your MobiCommerce account has been created",
				"message" => $message
				);
			$this->sendMail($mailParams);

			printResult($return);
			exit;
		}
	}

	public function requestIos()
	{
		$requiredParams = array(
			"appcode" => "Appcode",
			"appkey"  => "Appkey",
			"udid"    => "UDID",
			);

		foreach($requiredParams as $_param => $_name){
			$_value = $this->input->get_post($_param, false);
			if($_value === false){
				printResult($this->errorStatus("$_name field is required"));
				exit;
			}
		}

		$appcode = $this->input->get_post('appcode');
		$appkey = $this->input->get_post('appkey');
		$udid = trim($this->input->get_post('udid'));
		$udidArray = explode(',', $udid);
		foreach($udidArray as $_udid){
			if(strlen($_udid) != 40){
				printResult($this->errorStatus("Please submit valid UDIDs"));
				exit;
			}
		}
		$udid = array_map('trim', $udidArray);
		$this->build_model->addUDID($appcode, $appkey, $udid);
	}

	public function submitudid($appkey = null, $appcode = null)
	{

		if(empty($appcode) || empty($appkey))
			show_404();

		$detail = $this->build_model->getBuildDetail($appkey, $appcode);
		if(empty($detail))
			show_404();

		$udid = trim($this->input->get_post('udid'));
		if(empty($udid)){
			printResult($this->errorStatus("Please pass atleast 1 UDID"));
		}
		else{
			$udidArray = explode(',', $udid);
			foreach($udidArray as $_udid){
				if(strlen($_udid) != 40){
					printResult($this->errorStatus("Please submit valid UDIDs"));
					exit;
				}
			}
		}

		$udid = implode(",", $udidArray);
		$this->build_model->addUDID($appkey, $appcode, $udid);
		printResult($this->successStatus("We will publish app within 48 working hours. Thank you."));
	}
}

/* End of file build.php */
/* Location: ./application/controllers/build.php */