<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->shop_model->get_categories();
    }

	public function index() {
		$this->data['title'] = 'Main_page';

		
		$this->load->view('header', $this->data);
		$this->load->view('s_page', $this->data);
		$this->load->view('footer', $this->data);
	}
}
