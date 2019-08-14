<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Install extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('licence_model');
	}

	public function index()
	{
		$requiredParams = array(
			"website_url" => "Website URL",
			"country"     => "Country"
			);

		foreach($requiredParams as $_param => $_name){
			$_value = $this->input->get_post($_param, false);
			if($_value === false){
				printResult($this->errorStatus("$_name field is required"));
				exit;
			}
		}

		$params = array(
			"ail_website_url" => $this->input->get_post("website_url"),
			"ail_admin_email" => $this->input->get_post("admin_email"),
			"ail_sales_email" => $this->input->get_post("sales_email"),
			"ail_country"     => $this->input->get_post("country"),
			);

		$licence_key = $this->licence_model->addLicence($params);
		$return = $this->successStatus();
		$return['data']['licence_key'] = $licence_key;
		printResult($return);
		exit;
	}
}

/* End of file install.php */
/* Location: ./application/controllers/install.php */