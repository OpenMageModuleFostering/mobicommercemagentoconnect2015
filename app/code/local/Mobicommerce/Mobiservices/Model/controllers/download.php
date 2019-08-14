<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Download extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('build_model');
	}

	public function android($appkey = null, $appcode = null)
	{
		$detail = $this->build_model->getBuildDetail($appkey, $appcode);
		if(empty($detail))
			show_404();
		
		if ($this->agent->is_mobile()){
			if($detail['android_status'] == 'ready'){
				$filename = DOCUMENTROOT.'/apps/'.$appcode.'/v100/apks/android.apk';
				header('Content-type: application/vnd.android.package-archive');
				header('Content-Disposition: attachment; filename="'.$detail['app_name'].'.apk"');
				header('Content-Length: '.filesize($filename));
				readfile($filename);
			}
			else{
				show_404();	
			}
		}
		else{
			echo "The mobile app can't be installed on this device, please open the URL in your mobile device.";
			exit;
		}
	}
}

/* End of file download.php */
/* Location: ./application/controllers/download.php */