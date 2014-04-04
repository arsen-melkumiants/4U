<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shop_controller extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->shop_model->get_categories();
		
		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
    }

	public function index() {
		show_404();
		$this->data['title'] = 'Main_page';

		load_views();
	}

	public function category($name = false) {
		if (empty($name)) {
			show_404();
		}

		$this->data['category_info'] = $this->shop_model->get_category_info($name);

		if (empty($this->data['category_info'])) {
			show_404();
		}

		$this->data['title']        = 'Категория "'.$this->data['category_info']['name'].'"';
		$this->data['products']     = $this->shop_model->get_products_by_category($this->data['category_info']['id']);
		$this->data['center_block'] = $this->load->view('category', $this->data, true);

		load_views();
	}
	
	public function product($id = false, $name = false) {
		if (empty($id)) {
			show_404();
		}

		$this->data['product_info'] = $this->shop_model->get_product_info($id);

		if (empty($this->data['product_info'])) {
			show_404();
		}

		$alias = url_title(translitIt($this->data['product_info']['name']), 'underscore', TRUE); 
		if ($alias != $name) {
			redirect('product/'.$id.'/'.$alias, 'refresh');
		}

		$this->data['categories'] = $this->shop_model->parent_categories($this->data['product_info']['cat_id']);

		$this->data['title']        = $this->data['product_info']['name'];
		$this->data['center_block'] = $this->load->view('product', $this->data, true);

		load_views();
	}
}
