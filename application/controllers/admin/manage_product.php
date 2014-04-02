<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_product extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;
	
	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Продукты',
			'header_descr' => 'Список продуктов',
		),
		'add'              => array(
			'header'       => 'Добавления продукта',
			'header_descr' => 'Информация о продукте',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование информации о продукте',
		),
		'delete'           => array(
			'header'       => 'Удаление продукта "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		$product_categories = $this->admin_product_model->get_product_categories();

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('cat_id', array(
				'title' => 'Категория',
				'extra' => $product_categories ,
				'func'  => function($row, $params) {
					if (isset($params['extra'][$row['cat_id']]['name'])) {
						return '<span class="label label-info">'.$params['extra'][$row['cat_id']]['name'].'</span>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			->btn(array(
				'link' => $this->MAIN_URL.'add',
				'name' => 'Добавить',
				'header' => true,
			))
			->create(function($CI) {
				return $CI->admin_product_model->get_all_products();
			});

		load_admin_views();
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE);
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info )) {
			custom_404();
		}
		set_header_info($product_info);

		$this->data['center_block'] = $this->edit_form($product_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($product_info = false) {
		$product_categories = $this->admin_product_model->get_product_categories();
		array_unshift($product_categories, array('id' => 0, 'name' => 'Без категории'));
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|numeric',
				'label'       => 'Цена',
			))
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Категория',
				'options'     => $product_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Текст',
			))
			->text('keywords', array(
				'value'       => $product_info['keywords'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Ключевые слова',
			))
			->text('title', array(
				'value'       => $product_info['title'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Заголовок страницы',
			))
			->text('description', array(
				'value'       => $product_info['description'] ?: false,
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Описание',
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function delete($id = false, $type = false) {
		if (empty($id)) {
			custom_404();
		}
		
		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);

		admin_method('delete', $this->DB_TABLE, $product_info);
	}
	
	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}
		
		$product_info = $this->admin_product_model->get_product_info($id);

		if (empty($product_info)) {
			custom_404();
		}
		set_header_info($product_info);

		admin_method('active', $this->DB_TABLE, $product_info);
	}

}
