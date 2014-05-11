<?php defined('BASEPATH') OR exit('No direct script access allowed');

class manage_currency extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;
	
	public $DB_TABLE = 'shop_currencies';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Валюты',
			'header_descr' => 'Список валют',
		),
		'add'              => array(
			'header'       => 'Добавление',
			'header_descr' => 'Добавление валюты',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование валюты',
		),
		'delete'           => array(
			'header'       => 'Удаление валюты "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('code', array(
				'title' => 'Код',
			))
			->text('symbol', array(
				'title' => 'Символ',
			))
			->edit(array('link'   => $this->MAIN_URL.'edit/%d', 'modal' => 1))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			->active(array('link' => $this->MAIN_URL.'active/%d'))
			->btn(array(
				'link'   => $this->MAIN_URL.'add',
				'name'   => 'Добавить',
				'header' => true,
				'modal'  => true,
			))
			->create(function($CI) {
				return $CI->db->get($CI->DB_TABLE);
			});

		load_admin_views();
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE, array('except_fields' => array('add_date')));
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$currency_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($currency_info )) {
			custom_404();
		}
		set_header_info($currency_info);

		$this->data['center_block'] = $this->edit_form($currency_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($currency_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $currency_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('code', array(
				'value'       => $currency_info['code'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|exact_length[3]',
				'label'       => 'Код',
			))
			->text('symbol', array(
				'value'       => $currency_info['symbol'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|max_length[3]',
				'label'       => 'Символ',
			))
			->btn(array('value' => empty($currency_info) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function delete($id = false) {
		if (empty($id)) {
			custom_404();
		}
		
		$currency_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($currency_info)) {
			custom_404();
		}
		set_header_info($currency_info);

		admin_method('delete', $this->DB_TABLE, $currency_info);
	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}
		
		$currency_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($currency_info)) {
			custom_404();
		}
		set_header_info($currency_info);

		admin_method('active', $this->DB_TABLE, $currency_info);
	}
}
