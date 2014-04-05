<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
		));
		$this->load->helper('url');

		// Load MongoDB library instead of native db driver if required
		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('personal/login', 'refresh');
		}

		$this->lang->load('auth');
		$this->load->helper('language');

		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		//$this->data['left_block'] = $this->shop_model->get_categories();
		$this->data['left_block'] = $this->load->view('profile_menu', $this->data, true);

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = 'My profile';


		$this->data['profile_info'] = $this->ion_auth->user()->row_array();

		$allowed_fields = array_flip(array('username','email','active','company','address','city','state','country','zip','phone'));
		foreach ($this->data['profile_info'] as $key => $field) {
			if(!isset($allowed_fields[$key])) {
				unset($this->data['profile_info'][$key]);
			}
		}

		$this->data['center_block'] = $this->load->view('profile', $this->data, true);

		load_views();
	}

	function cash() {
		$this->data['title'] = $this->data['header'] = 'My cash';

		load_views();
	}

	function add_product() {
	}
}
