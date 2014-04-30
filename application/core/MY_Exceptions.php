<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
class MY_Exceptions extends CI_Exceptions {

	function __construct(){
		parent::__construct();
	}

	function show_404(){ // error page logic
		header("HTTP/1.1 404 Not Found");
		// First, assign the CodeIgniter object to a variable
		$CI =& get_instance();

		parent::__construct();
		$CI->load->model(array(
			'menu_model',
			'shop_model',
		));
		$CI->load->library(array(
			'session',
			'ion_auth',
		));
		$CI->data['main_menu']  = $CI->menu_model->get_menu('upper');
		$CI->data['left_block'] = $CI->shop_model->get_categories();
		// do what you want here, even db stuff or just 
		// load your template with a custom 404

		$CI->data['title'] = $CI->data['header'] = lang('page_doesnt_exist');

		$CI->output->set_status_header('404');
		$CI->data['center_block'] = '<h1 class="text_404">404</h1>';

		$out = $CI->load->view('header', $CI->data, true);
		$out .= $CI->load->view('s_page', $CI->data, true);
		$out .= $CI->load->view('footer', $CI->data, true);
		echo $out;
	}
}
