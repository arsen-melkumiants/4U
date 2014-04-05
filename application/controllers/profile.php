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
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = 'My profile';

		$allowed_fields = array_flip(array('username','email','active','company','address','city','state','country','zip','phone'));
		foreach ($this->data['user_info'] as $key => $field) {
			if(!isset($allowed_fields[$key])) {
				unset($this->data['user_info'][$key]);
			}
		}

		$this->data['center_block'] = $this->load->view('profile', $this->data, true);

		load_views();
	}

	function cash() {
		$this->data['title'] = $this->data['header'] = 'My cash';

		load_views();
	}

	function sales($type = 'active') {
		$this->data['title'] = $this->data['name'] = 'My products';
		$this->data['type_list'] = array(
			'active'      => array(1),
			'moderate'    => array(0,2),
			'sold'        => '',
		);
		$type = isset($this->data['type_list'][$type]) ? $type : 'active';
		$this->data['type'] = $type;

		$product_categories = $this->shop_model->get_product_categories();

		$this->load->library('table');
		$this->data['table'] = $this->table
			->text('cat_id', array(
				'title' => 'Name',
				'extra' => $product_categories ,
				'func'  => function($row, $params) {
					return '<div class="image"></div>';
				}
		))
			->text('price', array(
				'title' => 'Price',
				'extra' => $product_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->edit(array('link' => 'profile/edit_product/%d'))
			->delete(array('link' => 'profile/delete_product/%d', 'modal' => 1))
			->create(function($CI) {
				return $CI->db
					->select('p.*, c.symbol, c.code')
					->from('shop_products as p')
					->join('shop_currencies as c', 'p.currency = c.id')
					->where(array(
						'p.author_id' => $CI->data['user_info']['id'],
						'p.status' => 0,
					))
					->get();
			}, array('no_header' => 1, 'class' => 'table'));
		
		$this->data['center_block'] = $this->load->view('profile_products', $this->data, true);

		load_views();
	}
	

	function add_product() {
		$this->data['title'] = $this->data['header'] = 'Add product';
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_views();
		} else {
			$info = !empty($data['add_data']) ? array_merge($data['add_data'], $this->CI->input->post()) : $this->CI->input->post();
			unset($data['submit']);
			$data['add_date'] = time();
			unset($data['add_data'], $data['except_fields']);
			$this->db->insert('shop_products', $info);
			$this->session->set_flashdata('success', 'Продукт успешно добавлен');
			redirect('profile/product_gallery', 'refresh');
		}

	}

	private function edit_form($product_info = false) {
		$product_categories = $this->shop_model->get_product_categories();
		array_unshift($product_categories, array('id' => '', 'name' => 'Без категории'));
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Name',
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|numeric',
				'symbol'      => '$',
				'icon_post'   => true,
				'label'       => 'Price',
			))
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Category',
				'options'     => $product_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Content',
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url(), 'error_inline' => 'true'));
	}
}
