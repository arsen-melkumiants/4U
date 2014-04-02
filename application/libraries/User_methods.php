<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_methods {

	public $CI;

	public function __construct() {
		$this->CI =& get_instance();
		$this->CI->IS_AJAX = $this->CI->input->is_ajax_request();
	}

	public function load_views() {
		if ($this->CI->IS_AJAX) {
			$output = $this->CI->load->view('ajax', $this->CI->data, true);
			echo $output;
		} else {
			$this->CI->load->view('header', $this->CI->data);
			$this->CI->load->view('s_page', $this->CI->data);
			$this->CI->load->view('footer', $this->CI->data);
		}
	}

	public function user_constructor() {
		set_alert($this->CI->session->flashdata('success'), false, 'success');
		set_alert($this->CI->session->flashdata('danger'), false, 'danger');
	}
}
