<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_category extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Категории',
			'header_descr' => 'Список категорий продуктов',
		),
		'add'              => array(
			'header'       => 'Добавление категории',
			'header_descr' => 'Добавление категории продуктов',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование категории продуктов',
		),
		'delete'           => array(
			'header'       => 'Удаление категории "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->load->library('ion_auth');
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_control_menu_model');
		$this->data['top_menu'] = $this->admin_control_menu_model->get_control_menu('top');
		$this->load->model(ADM_FOLDER.'admin_category_model');
		
		$this->data['title'] = '4U :: ';

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		$this->IS_AJAX = $this->input->is_ajax_request();
	
		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
		set_header_info();
	}

	public function index() {
		if ($this->IS_AJAX && !empty($_POST['tree'])) {
			$this->admin_category_model->update_category_tree($_POST['tree']);
			exit;
		}
		$category_items = $this->admin_category_model->get_category_items();
		if (empty($category_items)) {
			custom_404();
		}

		$this->data['center_block']  = $this->admin_category_model->get_category_tree($category_items, 0, $this->MAIN_URL);

		load_admin_views();
	}

	public function add() {
		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$this->admin_category_model->add_category($data);
			$this->session->set_flashdata('success', 'Данные успешно добавлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect($this->MAIN_URL.$name, 'refresh');
			}
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$category_info = $this->admin_category_model->get_category_info($id);

		if (empty($category_info)) {
			custom_404();
		}
		set_header_info($category_info);

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $category_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_form($category_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$data = $this->input->post();
			unset($data['submit']);
			$category_info = $this->admin_category_model->update_category($data, $id);
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
			if ($this->IS_AJAX) {
				echo 'refresh';
			} else {
				redirect(current_url(), 'refresh');
			}
		}
	}

	public function delete($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$category_info = $this->admin_category_model->get_category_info($id);

		if (empty($category_info)) {
			custom_404();
		}
		set_header_info($category_info);

		if ($this->IS_AJAX) {
			if (isset($_POST['delete'])) {
				$this->admin_category_model->delete_category($id);
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
			$this->admin_category_model->delete_category($id);
			$this->session->set_flashdata('danger', 'Данные успешно удалены');
			redirect($this->MAIN_URL, 'refresh');
		}
	}
	
	private function edit_form($category_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $category_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'value'       => $category_info['alias'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|'.(!$category_info['id'] ? 'is_unique[shop_categories.alias]' : 'is_unique_without[shop_categories.alias.'.$category_info['id'].']'),
				'label'       => 'Ссылка',
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}
}
