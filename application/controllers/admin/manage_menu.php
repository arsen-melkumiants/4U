<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_menu extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Меню',
			'header_descr' => false,
		),
		'menu'             => array(
			'header'       => '"%ru_name" меню',
			'header_descr' => 'Список пунктов меню',
		),
		'add'              => array(
			'header'       => 'Добавление меню',
			'header_descr' => 'Добавление пункта меню',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование пункта меню',
		),
		'delete'           => array(
			'header'       => 'Удаление пункта меню "%name"',
			'header_descr' => false,
		),
	);

	public $TYPES = array(
		'content'         => 'Контент',
		'shop_categories' => 'Категории товаров',
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_menu_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		custom_404();
		load_admin_views();
	}

	public function menu($name = false) {
		if ($this->IS_AJAX && !empty($_POST['tree'])) {
			$this->admin_menu_model->update_menu_tree($_POST['tree'], $name);
			exit;
		}
		$menu_items = $this->admin_menu_model->get_menu_items($name);
		if (empty($menu_items)) {
			custom_404();
		}
		set_header_info($menu_items[0]);

		$this->data['center_block']  = $this->admin_menu_model->get_menu_tree($menu_items, 0, $this->MAIN_URL, $name);

		load_admin_views();
	}

	public function add($name = false) {
		$menu_info = $this->admin_menu_model->get_menu_info($name);
		if (empty($menu_info)) {
			custom_404();
		}

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			add_method('menu_items', array('add_date'));
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$menu_info = $this->admin_menu_model->get_one_menu_item($id);

		if (empty($menu_info)) {
			custom_404();
		}
		set_header_info($menu_info);

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $menu_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form($menu_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			edit_method('menu_items', $id, array('add_date'));
		}
	}

	public function delete($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$menu_info = $this->admin_menu_model->get_one_menu_item($id);

		if (empty($menu_info)) {
			custom_404();
		}
		set_header_info($menu_info);

		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->admin_menu_model->delete_menu_item($id);
				$this->session->set_flashdata('danger', 'Данные успешно удалены');
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = $this->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
					->create(array('action' => current_url(), 'btn_offset' => 4));
				echo $this->load->view(ADM_FOLDER.'ajax', '', true);
			}
		} else {
			$this->admin_menu_model->delete_menu_item($id);
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			$menu_name = $this->admin_menu_model->get_menu_name($menu_info['menu_id']);
			redirect(($menu_name ? $this->MAIN_URL.$menu_name : ADM_URL), 'refresh');
		}
	}
	
	private function edit_form($menu_info = false) {
		$this->data['select_contents'] = $this->admin_menu_model->get_content_list();
		$menu_type = !empty($_POST['type']) ? $_POST['type'] : (!empty($menu_info['type']) ? $menu_info['type'] : false);
		$this->load->library('form');
		$html = $this->form
			->text('name', array(
				'value'         => $menu_info['name'] ?: false,
				'valid_rules'   => 'required|trim|xss_clean',
				'label'         => 'Имя',
			))
			->text('alias', array(
				'value'         => $menu_info['alias'] ?: false,
				'valid_rules'   => 'required|trim|xss_clean|'.(!$menu_info['id'] ? 'is_unique[menu_items.alias]' : 'is_unique_without[menu_items.alias.'.$menu_info['id'].']'),
				'label'         => 'Ссылка',
			))
			->select('type', array(
				'value'         => $menu_info['type'] ?: false,
				'valid_rules'   => 'trim|xss_clean',
				'label'         => 'Тип меню',
				'options'       => $this->TYPES,
				'search'        => true,
				'class'         => 'type_menu_list',
			))
			->select('item_id', array(
				'value'         => $menu_info['item_id'] ?: false,
				'valid_rules'   => 'trim|xss_clean',
				'label'         => 'Список',
				'options'       => isset($this->data['select_contents'][$menu_type]) ? $this->data['select_contents'][$menu_type] : $this->data['select_contents'][$menu_type]['content'],
				'search'        => true,
				'class'         => 'items_list',
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));

		$html .= $this->load->view(ADM_FOLDER.'menu_js', $this->data, true);
		return $html;
	}

	function get_select_by_type() {
		return 'done';
	}
}
