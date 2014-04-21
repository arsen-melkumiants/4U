<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main_controller extends CI_Controller {
	
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
		$this->data['title'] = SITE_NAME;

		$this->data['name'] = 'Strongly recommended';
		$this->data['products'] = $this->shop_model->get_recomended_products(6);
		$this->data['center_block'] = $this->load->view('product_block', $this->data, true);

		$this->data['name'] = 'Best sales';
		$this->data['products'] = $this->shop_model->get_best_sales_products(6);
		$this->data['center_block'] .= $this->load->view('product_block', $this->data, true);

		$this->data['name'] = 'New products';
		$this->data['products'] = $this->shop_model->get_new_products(6);
		$this->data['center_block'] .= $this->load->view('product_block', $this->data, true);
		
		load_views();
	}

	function menu_content($name = false) {
		if(empty($name)) {
			show_404();
		}

		$menu_info = $this->db->where('alias', $name)->get('menu_items')->row_array();
		if (empty($menu_info)) {
			show_404();
		}
		$this->data['title'] = $this->data['header'] = $menu_info['name'];

		if ($menu_info['type'] == 'content') {
			$content_info = $this->db->where('id', $menu_info['item_id'])->get('content')->row_array();
			if (empty($content_info)) {
				show_404();
			}
			$this->data['center_block'] = '<h1>'.$content_info['name'].'</h1>';
			$this->data['center_block'] .= '<div>'.$content_info['content'].'</div>';
		} else {
			show_404();
		}
		
		load_views();
	}
}
